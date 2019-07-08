<?php

namespace App\Sync;

use Plasticode\IO\File;

class Synchronizer
{
    private $connectorFactory;
    private $packageLoader;
    
    public function __construct($connectorFactory, $packageLoader)
    {
        $this->connectorFactory = $connectorFactory;
        $this->packageLoader = $packageLoader;
    }

    /**
     * Builds sync data.
     * 
     * @param Sync $sync
     */
    public function buildSync($sync)
    {
        return $this->do($sync, function($connector) use ($sync) {
            return $this->buildSyncData($sync, $connector);
        });
    }
    
    public function syncFile($sync, $file)
    {
        return $this->do($sync, function($connector) use ($sync, $file) {
            $loader = new FtpLoader;
            
            $content = $loader->getFile($connector->primaryConn, $sync['primary_path'], $file);
            $fileInfo = $loader->putFile($connector->replicaConn, $sync['replica_path'], $file, $content);
            
            return $fileInfo;
        });
    }

    /**
     * Generic sync function wrapper, connect->action->disconnect->return result
     */
    private function do($sync, callable $action)
    {
        $connector = $this->connectorFactory->make($sync);
        $connector->connect($sync);

        $result = $action($connector);

        $connector->disconnect();

        return $result;
    }

    private function buildSyncData($sync, $connector)
    {
        $syncPackage = [
            'primary' => [
                'connection' => $connector->primaryConn,
                'path' => $sync['primary_path'],
            ],
            'replicas' => [
                [
                    'connection' => $connector->replicaConn,
                    'path' => $sync['replica_path'],
                ],
            ],
            'ignore' => $sync['ignore'],
            'ignore_size' => $sync['ignore_size'],
        ];

        return [
            'files' => $this->packageLoader->load($syncPackage),
        ];
    }
}

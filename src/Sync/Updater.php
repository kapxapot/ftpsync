<?php

namespace App\Sync;

class Updater
{
    private $connector;

    public function __construct($connector)
    {
        $this->connector = $connector;
    }
    
    public function syncFile($sync, $path, $name)
    {
        $this->connector->connect($sync);

        // sync...
        $primaryConn = $this->connector->primaryConn;
        $replicaConn = $this->connector->replicaConn;
        
        // load file from primary
        // upload file to replica
        // get new file info: size, date modified
        // return new file data

        $this->connector->disconnect();
        
        return $result;
    }
}

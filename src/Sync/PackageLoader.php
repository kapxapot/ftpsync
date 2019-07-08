<?php

namespace App\Sync;

use Plasticode\Util\Date;
use Plasticode\Util\Strings;

class PackageLoader
{
    private $loader;
    
    public function __construct($loader)
    {
        $this->loader = $loader;
    }
    
    public function load($package)
    {
        $items = $this->getItems($package['primary'], $package['replicas'], $package['ignore']);
        
        return $this->buildFilesData($items, $package['ignore_size']);
    }
    
    private function getItems($primary, $replicas, $ignoredItems)
    {
        $result = [
            'primary' => $this->loader->getDirectoryItems($primary['connection'], $primary['path'], $ignoredItems),
        ];

        foreach ($replicas as $replica) {
            $result['replicas'][] = $this->loader->getDirectoryItems($replica['connection'], $replica['path'], $ignoredItems);
        }
    
        return $result;
    }

    private function buildFilesData($items, $ignoreSizeItems)
    {
        $primaryFiles = $this->filterFiles($items['primary']) ?? [];
        
        foreach ($primaryFiles as $fullName => $item) {
            $size = $item['size'];
            $date = $item['modified_at'];
            
            $item['date_iso'] = Date::formatIso($date);
            $item['ignored'] = Strings::startsWithAny($fullName, $ignoreSizeItems);
            
            $inSync = true;
            
            foreach ($items['replicas'] as $replicaItems) {
                $replicaItem = $replicaItems[$fullName] ?? null;
                
                if ($replicaItem) {
                    $replicaSize = $replicaItem['size'];
                    $replicaDate = $replicaItem['modified_at'];
                    
                    $replicaItem['date_iso'] = Date::formatIso($replicaDate);

                    $replicaItem['diff_size'] = ($replicaSize != $size);
                    $replicaItem['expired'] = $replicaDate < $date;
                } else {
                    $replicaItem = [ 'no_file' => true ];
                }
                
                $inSync = $inSync && !$replicaItem['diff_size'] && !$replicaItem['expired'] && !$replicaItem['no_file'];
                
                $item['replicas'][] = $replicaItem;
            }
            
            $item['in_sync'] = $inSync;

            $files[] = $item;
        }

        return $files ?? [];
    }

    private function filterFiles($items)
    {
        if ($items == null) return null;
        
        return array_filter($items, function ($item) {
            return !$item['dir'];
        });
    }
}

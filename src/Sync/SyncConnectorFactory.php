<?php

namespace App\Sync;

class SyncConnectorFactory
{
    private $transportConnector;

    public function __construct($transportConnector)
    {
        $this->transportConnector = $transportConnector;
    }
    
    public function make($sync)
    {
        return new SyncConnector($this->transportConnector, $sync);
    }
}

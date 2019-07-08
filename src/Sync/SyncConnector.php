<?php

namespace App\Sync;

/**
 * Connects and disconnects Sync's primary and replica connections
 */
class SyncConnector
{
    const DISCONNECTED = 1;
    const CONNECTED = 2;
    
    private $transportConnector;

    public $sync;
    public $state;
    
    public $primaryConn;
    public $replicaConn;

    public function __construct($transportConnector, $sync)
    {
        $this->transportConnector = $transportConnector;
        $this->sync = $sync;
        $this->state = self::DISCONNECTED;
    }
    
    public function connect()
    {
        if ($this->state == self::CONNECTED) {
            throw new \Exception('Already connected!');
        }
        
        $this->primaryConn = $this->connectToFolder($this->sync['primary']);
        $this->replicaConn = $this->connectToFolder($this->sync['replica']);
        
        $this->state = self::CONNECTED;
        
        return $this->state;
    }

    public function disconnect() {
        if ($this->state == self::DISCONNECTED) {
            throw new \Exception('Already disconnected!');
        }
        
        $this->disconnectFromConnection($this->primaryConn);
        $this->disconnectFromConnection($this->replicaConn);
        
        $this->state = self::DISCONNECTED;
        
        return $this->state;
    }

    private function connectToFolder($folder)
    {
        $connection = $folder['connection'];

        return $this->transportConnector->connect($connection['host'], $connection['user'], $connection['password']);
    }
    
    private function disconnectFromConnection($conn)
    {
        $this->transportConnector->disconnect($conn);
    }
}

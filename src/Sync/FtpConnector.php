<?php

namespace App\Sync;

class FtpConnector
{
    public function connect($server, $user, $password)
    {
        // set up basic connection
        $conn = ftp_connect($server);
        if (!$conn) {
            throw new \Exception("Unable to connect to FTP server {$server}.");
        }
        
        // login with username and password
        $loginResult = ftp_login($conn, $user, $password);
        if (!$loginResult) {
            throw new \Exception("Error connecting to FTP server {$server} on behalf of the user {$user}.");
        }
        
        // turning on passive mode
        ftp_pasv($conn, true);
        
        return $conn;
    }
    
    public function disconnect($conn)
    {
        ftp_close($conn);
    }
}

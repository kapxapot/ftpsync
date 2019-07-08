<?php

namespace App\Sync;

use Plasticode\IO\File;
use Plasticode\Util\Arrays;
use Plasticode\Util\Strings;

class FtpLoader
{
    /**
     * Loads FTP directory recursively with flattened structure.
     * 
     * @param resource $ftpStream FTP connection object (FTP stream).
     * @param string $dir Directory to load.
     * @param string[] $ignoredItems Items to ignore (skip).
     * 
     * @return array Array of item infos (arrays).
     */
    public function getDirectoryItems($ftpStream, $dir, $ignoredItems)
    {
        $items = $this->getDirectoryData($ftpStream, $dir, true);
        $items = $this->flattenDirectoryItems($items, $ignoredItems);
        
        return $items;
    }

    /**
     * Loads FTP directory (recursively).
     * 
     * @param resource $ftpStream FTP connection object (FTP stream).
     */
    private function getDirectoryData($ftpStream, $directory = '.', $recursive = false)
    {
        if (is_array($children = @ftp_rawlist($ftpStream, $directory))) {
            $dirs = [];
            $files = [];
    
            foreach ($children as $child) {
                $item = [];

                $chunks = preg_split("/\s+/", $child);

                list(, , , , $item['size']) = $chunks;
                
                $item['dir'] = $child{0} === 'd';
    
                array_splice($chunks, 0, 8);
                $item['name'] = implode(" ", $chunks);
    
                if ($item['dir']) {
                    if ($item['name'] != '.' && $item['name'] != '..') {
                        if ($recursive) {
                            $subDirPath = $this->combine($directory, $item['name']);
                            $item['items'] = $this->getDirectoryData($ftpStream, $subDirPath, true);
                        }
                        
                        $dirs[] = $item;
                    }
                } else {
                    $item['modified_at'] = $this->getModifiedAt($ftpStream, $this->combine($directory, $item['name']));
                    $files[] = $item;
                }
            }
    
            return array_merge($files, $dirs);
        }
    }
    
    private function combine($path, $file)
    {
        return $path . '/' . $file;
    }
    
    private function getFileInfo($ftpStream, $file)
    {
        $dirData = $this->getDirectoryData($ftpStream, dirname($file));
        $fileInfo = Arrays::firstBy($dirData, 'name', basename($file));
        
        return $fileInfo;
    }
    
    private function getFileSize($ftpStream, $file)
    {
        $fileInfo = $this->getFileInfo($ftpStrem, $file);
        return $fileInfo['size'] ?? null;
    }
    
    private function getModifiedAt($ftpStream, $file)
    {
        return ftp_mdtm($ftpStream, $file);
    }
    
    /**
     * Flattens directory items tree into an array.
     */
    private function flattenDirectoryItems($items, $ignoredItems, $path = null)
    {
        $result = [];
        
        foreach ($items as $item) {
            $item['path'] = $path;
            $fullName = ($path ? $path . '/' : '') . $item['name'];
            
            $item['full_name'] = $fullName;
            
            $ignored = Strings::startsWithAny($fullName, $ignoredItems);
            
            if ($ignored) continue;

            $result[$fullName] = $item;
            
            $subItems = $this->flattenDirectoryItems($item['items'] ?? [], $ignoredItems, $fullName);
            $result = array_merge($result, $subItems);
        }
    
        return $result;
    }
    
    public function getFile($ftpStream, $path, $file)
    {
        $file = $this->combine($path, $file);
        
        $tmpHandle = fopen('php://temp', 'r+');

        if (!@ftp_fget($ftpStream, $tmpHandle, $file, FTP_BINARY)) {
            throw new \Exception("Unable to read remote file: {$file}.");
        }

        rewind($tmpHandle);
        $content = stream_get_contents($tmpHandle);

        return $content;
    }
    
    private function ensurePathExists($ftpStream, $path)
    {
        if (!@ftp_chdir($ftpStream, $path)) {
            ftp_mkdir($ftpStream, $path);
        }
    }
    
    public function putFile($ftpStream, $path, $file, $content)
    {
        $file = $this->combine($path, $file);
        
        $dir = dirname($file);
        $this->ensurePathExists($ftpStream, $dir);
        
        $tmpHandle = fopen('php://temp', 'r+');
        fwrite($tmpHandle, $content);
        rewind($tmpHandle);
        
        if (!@ftp_fput($ftpStream, $file, $tmpHandle, FTP_BINARY)) {
            throw new \Exception("Unsuccessful FTP file upload: {$file}.");
        }
        
        return $this->getFileInfo($ftpStream, $file);
    }
}

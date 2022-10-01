<?php

define('STORAGE_MODE', 1);

class TokenMethod
{
    const FILE_MODE = 1;
    const DB_MODE = 2;
    const SERVER_MEMORY_MODE = 3;
    const CLOUD_MODE = 4;

    private $driver;
    private $token;

    public function __construct()
    {
        switch (STORAGE_MODE) {
            case self::FILE_MODE:
                $this->driver = new StorageDriverFile;
                break;
            case self::DB_MODE:
                $this->driver = new StorageDriverDB;
                break;
            case self::SERVER_MEMORY_MODE:
                $this->driver = new StorageDriverServerMemory;
                break;
            case self::CLOUD_MODE:
                $this->driver = new StorageDriverCloud;
                break;
            default;
                $this->driver = new StorageDriverEtc;
                break;
        }
    }

    public function getSecretKey()
    {
        $this->token = $this->driver->get();
    }


    public function getUserData(string $url)
    {
        if(empty($this->token)) return 'Empty token';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, 'login:password');
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'X-Token: ' . $this->token,
            'Accept: application/json',
        ]);
        $response = curl_exec($curl);
        $resultStatus = curl_getinfo($curl);
        if($resultStatus['http_code'] == 200) {
            return $response;
        } else {
            return 'Call Failed ' . print_r($resultStatus);
        }
    }
}

interface Driver
{
    public function get();
}

class StorageDriverFile implements Driver
{
    public function get()
    {
        return 'Токен из файла';
    }
}

class StorageDriverDB implements Driver
{
    public function get()
    {
        return 'Токен из базы';
    }
}

class StorageDriverServerMemory implements Driver
{
    public function get()
    {
        return 'Токен из сервера';
    }
}

class StorageDriverCloud implements Driver
{
    public function get()
    {
        return 'Токен из облака';
    }
}

class StorageDriverEtc implements Driver
{
    public function get()
    {
        return 'Токен из откуда-то';
    }
}

$tm = new TokenMethod();
$tm->getSecretKey();
$tm->getUserData('/');
<?php

interface ConectionInterface
{
    public function request(string $url, string $method, array $options);
}

class XMLHttpService implements ConectionInterface
{

    public function request(string $url, string $method, array $options = [])
    {
        return 'XMLHttpService conection';
    }
}

class Http
{
    private $service;

    public function __construct(ConectionInterface $serviceConection)
    {
        $this->service = $serviceConection;
    }

    public function get(string $url, array $options = [])
    {
        $this->service->request($url, 'GET', $options);
    }

    public function post(string $url)
    {
        $this->service->request($url, 'POST');
    }
}

$serviceConection = new XMLHttpService();
$sc = new Http($serviceConection);
$sc->get('/');

<?php

interface HandlerObjectsInterface
{
    public function getObjectName();
}

class Object1 implements HandlerObjectsInterface
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getObjectName()
    {
        return 'handle_'.$this->name;
    }
}

class Object2 implements HandlerObjectsInterface
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getObjectName()
    {
        return 'handle_'.$this->name;
    }
}

class SomeObjectsHandler
{

    public $objects;

    public function __construct($objects = [])
    {
        $this->objects = $objects;
    }

    public function handleObjects(): array
    {
        $handlers = [];
        foreach ($this->objects as $object) {
            $handlers[] = $object->getObjectName();
        }
        return $handlers;
    }
}

$objects = [
    new Object1('object_1'),
    new Object2('object_2')
];

$soh = new SomeObjectsHandler($objects);
$arr = $soh->handleObjects();
print_r($arr);
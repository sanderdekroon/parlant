<?php

namespace Sanderdekroon\Parlant;

class Container
{

    private $registry = [];


    public function bind($key, $data)
    {
        if ($this->bound($key)) {
            return $this->rebind($key, $data);
        }

        return $this->registry[$key] = $data;
    }


    public function append($key, $data)
    {
        return $this->registry[$key][] = $data;
    }


    public function get($key)
    {
        if (!$this->bound($key)) {
            return null;
        }

        return $this->registry[$key];
    }


    public function all()
    {
        return $this->registry;
    }


    public function has($key)
    {
        return $this->bound($key);
    }


    private function bound($key)
    {
        return array_key_exists($key, $this->registry);
    }


    private function rebind($key, $data)
    {
        return $this->registry[$key] = $data;
    }
}

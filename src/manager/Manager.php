<?php

namespace Sanderdekroon\Parlant;

class Manager
{

    protected $builder;


    public function __construct()
    {
    }


    public function setBuilder(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }


    public function setupConfiguration()
    {
        $this->builder()->type('any');
    }


    public function configure(array $configuration)
    {
        // $options = ['posttype', 'poststatus', 'returns'];
        $this->builder()->setConfiguration($configuration);
    }


    private function builder()
    {
        return $this->builder;
    }


    public function __call($method, $paramaters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$paramaters);
        }

        return $this->builder()->$method(...$paramaters);
    }
}

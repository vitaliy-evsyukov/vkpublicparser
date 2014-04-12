<?php


namespace components\system;


use components\Component;

class Application extends Component
{
    public function __construct(Config $config = null)
    {
        parent::__construct($config);
        $this->application = $this;
    }

    public function getParameter($name)
    {
        return $this->$name;
    }
}
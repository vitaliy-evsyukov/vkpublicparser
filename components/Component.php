<?php


namespace components;


use components\system\Config;

abstract class Component
{
    /**
     * @var Component
     */
    protected $container;

    protected $services = array();

    protected $config = array();

    public function __construct(Config $config = null)
    {
        if ($config) {
            $this->config = $config->getData();
        }
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setContainer(Component $container)
    {
        $this->container = $container;
    }

    public function __get($name)
    {
        if (!isset($this->config[$name])) {
            $this->config[$name] = null;
        }
        if ($this->config[$name] instanceof \Closure) {
            $this->config[$name] = $this->config[$name]();
        }
        return $this->config[$name];
    }

    /**
     * @param string $serviceName
     * @return Component
     * @throws \InvalidArgumentException
     */
    public function get($serviceName)
    {
        $name = 'services.' . $serviceName;
        if (!isset($this->config[$name])) {
            throw new \InvalidArgumentException('Не найден сервис ' . $serviceName);
        }
        $component = $this->config[$name]();
        $component->setContainer($this);
        return $component;
    }
} 
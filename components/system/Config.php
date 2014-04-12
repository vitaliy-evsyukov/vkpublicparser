<?php


namespace components\system;

use components\Component;

class Config extends Component
{
    const CLASS_PARAM = 'class';

    protected $configs = array();

    protected $fileName;

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getData()
    {
        $fileName = $this->fileName;
        if (!isset($this->configs[$fileName])) {
            $config = array();
            require_once $fileName;
            $parseResult = $this->parseConfig($config);
            $process     = array();
            foreach ($parseResult as $name => &$value) {
                if (is_string($value) && (strpos($value, 'services.') !== false)) {
                    $value = array_merge($parseResult[$value], array('context' => $value));
                }
                if (is_array($value)) {
                    $process[$name] = $value;
                }
            }
            foreach ($process as $name => $info) {
                $parameters    = $info['config'];
                $configuration = array();
                $context       = isset($info['context']) ? $info['context'] : $name;
                foreach ($parameters as $parameter) {
                    $configuration[$parameter] = &$parseResult[$context . '.' . $parameter];
                }
                $parseResult[$name] = $info['value']($configuration);
            }
            $this->configs[$fileName] = $parseResult;
        }
        return $this->configs[$fileName];
    }

    public function get($serviceName)
    {
        $this->config = $this->getData();
        return parent::get($serviceName);
    }

    private function parseConfig(array $config)
    {
        $result = array();
        $class  = Config::CLASS_PARAM;
        if (isset($config[$class])) {
            $value = $config[$class];
            if (!is_string($value)) {
                throw new \LogicException('Название класса в конфигурации должно быть строкой');
            }
            unset($config[$class]);
            $value          = function ($config) use ($value) {
                static $generator = null;
                if (is_null($generator)) {
                    $generator = function () use ($value, $config) {
                        static $instance = null;
                        if (is_null($instance)) {
                            $instance = new $value;
                            $instance->setConfig($config);
                        }
                        return $instance;
                    };
                }
                return $generator;
            };
            $result[$class] = array(
                'config' => array_keys($config),
                'value'  => $value
            );
        }
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $parsed = $this->parseConfig($value);
                foreach ($parsed as $parsedKey => $parsedValue) {
                    if ($parsedKey === Config::CLASS_PARAM) {
                        $name = $key;
                    } else {
                        $name = $key . '.' . $parsedKey;
                    }
                    $result[$name] = $parsedValue;
                }
                unset($parsed);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
} 
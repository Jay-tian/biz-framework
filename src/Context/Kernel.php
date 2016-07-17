<?php

namespace Codeages\Biz\Framework\Context;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\DriverManager;
use Codeages\Biz\Framework\Dao\DaoProxy;

abstract class Kernel extends Container
{
    protected $config;

    protected $user;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function boot()
    {
        $this['db'] = function ($container) {

            $cfg = $this->config('database');

            return DriverManager::getConnection(array(
                'wrapperClass' => 'Codeages\Biz\Framework\Dao\Connection',
                'dbname' => $cfg['name'],
                'user' => $cfg['user'],
                'password' => $cfg['password'],
                'host' => $cfg['host'],
                'driver' => $cfg['driver'],
                'charset' => $cfg['charset'],
            ));

        };
    }

    public function config($name, $default = null)
    {
        if (!isset($this->config[$name])) {
            return $default;
        }

        return $this->config[$name];
    }

    public function service($name)
    {
        if (!isset($this->container[$name])) {
            $class = "{$this->getNamespace()}\\Service\\Impl\\{$name}Impl";
            $this->container[$name] = new $class($this);
        }

        return $this->container[$name];
    }

    public function dao($name)
    {
        if (!isset($this->container[$name])) {
            $class = "{$this->getNamespace()}\\Dao\\Impl\\{$name}Impl";
            $this->container[$name] = new DaoProxy(new $class($this));
        }

        return $this->container[$name];
    }

    public function setUser(CurrentUserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        parent::register($provider, $values);

        return $this;
    }

    abstract public function getNamespace();
}
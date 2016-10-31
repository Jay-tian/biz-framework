<?php

namespace Codeages\Biz\Framework\Dao;

class DaoProxy
{
    protected $container;
    protected static $cacheDelegate;
    protected $dao;

    public function __construct($container, $dao)
    {
        $this->container = $container;
        $this->dao = $dao;

        if ($this->cacheDeclare($this->dao)) {
            if(empty(self::$cacheDelegate)) {
                self::$cacheDelegate = $container['cache.dao.delegate'];
            }
            self::$cacheDelegate->parseDao($dao);
        }

    }

    protected function cacheDeclare()
    {
        $declares = $this->dao->declares();
        return !empty($declares['cache']);
    }

    public function __call($method, $arguments)
    {
        $that       = $this;
        $daoProxyMethod = $this->getDaoProxyMethod($method);

        if ($this->cacheDeclare($this->dao) && $daoProxyMethod) {
            return self::$cacheDelegate->proccess($this->dao, $method, $arguments, function ($method, $arguments) use ($that, $daoProxyMethod) {
                return $that->$daoProxyMethod($method, $arguments);
            });
        } elseif ($this->getPrefix($method, array('search'))) {
            return $this->_search($method, $arguments);
        } else {
            return $this->_callRealDao($method, $arguments);
        }
    }

    protected function getDaoProxyMethod($method)
    {
        $prefix = $this->getPrefix($method, array('get', 'find', 'create', 'update', 'delete'));
        if ($prefix) {
            return "_{$prefix}";
        }

        if ($this->getPrefix($method, array('wave'))) {
            return "_callRealDao";
        }
    }

    protected function getPrefix($str, $prefixs)
    {
        $_prefix = '';
        foreach ($prefixs as $prefix) {
            if (strpos($str, $prefix) === 0) {
                $_prefix = $prefix;
                break;
            }
        }

        return $_prefix;
    }

    protected function _update($method, $arguments)
    {
        $declares = $this->dao->declares();

        if (isset($declares['timestamps'][1])) {
            $arguments[1][$declares['timestamps'][1]] = time();
        }
        $arguments[1] = $this->_serialize($arguments[1]);

        $row = $this->_callRealDao($method, $arguments);

        return $this->_unserialize($row);
    }

    protected function _create($method, $arguments)
    {
        $declares = $this->dao->declares();
        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = time();
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = time();
        }

        $arguments[0] = $this->_serialize($arguments[0]);
        $row          = $this->_callRealDao($method, $arguments);

        return $this->_unserialize($row);
    }

    protected function _delete($method, $arguments)
    {
        return $this->_callRealDao($method, $arguments);
    }

    protected function _get($method, $arguments)
    {
        $row = $this->_callRealDao($method, $arguments);
        return $this->_unserialize($row);
    }

    protected function _find($method, $arguments)
    {
        $rows = $this->_callRealDao($method, $arguments);
        return $this->_unserializes($rows);
    }

    protected function _search($method, $arguments)
    {
        $rows = $this->_callRealDao($method, $arguments);
        return $this->_unserializes($rows);
    }

    protected function _callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    protected function _unserialize(&$row)
    {
        if (empty($row)) {
            return $row;
        }

        $declares   = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }
            $method    = "_{$method}Unserialize";
            $row[$key] = $this->$method($row[$key]);
        }

        return $row;
    }

    protected function _unserializes(array &$rows)
    {
        foreach ($rows as &$row) {
            $this->_unserialize($row);
        }

        return $rows;
    }

    protected function _serialize(&$row)
    {
        $declares   = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }
            $method    = "_{$method}Serialize";
            $row[$key] = $this->$method($row[$key]);
        }

        return $row;
    }

    protected function _jsonSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value);
    }

    protected function _jsonUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return json_decode($value, true);
    }

    protected function _delimiterSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return '|'.implode('|', $value).'|';
    }

    protected function _delimiterUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return explode('|', trim($value, '|'));
    }
}

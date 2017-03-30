<?php
namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;

class DoubleCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    private $first;

    private $second;

    public function setStrategies($first, $second)
    {
        $this->first = $first;
        $this->second = $second;

    }

    public function beforeGet($method, $arguments)
    {
        $cache = $this->first->beforeGet($method, $arguments);
        if ($cache) {
            return $cache;
        }

        return $this->second->beforeGet($method, $arguments);
    }

    public function afterGet($method, $arguments, $row)
    {
        $this->first->afterGet($method, $arguments, $row);
        $this->second->afterGet($method, $arguments, $row);
    }

    public function beforeFind($methd, $arguments)
    {
        $cache = $this->first->beforeFind($method, $arguments);
        if ($cache) {
            return $cache;
        }

        return $this->second->beforeFind($method, $arguments);
    }

    public function afterFind($methd, $arguments, array $rows)
    {
        $this->first->afterGet($method, $arguments, $rows);
        $this->second->afterGet($method, $arguments, $rows);
    }

    public function beforeSearch($methd, $arguments)
    {
        $cache = $this->first->beforeSearch($method, $arguments);
        if ($cache) {
            return $cache;
        }

        return $this->second->beforeSearch($method, $arguments);
    }

    public function afterSearch($methd, $arguments, array $rows)
    {
        $this->first->afterSearch($method, $arguments, $rows);
        $this->second->afterSearch($method, $arguments, $rows);
    }

    public function afterCreate($methd, $arguments, $row)
    {
        $this->first->afterCreate($method, $arguments, $row);
        $this->second->afterCreate($method, $arguments, $row);
    }

    public function afterUpdate($methd, $arguments, $row)
    {
        $this->first->afterUpdate($method, $arguments, $row);
        $this->second->afterUpdate($method, $arguments, $row);
    }

    public function afterDelete($methd, $arguments)
    {
        $this->first->afterDelete($method, $arguments);
        $this->second->afterDelete($method, $arguments);
    }
}
<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 行级别缓存策略
 */
class RowStrategy implements CacheStrategy
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var MetadataReader
     */
    private $metadataReader;

    const LIFE_TIME = 3600;

    public function __construct($redis)
    {
        $this->redis = $redis;
        $this->metadataReader = new MetadataReader();
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        if (strpos($method, 'get') !== 0) {
            return false;
        }

        $metadata = $this->metadataReader->read($dao);

        $key = $this->getCacheKey($dao, $metadata, $method, $arguments);
        if (!$key) {
            return false;
        }

        $cache = $this->redis->get($key);
        if ($cache === false) {
            return false;
        }

        if ($method === 'get') {
            return $cache;
        }

        return $this->redis->get($cache);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        if (strpos($method, 'get') !== 0) {
            return;
        }

        $metadata = $this->metadataReader->read($dao);

        $key = $this->getCacheKey($dao, $metadata, $method, $arguments);
        if (!$key) {
            return;
        }

        if ($method === 'get') {
            $this->redis->set($key, $data, self::LIFE_TIME);
        } else {
            $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $data['id']);
            $this->redis->set($primaryKey, $data, self::LIFE_TIME);
            $this->redis->set($key, $primaryKey, self::LIFE_TIME);
        }
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        return;
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $metadata = $this->metadataReader->read($dao);
        $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $row['id']);
        $this->redis->del($primaryKey);

//        @todo
//        foreach ($arguments[1] as $field) {
//            if (empty($metadata['update_rel_query_methods'][$field])) {
//                continue;
//            }
//
//            foreach ($metadata['update_rel_query_methods'][$field] as $method) {
//
//            }
//        }
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $metadata = $this->metadataReader->read($dao);
        // $arguments[0] is GeneralDaoInterface delete function first argument `id`.
        $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $arguments[0]);
        $this->redis->del($primaryKey);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $metadata = $this->metadataReader->read($dao);
        // $arguments[0] is GeneralDaoInterface wave function first argument `$ids`.
        foreach ($arguments[0] as $id) {
            $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $id);
            $this->redis->del($primaryKey);
        }
    }

    protected function getCacheKey(GeneralDaoInterface $dao, $metadata, $method, $arguments)
    {
        $argumentsForKey = array();

        if (empty($metadata['cache_key_of_arg_index'][$method])) {
            return false;
        }

        foreach ($metadata['cache_key_of_arg_index'][$method] as $index) {
            $argumentsForKey[] = $arguments[$index];
        }

        $key = "dao:{$dao->table()}:{$method}:";

        return $key.implode(',', $argumentsForKey);
    }

    protected function getPrimaryCacheKey(GeneralDaoInterface $dao, $metadata, $id)
    {
        return $this->getCacheKey($dao, $metadata, 'get', [$id]);
    }
}
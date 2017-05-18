<?php

namespace Codeages\Biz\Framework\Util;

class Lock
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    private $connection = null;

    public function get($lockName, $lockTime)
    {
        $result = $this->getConnection()->fetchAssoc("SELECT GET_LOCK('lock_{$lockName}', {$lockTime}) AS getLock");

        return $result['getLock'];
    }

    public function release($lockName)
    {
        $result = $this->getConnection()->fetchAssoc("SELECT RELEASE_LOCK('im_{$lockName}') AS releaseLock");

        return $result['releaseLock'];
    }

    protected function getConnection()
    {
        return $this->biz['db'];
    }
}

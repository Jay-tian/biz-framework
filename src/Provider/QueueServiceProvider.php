<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Queue\Driver\SyncQueue;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;

class QueueServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/Queue';
        $biz['autoload.aliases']['Queue'] = 'Codeages\Biz\Framework\Queue';

        $biz['queue.default'] = function ($biz) {
            return new SyncQueue('default', $biz);
        };

        $biz['queue.driver.database'] = function ($biz) {
            return new DatabaseQueue($biz);
        };
    }
}
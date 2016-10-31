<?php

/*
 * 此文件来自 Silex 项目(https://github.com/silexphp/Silex).
 *
 * 版权信息请看 LICENSE.SILEX
 */

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Symfony\Bridge\Doctrine\Logger\DbalLogger;
use Codeages\Biz\Framework\Dao\CacheDelegate;
use Codeages\Biz\Framework\Dao\CacheStrategy\TableCacheStrategy;
use Codeages\Biz\Framework\Dao\CacheStrategy\PromiseCacheStrategy;

/**
 * Cache Provider.
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['cache.config'] = array(
            'default' => array(
                "host"           => "127.0.0.1",
                "port"           => 6378,
                "timeout"        => 1,
                "reserved"       => null,
                "retry_interval" => 100
            ),
            'session' => array()
        );

        $app['cache.dao.delegate'] = function ($app) {
            return new CacheDelegate($app);
        };

        $app['cache.dao.strategy.table'] = function ($app) {
            return new TableCacheStrategy($app);
        };

        $app['cache.dao.strategy.promise'] = function ($app) {
            return new PromiseCacheStrategy($app);
        };
    }
}

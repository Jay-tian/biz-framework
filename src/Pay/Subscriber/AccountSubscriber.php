<?php

namespace Codeages\Biz\Framework\Pay\Service\Impl;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.register' => 'onUserRegister'
        );
    }

    protected function onUserRegister(Event $event)
    {
        $user = $event->getSubject();
        $account = array(
            'user_id' => $user['id']
        );
        $this->getAccountService()->createUserBalance($account);
    }

    protected function getAccountService()
    {
        return $this->getBiz()->service('Pay:AccountService');
    }
}
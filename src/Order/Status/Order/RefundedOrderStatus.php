<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingOrderStatus::NAME);
    }

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }
}
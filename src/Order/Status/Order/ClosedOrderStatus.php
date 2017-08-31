<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class ClosedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'closed';

    public function getName()
    {
        return self::NAME;
    }

    public function getPriorStatus()
    {
        return array(CreatedOrderStatus::NAME, PayingOrderStatus::NAME);
    }

    public function process($data = array())
    {
        $closeTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => ClosedOrderStatus::NAME,
            'close_time' => $closeTime
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => ClosedOrderStatus::NAME,
                'close_time' => $closeTime
            ));
        }

        $this->getPayService()->closeTradesByOrderSn($order['sn']);

        return $order;
    }

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }
}
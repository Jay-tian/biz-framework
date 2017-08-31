<?php

namespace Codeages\Biz\Framework\Order\Dao\Impl;

use Codeages\Biz\Framework\Order\Dao\OrderRefundDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrderRefundDaoImpl extends GeneralDaoImpl implements OrderRefundDao
{
    protected $table = 'biz_order_refund';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(),
            'orderbys' => array('created_time'),
        );
    }
}
<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderRefundService
{
    public function searchRefunds($conditions, $orderby, $start, $limit);

    public function countRefunds($conditions);

}
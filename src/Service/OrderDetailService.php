<?php

namespace App\Service;

use App\Repository\OrdersRepository;
use App\Repository\OrdersDetailsRepository;

class OrderDetailService
{
    private $ordersRepository;
    private $ordersDetailsRepository;

    public function __construct(OrdersRepository $orderRepository, OrdersDetailsRepository $orderDetailRepository)
    {
        $this->ordersRepository = $orderRepository;
        $this->ordersDetailsRepository = $orderDetailRepository;
    }

    public function getOrderDetails(int $orderId): array
    {
        $order = $this->ordersRepository->find($orderId);
        $details = $this->ordersDetailsRepository->findBy(['orders' => $order]);

        $total = 0;
        $items = [];

        foreach ($details as $detail) {
            $item = [
                'product' => $detail->getProducts()->getName(),
                'price' => $detail->getPrice(),
                'quantity' => $detail->getQuantity(),
                'subtotal' => $detail->getPrice() * $detail->getQuantity()
            ];
            $total += $item['subtotal'];
            $items[] = $item;
        }

        return [
            'order' => $order,
            'items' => $items,
            'total' => $total
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly int $ownerUserId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->ownerUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewOrderPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'      => $this->order->id,
            'order_number'  => $this->order->order_number,
            'total'         => $this->order->total,
            'status'        => $this->order->status,
            'customer_name' => $this->order->user->name ?? 'Cliente',
            'address'       => $this->order->delivery_address,
            'items_count'   => $this->order->items->count(),
        ];
    }
}

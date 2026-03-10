<?php

namespace App\Consumers;

use App\Models\Order;
use App\Services\SagaOrchestrator;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * SagaConsumer listens to saga events and updates order status.
 *
 * Events handled:
 *   inventory.reserved          → update saga_state, wait for payment
 *   inventory.reservation.failed → mark order FAILED
 *   payment.processed           → mark order COMPLETED
 *   payment.failed              → compensate inventory, mark order FAILED
 */
class SagaConsumer
{
    private const EXCHANGE = 'saga.events';

    public static function run(): void
    {
        $host   = env('RABBITMQ_HOST', 'rabbitmq');
        $port   = (int) env('RABBITMQ_PORT', 5672);
        $user   = env('RABBITMQ_USER', 'guest');
        $pass   = env('RABBITMQ_PASS', 'guest');

        $connection = new AMQPStreamConnection($host, $port, $user, $pass);
        $channel    = $connection->channel();

        $channel->exchange_declare(self::EXCHANGE, 'topic', false, true, false);

        // inventory.reserved
        [$q1] = $channel->queue_declare('order.inventory.reserved', false, true, false, false);
        $channel->queue_bind($q1, self::EXCHANGE, 'inventory.reserved');

        // inventory.reservation.failed
        [$q2] = $channel->queue_declare('order.inventory.reservation.failed', false, true, false, false);
        $channel->queue_bind($q2, self::EXCHANGE, 'inventory.reservation.failed');

        // payment.processed
        [$q3] = $channel->queue_declare('order.payment.processed', false, true, false, false);
        $channel->queue_bind($q3, self::EXCHANGE, 'payment.processed');

        // payment.failed
        [$q4] = $channel->queue_declare('order.payment.failed', false, true, false, false);
        $channel->queue_bind($q4, self::EXCHANGE, 'payment.failed');

        $channel->basic_qos(null, 1, false);

        $orchestrator = new SagaOrchestrator();

        // ── inventory.reserved ──────────────────────────────────────────────
        $channel->basic_consume($q1, '', false, false, false, false,
            function (AMQPMessage $msg) use ($channel): void {
                $data  = json_decode($msg->body, true);
                $order = Order::find($data['order_id']);
                if ($order) {
                    $order->update([
                        'unit_price'  => $data['unit_price'] ?? 0,
                        'total_price' => ($data['unit_price'] ?? 0) * $order->quantity,
                        'saga_state'  => 'inventory_reserved',
                    ]);
                    echo "[order-service] ← inventory.reserved  order_id={$data['order_id']}\n";
                }
                $channel->basic_ack($msg->delivery_info['delivery_tag']);
            }
        );

        // ── inventory.reservation.failed ────────────────────────────────────
        $channel->basic_consume($q2, '', false, false, false, false,
            function (AMQPMessage $msg) use ($channel): void {
                $data  = json_decode($msg->body, true);
                $order = Order::find($data['order_id']);
                if ($order) {
                    $order->update([
                        'status'         => Order::STATUS_FAILED,
                        'failure_reason' => $data['reason'] ?? 'Inventory reservation failed',
                        'saga_state'     => 'inventory_reservation_failed',
                    ]);
                    echo "[order-service] ← inventory.reservation.failed  order_id={$data['order_id']}\n";
                }
                $channel->basic_ack($msg->delivery_info['delivery_tag']);
            }
        );

        // ── payment.processed ───────────────────────────────────────────────
        $channel->basic_consume($q3, '', false, false, false, false,
            function (AMQPMessage $msg) use ($channel): void {
                $data  = json_decode($msg->body, true);
                $order = Order::find($data['order_id']);
                if ($order) {
                    $order->update([
                        'status'     => Order::STATUS_COMPLETED,
                        'payment_id' => $data['payment_id'] ?? null,
                        'saga_state' => 'payment_processed',
                    ]);
                    echo "[order-service] ← payment.processed  order_id={$data['order_id']}\n";
                }
                $channel->basic_ack($msg->delivery_info['delivery_tag']);
            }
        );

        // ── payment.failed ──────────────────────────────────────────────────
        $channel->basic_consume($q4, '', false, false, false, false,
            function (AMQPMessage $msg) use ($channel, $orchestrator): void {
                $data  = json_decode($msg->body, true);
                $order = Order::find($data['order_id']);
                if ($order) {
                    // Compensating transaction: release inventory
                    $orchestrator->compensateInventory([
                        'order_id'   => $order->id,
                        'product_id' => $order->product_id,
                        'quantity'   => $order->quantity,
                    ]);

                    $order->update([
                        'status'         => Order::STATUS_FAILED,
                        'failure_reason' => $data['reason'] ?? 'Payment failed',
                        'saga_state'     => 'payment_failed_compensated',
                    ]);
                    echo "[order-service] ← payment.failed  order_id={$data['order_id']} → triggered compensation\n";
                }
                $channel->basic_ack($msg->delivery_info['delivery_tag']);
            }
        );

        echo "[order-service] Saga consumer started, waiting for events...\n";

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}

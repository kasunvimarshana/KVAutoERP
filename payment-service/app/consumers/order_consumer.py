"""
RabbitMQ consumer for the Payment Service – handles Saga events:

  inventory.reserved  → process payment
  payment events are published back to saga exchange
"""

import asyncio
import json
import logging
import os
import random
from datetime import datetime, timezone

import aio_pika
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import AsyncSessionLocal
from app.models import Payment, PaymentStatus

logger = logging.getLogger(__name__)

EXCHANGE       = "saga.events"
QUEUE_PAYMENT  = "payment.process"
RABBITMQ_URL   = os.getenv("RABBITMQ_URL", "amqp://guest:guest@localhost/")


async def _publish(channel: aio_pika.Channel, routing_key: str, payload: dict) -> None:
    exchange = await channel.get_exchange(EXCHANGE)
    await exchange.publish(
        aio_pika.Message(
            body=json.dumps(payload).encode(),
            delivery_mode=aio_pika.DeliveryMode.PERSISTENT,
            content_type="application/json",
        ),
        routing_key=routing_key,
    )
    logger.info("→ Published %s %s", routing_key, payload)


async def _handle_inventory_reserved(
    message: aio_pika.IncomingMessage,
    channel: aio_pika.Channel,
) -> None:
    async with message.process():
        data = json.loads(message.body)
        order_id   = data["order_id"]
        user_id    = data.get("user_id", 0)
        amount     = data.get("unit_price", 0) * data.get("quantity", 1)

        logger.info("← inventory.reserved  order_id=%s", order_id)

        async with AsyncSessionLocal() as db:
            payment = Payment(
                order_id=order_id,
                user_id=user_id,
                amount=amount,
                status=PaymentStatus.PENDING,
            )
            db.add(payment)
            await db.commit()
            await db.refresh(payment)

            # Simulate payment processing (90% success rate for demo)
            success = random.random() < 0.9

            if success:
                payment.status = PaymentStatus.COMPLETED
                await db.commit()
                await _publish(channel, "payment.processed", {
                    "order_id":   order_id,
                    "payment_id": payment.id,
                    "amount":     amount,
                })
            else:
                payment.status = PaymentStatus.FAILED
                payment.reason = "Payment gateway declined"
                await db.commit()
                await _publish(channel, "payment.failed", {
                    "order_id":   order_id,
                    "payment_id": payment.id,
                    "reason":     "Payment gateway declined",
                })


async def start_consumer() -> None:
    """Connect to RabbitMQ and start consuming saga events."""
    retries = 0
    while True:
        try:
            connection = await aio_pika.connect_robust(RABBITMQ_URL)
            break
        except Exception as exc:
            retries += 1
            logger.warning("RabbitMQ not ready (attempt %d): %s", retries, exc)
            await asyncio.sleep(5)

    async with connection:
        channel = await connection.channel()
        await channel.set_qos(prefetch_count=1)

        exchange = await channel.declare_exchange(
            EXCHANGE, aio_pika.ExchangeType.TOPIC, durable=True
        )

        queue = await channel.declare_queue(QUEUE_PAYMENT, durable=True)
        await queue.bind(exchange, routing_key="inventory.reserved")

        logger.info("[payment-service] Saga consumer started, waiting for events...")

        async for message in queue:
            await _handle_inventory_reserved(message, channel)

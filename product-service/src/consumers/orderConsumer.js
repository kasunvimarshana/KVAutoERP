'use strict';

/**
 * RabbitMQ consumer for Product Service – handles Saga events:
 *
 *  order.created       → reserve inventory
 *  inventory.release   → release (compensate) reserved inventory
 */

const amqp = require('amqplib');
const Product = require('../models/Product');

const EXCHANGE        = 'saga.events';
const QUEUE_RESERVE   = 'product.reserve';
const QUEUE_RELEASE   = 'product.release';

/**
 * Publish an event to the saga exchange.
 */
async function publish(channel, routingKey, payload) {
  channel.publish(
    EXCHANGE,
    routingKey,
    Buffer.from(JSON.stringify(payload)),
    { persistent: true, contentType: 'application/json' }
  );
  console.log(`[product-service] → Published ${routingKey}`, payload);
}

/**
 * Handle order.created: try to reserve the requested quantity.
 */
async function handleOrderCreated(channel, msg) {
  const data = JSON.parse(msg.content.toString());
  const { order_id, product_id, quantity } = data;

  console.log(`[product-service] ← order.created  order_id=${order_id}`);

  try {
    // Atomically check available and increment reserved
    const product = await Product.findOneAndUpdate(
      {
        _id: product_id,
        $expr: { $gte: [{ $subtract: ['$stock', '$reserved'] }, quantity] },
      },
      { $inc: { reserved: quantity } },
      { new: true }
    );

    if (!product) {
      await publish(channel, 'inventory.reservation.failed', {
        order_id,
        product_id,
        reason: 'Insufficient stock',
      });
    } else {
      await publish(channel, 'inventory.reserved', {
        order_id,
        product_id,
        quantity,
        unit_price: product.price,
      });
    }
  } catch (err) {
    console.error('[product-service] Error reserving inventory:', err);
    await publish(channel, 'inventory.reservation.failed', {
      order_id,
      product_id,
      reason: err.message,
    });
  }

  channel.ack(msg);
}

/**
 * Handle inventory.release: release previously reserved quantity (compensation).
 */
async function handleInventoryRelease(channel, msg) {
  const data = JSON.parse(msg.content.toString());
  const { order_id, product_id, quantity } = data;

  console.log(`[product-service] ← inventory.release  order_id=${order_id}`);

  try {
    await Product.findByIdAndUpdate(product_id, {
      $inc: { reserved: -quantity },
    });
    console.log(`[product-service] Released ${quantity} units for product ${product_id}`);
  } catch (err) {
    console.error('[product-service] Error releasing inventory:', err);
  }

  channel.ack(msg);
}

/**
 * Start the consumer.
 */
async function startConsumer(rabbitmqUrl) {
  const conn    = await amqp.connect(rabbitmqUrl);
  const channel = await conn.createChannel();

  // Declare exchange
  await channel.assertExchange(EXCHANGE, 'topic', { durable: true });

  // Declare and bind queues
  await channel.assertQueue(QUEUE_RESERVE, { durable: true });
  await channel.bindQueue(QUEUE_RESERVE, EXCHANGE, 'order.created');

  await channel.assertQueue(QUEUE_RELEASE, { durable: true });
  await channel.bindQueue(QUEUE_RELEASE, EXCHANGE, 'inventory.release');

  channel.prefetch(1);

  channel.consume(QUEUE_RESERVE, (msg) => handleOrderCreated(channel, msg));
  channel.consume(QUEUE_RELEASE, (msg) => handleInventoryRelease(channel, msg));

  console.log('[product-service] Saga consumer started, waiting for events...');

  conn.on('close', () => {
    console.warn('[product-service] RabbitMQ connection closed, reconnecting...');
    setTimeout(() => startConsumer(rabbitmqUrl), 5000);
  });
}

module.exports = { startConsumer };

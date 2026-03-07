const inventoryService = require('../services/inventoryService');

const QUEUE_NAME = 'inventory_product_events';
const ROUTING_KEYS = ['product.created', 'product.updated', 'product.deleted'];

const startSubscriber = async (channel) => {
  if (!channel) {
    console.warn('RabbitMQ channel not available, skipping subscriber setup');
    return;
  }
  await channel.assertQueue(QUEUE_NAME, { durable: true });
  for (const key of ROUTING_KEYS) {
    await channel.bindQueue(QUEUE_NAME, 'product_events', key);
  }
  channel.consume(QUEUE_NAME, async (msg) => {
    if (!msg) return;
    try {
      const routingKey = msg.fields.routingKey;
      const data = JSON.parse(msg.content.toString());
      console.log(`Received event: ${routingKey}`, data);
      if (routingKey === 'product.created') {
        await inventoryService.handleProductCreated(data);
      } else if (routingKey === 'product.updated') {
        await inventoryService.handleProductUpdated(data);
      } else if (routingKey === 'product.deleted') {
        await inventoryService.handleProductDeleted(data);
      }
      channel.ack(msg);
    } catch (err) {
      console.error('Error processing event:', err);
      channel.nack(msg, false, false);
    }
  });
  console.log('Product event subscriber started');
};

module.exports = { startSubscriber };

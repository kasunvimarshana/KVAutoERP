const amqp = require('amqplib');

let connection = null;
let channel = null;

const connectRabbitMQ = async (retries = 5) => {
  const url = process.env.RABBITMQ_URL || 'amqp://guest:guest@rabbitmq:5672';
  for (let i = 0; i < retries; i++) {
    try {
      connection = await amqp.connect(url);
      channel = await connection.createChannel();
      await channel.assertExchange('product_events', 'topic', { durable: true });
      console.log('Connected to RabbitMQ');
      return channel;
    } catch (err) {
      console.error(`RabbitMQ connection attempt ${i + 1} failed: ${err.message}`);
      if (i < retries - 1) await new Promise(r => setTimeout(r, 5000));
    }
  }
  console.warn('Could not connect to RabbitMQ after retries, proceeding without it');
  return null;
};

const getChannel = () => channel;

module.exports = { connectRabbitMQ, getChannel };

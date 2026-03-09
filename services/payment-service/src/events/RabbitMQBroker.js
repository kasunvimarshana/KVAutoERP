'use strict';

/**
 * RabbitMQ Broker - Node.js Implementation.
 *
 * Implements the pluggable message broker interface for RabbitMQ.
 */

const amqplib = require('amqplib');
const logger  = require('../utils/logger');

class RabbitMQBroker {
  constructor(url) {
    this.url        = url;
    this.connection = null;
    this.channel    = null;
  }

  /**
   * Publish a message to RabbitMQ.
   *
   * @param {string} topic   Exchange or routing key
   * @param {Object} message
   * @param {Object} options
   * @returns {Promise<boolean>}
   */
  async publish(topic, message, options = {}) {
    try {
      const channel      = await this.getChannel();
      const exchangeName = options.exchange || 'ims.events';
      const routingKey   = options.routing_key || topic;

      await channel.assertExchange(exchangeName, 'topic', { durable: true });

      const body = JSON.stringify({
        ...message,
        __topic: topic,
        __timestamp: new Date().toISOString(),
        __id: require('uuid').v4(),
      });

      channel.publish(exchangeName, routingKey, Buffer.from(body), {
        contentType: 'application/json',
        persistent: true,
      });

      return true;
    } catch (error) {
      logger.error('RabbitMQ publish failed', { topic, error: error.message });
      return false;
    }
  }

  /**
   * Subscribe to a RabbitMQ queue.
   *
   * @param {string}   topic
   * @param {Function} handler
   * @param {Object}   options
   * @returns {Promise<void>}
   */
  async subscribe(topic, handler, options = {}) {
    const channel   = await this.getChannel();
    const queueName = options.queue || topic;

    await channel.assertQueue(queueName, { durable: true });

    channel.consume(queueName, async (msg) => {
      if (!msg) return;

      try {
        const data = JSON.parse(msg.content.toString());
        await handler(data, msg);
        channel.ack(msg);
      } catch (error) {
        logger.error(`Error processing message from [${topic}]`, { error: error.message });
        channel.nack(msg, false, false);
      }
    });
  }

  async acknowledge(msg) {
    const channel = await this.getChannel();
    channel.ack(msg);
  }

  async reject(msg, requeue = false) {
    const channel = await this.getChannel();
    channel.nack(msg, false, requeue);
  }

  isConnected() {
    return this.connection !== null;
  }

  getDriver() {
    return 'rabbitmq';
  }

  async getChannel() {
    if (!this.connection) {
      this.connection = await amqplib.connect(this.url);
      this.channel    = await this.connection.createChannel();
    }
    return this.channel;
  }
}

module.exports = RabbitMQBroker;

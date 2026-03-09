'use strict';

/**
 * Kafka Broker - Node.js Implementation using KafkaJS.
 */

const { Kafka } = require('kafkajs');
const logger    = require('../utils/logger');

class KafkaBroker {
  constructor(brokers) {
    this.kafka = new Kafka({
      clientId: 'ims-payment-service',
      brokers: brokers.split(','),
    });
    this.producer = null;
  }

  async publish(topic, message, options = {}) {
    try {
      if (!this.producer) {
        this.producer = this.kafka.producer();
        await this.producer.connect();
      }

      const body = JSON.stringify({
        ...message,
        __topic: topic,
        __timestamp: new Date().toISOString(),
        __id: require('uuid').v4(),
      });

      await this.producer.send({
        topic,
        messages: [{ value: body, key: options.key || null }],
      });

      return true;
    } catch (error) {
      logger.error('Kafka publish failed', { topic, error: error.message });
      return false;
    }
  }

  async subscribe(topic, handler, options = {}) {
    const consumer = this.kafka.consumer({ groupId: options.groupId || 'ims-payment-consumers' });
    await consumer.connect();
    await consumer.subscribe({ topic, fromBeginning: options.fromBeginning || false });

    await consumer.run({
      eachMessage: async ({ topic, partition, message }) => {
        try {
          const data = JSON.parse(message.value.toString());
          await handler(data, message);
        } catch (error) {
          logger.error(`Kafka message processing failed [${topic}]`, { error: error.message });
        }
      },
    });
  }

  acknowledge() { /* KafkaJS auto-commits */ }

  reject() { /* KafkaJS handles offset management */ }

  isConnected() { return this.producer !== null; }

  getDriver() { return 'kafka'; }
}

module.exports = KafkaBroker;

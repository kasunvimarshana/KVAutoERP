'use strict';

/**
 * Message Broker Factory - Node.js Implementation.
 *
 * Creates pluggable message broker instances.
 * Mirrors the Laravel MessageBrokerFactory pattern.
 */

const RabbitMQBroker = require('./RabbitMQBroker');
const KafkaBroker    = require('./KafkaBroker');

class MessageBrokerFactory {
  /**
   * Create a message broker instance.
   *
   * @param {string|null} driver 'rabbitmq' | 'kafka'
   * @returns {RabbitMQBroker|KafkaBroker}
   */
  static create(driver = null) {
    const resolvedDriver = driver || process.env.MESSAGE_BROKER_DRIVER || 'rabbitmq';

    switch (resolvedDriver) {
      case 'rabbitmq':
        return new RabbitMQBroker(process.env.RABBITMQ_URL || 'amqp://guest:guest@rabbitmq:5672');
      case 'kafka':
        return new KafkaBroker(process.env.KAFKA_BROKERS || 'kafka:9092');
      default:
        throw new Error(`Unsupported message broker driver: [${resolvedDriver}]`);
    }
  }
}

module.exports = MessageBrokerFactory;

'use strict';

/**
 * Payment Service - Business Logic
 *
 * Handles payment processing, refunds, and saga compensation.
 */

const { v4: uuidv4 } = require('uuid');
const logger = require('../utils/logger');
const PaymentRepository = require('../repositories/PaymentRepository');
const MessageBrokerFactory = require('../events/MessageBrokerFactory');

class PaymentService {
  constructor() {
    this.paymentRepository = new PaymentRepository();
  }

  /**
   * Process a payment charge for an order.
   *
   * @param {string} tenantId
   * @param {Object} data - { order_id, customer_id, amount, currency, payment_method }
   * @returns {Promise<Object>} Payment record
   */
  async charge(tenantId, data) {
    const { order_id, customer_id, amount, currency = 'USD', payment_method } = data;

    // Validate payment method (in production, tokenize with Stripe/Braintree etc.)
    await this.validatePaymentMethod(payment_method);

    // Create payment record
    const payment = await this.paymentRepository.create({
      id: uuidv4(),
      tenant_id: tenantId,
      order_id,
      customer_id,
      amount: parseFloat(amount),
      currency,
      payment_method_type: payment_method.type,
      payment_method_token: payment_method.token,
      status: 'pending',
      metadata: data.metadata || {},
    });

    try {
      // Process the charge (integrate with payment gateway in production)
      const chargeResult = await this.processCharge(payment, payment_method);

      // Update payment record with result
      const updatedPayment = await this.paymentRepository.update(payment.id, {
        status: 'completed',
        gateway_payment_id: chargeResult.gateway_id,
        gateway_response: chargeResult,
        completed_at: new Date().toISOString(),
      });

      // Publish payment completed event
      await this.publishEvent(tenantId, 'payment.completed', {
        payment_id: payment.id,
        order_id,
        amount,
        currency,
        status: 'completed',
      });

      logger.info('Payment charged successfully', {
        payment_id: payment.id,
        order_id,
        amount,
        tenant_id: tenantId,
      });

      return updatedPayment;
    } catch (error) {
      // Mark payment as failed
      await this.paymentRepository.update(payment.id, {
        status: 'failed',
        error_message: error.message,
      });

      // Publish payment failed event
      await this.publishEvent(tenantId, 'payment.failed', {
        payment_id: payment.id,
        order_id,
        error: error.message,
      });

      throw error;
    }
  }

  /**
   * Refund a payment (Saga compensating transaction).
   *
   * Idempotent - safe to call multiple times for the same payment.
   *
   * @param {string} tenantId
   * @param {string} paymentId
   * @param {Object} data - { reason, order_id }
   * @returns {Promise<Object>} Refund record
   */
  async refund(tenantId, paymentId, data) {
    const payment = await this.paymentRepository.findByIdAndTenant(paymentId, tenantId);

    if (!payment) {
      throw new Error(`Payment [${paymentId}] not found.`);
    }

    // Idempotency check - don't double-refund
    if (payment.status === 'refunded') {
      logger.info('Payment already refunded - idempotent response', { payment_id: paymentId });
      return payment;
    }

    if (payment.status !== 'completed') {
      throw new Error(`Cannot refund payment [${paymentId}] with status [${payment.status}].`);
    }

    // Process refund via gateway (in production)
    const refundResult = await this.processRefund(payment);

    const updatedPayment = await this.paymentRepository.update(paymentId, {
      status: 'refunded',
      refund_id: refundResult.refund_id,
      refund_reason: data.reason,
      refunded_at: new Date().toISOString(),
    });

    // Publish refund event
    await this.publishEvent(tenantId, 'payment.refunded', {
      payment_id: paymentId,
      order_id: payment.order_id,
      amount: payment.amount,
      reason: data.reason,
    });

    logger.info('Payment refunded (saga compensation)', {
      payment_id: paymentId,
      order_id: payment.order_id,
      amount: payment.amount,
    });

    return updatedPayment;
  }

  /**
   * Get a payment by ID.
   *
   * @param {string} tenantId
   * @param {string} paymentId
   * @returns {Promise<Object>}
   */
  async getById(tenantId, paymentId) {
    const payment = await this.paymentRepository.findByIdAndTenant(paymentId, tenantId);

    if (!payment) {
      const error = new Error(`Payment [${paymentId}] not found.`);
      error.status = 404;
      throw error;
    }

    return payment;
  }

  /**
   * List payments with filtering and pagination.
   *
   * @param {string} tenantId
   * @param {Object} params - { page, per_page, filters, sort_by, sort_dir }
   * @returns {Promise<Object>}
   */
  async list(tenantId, params = {}) {
    return this.paymentRepository.findAll(tenantId, params);
  }

  // =========================================================================
  // Private Methods
  // =========================================================================

  /**
   * Validate payment method token.
   *
   * @param {Object} paymentMethod
   * @returns {Promise<void>}
   */
  async validatePaymentMethod(paymentMethod) {
    if (!paymentMethod || !paymentMethod.type || !paymentMethod.token) {
      throw new Error('Invalid payment method: type and token are required.');
    }

    const validTypes = ['credit_card', 'debit_card', 'bank_transfer', 'wallet'];
    if (!validTypes.includes(paymentMethod.type)) {
      throw new Error(`Invalid payment method type: [${paymentMethod.type}].`);
    }
  }

  /**
   * Process charge through payment gateway.
   * In production, integrate with Stripe, Braintree, PayPal, etc.
   *
   * @param {Object} payment
   * @param {Object} paymentMethod
   * @returns {Promise<Object>}
   */
  async processCharge(payment, paymentMethod) {
    // Simulate gateway processing
    // In production: await stripe.paymentIntents.create(...)
    return {
      gateway_id: `gw_${uuidv4().replace(/-/g, '').substring(0, 24)}`,
      status: 'success',
      amount: payment.amount,
      currency: payment.currency,
    };
  }

  /**
   * Process refund through payment gateway.
   *
   * @param {Object} payment
   * @returns {Promise<Object>}
   */
  async processRefund(payment) {
    // Simulate gateway refund
    // In production: await stripe.refunds.create({ payment_intent: payment.gateway_payment_id })
    return {
      refund_id: `ref_${uuidv4().replace(/-/g, '').substring(0, 24)}`,
      status: 'success',
      amount: payment.amount,
    };
  }

  /**
   * Publish an event to the message broker.
   *
   * @param {string} tenantId
   * @param {string} event
   * @param {Object} payload
   * @returns {Promise<void>}
   */
  async publishEvent(tenantId, event, payload) {
    try {
      const broker = MessageBrokerFactory.create();
      await broker.publish('payment.events', {
        event,
        tenant_id: tenantId,
        payload,
        timestamp: new Date().toISOString(),
      });
    } catch (error) {
      // Non-critical - log and continue
      logger.warn('Failed to publish payment event', { event, error: error.message });
    }
  }
}

module.exports = PaymentService;

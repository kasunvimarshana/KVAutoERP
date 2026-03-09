'use strict';

/**
 * Payment Controller.
 *
 * Thin controller: delegates all business logic to PaymentService.
 * Handles only request ingestion and response formatting.
 */

const PaymentService = require('../services/PaymentService');
const logger = require('../utils/logger');

class PaymentController {
  constructor() {
    this.paymentService = new PaymentService();
    // Bind methods to maintain 'this' context
    this.index   = this.index.bind(this);
    this.show    = this.show.bind(this);
    this.charge  = this.charge.bind(this);
    this.refund  = this.refund.bind(this);
  }

  /**
   * List payments.
   * GET /api/payments
   */
  async index(req, res, next) {
    try {
      const result = await this.paymentService.list(
        req.tenantId,
        req.query,
      );

      return res.json({
        success: true,
        data: result.data,
        meta: result.meta,
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Get a single payment.
   * GET /api/payments/:id
   */
  async show(req, res, next) {
    try {
      const payment = await this.paymentService.getById(
        req.tenantId,
        req.params.id,
      );

      return res.json({ success: true, data: payment });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Process a payment charge.
   * POST /api/payments/charge
   */
  async charge(req, res, next) {
    try {
      const payment = await this.paymentService.charge(
        req.tenantId,
        req.body,
      );

      return res.status(201).json({
        success: true,
        data: {
          payment_id: payment.id,
          order_id: payment.order_id,
          status: payment.status,
          amount: payment.amount,
          currency: payment.currency,
          completed_at: payment.completed_at,
        },
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Refund a payment (Saga compensating transaction).
   * POST /api/payments/:id/refund
   */
  async refund(req, res, next) {
    try {
      const payment = await this.paymentService.refund(
        req.tenantId,
        req.params.id,
        req.body,
      );

      return res.json({
        success: true,
        data: {
          payment_id: payment.id,
          status: payment.status,
          refund_reason: payment.refund_reason,
          refunded_at: payment.refunded_at,
        },
      });
    } catch (error) {
      next(error);
    }
  }
}

module.exports = new PaymentController();

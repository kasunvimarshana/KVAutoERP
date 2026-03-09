'use strict';

/**
 * PaymentService Unit Tests
 */

const PaymentService = require('../../src/services/PaymentService');
const PaymentRepository = require('../../src/repositories/PaymentRepository');

// Mock dependencies
jest.mock('../../src/repositories/PaymentRepository');
jest.mock('../../src/events/MessageBrokerFactory', () => ({
  create: jest.fn(() => ({
    publish: jest.fn().mockResolvedValue(true),
  })),
}));

describe('PaymentService', () => {
  let paymentService;
  let mockRepository;

  beforeEach(() => {
    jest.clearAllMocks();
    mockRepository = {
      create: jest.fn(),
      update: jest.fn(),
      findByIdAndTenant: jest.fn(),
      findAll: jest.fn(),
    };
    PaymentRepository.mockImplementation(() => mockRepository);
    paymentService = new PaymentService();
  });

  // ===========================================================================
  // charge() tests
  // ===========================================================================

  describe('charge()', () => {
    it('should create a payment and return completed status', async () => {
      const paymentData = {
        id: 'payment-uuid',
        tenant_id: 'tenant-1',
        order_id: 'order-1',
        amount: 100.00,
        status: 'pending',
      };

      const completedPayment = { ...paymentData, status: 'completed', gateway_payment_id: 'gw_123' };

      mockRepository.create.mockResolvedValue(paymentData);
      mockRepository.update.mockResolvedValue(completedPayment);

      const result = await paymentService.charge('tenant-1', {
        order_id: 'order-1',
        customer_id: 'customer-1',
        amount: 100.00,
        currency: 'USD',
        payment_method: { type: 'credit_card', token: 'tok_visa' },
      });

      expect(mockRepository.create).toHaveBeenCalledOnce();
      expect(mockRepository.update).toHaveBeenCalledWith(
        paymentData.id,
        expect.objectContaining({ status: 'completed' }),
      );
      expect(result.status).toBe('completed');
    });

    it('should mark payment as failed when gateway throws', async () => {
      const paymentData = {
        id: 'payment-uuid',
        status: 'pending',
        amount: 100,
        order_id: 'order-1',
      };

      mockRepository.create.mockResolvedValue(paymentData);
      mockRepository.update.mockResolvedValue({ ...paymentData, status: 'failed' });

      // Simulate gateway failure by overriding processCharge
      jest.spyOn(paymentService, 'processCharge').mockRejectedValue(
        new Error('Card declined'),
      );

      await expect(paymentService.charge('tenant-1', {
        order_id: 'order-1',
        customer_id: 'customer-1',
        amount: 100,
        currency: 'USD',
        payment_method: { type: 'credit_card', token: 'tok_visa' },
      })).rejects.toThrow('Card declined');

      expect(mockRepository.update).toHaveBeenCalledWith(
        paymentData.id,
        expect.objectContaining({ status: 'failed' }),
      );
    });

    it('should reject invalid payment method type', async () => {
      await expect(paymentService.charge('tenant-1', {
        order_id: 'order-1',
        customer_id: 'customer-1',
        amount: 100,
        currency: 'USD',
        payment_method: { type: 'crypto', token: 'btc_addr' },
      })).rejects.toThrow('Invalid payment method type');
    });
  });

  // ===========================================================================
  // refund() tests (Saga compensation)
  // ===========================================================================

  describe('refund()', () => {
    it('should refund a completed payment', async () => {
      const payment = {
        id: 'payment-uuid',
        tenant_id: 'tenant-1',
        order_id: 'order-1',
        amount: 100,
        status: 'completed',
      };
      const refundedPayment = { ...payment, status: 'refunded', refunded_at: new Date().toISOString() };

      mockRepository.findByIdAndTenant.mockResolvedValue(payment);
      mockRepository.update.mockResolvedValue(refundedPayment);

      const result = await paymentService.refund('tenant-1', 'payment-uuid', { reason: 'Order cancelled' });

      expect(result.status).toBe('refunded');
    });

    it('should be idempotent - return already-refunded payment', async () => {
      const payment = {
        id: 'payment-uuid',
        status: 'refunded',
        refunded_at: new Date().toISOString(),
      };

      mockRepository.findByIdAndTenant.mockResolvedValue(payment);

      const result = await paymentService.refund('tenant-1', 'payment-uuid', { reason: 'Duplicate' });

      expect(result.status).toBe('refunded');
      expect(mockRepository.update).not.toHaveBeenCalled();
    });

    it('should throw for non-completed payment', async () => {
      const payment = { id: 'payment-uuid', status: 'pending' };
      mockRepository.findByIdAndTenant.mockResolvedValue(payment);

      await expect(
        paymentService.refund('tenant-1', 'payment-uuid', { reason: 'test' })
      ).rejects.toThrow('Cannot refund payment');
    });

    it('should throw 404 for non-existent payment', async () => {
      mockRepository.findByIdAndTenant.mockResolvedValue(null);

      await expect(
        paymentService.refund('tenant-1', 'unknown-id', { reason: 'test' })
      ).rejects.toThrow('not found');
    });
  });

  // ===========================================================================
  // list() - Conditional Pagination Tests
  // ===========================================================================

  describe('list()', () => {
    it('should return paginated results when per_page provided', async () => {
      const paginatedResult = {
        data: [{ id: 'p1' }, { id: 'p2' }],
        meta: { current_page: 1, per_page: 10, total: 2, last_page: 1 },
      };

      mockRepository.findAll.mockResolvedValue(paginatedResult);

      const result = await paymentService.list('tenant-1', { per_page: 10, page: 1 });

      expect(result.meta).toBeDefined();
      expect(result.meta.per_page).toBe(10);
    });

    it('should return all results without per_page', async () => {
      const allResults = { data: [{ id: 'p1' }, { id: 'p2' }] };
      mockRepository.findAll.mockResolvedValue(allResults);

      const result = await paymentService.list('tenant-1', {});

      expect(result.meta).toBeUndefined();
      expect(result.data).toHaveLength(2);
    });
  });
});

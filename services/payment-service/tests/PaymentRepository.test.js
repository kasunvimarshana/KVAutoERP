'use strict';

/**
 * PaymentRepository Unit Tests - Conditional Pagination
 */

const PaymentRepository = require('../../src/repositories/PaymentRepository');

// Mock pg Pool
jest.mock('pg', () => {
  const mockQuery = jest.fn();
  return {
    Pool: jest.fn().mockImplementation(() => ({ query: mockQuery })),
    __mockQuery: mockQuery,
  };
});

const pg = require('pg');

describe('PaymentRepository - Conditional Pagination', () => {
  let repository;
  let mockQuery;

  beforeEach(() => {
    jest.clearAllMocks();
    mockQuery = pg.__mockQuery;
    repository = new PaymentRepository();
  });

  describe('findAll()', () => {
    it('should return all results without meta when per_page is not provided', async () => {
      const rows = [{ id: 'p1' }, { id: 'p2' }];
      mockQuery.mockResolvedValue({ rows });

      const result = await repository.findAll('tenant-1', {});

      expect(result.data).toEqual(rows);
      expect(result.meta).toBeUndefined();
    });

    it('should return paginated results with meta when per_page is provided', async () => {
      const rows  = [{ id: 'p1' }];
      const count = [{ count: '25' }];

      mockQuery
        .mockResolvedValueOnce({ rows })   // data query
        .mockResolvedValueOnce({ rows: count }); // count query

      const result = await repository.findAll('tenant-1', { per_page: 10, page: 1 });

      expect(result.data).toEqual(rows);
      expect(result.meta).toBeDefined();
      expect(result.meta.per_page).toBe(10);
      expect(result.meta.total).toBe(25);
      expect(result.meta.last_page).toBe(3);
      expect(result.meta.current_page).toBe(1);
    });

    it('should cap per_page at 200', async () => {
      mockQuery
        .mockResolvedValueOnce({ rows: [] })
        .mockResolvedValueOnce({ rows: [{ count: '0' }] });

      const result = await repository.findAll('tenant-1', { per_page: 9999 });

      expect(result.meta.per_page).toBe(200);
    });

    it('should default page to 1 when invalid', async () => {
      mockQuery
        .mockResolvedValueOnce({ rows: [] })
        .mockResolvedValueOnce({ rows: [{ count: '0' }] });

      const result = await repository.findAll('tenant-1', { per_page: 10, page: -5 });

      expect(result.meta.current_page).toBe(1);
    });
  });
});

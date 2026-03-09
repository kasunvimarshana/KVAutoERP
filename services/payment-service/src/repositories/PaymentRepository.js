'use strict';

/**
 * Payment Repository.
 *
 * Data access layer for payment records using PostgreSQL.
 * Implements conditional pagination matching the Laravel BaseRepository pattern.
 */

const { Pool } = require('pg');
const logger = require('../utils/logger');

class PaymentRepository {
  constructor() {
    this.pool = new Pool({ connectionString: process.env.DATABASE_URL });
  }

  /**
   * Create a new payment record.
   *
   * @param {Object} data
   * @returns {Promise<Object>}
   */
  async create(data) {
    const {
      id, tenant_id, order_id, customer_id, amount, currency,
      payment_method_type, payment_method_token, status, metadata,
    } = data;

    const result = await this.pool.query(
      `INSERT INTO payments
         (id, tenant_id, order_id, customer_id, amount, currency,
          payment_method_type, payment_method_token, status, metadata, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, NOW(), NOW())
       RETURNING *`,
      [id, tenant_id, order_id, customer_id, amount, currency,
       payment_method_type, payment_method_token, status, JSON.stringify(metadata || {})],
    );

    return result.rows[0];
  }

  /**
   * Update a payment record.
   *
   * @param {string} id
   * @param {Object} data
   * @returns {Promise<Object>}
   */
  async update(id, data) {
    const fields = Object.keys(data)
      .map((key, idx) => `${key} = $${idx + 2}`)
      .join(', ');

    const values = Object.values(data);

    const result = await this.pool.query(
      `UPDATE payments SET ${fields}, updated_at = NOW() WHERE id = $1 RETURNING *`,
      [id, ...values],
    );

    return result.rows[0];
  }

  /**
   * Find a payment by ID scoped to a tenant.
   *
   * @param {string} id
   * @param {string} tenantId
   * @returns {Promise<Object|null>}
   */
  async findByIdAndTenant(id, tenantId) {
    const result = await this.pool.query(
      'SELECT * FROM payments WHERE id = $1 AND tenant_id = $2',
      [id, tenantId],
    );

    return result.rows[0] || null;
  }

  /**
   * Find all payments for a tenant with conditional pagination.
   *
   * Mirrors the Laravel BaseRepository pagination behavior:
   *   - Returns paginated results when 'per_page' is present
   *   - Returns all results otherwise
   *
   * @param {string} tenantId
   * @param {Object} params - { page, per_page, filters, sort_by, sort_dir }
   * @returns {Promise<{data: Array, meta?: Object}>}
   */
  async findAll(tenantId, params = {}) {
    const {
      page = 1,
      per_page,
      sort_by = 'created_at',
      sort_dir = 'DESC',
      filters = {},
    } = params;

    // Build WHERE conditions
    const conditions = ['tenant_id = $1'];
    const values = [tenantId];
    let paramIdx = 2;

    if (filters.status) {
      conditions.push(`status = $${paramIdx++}`);
      values.push(filters.status);
    }

    if (filters.order_id) {
      conditions.push(`order_id = $${paramIdx++}`);
      values.push(filters.order_id);
    }

    if (filters.customer_id) {
      conditions.push(`customer_id = $${paramIdx++}`);
      values.push(filters.customer_id);
    }

    const where   = conditions.join(' AND ');
    const orderBy = `${this.sanitizeColumn(sort_by)} ${sort_dir === 'asc' ? 'ASC' : 'DESC'}`;

    // Conditional pagination - matches Laravel BaseRepository behavior
    if (per_page === undefined) {
      // Return all results
      const result = await this.pool.query(
        `SELECT * FROM payments WHERE ${where} ORDER BY ${orderBy}`,
        values,
      );
      return { data: result.rows };
    }

    // Paginated results
    const perPageInt  = Math.min(Math.max(parseInt(per_page, 10) || 15, 1), 200);
    const pageInt     = Math.max(parseInt(page, 10) || 1, 1);
    const offset      = (pageInt - 1) * perPageInt;

    const [dataResult, countResult] = await Promise.all([
      this.pool.query(
        `SELECT * FROM payments WHERE ${where} ORDER BY ${orderBy} LIMIT $${paramIdx} OFFSET $${paramIdx + 1}`,
        [...values, perPageInt, offset],
      ),
      this.pool.query(
        `SELECT COUNT(*) FROM payments WHERE ${where}`,
        values,
      ),
    ]);

    const total    = parseInt(countResult.rows[0].count, 10);
    const lastPage = Math.ceil(total / perPageInt);

    return {
      data: dataResult.rows,
      meta: {
        current_page: pageInt,
        per_page: perPageInt,
        total,
        last_page: lastPage,
        from: offset + 1,
        to: Math.min(offset + perPageInt, total),
      },
    };
  }

  /**
   * Sanitize column name to prevent SQL injection.
   *
   * @param {string} column
   * @returns {string}
   */
  sanitizeColumn(column) {
    const allowed = ['created_at', 'updated_at', 'amount', 'status', 'currency'];
    return allowed.includes(column) ? column : 'created_at';
  }
}

module.exports = PaymentRepository;

'use strict';

/**
 * Payment Service Database Migration.
 *
 * Creates the PostgreSQL schema for payment records.
 */

const { Pool } = require('pg');

async function migrate() {
  const pool = new Pool({ connectionString: process.env.DATABASE_URL });

  try {
    await pool.query(`
      CREATE TABLE IF NOT EXISTS payments (
        id                  UUID PRIMARY KEY,
        tenant_id           VARCHAR(255) NOT NULL,
        order_id            VARCHAR(255) NOT NULL,
        customer_id         VARCHAR(255) NOT NULL,
        amount              DECIMAL(12, 4) NOT NULL,
        currency            VARCHAR(3) NOT NULL DEFAULT 'USD',
        payment_method_type VARCHAR(50) NOT NULL,
        payment_method_token TEXT NOT NULL,
        status              VARCHAR(30) NOT NULL DEFAULT 'pending',
        gateway_payment_id  VARCHAR(255),
        gateway_response    JSONB,
        refund_id           VARCHAR(255),
        refund_reason       TEXT,
        error_message       TEXT,
        metadata            JSONB DEFAULT '{}',
        completed_at        TIMESTAMPTZ,
        refunded_at         TIMESTAMPTZ,
        created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
        updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
      );

      CREATE INDEX IF NOT EXISTS payments_tenant_id_idx        ON payments(tenant_id);
      CREATE INDEX IF NOT EXISTS payments_order_id_idx         ON payments(order_id);
      CREATE INDEX IF NOT EXISTS payments_tenant_status_idx    ON payments(tenant_id, status);
      CREATE INDEX IF NOT EXISTS payments_tenant_customer_idx  ON payments(tenant_id, customer_id);
    `);

    console.log('Payment service migration completed successfully.');
  } finally {
    await pool.end();
  }
}

migrate().catch((err) => {
  console.error('Migration failed:', err);
  process.exit(1);
});

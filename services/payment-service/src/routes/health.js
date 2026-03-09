'use strict';

/**
 * Health Check Routes.
 */

const express = require('express');
const router  = express.Router();
const { Pool } = require('pg');
const redis   = require('redis');

const pool = new Pool({ connectionString: process.env.DATABASE_URL });

/**
 * Basic liveness probe.
 * GET /api/health
 */
router.get('/', (req, res) => {
  res.json({
    status: 'healthy',
    service: 'ims-payment-service',
    version: process.env.npm_package_version || '1.0.0',
    timestamp: new Date().toISOString(),
  });
});

/**
 * Deep readiness probe.
 * GET /api/health/ready
 */
router.get('/ready', async (req, res) => {
  const checks = {
    database: await checkDatabase(),
    redis: await checkRedis(),
  };

  const isReady = Object.values(checks).every((c) => c.healthy);

  return res.status(isReady ? 200 : 503).json({
    status: isReady ? 'ready' : 'not_ready',
    service: 'ims-payment-service',
    checks,
    timestamp: new Date().toISOString(),
  });
});

async function checkDatabase() {
  try {
    await pool.query('SELECT 1');
    return { healthy: true, message: 'Connected' };
  } catch (err) {
    return { healthy: false, message: err.message };
  }
}

async function checkRedis() {
  try {
    const client = redis.createClient({ url: process.env.REDIS_URL });
    await client.connect();
    await client.ping();
    await client.disconnect();
    return { healthy: true, message: 'Connected' };
  } catch (err) {
    return { healthy: false, message: err.message };
  }
}

module.exports = router;

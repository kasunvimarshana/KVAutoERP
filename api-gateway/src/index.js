'use strict';

require('dotenv').config();

const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');
const morgan = require('morgan');
const cors = require('cors');
const rateLimit = require('express-rate-limit');
const jwt = require('jsonwebtoken');

const app = express();
const PORT = process.env.PORT || 8000;
const JWT_SECRET = process.env.JWT_SECRET || 'user_service_jwt_secret_key_2024';

// ─────────────────────────────────────────────
// Service registry
// ─────────────────────────────────────────────
const services = {
  users:    process.env.USER_SERVICE_URL    || 'http://localhost:8001',
  products: process.env.PRODUCT_SERVICE_URL || 'http://localhost:8002',
  orders:   process.env.ORDER_SERVICE_URL   || 'http://localhost:8003',
  payments: process.env.PAYMENT_SERVICE_URL || 'http://localhost:8004',
};

// ─────────────────────────────────────────────
// Middleware
// ─────────────────────────────────────────────
app.use(cors());
app.use(morgan('combined'));

// Global rate limiter
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 200,
  standardHeaders: true,
  legacyHeaders: false,
  message: { error: 'Too many requests, please try again later.' },
});
app.use(limiter);

// ─────────────────────────────────────────────
// JWT Auth middleware (skip public routes)
// ─────────────────────────────────────────────
const PUBLIC_PATHS = [
  '/api/users/register',
  '/api/users/login',
  '/health',
];

function authMiddleware(req, res, next) {
  if (PUBLIC_PATHS.some(p => req.path.startsWith(p))) {
    return next();
  }

  const authHeader = req.headers['authorization'];
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Missing or invalid Authorization header' });
  }

  const token = authHeader.split(' ')[1];
  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.headers['x-user-id']    = String(decoded.sub || decoded.user_id || '');
    req.headers['x-user-email'] = decoded.email || '';
    req.headers['x-user-roles'] = JSON.stringify(decoded.roles || []);
    next();
  } catch (err) {
    return res.status(401).json({ error: 'Invalid or expired token' });
  }
}

app.use(authMiddleware);

// ─────────────────────────────────────────────
// Health check
// ─────────────────────────────────────────────
app.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    gateway: 'api-gateway',
    services: Object.keys(services),
    timestamp: new Date().toISOString(),
  });
});

// ─────────────────────────────────────────────
// Proxy routes
// ─────────────────────────────────────────────
const proxyDefaults = {
  changeOrigin: true,
  on: {
    error: (err, req, res) => {
      console.error(`[Gateway] Proxy error: ${err.message}`);
      res.status(502).json({ error: 'Service unavailable', detail: err.message });
    },
  },
};

// /api/users/** → user-service
app.use(
  '/api/users',
  createProxyMiddleware({
    target: services.users,
    pathRewrite: { '^/api/users': '/api/users' },
    ...proxyDefaults,
  })
);

// /api/products/** → product-service
app.use(
  '/api/products',
  createProxyMiddleware({
    target: services.products,
    pathRewrite: { '^/api/products': '/api/products' },
    ...proxyDefaults,
  })
);

// /api/orders/** → order-service
app.use(
  '/api/orders',
  createProxyMiddleware({
    target: services.orders,
    pathRewrite: { '^/api/orders': '/api/orders' },
    ...proxyDefaults,
  })
);

// /api/payments/** → payment-service
app.use(
  '/api/payments',
  createProxyMiddleware({
    target: services.payments,
    pathRewrite: { '^/api/payments': '/api/payments' },
    ...proxyDefaults,
  })
);

// ─────────────────────────────────────────────
// 404 fallback
// ─────────────────────────────────────────────
app.use((req, res) => {
  res.status(404).json({ error: 'Route not found' });
});

// ─────────────────────────────────────────────
// Start server
// ─────────────────────────────────────────────
if (require.main === module) {
  app.listen(PORT, () => {
    console.log(`[API Gateway] Listening on port ${PORT}`);
    console.log('[API Gateway] Routes:');
    console.log(`  /api/users    → ${services.users}`);
    console.log(`  /api/products → ${services.products}`);
    console.log(`  /api/orders   → ${services.orders}`);
    console.log(`  /api/payments → ${services.payments}`);
  });
}

module.exports = app;

'use strict';

/**
 * IMS Payment Service - Express Application
 *
 * Node.js/Express microservice demonstrating a different technology stack
 * than the Laravel services, while integrating into the same IMS ecosystem.
 *
 * Responsibilities:
 *   - Payment processing (charge, refund, void)
 *   - Payment method management
 *   - Saga compensating transactions (refunds)
 *   - Webhook notifications for payment events
 */

require('dotenv').config();

const express      = require('express');
const helmet       = require('helmet');
const cors         = require('cors');
const morgan       = require('morgan');
const rateLimit    = require('express-rate-limit');

const logger       = require('./utils/logger');
const errorHandler = require('./middleware/errorHandler');
const authMiddleware = require('./middleware/auth');
const tenantMiddleware = require('./middleware/tenant');

// Route modules
const healthRoutes  = require('./routes/health');
const paymentRoutes = require('./routes/payments');
const webhookRoutes = require('./routes/webhooks');

const app = express();

// ============================================================
// Security Middleware
// ============================================================
app.use(helmet());
app.use(cors({
  origin: process.env.CORS_ORIGINS?.split(',') || '*',
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Tenant-ID'],
}));

// Rate limiting
app.use(rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100,
  message: { success: false, message: 'Too many requests, please try again later.' },
}));

// ============================================================
// Request Parsing
// ============================================================
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(morgan('combined', { stream: { write: (msg) => logger.info(msg.trim()) } }));

// ============================================================
// Routes
// ============================================================

// Health checks (no auth)
app.use('/api/health', healthRoutes);

// Protected routes
app.use('/api/payments', authMiddleware, tenantMiddleware, paymentRoutes);
app.use('/api/webhooks', webhookRoutes);

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    message: `Route [${req.method} ${req.path}] not found.`,
    code: 'ROUTE_NOT_FOUND',
  });
});

// Global error handler
app.use(errorHandler);

// ============================================================
// Start Server
// ============================================================
const PORT = parseInt(process.env.PORT || '8004', 10);

const server = app.listen(PORT, () => {
  logger.info(`IMS Payment Service listening on port ${PORT}`, {
    service: 'payment-service',
    port: PORT,
    env: process.env.NODE_ENV,
  });
});

// Graceful shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM received - shutting down gracefully');
  server.close(() => {
    logger.info('Server closed');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  logger.info('SIGINT received - shutting down');
  server.close(() => process.exit(0));
});

module.exports = app;

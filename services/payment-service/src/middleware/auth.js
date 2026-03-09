'use strict';

/**
 * Auth Middleware.
 *
 * Validates JWT tokens issued by the Auth Service (Laravel Passport).
 * Tenant-aware: extracts tenant_id from token claims.
 */

const jwt = require('jsonwebtoken');
const logger = require('../utils/logger');

/**
 * @param {import('express').Request} req
 * @param {import('express').Response} res
 * @param {import('express').NextFunction} next
 */
module.exports = function authMiddleware(req, res, next) {
  const authHeader = req.headers.authorization;

  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({
      success: false,
      message: 'Authentication required.',
      code: 'UNAUTHENTICATED',
    });
  }

  const token = authHeader.slice(7);

  try {
    const publicKey = process.env.JWT_PUBLIC_KEY?.replace(/\\n/g, '\n');

    if (!publicKey) {
      logger.warn('JWT_PUBLIC_KEY not configured');
      return res.status(500).json({
        success: false,
        message: 'Authentication service misconfigured.',
      });
    }

    const decoded = jwt.verify(token, publicKey, {
      algorithms: ['RS256'],
    });

    req.user      = decoded;
    req.userId    = decoded.sub;
    req.tenantId  = decoded.tenant_id || req.headers['x-tenant-id'];

    next();
  } catch (error) {
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Access token expired.',
        code: 'TOKEN_EXPIRED',
      });
    }

    return res.status(401).json({
      success: false,
      message: 'Invalid access token.',
      code: 'TOKEN_INVALID',
    });
  }
};

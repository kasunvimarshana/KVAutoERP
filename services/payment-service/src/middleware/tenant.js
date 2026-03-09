'use strict';

/**
 * Tenant Middleware.
 *
 * Ensures a valid tenant context is present on every request.
 * Resolves tenant from X-Tenant-ID header or JWT claim.
 */

module.exports = function tenantMiddleware(req, res, next) {
  const tenantId = req.tenantId || req.headers['x-tenant-id'];

  if (!tenantId) {
    return res.status(400).json({
      success: false,
      message: 'Tenant context is required.',
      code: 'TENANT_REQUIRED',
    });
  }

  req.tenantId = tenantId;
  res.setHeader('X-Tenant-ID', tenantId);

  next();
};

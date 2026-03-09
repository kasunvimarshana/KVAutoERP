'use strict';

/**
 * Payment Routes.
 */

const express = require('express');
const router  = express.Router();
const PaymentController = require('../controllers/PaymentController');

// List payments
router.get('/', PaymentController.index);

// Get single payment
router.get('/:id', PaymentController.show);

// Charge (create payment)
router.post('/charge', PaymentController.charge);

// Refund (saga compensating transaction)
router.post('/:id/refund', PaymentController.refund);

module.exports = router;

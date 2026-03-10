'use strict';

const express = require('express');
const rateLimit = require('express-rate-limit');
const Product = require('../models/Product');

const router = express.Router();

// Route-level rate limiter
const routeLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 300,
  standardHeaders: true,
  legacyHeaders: false,
  message: { error: 'Too many requests, please try again later.' },
});

router.use(routeLimiter);

// GET /api/products - list all
router.get('/', async (req, res) => {
  try {
    const { category, page = 1, limit = 20 } = req.query;
    const filter = category ? { category } : {};
    const products = await Product.find(filter)
      .skip((page - 1) * limit)
      .limit(Number(limit))
      .lean({ virtuals: true });

    return res.json({ products, page: Number(page), limit: Number(limit) });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
});

// GET /api/products/:id
router.get('/:id', async (req, res) => {
  try {
    const product = await Product.findById(req.params.id).lean({ virtuals: true });
    if (!product) return res.status(404).json({ error: 'Product not found' });
    return res.json({ product });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
});

// POST /api/products - create
router.post('/', async (req, res) => {
  try {
    const { name, description, price, stock, category } = req.body;
    if (!name || price === undefined) {
      return res.status(422).json({ error: 'name and price are required' });
    }
    const product = await Product.create({ name, description, price, stock, category });
    return res.status(201).json({ product });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
});

// PUT /api/products/:id - update
router.put('/:id', async (req, res) => {
  try {
    const product = await Product.findByIdAndUpdate(
      req.params.id,
      { $set: req.body },
      { new: true, runValidators: true }
    );
    if (!product) return res.status(404).json({ error: 'Product not found' });
    return res.json({ product });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
});

// DELETE /api/products/:id
router.delete('/:id', async (req, res) => {
  try {
    const product = await Product.findByIdAndDelete(req.params.id);
    if (!product) return res.status(404).json({ error: 'Product not found' });
    return res.json({ message: 'Product deleted' });
  } catch (err) {
    return res.status(500).json({ error: err.message });
  }
});

module.exports = router;

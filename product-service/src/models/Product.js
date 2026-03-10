'use strict';

const mongoose = require('mongoose');

const productSchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: true,
      trim: true,
    },
    description: {
      type: String,
      default: '',
    },
    price: {
      type: Number,
      required: true,
      min: 0,
    },
    stock: {
      type: Number,
      required: true,
      min: 0,
      default: 0,
    },
    reserved: {
      type: Number,
      default: 0,
      min: 0,
    },
    category: {
      type: String,
      default: 'general',
    },
  },
  {
    timestamps: true,
    toJSON: { virtuals: true },
  }
);

// Virtual: available = stock - reserved
productSchema.virtual('available').get(function () {
  return this.stock - this.reserved;
});

module.exports = mongoose.model('Product', productSchema);

const mongoose = require('mongoose');

const inventorySchema = new mongoose.Schema({
  product_id: { type: Number, required: true },
  product_name: { type: String, required: true, index: true },
  product_sku: { type: String, required: true },
  quantity: { type: Number, required: true, default: 0, min: 0 },
  warehouse_location: { type: String, default: 'DEFAULT' },
  last_updated: { type: Date, default: Date.now },
}, { timestamps: true });

inventorySchema.index({ product_name: 1 });
inventorySchema.index({ product_sku: 1 });

module.exports = mongoose.model('Inventory', inventorySchema);

const Inventory = require('../models/Inventory');

class InventoryRepository {
  async findAll(filters = {}) {
    const query = {};
    if (filters.product_name) {
      query.product_name = { $regex: filters.product_name, $options: 'i' };
    }
    if (filters.product_sku) {
      query.product_sku = filters.product_sku;
    }
    const page = parseInt(filters.page) || 1;
    const limit = parseInt(filters.per_page) || 15;
    const skip = (page - 1) * limit;
    const [data, total] = await Promise.all([
      Inventory.find(query).skip(skip).limit(limit).sort({ createdAt: -1 }),
      Inventory.countDocuments(query),
    ]);
    return { data, total, page, limit };
  }

  async findById(id) {
    return Inventory.findById(id);
  }

  async findByProductName(productName) {
    return Inventory.find({ product_name: { $regex: productName, $options: 'i' } });
  }

  async create(data) {
    return Inventory.create(data);
  }

  async update(id, data) {
    return Inventory.findByIdAndUpdate(id, { ...data, last_updated: new Date() }, { new: true, runValidators: true });
  }

  async updateByProductName(productName, data) {
    return Inventory.updateMany(
      { product_name: { $regex: productName, $options: 'i' } },
      { ...data, last_updated: new Date() }
    );
  }

  async delete(id) {
    return Inventory.findByIdAndDelete(id);
  }

  async deleteByProductName(productName) {
    return Inventory.deleteMany({ product_name: productName });
  }

  async upsertByProductId(productId, data) {
    return Inventory.findOneAndUpdate(
      { product_id: productId },
      { ...data, last_updated: new Date() },
      { upsert: true, new: true, runValidators: true }
    );
  }
}

module.exports = new InventoryRepository();

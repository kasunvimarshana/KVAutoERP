const inventoryRepository = require('../repositories/inventoryRepository');

class InventoryService {
  async getAllInventory(filters = {}) {
    return inventoryRepository.findAll(filters);
  }

  async getInventoryItem(id) {
    const item = await inventoryRepository.findById(id);
    if (!item) throw Object.assign(new Error('Inventory item not found'), { statusCode: 404 });
    return item;
  }

  async getInventoryByProductName(productName) {
    return inventoryRepository.findByProductName(productName);
  }

  async createInventory(data) {
    return inventoryRepository.create(data);
  }

  async updateInventory(id, data) {
    const item = await inventoryRepository.update(id, data);
    if (!item) throw Object.assign(new Error('Inventory item not found'), { statusCode: 404 });
    return item;
  }

  async updateInventoryByProductName(productName, data) {
    return inventoryRepository.updateByProductName(productName, data);
  }

  async deleteInventory(id) {
    const item = await inventoryRepository.delete(id);
    if (!item) throw Object.assign(new Error('Inventory item not found'), { statusCode: 404 });
    return item;
  }

  async deleteInventoryByProductName(productName) {
    return inventoryRepository.deleteByProductName(productName);
  }

  async handleProductCreated(productData) {
    return inventoryRepository.upsertByProductId(productData.id, {
      product_id: productData.id,
      product_name: productData.name,
      product_sku: productData.sku,
      quantity: productData.stock_quantity || 0,
    });
  }

  async handleProductUpdated(productData) {
    return inventoryRepository.upsertByProductId(productData.id, {
      product_id: productData.id,
      product_name: productData.name,
      product_sku: productData.sku,
      quantity: productData.stock_quantity || 0,
    });
  }

  async handleProductDeleted(productData) {
    return inventoryRepository.deleteByProductName(productData.name);
  }
}

module.exports = new InventoryService();

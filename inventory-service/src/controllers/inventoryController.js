const inventoryService = require('../services/inventoryService');

class InventoryController {
  async index(req, res, next) {
    try {
      const result = await inventoryService.getAllInventory(req.query);
      res.json({
        success: true,
        data: result.data,
        meta: {
          total: result.total,
          page: result.page,
          per_page: result.limit,
          last_page: Math.ceil(result.total / result.limit),
        },
      });
    } catch (err) { next(err); }
  }

  async show(req, res, next) {
    try {
      const item = await inventoryService.getInventoryItem(req.params.id);
      res.json({ success: true, data: item });
    } catch (err) { next(err); }
  }

  async store(req, res, next) {
    try {
      const item = await inventoryService.createInventory(req.body);
      res.status(201).json({ success: true, data: item });
    } catch (err) { next(err); }
  }

  async update(req, res, next) {
    try {
      const item = await inventoryService.updateInventory(req.params.id, req.body);
      res.json({ success: true, data: item });
    } catch (err) { next(err); }
  }

  async updateByProductName(req, res, next) {
    try {
      const result = await inventoryService.updateInventoryByProductName(req.params.productName, req.body);
      res.json({ success: true, data: result });
    } catch (err) { next(err); }
  }

  async destroy(req, res, next) {
    try {
      await inventoryService.deleteInventory(req.params.id);
      res.json({ success: true, message: 'Inventory item deleted' });
    } catch (err) { next(err); }
  }

  async destroyByProductName(req, res, next) {
    try {
      const result = await inventoryService.deleteInventoryByProductName(req.params.productName);
      res.json({ success: true, data: result, message: 'Inventory items deleted' });
    } catch (err) { next(err); }
  }

  async getByProductName(req, res, next) {
    try {
      const items = await inventoryService.getInventoryByProductName(req.params.productName);
      res.json({ success: true, data: items });
    } catch (err) { next(err); }
  }
}

module.exports = new InventoryController();

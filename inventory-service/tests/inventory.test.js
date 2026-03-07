const request = require('supertest');
const mongoose = require('mongoose');

// Mock mongoose before requiring app
jest.mock('../src/config/database', () => ({
  connectDatabase: jest.fn().mockResolvedValue(undefined),
}));
jest.mock('../src/config/rabbitmq', () => ({
  connectRabbitMQ: jest.fn().mockResolvedValue(null),
  getChannel: jest.fn().mockReturnValue(null),
}));
jest.mock('../src/subscribers/productEventSubscriber', () => ({
  startSubscriber: jest.fn().mockResolvedValue(undefined),
}));

const inventoryRepository = require('../src/repositories/inventoryRepository');

// Mock all repository methods
jest.mock('../src/repositories/inventoryRepository', () => ({
  findAll: jest.fn(),
  findById: jest.fn(),
  findByProductName: jest.fn(),
  create: jest.fn(),
  update: jest.fn(),
  updateByProductName: jest.fn(),
  delete: jest.fn(),
  deleteByProductName: jest.fn(),
  upsertByProductId: jest.fn(),
}));

const app = require('../src/app');

describe('Inventory API', () => {
  beforeEach(() => jest.clearAllMocks());

  test('GET /health returns ok', async () => {
    const res = await request(app).get('/health');
    expect(res.status).toBe(200);
    expect(res.body.status).toBe('ok');
  });

  test('GET /api/inventory returns list', async () => {
    inventoryRepository.findAll.mockResolvedValue({ data: [], total: 0, page: 1, limit: 15 });
    const res = await request(app).get('/api/inventory');
    expect(res.status).toBe(200);
    expect(res.body.success).toBe(true);
  });

  test('POST /api/inventory creates item', async () => {
    const item = { _id: '123', product_id: 1, product_name: 'Test', product_sku: 'SKU-1', quantity: 10 };
    inventoryRepository.create.mockResolvedValue(item);
    const res = await request(app).post('/api/inventory').send(item);
    expect(res.status).toBe(201);
    expect(res.body.success).toBe(true);
  });

  test('GET /api/inventory/:id returns 404 for missing item', async () => {
    inventoryRepository.findById.mockResolvedValue(null);
    // Use a valid MongoDB ObjectId format for the test
    const res = await request(app).get('/api/inventory/507f1f77bcf86cd799439011');
    expect(res.status).toBe(404);
  });

  test('DELETE /api/inventory/product/:productName deletes by product name', async () => {
    inventoryRepository.deleteByProductName.mockResolvedValue({ deletedCount: 2 });
    const res = await request(app).delete('/api/inventory/product/TestProduct');
    expect(res.status).toBe(200);
    expect(res.body.success).toBe(true);
  });
});

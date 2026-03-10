'use strict';

/**
 * Unit tests for Product Service routes.
 * These tests mock Mongoose so no MongoDB connection is needed.
 */

jest.mock('mongoose', () => {
  const actual = jest.requireActual('mongoose');
  return {
    ...actual,
    connect: jest.fn().mockResolvedValue(undefined),
    connection: { readyState: 1 },
    model: jest.fn().mockReturnValue({}),
    Schema: actual.Schema,
  };
});

// Mock the consumer so we don't connect to RabbitMQ
jest.mock('../src/consumers/orderConsumer', () => ({
  startConsumer: jest.fn().mockResolvedValue(undefined),
}));

// Mock Product model methods
const mockProduct = {
  _id: '6576b7f4e0f2a1234567890a',
  name: 'Test Widget',
  price: 19.99,
  stock: 50,
  reserved: 0,
  available: 50,
};

jest.mock('../src/models/Product', () => {
  const findMock = jest.fn().mockReturnValue({
    skip: jest.fn().mockReturnThis(),
    limit: jest.fn().mockReturnThis(),
    lean: jest.fn().mockResolvedValue([mockProduct]),
  });
  const findByIdMock = jest.fn().mockReturnValue({
    lean: jest.fn().mockResolvedValue(mockProduct),
  });
  const createMock = jest.fn().mockResolvedValue(mockProduct);

  const ProductMock = {
    find: findMock,
    findById: findByIdMock,
    create: createMock,
    findByIdAndUpdate: jest.fn().mockResolvedValue(mockProduct),
    findByIdAndDelete: jest.fn().mockResolvedValue(mockProduct),
  };
  return ProductMock;
});

const request = require('supertest');
const app = require('../src/index');

describe('Product Service', () => {
  test('GET /health returns 200', async () => {
    const res = await request(app).get('/health');
    expect(res.statusCode).toBe(200);
    expect(res.body.status).toBe('ok');
    expect(res.body.service).toBe('product-service');
  });

  test('GET /api/products returns product list', async () => {
    const res = await request(app).get('/api/products');
    expect(res.statusCode).toBe(200);
    expect(res.body).toHaveProperty('products');
    expect(Array.isArray(res.body.products)).toBe(true);
  });

  test('GET /api/products/:id returns a product', async () => {
    const res = await request(app).get('/api/products/6576b7f4e0f2a1234567890a');
    expect(res.statusCode).toBe(200);
    expect(res.body).toHaveProperty('product');
  });

  test('POST /api/products with valid data returns 201', async () => {
    const res = await request(app)
      .post('/api/products')
      .send({ name: 'New Widget', price: 9.99, stock: 100 });
    expect(res.statusCode).toBe(201);
    expect(res.body).toHaveProperty('product');
  });

  test('POST /api/products without name returns 422', async () => {
    const res = await request(app)
      .post('/api/products')
      .send({ price: 9.99 });
    expect(res.statusCode).toBe(422);
    expect(res.body).toHaveProperty('error');
  });

  test('Unknown route returns 404', async () => {
    const res = await request(app).get('/api/nonexistent');
    expect(res.statusCode).toBe(404);
  });
});

'use strict';

const request = require('supertest');
const jwt = require('jsonwebtoken');
const app = require('../src/index');

const JWT_SECRET = 'user_service_jwt_secret_key_2024';

function makeToken(payload = {}) {
  return jwt.sign(
    { sub: 1, email: 'test@example.com', roles: ['customer'], ...payload },
    JWT_SECRET,
    { expiresIn: '1h' }
  );
}

describe('API Gateway', () => {
  test('GET /health returns 200', async () => {
    const res = await request(app).get('/health');
    expect(res.statusCode).toBe(200);
    expect(res.body.status).toBe('ok');
    expect(res.body.gateway).toBe('api-gateway');
  });

  test('Protected route without token returns 401', async () => {
    const res = await request(app).get('/api/orders/123');
    expect(res.statusCode).toBe(401);
  });

  test('Unknown route without token returns 401 (auth before 404)', async () => {
    const res = await request(app).get('/api/unknown');
    expect(res.statusCode).toBe(401);
  });

  test('Completely unknown top-level path with valid token returns 404', async () => {
    const token = makeToken();
    const res = await request(app)
      .get('/nonexistent-path')
      .set('Authorization', `Bearer ${token}`);
    expect(res.statusCode).toBe(404);
  });

  test('Invalid token returns 401', async () => {
    const res = await request(app)
      .get('/api/products')
      .set('Authorization', 'Bearer invalid_token');
    expect(res.statusCode).toBe(401);
  });

  test('Valid token passes auth middleware', async () => {
    const token = makeToken();
    // Will get a proxy error (502) since downstream is not running, but NOT 401
    const res = await request(app)
      .get('/api/users/me')
      .set('Authorization', `Bearer ${token}`);
    expect(res.statusCode).not.toBe(401);
  });
});

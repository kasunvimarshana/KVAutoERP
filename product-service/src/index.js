'use strict';

require('dotenv').config();

const express   = require('express');
const mongoose  = require('mongoose');
const morgan    = require('morgan');
const cors      = require('cors');

const productsRouter = require('./routes/products');
const { startConsumer } = require('./consumers/orderConsumer');

const app         = express();
const PORT        = process.env.PORT        || 8000;
const MONGO_URI   = process.env.MONGO_URI   || 'mongodb://localhost:27017/products_db';
const RABBITMQ_URL = process.env.RABBITMQ_URL || 'amqp://localhost';

// ─────────────────────────────────────────────
// Middleware
// ─────────────────────────────────────────────
app.use(cors());
app.use(morgan('combined'));
app.use(express.json());

// ─────────────────────────────────────────────
// Routes
// ─────────────────────────────────────────────
app.get('/health', (req, res) => {
  res.json({
    status:  'ok',
    service: 'product-service',
    db:      mongoose.connection.readyState === 1 ? 'connected' : 'disconnected',
  });
});

app.use('/api/products', productsRouter);

app.use((req, res) => res.status(404).json({ error: 'Not found' }));

// ─────────────────────────────────────────────
// Bootstrap
// ─────────────────────────────────────────────
async function bootstrap() {
  await mongoose.connect(MONGO_URI);
  console.log('[product-service] MongoDB connected');

  // Start RabbitMQ saga consumer (with retry)
  const tryConsumer = async (retries = 0) => {
    try {
      await startConsumer(RABBITMQ_URL);
    } catch (err) {
      if (retries < 10) {
        console.warn(`[product-service] RabbitMQ not ready, retry ${retries + 1}...`);
        await new Promise(r => setTimeout(r, 5000));
        return tryConsumer(retries + 1);
      }
      console.error('[product-service] Could not connect to RabbitMQ:', err.message);
    }
  };
  tryConsumer();

  app.listen(PORT, () => {
    console.log(`[product-service] Listening on port ${PORT}`);
  });
}

if (require.main === module) {
  bootstrap().catch(err => {
    console.error('[product-service] Fatal error:', err);
    process.exit(1);
  });
}

module.exports = app;

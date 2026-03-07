require('dotenv').config();
const express = require('express');
const { connectDatabase } = require('./config/database');
const { connectRabbitMQ } = require('./config/rabbitmq');
const { startSubscriber } = require('./subscribers/productEventSubscriber');
const inventoryRoutes = require('./routes/inventoryRoutes');
const errorHandler = require('./middleware/errorHandler');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.get('/health', (req, res) => res.json({ status: 'ok', service: 'inventory-service' }));
app.use('/api/inventory', inventoryRoutes);
app.use(errorHandler);

const PORT = process.env.PORT || 3000;

const bootstrap = async () => {
  await connectDatabase();
  const channel = await connectRabbitMQ();
  await startSubscriber(channel);
  app.listen(PORT, () => console.log(`Inventory service running on port ${PORT}`));
};

if (require.main === module) {
  bootstrap().catch(console.error);
}

module.exports = app;

const mongoose = require('mongoose');

const connectDatabase = async () => {
  const uri = process.env.MONGODB_URI || 'mongodb://mongodb:27017/inventory';
  await mongoose.connect(uri);
  console.log('Connected to MongoDB');
};

module.exports = { connectDatabase };

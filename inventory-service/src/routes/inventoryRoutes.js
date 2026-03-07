const express = require('express');
const router = express.Router();
const inventoryController = require('../controllers/inventoryController');

router.get('/', inventoryController.index.bind(inventoryController));
router.post('/', inventoryController.store.bind(inventoryController));
router.get('/product/:productName', inventoryController.getByProductName.bind(inventoryController));
router.put('/product/:productName', inventoryController.updateByProductName.bind(inventoryController));
router.delete('/product/:productName', inventoryController.destroyByProductName.bind(inventoryController));
router.get('/:id', inventoryController.show.bind(inventoryController));
router.put('/:id', inventoryController.update.bind(inventoryController));
router.delete('/:id', inventoryController.destroy.bind(inventoryController));

module.exports = router;

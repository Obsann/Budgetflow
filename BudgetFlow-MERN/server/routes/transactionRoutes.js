const express = require('express');
const router = express.Router();
const {
    getTransactions,
    getTransaction,
    createTransaction,
    updateTransaction,
    deleteTransaction
} = require('../controllers/transactionController');
const { protect } = require('../middleware/auth');
const { validate } = require('../middleware/validate');

router.use(protect); // All routes require authentication

router.route('/')
    .get(getTransactions)
    .post(validate('transaction'), createTransaction);

router.route('/:id')
    .get(getTransaction)
    .put(validate('transaction'), updateTransaction)
    .delete(deleteTransaction);

module.exports = router;

const express = require('express');
const router = express.Router();
const {
    getAllocations,
    createAllocation,
    updateAllocation,
    deleteAllocation,
    togglePaid
} = require('../controllers/allocationController');
const { protect } = require('../middleware/auth');
const { validate } = require('../middleware/validate');

router.use(protect); // All routes require authentication

router.route('/')
    .get(getAllocations)
    .post(validate('allocation'), createAllocation);

router.route('/:id')
    .put(validate('allocation'), updateAllocation)
    .delete(deleteAllocation);

router.patch('/:id/toggle', togglePaid);

module.exports = router;

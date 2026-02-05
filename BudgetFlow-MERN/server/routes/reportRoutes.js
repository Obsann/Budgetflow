const express = require('express');
const router = express.Router();
const { getReport, exportCSV } = require('../controllers/reportController');
const { protect } = require('../middleware/auth');

router.get('/', protect, getReport);
router.get('/export', protect, exportCSV);

module.exports = router;

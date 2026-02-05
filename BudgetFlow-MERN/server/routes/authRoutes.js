const express = require('express');
const router = express.Router();
const { register, login, getMe, logout } = require('../controllers/authController');
const { protect } = require('../middleware/auth');
const { loginLimiter } = require('../middleware/rateLimit');
const { validate } = require('../middleware/validate');

router.post('/register', validate('register'), register);
router.post('/login', loginLimiter, validate('login'), login);
router.get('/me', protect, getMe);
router.post('/logout', protect, logout);

module.exports = router;

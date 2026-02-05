const Joi = require('joi');

// Validation schemas
const schemas = {
    register: Joi.object({
        username: Joi.string().min(3).max(50).required(),
        email: Joi.string().email().required(),
        password: Joi.string().min(6).required()
    }),

    login: Joi.object({
        email: Joi.string().email().required(),
        password: Joi.string().required()
    }),

    transaction: Joi.object({
        category: Joi.string().required(),
        amount: Joi.number().positive().required(),
        type: Joi.string().valid('income', 'expense').required(),
        description: Joi.string().max(255).allow(''),
        transactionDate: Joi.date().required()
    }),

    allocation: Joi.object({
        name: Joi.string().max(100).required(),
        amount: Joi.number().positive().required(),
        isPaid: Joi.boolean()
    })
};

// Validation middleware factory
const validate = (schemaName) => {
    return (req, res, next) => {
        const schema = schemas[schemaName];
        if (!schema) {
            return res.status(500).json({
                success: false,
                message: 'Validation schema not found'
            });
        }

        const { error, value } = schema.validate(req.body, { abortEarly: false });

        if (error) {
            const errors = error.details.map(detail => detail.message);
            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors
            });
        }

        req.validatedBody = value;
        next();
    };
};

module.exports = { validate, schemas };

const mongoose = require('mongoose');
const dotenv = require('dotenv');
const User = require('../models/User');

dotenv.config();

const debugDB = async () => {
    try {
        console.log('--------------------------------------------------');
        console.log('DEBUG SCRIPT START');
        console.log('URI:', process.env.MONGO_URI);

        await mongoose.connect(process.env.MONGO_URI);
        console.log('Connected to MongoDB successfully.');

        const users = await User.find({});
        console.log(`User Count: ${users.length}`);

        const fs = require('fs');
        const output = [];
        output.push(`User Count: ${users.length}`);

        if (users.length > 0) {
            output.push('Users found in DB:');
            output.push(JSON.stringify(users, null, 2));
        } else {
            output.push('NO USERS FOUND in this database.');
        }
        fs.writeFileSync('debug_output.txt', output.join('\n'));
        console.log('Written to debug_output.txt');
        process.exit(0);
    } catch (error) {
        console.error('FATAL ERROR:', error);
        process.exit(1);
    }
};

debugDB();

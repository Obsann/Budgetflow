# 💼 BudgetFlow MERN

**A Modern Personal Budget & Expense Tracking Web App (MongoDB, Express, React, Node.js)**

BudgetFlow MERN is a full-stack expense tracker built with the MERN stack, migrated from the original PHP/MySQL version. It helps users record, manage, and analyze their finances with a beautiful glassmorphism UI.

---

## 🚀 Features

- **User Authentication**: JWT-based registration and login with secure password hashing
- **Transaction Management**: Full CRUD for income and expense records
- **Budget Allocations**: Zero-based budgeting with paid/unpaid tracking
- **Dashboard**: Visual statistics with charts and category breakdowns
- **Reports**: Filtered data views with CSV export functionality
- **Security**: Rate limiting, input validation, and protected API routes

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | React 18, Vite, React Router, Recharts, Axios |
| **Backend** | Node.js, Express.js |
| **Database** | MongoDB with Mongoose ODM |
| **Auth** | JWT (JSON Web Tokens), bcryptjs |
| **Security** | express-rate-limit, Joi validation |
| **Styling** | Custom CSS with glassmorphism design |

---

## 📥 Installation

### Prerequisites
- Node.js 18+ installed
- MongoDB installed locally or MongoDB Atlas account

### 1. Backend Setup

```bash
cd BudgetFlow-MERN/server

# Install dependencies
npm install

# Configure environment (edit .env file with your MongoDB URI)
# Default: mongodb://localhost:27017/budgetflow

# Start development server
npm run dev
```

The server will run on `http://localhost:5000`

### 2. Frontend Setup

```bash
cd BudgetFlow-MERN/client

# Install dependencies
npm install

# Start development server
npm run dev
```

The frontend will run on `http://localhost:5173`

### 3. Seed Categories

On first load, categories are automatically seeded. Or manually seed via:
```bash
curl -X POST http://localhost:5000/api/categories/seed
```

---

## 📁 Project Structure

```
BudgetFlow-MERN/
├── client/                    # React frontend
│   ├── src/
│   │   ├── components/        # Navbar, reusable components
│   │   ├── pages/             # Login, Register, Dashboard, etc.
│   │   ├── context/           # AuthContext for state management
│   │   ├── services/          # API service layer
│   │   └── App.jsx            # Main app with routing
│   └── package.json
├── server/                    # Express backend
│   ├── config/                # MongoDB connection
│   ├── middleware/            # Auth, rate limiting, validation
│   ├── models/                # Mongoose schemas
│   ├── routes/                # API routes
│   ├── controllers/           # Business logic
│   └── server.js              # Express server entry
└── README.md
```

---

## 🔒 API Endpoints

| Route | Methods | Description |
|-------|---------|-------------|
| `/api/auth/register` | POST | User registration |
| `/api/auth/login` | POST | User login |
| `/api/auth/me` | GET | Get current user |
| `/api/transactions` | GET, POST | List/Create transactions |
| `/api/transactions/:id` | GET, PUT, DELETE | Single transaction CRUD |
| `/api/allocations` | GET, POST | List/Create allocations |
| `/api/allocations/:id` | PUT, DELETE | Update/Delete allocation |
| `/api/allocations/:id/toggle` | PATCH | Toggle paid status |
| `/api/dashboard` | GET | Dashboard statistics |
| `/api/reports` | GET | Filtered report data |
| `/api/reports/export` | GET | CSV export |
| `/api/categories` | GET | List categories |

---

## 📷 Screenshots

*Run the application to see the beautiful glassmorphism UI!*

---

## 📄 License

MIT License

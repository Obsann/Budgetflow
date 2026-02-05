# 💼 BudgetFlow

**A Personal Budget & Expense Tracking Web App (PHP + MySQL)**
<<<<<<< HEAD
=======

BudgetFlow is a **student-friendly expense tracker** that helps users record, manage, search, and export their budgets using a local PHP/MySQL stack. It’s designed for clarity, practical database interaction, and foundational web application structure — ideal for portfolios, internships, and early backend engineering roles.

---

## 🚀 Overview

BudgetFlow solves a common personal finance challenge: **tracking income and expenses over time in a simple web UI**.

It demonstrates:
- User authentication and session handling
- CRUD operations on expense entries
- Report generation and CSV export
- Interaction with a relational database (MySQL)

This project highlights practical use of **core backend technologies** without external frameworks.

---

## 🧠 Features

- **User registration and login**  
  Secure session-based authentication.

- **Add / view / update / delete expenses**  
  Full CRUD functionality for expense records.

- **Search and filtering**  
  Filter expenses based on keywords or dates.

- **Report export**  
  Download expense reports for offline use.

- **Responsive UI skeleton**  
  Basic styling to support usability.

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP (Vanilla) |
| Database | MySQL |
| Frontend | HTML, CSS |
| Server | XAMPP / WAMP / MAMP |
| Version control | Git + GitHub |

---

## 📥 Installation

### Setup Instructions

1. **Clone the repository**

   ```sh
   git clone https://github.com/Obsann/Budgetflow.git
>>>>>>> 7310a829ee246569a0de980013aad71663049881

BudgetFlow is a **student-friendly expense tracker** that helps users record, manage, search, and export their budgets using a local PHP/MySQL stack. It’s designed for clarity, practical database interaction, and foundational web application structure — ideal for portfolios, internships, and early backend engineering roles.

---

## 🚀 Overview

BudgetFlow solves a common personal finance challenge: **tracking income and expenses over time in a simple web UI**.

> **⚠️ Portfolio Demo Notice**: The live deployment of this project is a **Frontend-Only Demo**. It showcases the UI/UX and design. For full backend functionality (Database, Login, CRUD), please follow the [Installation](#installation) instructions to run it locally.

It demonstrates:
- User authentication and session handling
- CRUD operations on expense entries
- Report generation and CSV export
- Interaction with a relational database (MySQL)

This project highlights practical use of **core backend technologies** without external frameworks.

---

## 🧠 Features

- **User registration and login**  
  Secure session-based authentication with CSRF protection and Rate Limiting.

- **Add / view / update / delete expenses**  
  Full CRUD functionality for expense records.

- **Zero-Based Budgeting**  
  Plan your income with "Budget Allocations" before you spend.

- **Search and filtering**  
  Filter expenses based on keywords, dates, or categories.

- **Report export**  
  Download expense reports for offline use (CSV).

- **Responsive UI**  
  Modern glassmorphism design with Tailwind CSS.

---

## 🔒 Security & Architecture Design

This application goes beyond basic functionality to demonstrate **secure coding practices** essential for production-grade software:

### 1. Security Defenses
*   **CSRF Protection**: All mutating requests (POST, PUT, DELETE) are protected by a cryptographically secure token. The token is generated on login, stored in the frontend, and validated by backend middleware.
*   **Rate Limiting**: Custom implementation blocks IP addresses after **5 failed login attempts** in 15 minutes to prevent brute-force attacks.
*   **Input Sanitization**: All incoming data is rigorously cleaned and validated (type checking, format verification) before processing.
*   **SQL Injection Prevention**: 100% adherence to **PDO Prepared Statements**.

### 2. Architecture: "Headless" Monolith
*   **Frontend-Backend Separation**: The frontend (HTML/JS) is decoupled from the backend (PHP API). They communicate strictly via JSON APIs.
*   **Centralized Middleware**: A custom `middleware.php` handles all cross-cutting concerns (Authentication, CORS, CSRF, Headers) in a single place (DRY Principle).
*   **Zero-Based Budgeting Logic**: The database schema and business logic enforce financial planning by distinguishing between *Planned Budgets* (Allocations) and *Actual Expenses* (Transactions).

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8+ (Vanilla, PDO, API-First) |
| Database | MySQL |
| Frontend | HTML5, Vanilla JavaScript, Tailwind CSS |
| Server | XAMPP / WAMP / MAMP |
| Version control | Git + GitHub |

---

## 📥 Installation

### 1. Setup Database
1.  Start your MySQL module (e.g., via XAMPP Control Panel).
2.  Open your database management tool (usually [http://localhost/phpmyadmin](http://localhost/phpmyadmin)).
3.  Create a new empty database named `budgetflow_db`.
4.  Import the schema file:
    *   Go to the **Import** tab.
    *   Choose file: `backend/database/schema.sql` (and `backend/sql/schema_update.sql` if upgrading) from this project folder.
    *   Click **Go**.

### 2. Setup Project
1.  Move this entire `BudgetFlow` folder into your server's web root directory.
    *   **XAMPP**: `C:\xampp\htdocs\`
    *   **WAMP**: `C:\wamp64\www\`
2.  Configuration:
    *   Review `backend/includes/db.php`.
    *   Ensure credentials match your database env (default for XAMPP is user: `root`, password: ``).
    *   (Optional) Copy `backend/.env.example` to `backend/.env` for secure config.

### 3. Application Access
*   Open your browser and navigate to:
    `http://localhost/BudgetFlow/frontend/index.html`
*   You will be redirected to the Login/Register page.

## Login Credentials
*   Users register their own accounts.
*   Once registered, use those credentials to login.

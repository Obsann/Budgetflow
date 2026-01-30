# 💼 BudgetFlow

**A Personal Budget & Expense Tracking Web App (PHP + MySQL)**

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

## How to Run

### 1. Requirements
*   A localized server stack like **XAMPP**, **WAMP**, or **MAMP**.
    *   **PHP 8+**
    *   **MySQL Database**

### 2. Setup Database
1.  Start your MySQL module (e.g., via XAMPP Control Panel).
2.  Open your database management tool (usually [http://localhost/phpmyadmin](http://localhost/phpmyadmin)).
3.  Create a new empty database named `budgetflow_db`.
4.  Import the schema file:
    *   Go to the **Import** tab.
    *   Choose file: `database/schema.sql` from this project folder.
    *   Click **Go**.

### 3. Setup Project
1.  Move this entire `BudgetFlow` folder into your server's web root directory.
    *   **XAMPP**: `C:\xampp\htdocs\`
    *   **WAMP**: `C:\wamp64\www\`
2.  Configuration:
    *   Open `includes/db.php`.
    *   Ensure `$user` and `$pass` match your database credentials (default for XAMPP is user: `root`, password: ``).

### 4. Application Access
*   Open your browser and navigate to:
    `http://localhost/BudgetFlow/register.php`

## Login Credentials
*   Users register their own accounts.
*   Once registered, use those credentials to login.

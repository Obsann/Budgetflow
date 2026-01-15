# BudgetFlow

A student expense tracker built with PHP and MySQL.

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

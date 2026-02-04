<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a; /* Slate 900 */
            color: #f8fafc;
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">

<?php if (isset($_SESSION['user_id'])): ?>
    <nav class="glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-blue-500 hover:from-teal-300 hover:to-blue-400 transition">
                        BudgetFlow
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="dashboard.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/10">Dashboard</a>
                        <a href="view_all.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/10">Transactions</a>
                        <a href="create.php" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-medium transition shadow-lg shadow-blue-500/30">Add Expense</a>
                        <a href="search.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/10">Search</a>
                        <a href="report.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/10">Reports</a>
                    </div>
                </div>
                <div>
                   <a href="logout.php" class="text-red-400 hover:text-red-300 text-sm font-medium transition">Logout</a>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="flex-grow container mx-auto px-4 py-8">

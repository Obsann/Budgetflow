<?php
// login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$username = '';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors['login'] = "Please enter both username and password";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            redirect('dashboard.php');
        } else {
            $errors['login'] = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BudgetFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-900 bg-gradient-to-br from-gray-900 to-black">

    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-blue-500">BudgetFlow</h1>
            <p class="text-gray-400 mt-2">Sign in to manage your budget</p>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-8 shadow-2xl">
            <?php if (isset($errors['login'])): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 px-4 py-3 rounded mb-6 text-sm">
                    <?php echo $errors['login']; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 rounded-lg shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm">
                    Don't have an account? <a href="register.php" class="text-blue-400 hover:text-blue-300 font-medium">Register here</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>

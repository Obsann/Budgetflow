<?php
// register.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$username = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username)) {
        $errors['username'] = "Username is required";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors['username'] = "Username already taken";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            $success = "Registration successful! You can now login.";
            $username = '';
        } catch (PDOException $e) {
            $errors['general'] = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BudgetFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-900 bg-gradient-to-br from-gray-900 to-black">

    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-blue-500">BudgetFlow</h1>
            <p class="text-gray-400 mt-2">Create your account</p>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-8 shadow-2xl">
            <?php if ($success): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-500 px-4 py-3 rounded mb-6 text-sm text-center">
                    <?php echo $success; ?> <br>
                    <a href="login.php" class="underline font-bold">Login here</a>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    <?php echo display_error($errors, 'username'); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    <?php echo display_error($errors, 'password'); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" 
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    <?php echo display_error($errors, 'confirm_password'); ?>
                </div>
                
                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-500 text-white font-semibold py-3 rounded-lg shadow-lg shadow-teal-500/30 transition transform hover:scale-[1.02] mt-4">
                    Register
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm">
                    Already have an account? <a href="login.php" class="text-blue-400 hover:text-blue-300 font-medium">Login here</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>

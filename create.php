<?php
// create.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$amount = '';
$description = '';
$category_id = '';
$date = date('Y-m-d');
$success = '';

$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = clean_input($_POST['amount']);
    $description = clean_input($_POST['description']);
    $category_id = clean_input($_POST['category_id']);
    $date = clean_input($_POST['transaction_date']);

    if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors['amount'] = "Enter a valid amount";
    if (empty($category_id)) $errors['category_id'] = "Select a category";
    if (empty($date)) $errors['date'] = "Select a date";

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO transactions (user_id, category_id, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $category_id, $amount, $description, $date]);
            $success = "Transaction added successfully!";
            $amount = ''; $description = ''; $category_id = ''; $date = date('Y-m-d');
        } catch (PDOException $e) {
            $errors['general'] = "Error: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="max-w-xl mx-auto">
    <div class="glass rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Add New Expense</h2>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="create.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Amount (ETB)</label>
                <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($amount); ?>" autofocus
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white text-lg font-mono placeholder-gray-600 focus:outline-none focus:border-blue-500 transition">
                <?php echo display_error($errors, 'amount'); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Category</label>
                <select name="category_id" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php echo display_error($errors, 'category_id'); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                <input type="text" name="description" value="<?php echo htmlspecialchars($description); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Date</label>
                <input type="date" name="transaction_date" value="<?php echo htmlspecialchars($date); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                <?php echo display_error($errors, 'date'); ?>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3 rounded-lg shadow-lg shadow-indigo-500/30 transition transform hover:scale-[1.02]">
                    Save Record
                </button>
                <a href="dashboard.php" class="px-6 py-3 rounded-lg border border-gray-600 text-gray-400 hover:text-white hover:bg-slate-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

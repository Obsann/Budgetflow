<?php
// update.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$success = '';

if (!isset($_GET['id'])) {
    redirect('view_all.php');
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $transaction = $stmt->fetch();
    if (!$transaction) redirect('view_all.php');
} catch (PDOException $e) { die($e->getMessage()); }

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$amount = $transaction['amount'];
$description = $transaction['description'];
$category_id = $transaction['category_id'];
$date = $transaction['transaction_date'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = clean_input($_POST['amount']);
    $description = clean_input($_POST['description']);
    $category_id = clean_input($_POST['category_id']);
    $date = clean_input($_POST['transaction_date']);

    if (empty($amount) || !is_numeric($amount) || $amount <= 0) $errors['amount'] = "Valid amount required";
    if (empty($category_id)) $errors['category_id'] = "Category required";
    if (empty($date)) $errors['date'] = "Date required";

    if (empty($errors)) {
        try {
            $sql = "UPDATE transactions SET category_id = ?, amount = ?, description = ?, transaction_date = ? WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category_id, $amount, $description, $date, $id, $_SESSION['user_id']]);
            $success = "Transaction updated!";
        } catch (PDOException $e) { $errors['general'] = $e->getMessage(); }
    }
}

include 'includes/header.php';
?>

<div class="max-w-xl mx-auto">
    <div class="glass rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Edit Expense</h2>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="update.php?id=<?php echo $id; ?>" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Amount (ETB)</label>
                <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($amount); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white text-lg font-mono focus:outline-none focus:border-blue-500 transition">
                <?php echo display_error($errors, 'amount'); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Category</label>
                <select name="category_id" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                <input type="text" name="description" value="<?php echo htmlspecialchars($description); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Date</label>
                <input type="date" name="transaction_date" value="<?php echo htmlspecialchars($date); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3 rounded-lg shadow-lg shadow-indigo-500/30 transition transform hover:scale-[1.02]">
                    Update
                </button>
                <a href="view_all.php" class="px-6 py-3 rounded-lg border border-gray-600 text-gray-400 hover:text-white hover:bg-slate-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
// search.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$results = [];
$search_performed = false;
$cat_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if (isset($_GET['search'])) {
    $search_performed = true;
    $sql = "SELECT t.*, c.name as category_name 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = :uid";
    $params = [':uid' => $_SESSION['user_id']];

    if (!empty($cat_id)) { $sql .= " AND t.category_id = :cat_id"; $params[':cat_id'] = $cat_id; }
    if (!empty($start_date)) { $sql .= " AND t.transaction_date >= :start_date"; $params[':start_date'] = $start_date; }
    if (!empty($end_date)) { $sql .= " AND t.transaction_date <= :end_date"; $params[':end_date'] = $end_date; }

    $sql .= " ORDER BY t.transaction_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-8">
    
    <!-- Search Controls -->
    <div class="glass rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Filter Transactions</h2>
        <form action="search.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-400 mb-1">Category</label>
                <select name="category_id" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <button type="submit" name="search" value="1" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2 rounded-lg shadow-lg shadow-blue-500/30 transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Results -->
    <?php if ($search_performed): ?>
        <div class="glass rounded-xl overflow-hidden p-8">
            <h3 class="text-xl font-bold text-white mb-4">Results</h3>
            
            <?php if (count($results) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-lefts">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-700">
                                <th class="pb-3">Date</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3">Description</th>
                                <th class="pb-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <?php $total = 0; foreach ($results as $row): $total += $row['amount']; ?>
                                <tr>
                                    <td class="py-3 text-gray-300"><?php echo date('M d, Y', strtotime($row['transaction_date'])); ?></td>
                                    <td><span class="text-sm text-blue-300"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                                    <td class="text-gray-400"><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="text-right font-bold text-white"><?php echo number_format($row['amount'], 2); ?> ETB</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-white/5 font-bold text-white">
                                <td colspan="3" class="p-4 text-right">Total Found:</td>
                                <td class="p-4 text-right text-emerald-400"><?php echo number_format($total, 2); ?> ETB</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No transactions found matching your criteria.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

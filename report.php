<?php
// report.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// 1. Fetch Actual Spending (Transactions)
$sql = "SELECT c.id as cat_id, c.name as category_name, SUM(t.amount) as total_amount 
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? 
        GROUP BY c.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$spending_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Planned Allocation (Active & Removed)
// We use conditional summation to separate active vs removed
$sql = "SELECT c.id as cat_id, c.name as category_name, 
        SUM(CASE WHEN ba.is_deleted = 0 THEN ba.amount ELSE 0 END) as active_planned,
        SUM(CASE WHEN ba.is_deleted = 1 THEN ba.amount ELSE 0 END) as removed_planned
        FROM budget_allocations ba 
        JOIN categories c ON ba.category_id = c.id 
        WHERE ba.user_id = ?
        GROUP BY c.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$allocation_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Merge Data
$report_data = [];
$cat_names = [];

// Process Spending
foreach ($spending_data as $row) {
    if (!isset($report_data[$row['cat_id']])) {
        $report_data[$row['cat_id']] = ['spent' => 0, 'planned' => 0, 'removed' => 0];
        $cat_names[$row['cat_id']] = $row['category_name'];
    }
    $report_data[$row['cat_id']]['spent'] += $row['total_amount'];
}

// Process Allocations
foreach ($allocation_data as $row) {
    if (!isset($report_data[$row['cat_id']])) {
        $report_data[$row['cat_id']] = ['spent' => 0, 'planned' => 0, 'removed' => 0];
        $cat_names[$row['cat_id']] = $row['category_name'];
    }
    $report_data[$row['cat_id']]['planned'] += $row['active_planned'];
    $report_data[$row['cat_id']]['removed'] += $row['removed_planned'];
}

$grand_total = 0;
foreach ($spending_data as $row) $grand_total += $row['total_amount'];

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-8">
    <div class="glass rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-2">Financial Report</h2>
        <p class="text-gray-400 mb-8">Comparison of Actual Spending vs. Planned Allocations</p>

        <?php if (count($report_data) > 0): ?>
            <div class="space-y-8">
                <!-- Grand Total -->
                <div class="flex justify-between items-end border-b border-gray-700 pb-4">
                    <span class="text-gray-400">Total Actual Spending</span>
                    <span class="text-4xl font-bold text-emerald-400"><?php echo number_format($grand_total, 2); ?> ETB</span>
                </div>

                <!-- Category Breakdown -->
                <div class="space-y-6">
                    <?php foreach ($report_data as $cat_id => $data): 
                        $percent = ($grand_total > 0) ? ($data['spent'] / $grand_total) * 100 : 0;
                        $cat_name = $cat_names[$cat_id];
                        $planned = $data['planned'];
                        $spent = $data['spent'];
                        $removed = $data['removed'];
                    ?>
                        <div class="bg-white/5 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-bold text-white"><?php echo htmlspecialchars($cat_name); ?></h3>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4 mb-2">
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">Actual Spent</span>
                                    <span class="text-lg font-mono text-white"><?php echo number_format($spent, 2); ?> ETB</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">Active Plan</span>
                                    <span class="text-lg font-mono text-blue-300"><?php echo number_format($planned, 2); ?> ETB</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase text-red-300/70">Removed (Loss)</span>
                                    <span class="text-lg font-mono text-red-400"><?php echo number_format($removed, 2); ?> ETB</span>
                                </div>
                            </div>

                            <!-- Visual Bar -->
                            <div class="relative w-full bg-slate-800 rounded-full h-2">
                                <!-- Spent Bar -->
                                <div class="absolute top-0 left-0 h-2 rounded-full bg-emerald-500 z-10" style="width: <?php echo min($percent, 100); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No data available yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

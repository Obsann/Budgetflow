
dashboard.php
wolFtegduB


<?php
// dashboard.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
// Fetch Categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
// --- Logic ---
// 1. Handle Allocation Adding
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_allocation'])) {
    $name = clean_input($_POST['allocation_name']);
    $amount = clean_input($_POST['allocation_amount']);
    $category_id = clean_input($_POST['category_id']);
    
    if (!empty($name) && is_numeric($amount)) {
        // Handle optional category
        $cat_val = !empty($category_id) ? $category_id : null;
        
        $stmt = $pdo->prepare("INSERT INTO budget_allocations (user_id, name, amount, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name, $amount, $cat_val]);
    }
    redirect('dashboard.php');
}
// 2. Handle Task Completion Toggle
if (isset($_GET['toggle_id'])) {
    $id = $_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE budget_allocations SET is_paid = NOT is_paid WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    redirect('dashboard.php');
}
// 3. Handle Soft Deletion
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // Logic: Mark as deleted, do NOT remove row.
    $stmt = $pdo->prepare("UPDATE budget_allocations SET is_deleted = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    redirect('dashboard.php');
}
// 4. Fetch Allocations (Only active ones for display)
$stmt = $pdo->prepare("
    SELECT ba.*, c.name as category_name 
    FROM budget_allocations ba
    LEFT JOIN categories c ON ba.category_id = c.id
    WHERE ba.user_id = ? AND ba.is_deleted = 0 
    ORDER BY ba.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$allocations = $stmt->fetchAll();
// 5. Calculate Total Allocation (Include DELETED items to keep the 'Remaining' logic consistent)
$stmt = $pdo->prepare("SELECT SUM(amount) FROM budget_allocations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_allocation = $stmt->fetchColumn() ?: 0;
// 6. Fetch "Total Income"
$stmt = $pdo->prepare("
    SELECT SUM(t.amount) 
    FROM transactions t 
    JOIN categories c ON t.category_id = c.id 
    WHERE t.user_id = ? AND c.name = 'Income'
");
$stmt->execute([$_SESSION['user_id']]);
$total_income = $stmt->fetchColumn() ?: 0;
// 7. Data for Chart.js (Spending by Category for Active User)
$stmt = $pdo->prepare("
    SELECT c.name, SUM(t.amount) as total
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ? AND c.name != 'Income'
    GROUP BY c.name
");
$stmt->execute([$_SESSION['user_id']]);
$chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$labels = [];
$data = [];
foreach ($chart_data as $row) {
    $labels[] = $row['name'];
    $data[] = $row['total'];
}
include 'includes/header.php';
?>
<!-- Main Grid Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left Column: Financial Overview -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Primary Card: Remaining Income -->
        <div class="relative overflow-hidden rounded-2xl glass p-8">
            <div class="relative z-10">
                <h2 class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Remaining Unallocated Income</h2>
                <div class="mt-2 flex items-baseline">
                    <span class="text-4xl font-bold text-white"><?php echo number_format($total_income - $total_allocation, 2); ?> ETB</span>
                    <span class="ml-2 text-sm text-gray-400">available</span>
                </div>
                <div class="mt-4">
                     <span class="text-sm text-gray-400">
                        Total Income: <span class="text-white"><?php echo number_format($total_income, 2); ?> ETB</span> - 
                        <span class="text-red-400"><?php echo number_format($total_allocation, 2); ?> ETB</span> (Allocated & Removed)
                     </span>
                </div>
            </div>
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl"></div>
        </div>
         <!-- Allocation Tasks -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Income Deconstruction</h2>
                <span class="text-xs text-gray-500 bg-slate-800 px-2 py-1 rounded">Active Tasks</span>
            </div>
            <div class="grid gap-4">
                <?php foreach ($allocations as $item): ?>
                    <div class="group relative rounded-xl glass p-4 transition hover:bg-white/5 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Custom Checkbox Logic -->
                            <a href="?toggle_id=<?php echo $item['id']; ?>" class="flex-shrink-0 cursor-pointer">
                                <?php if ($item['is_paid']): ?>
                                    <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white shadow-[0_0_10px_rgba(16,185,129,0.5)]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                <?php else: ?>
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-500 group-hover:border-emerald-400 transition"></div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Task Details -->
                            <div>
                                <p class="text-lg font-medium <?php echo $item['is_paid'] ? 'text-gray-500 line-through decoration-emerald-500/50' : 'text-white'; ?> transition-all">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </p>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-mono text-gray-400"><?php echo number_format($item['amount'], 2); ?> ETB</span>
                                    <?php if($item['category_name']): ?>
                                        <span class="text-xs text-blue-300 bg-blue-900/30 px-2 py-0.5 rounded"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Actions (Delete) -->
                         <a href="?delete_id=<?php echo $item['id']; ?>" class="text-gray-600 hover:text-red-400 transition p-2" onclick="return confirm('Remove task? (Amount will still be deducted from total)');">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </a>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($allocations) === 0): ?>
                    <div class="text-center py-8 text-gray-500">
                        No active tasks. Add one to split your income!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Right Column: Add New Task & Quick Stats -->
    <div class="space-y-8">
        
        <!-- Add Task Form -->
        <div class="rounded-2xl glass p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Add Allocation</h3>
            <form action="dashboard.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Task Name</label>
                    <input type="text" name="allocation_name" placeholder="e.g. Rent" required
                           class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Amount (ETB)</label>
                    <input type="number" step="0.01" name="allocation_amount" placeholder="0.00" required
                           class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Category (Optional)</label>
                    <select name="category_id" class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="add_allocation" value="1">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3 rounded-lg shadow-lg shadow-indigo-500/20 transition transform hover:scale-[1.02]">
                    Split Income
                </button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

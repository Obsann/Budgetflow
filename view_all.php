<?php
// view_all.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
$count_stmt->execute([$_SESSION['user_id']]);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$stmt = $pdo->prepare("
    SELECT t.*, c.name as category_name 
    FROM transactions t 
    JOIN categories c ON t.category_id = c.id 
    WHERE t.user_id = ? 
    ORDER BY t.transaction_date DESC, t.id DESC 
    LIMIT ? OFFSET ?
");

$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-white mb-4 md:mb-0">All Transactions</h2>
        <div class="space-x-4">
             <a href="export.php" class="bg-slate-700 hover:bg-slate-600 text-gray-200 px-6 py-2 rounded-full font-semibold shadow-lg transition">
                Export CSV
            </a>
            <a href="create.php" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-full font-semibold shadow-lg shadow-blue-500/30 transition">
                + New Record
            </a>
        </div>
    </div>

    <div class="glass rounded-xl overflow-hidden">
        <?php if (count($transactions) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-800/50 text-gray-300 uppercase text-xs font-semibold tracking-wider">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Description</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        <?php foreach ($transactions as $row): ?>
                            <tr class="hover:bg-white/5 transition">
                                <td class="p-4 text-gray-300 whitespace-nowrap">
                                    <?php echo date('M d, Y', strtotime($row['transaction_date'])); ?>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-slate-700 text-blue-300 border border-slate-600">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-gray-400">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </td>
                                <td class="p-4 font-bold text-white">
                                    <?php echo number_format($row['amount'], 2); ?> ETB
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    <a href="update.php?id=<?php echo $row['id']; ?>" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition">Edit</a>
                                    <span class="text-gray-600">|</span>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Are you sure?');" 
                                       class="text-red-400 hover:text-red-300 text-sm font-medium transition">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="p-4 border-t border-gray-700/50 flex justify-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>" class="px-3 py-1 rounded border border-gray-600 text-gray-400 hover:bg-slate-700 transition">Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="px-3 py-1 rounded border <?php echo ($i == $page) ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-600 text-gray-400 hover:bg-slate-700'; ?> transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>" class="px-3 py-1 rounded border border-gray-600 text-gray-400 hover:bg-slate-700 transition">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="p-8 text-center text-gray-500">
                <p>No transactions found.</p>
                <a href="create.php" class="text-blue-400 hover:underline mt-2 inline-block">Create your first entry</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

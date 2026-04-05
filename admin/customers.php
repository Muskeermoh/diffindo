<?php
include '../includes/db.php';
include '../includes/auth.php';

require_admin();

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id_desc';

// Build query based on search and sort
$query = "SELECT * FROM users WHERE role = 'customer'";

if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
}

// Add sorting
switch ($sort) {
    case 'name_asc':
        $query .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY name DESC";
        break;
    case 'email_asc':
        $query .= " ORDER BY email ASC";
        break;
    case 'email_desc':
        $query .= " ORDER BY email DESC";
        break;
    case 'created_asc':
        $query .= " ORDER BY created_at ASC";
        break;
    case 'created_desc':
        $query .= " ORDER BY created_at DESC";
        break;
    case 'id_asc':
        $query .= " ORDER BY id ASC";
        break;
    case 'id_desc':
    default:
        $query .= " ORDER BY id DESC";
        break;
}

// Execute query
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->execute([$search_term, $search_term, $search_term]);
} else {
    $stmt->execute();
}
$customers = $stmt->fetchAll();

// Get customer statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$total_customers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$new_customers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM orders WHERE user_id IN (SELECT id FROM users WHERE role = 'customer')");
$customers_with_orders = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
                    <p class="text-sm text-gray-300">Manage Customers</p>
                </div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
                    <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                    <a href="customers.php" class="text-white hover:text-gray-300 font-bold">Customers</a>
                    <a href="support-staff.php" class="text-white hover:text-gray-300">Support Staff</a>
                    <a href="image-manager.php" class="text-white hover:text-gray-300">Images</a>
                    <a href="../reach-us.php" class="text-white hover:text-gray-300">Reach Us</a>
                    <a href="../index.php" class="text-white hover:text-gray-300">View Site</a>
                    <a href="../logout.php" class="text-white hover:text-gray-300">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Customers</h2>
            <p class="text-gray-600">Manage and view all registered customers</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a3 3 0 003-3v-2a3 3 0 00-3-3H3a3 3 0 00-3 3v2a3 3 0 003 3h3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">Total Customers</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $total_customers ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">New (30 days)</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $new_customers ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-600">With Orders</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $customers_with_orders ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <form method="GET" class="flex gap-2">
                        <input type="text" name="search" placeholder="Search by name, email, or phone..." 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="customers.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-2">Sort By</label>
                    <form method="GET" id="sortForm">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <?php endif; ?>
                        <select name="sort" onchange="document.getElementById('sortForm').submit()" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>Newest First</option>
                            <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                            <option value="email_asc" <?= $sort === 'email_asc' ? 'selected' : '' ?>>Email (A-Z)</option>
                            <option value="email_desc" <?= $sort === 'email_desc' ? 'selected' : '' ?>>Email (Z-A)</option>
                            <option value="created_desc" <?= $sort === 'created_desc' ? 'selected' : '' ?>>Most Recent</option>
                            <option value="created_asc" <?= $sort === 'created_asc' ? 'selected' : '' ?>>Oldest</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($customers) > 0): ?>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                // Get customer order count
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                                $stmt->execute([$customer['id']]);
                                $order_count = $stmt->fetch()['count'];
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $customer['id'] ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        <?= htmlspecialchars($customer['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= htmlspecialchars($customer['email']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= htmlspecialchars($customer['phone'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= date('d M, Y', strtotime($customer['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            <?= $order_count ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        <a href="customer-details.php?id=<?= $customer['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-900 font-medium">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    <?php if (!empty($search)): ?>
                                        No customers found matching your search.
                                    <?php else: ?>
                                        No customers yet.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="mt-6 text-sm text-gray-600">
            Showing <strong><?= count($customers) ?></strong> 
            <?php if (!empty($search)): ?>
                of <?= $total_customers ?> customers matching "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                customer<?= $total_customers !== 1 ? 's' : '' ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

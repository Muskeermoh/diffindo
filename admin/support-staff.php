<?php
include '../includes/db.php';
include '../includes/auth.php';

require_admin();

$success = '';
$error = '';
$action = $_GET['action'] ?? 'create'; // Default to 'create' if not specified
$staff_id = $_GET['id'] ?? null;
$staff = null;

// Handle form submission
if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $nic = $_POST['nic'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $form_action = $_POST['form_action'] ?? $action; // Get action from form or URL
    $edit_staff_id = $_POST['staff_id'] ?? $staff_id; // Get staff_id from form or URL

    // Validate inputs
    if (empty($name) || empty($email) || empty($nic) || empty($phone) || empty($address)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($form_action === 'create' && empty($password)) {
        $error = 'Password is required for new staff';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            if ($form_action === 'create') {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already exists';
                } else {
                    // Create new support staff
                    $hashed_password = hash('sha256', $password);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, nic, phone, address, password) 
                                          VALUES (?, ?, 'support_staff', ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $nic, $phone, $address, $hashed_password]);
                    $success = 'Support staff member created successfully';
                    // Reset form
                    $_POST = [];
                }
            } elseif ($form_action === 'edit' && $edit_staff_id) {
                // Update support staff
                if (!empty($password)) {
                    $hashed_password = hash('sha256', $password);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, nic = ?, phone = ?, address = ?, password = ? 
                                          WHERE id = ? AND role = 'support_staff'");
                    $stmt->execute([$name, $email, $nic, $phone, $address, $hashed_password, $edit_staff_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, nic = ?, phone = ?, address = ? 
                                          WHERE id = ? AND role = 'support_staff'");
                    $stmt->execute([$name, $email, $nic, $phone, $address, $edit_staff_id]);
                }
                $success = 'Support staff member updated successfully';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Load support staff if editing
if ($action === 'edit' && $staff_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'support_staff'");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();
    if (!$staff) {
        header("Location: support-staff.php");
        exit;
    }
}

// Delete support staff
if ($action === 'delete' && $staff_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'support_staff'");
        $stmt->execute([$staff_id]);
        $success = 'Support staff member deleted successfully';
        header("Refresh: 2; url=support-staff.php");
    } catch (PDOException $e) {
        $error = 'Error deleting staff: ' . $e->getMessage();
    }
}

// Get all support staff
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'support_staff' ORDER BY id DESC");
$all_staff = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $action === 'edit' ? 'Edit' : 'Create' ?> Support Staff - Admin</title>
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
                    <p class="text-sm text-gray-300">Manage Support Staff</p>
                </div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
                    <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                    <a href="customers.php" class="text-white hover:text-gray-300">Customers</a>
                    <a href="support-staff.php" class="text-white hover:text-gray-300 font-bold">Support Staff</a>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                <?= $action === 'edit' ? 'Edit Support Staff' : 'Create Support Staff' ?>
            </h2>
            <p class="text-gray-600">Manage support staff members</p>
        </div>

        <!-- Errors and Success Messages -->
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">
                        <?= $action === 'edit' ? 'Update Staff Details' : 'Add New Staff Member' ?>
                    </h3>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="form_action" value="<?= htmlspecialchars($action) ?>">
                        <?php if ($action === 'edit' && $staff_id): ?>
                            <input type="hidden" name="staff_id" value="<?= htmlspecialchars($staff_id) ?>">
                        <?php endif; ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : (isset($staff['name']) ? htmlspecialchars($staff['name']) : '') ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($staff['email']) ? htmlspecialchars($staff['email']) : '') ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIC Number *</label>
                            <input type="text" name="nic" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= isset($_POST['nic']) ? htmlspecialchars($_POST['nic']) : (isset($staff['nic']) ? htmlspecialchars($staff['nic']) : '') ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : (isset($staff['phone']) ? htmlspecialchars($staff['phone']) : '') ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <textarea name="address" required rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : (isset($staff['address']) ? htmlspecialchars($staff['address']) : '') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Password <?= $action === 'edit' ? '(leave blank to keep current)' : '(required)' ?> *
                            </label>
                            <input type="password" name="password" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   <?= $action === 'create' ? 'required' : '' ?>>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                            <input type="password" name="confirm_password" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="flex gap-2 pt-4">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-save mr-2"></i><?= $action === 'edit' ? 'Update' : 'Create' ?> Staff
                            </button>
                            <a href="support-staff.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition text-center">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Staff List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800">Support Staff Members</h3>
                        <?php if ($action !== 'create'): ?>
                            <a href="?action=create" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-plus mr-2"></i>Add New Staff
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">NIC</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (count($all_staff) > 0): ?>
                                    <?php foreach ($all_staff as $member): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($member['name']) ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                <?= htmlspecialchars($member['email']) ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                <?= htmlspecialchars($member['nic'] ?? 'N/A') ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                <?= htmlspecialchars($member['phone'] ?? 'N/A') ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm space-x-2">
                                                <a href="?action=edit&id=<?= $member['id'] ?>" 
                                                   class="text-blue-600 hover:text-blue-900 font-medium">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?action=delete&id=<?= $member['id'] ?>" 
                                                   class="text-red-600 hover:text-red-900 font-medium"
                                                   onclick="return confirm('Are you sure you want to delete this staff member?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No support staff members yet. 
                                            <?php if ($action !== 'create'): ?>
                                                <a href="?action=create" class="text-blue-600 hover:text-blue-900">Create one</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

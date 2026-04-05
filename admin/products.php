<?php
include '../includes/db.php';
include '../includes/auth.php';

require_admin();

$message = '';
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_product'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float) $_POST['price'];
        $image = 'default-cake.jpg'; // Default image
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../assets/images/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                // Generate unique filename
                $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image = $new_filename;
                } else {
                    $message = "Failed to upload image.";
                }
            } else {
                $message = "Invalid image file. Please upload JPG, PNG, GIF, or WebP files under 5MB.";
            }
        }
        
        if (!$message && !empty($name) && !empty($description) && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $description, $price, $image])) {
                $message = "Product added successfully!";
                $action = 'list';
            } else {
                $message = "Failed to add product.";
            }
        } else if (!$message) {
            $message = "Please fill all required fields with valid data.";
        }
    } elseif (isset($_POST['update_product'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float) $_POST['price'];
        $current_image = $_POST['current_image'];
        $image = $current_image; // Keep current image by default
        
        // Handle image upload for update
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../assets/images/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                // Generate unique filename
                $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it's not the default
                    if ($current_image && $current_image !== 'default-cake.jpg' && file_exists($upload_dir . $current_image)) {
                        unlink($upload_dir . $current_image);
                    }
                    $image = $new_filename;
                } else {
                    $message = "Failed to upload new image.";
                }
            } else {
                $message = "Invalid image file. Please upload JPG, PNG, GIF, or WebP files under 5MB.";
            }
        }
        
        if (!$message && !empty($name) && !empty($description) && $price > 0) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $price, $image, $product_id])) {
                $message = "Product updated successfully!";
                $action = 'list';
            } else {
                $message = "Failed to update product.";
            }
        } else if (!$message) {
            $message = "Please fill all required fields with valid data.";
        }
    } elseif (isset($_POST['delete_product'])) {
        $delete_product_id = (int) $_POST['id'];
        // Check if product is used in orders
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
        $stmt->execute([$delete_product_id]);
        $order_count = $stmt->fetch()['count'];
        
        if ($order_count > 0) {
            $message = "Cannot delete product as it has been ordered by customers.";
        } else {
            // Get current image to delete
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$delete_product_id]);
            $product_data = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$delete_product_id])) {
                // Delete image file if it's not the default
                if ($product_data && $product_data['image'] && $product_data['image'] !== 'default-cake.jpg') {
                    $image_path = '../assets/images/' . $product_data['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                $message = "Product deleted successfully!";
                $action = 'list';
            } else {
                $message = "Failed to delete product.";
            }
        }
    }
}

// Get product data for editing
$product = null;
if ($action === 'edit' && $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $message = "Product not found.";
        $action = 'list';
    }
}

// Get all products for listing
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-body">
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
                    <p class="text-sm text-gray-300">Diffindo (Cakes and Bakes)</p>
                </div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-gray-300">Dashboard</a>
                    <a href="products.php" class="text-white hover:text-gray-300 font-bold">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                    <a href="customers.php" class="text-white hover:text-gray-300">Customers</a>
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
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Product Management</h2>
                <p class="text-gray-600">Manage your cake products</p>
            </div>
            <?php if ($action === 'list'): ?>
                <a href="?action=add" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Add New Product
                </a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- Products List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php if (empty($products)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500 mb-4">No products available.</p>
                        <a href="?action=add" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Add Your First Product
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <img src="../assets/images/<?= htmlspecialchars($prod['image']) ?>" 
                                                     alt="<?= htmlspecialchars($prod['name']) ?>" 
                                                     class="w-12 h-12 object-cover rounded cursor-pointer hover:w-20 hover:h-20 transition-all duration-200"
                                                     title="Click to view larger"
                                                     onclick="showImageModal('<?= htmlspecialchars($prod['image']) ?>', '<?= htmlspecialchars($prod['name']) ?>')"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCA0OCA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQ4IiBoZWlnaHQ9IjQ4IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yNCAyMEMyMS43OTA5IDIwIDIwIDIxLjc5MDkgMjAgMjRDMjAgMjYuMjA5MSAyMS43OTA5IDI4IDI0IDI4QzI2LjIwOTEgMjggMjggMjYuMjA5MSAyOCAyNEMyOCAyMS43OTA5IDI2LjIwOTEgMjAgMjQgMjBaIiBmaWxsPSIjOUIxMDhEIi8+Cjwvc3ZnPgo='">
                                                <div>
                                                    <h4 class="font-medium text-gray-900"><?= htmlspecialchars($prod['name']) ?></h4>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                            <div class="truncate" title="<?= htmlspecialchars($prod['description']) ?>">
                                                <?= htmlspecialchars($prod['description']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            Rs <?= number_format($prod['price']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($prod['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="?action=edit&id=<?= $prod['id'] ?>" 
                                                   class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">
                                                    Edit
                                                </a>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="delete_product" value="1">
                                                    <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                                    <button type="submit" 
                                                            class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700"
                                                            onclick="return confirm('Are you sure you want to delete this product?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Product Form -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">
                        <?= $action === 'add' ? 'Add New Product' : 'Edit Product' ?>
                    </h3>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Product Name *</label>
                            <input type="text" name="name" required 
                                   value="<?= $product ? htmlspecialchars($product['name']) : '' ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Description *</label>
                            <textarea name="description" rows="4" required 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"><?= $product ? htmlspecialchars($product['description']) : '' ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Price (Rs) *</label>
                            <input type="number" name="price" step="0.01" min="0" required 
                                   value="<?= $product ? $product['price'] : '' ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Product Image</label>
                            <?php if ($product && $product['image']): ?>
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                                    <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" 
                                         alt="Current product image" 
                                         class="w-32 h-32 object-cover rounded border"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgdmlld0JveD0iMCAwIDEyOCAxMjgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik02NCA0OEM1Ny4zNzI2IDQ4IDUyIDUzLjM3MjYgNTIgNjBDNTIgNjYuNjI3NCA1Ny4zNzI2IDcyIDY0IDcyQzcwLjYyNzQgNzIgNzYgNjYuNjI3NCA3NiA2MEM3NiA1My4zNzI2IDcwLjYyNzQgNDggNjQgNDhaIiBmaWxsPSIjOUIxMDhEIi8+Cjwvc3ZnPgo='">
                                    <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image']) ?>">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" id="imageInput"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                   onchange="previewImage(this)">
                            <p class="text-xs text-gray-500 mt-1">
                                <?= $action === 'edit' ? 'Upload new image to replace current one. ' : '' ?>
                                Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB.
                            </p>
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                                <img id="previewImg" src="" alt="Image preview" 
                                     class="w-32 h-32 object-cover rounded border">
                            </div>
                        </div>
                        
                        <div class="flex space-x-4">
                            <button type="submit" 
                                    name="<?= $action === 'add' ? 'add_product' : 'update_product' ?>"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                <?= $action === 'add' ? 'Add Product' : 'Update Product' ?>
                            </button>
                            <a href="?action=list" 
                               class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-2xl max-h-screen overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-800"></h3>
                <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <img id="modalImage" src="" alt="Product image" class="max-w-full max-h-96 object-contain rounded">
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        function showImageModal(imageName, productName) {
            document.getElementById('modalImage').src = '../assets/images/' + imageName;
            document.getElementById('modalTitle').textContent = productName;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
<?php
include '../includes/db.php';
include '../includes/auth.php';

require_admin();

// Get all images from products
$stmt = $pdo->query("SELECT DISTINCT image FROM products WHERE image IS NOT NULL AND image != ''");
$product_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all files from images directory
$images_dir = '../assets/images/';
$all_files = [];
if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    foreach ($files as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $all_files[] = $file;
        }
    }
}

// Identify unused images
$unused_images = array_diff($all_files, $product_images);

$message = '';

// Handle image deletion
if (isset($_POST['delete_image'])) {
    $image_to_delete = $_POST['image_name'];
    $image_path = $images_dir . $image_to_delete;
    
    if (file_exists($image_path) && in_array($image_to_delete, $unused_images)) {
        if (unlink($image_path)) {
            $message = "Image deleted successfully!";
            // Refresh the page to update the lists
            header("Location: image-manager.php?message=" . urlencode($message));
            exit;
        } else {
            $message = "Failed to delete image.";
        }
    } else {
        $message = "Cannot delete image - it may be in use or doesn't exist.";
    }
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Image Manager - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    <a href="products.php" class="text-white hover:text-gray-300">Products</a>
                    <a href="orders.php" class="text-white hover:text-gray-300">Orders</a>
                    <a href="customers.php" class="text-white hover:text-gray-300">Customers</a>
                    <a href="support-staff.php" class="text-white hover:text-gray-300">Support Staff</a>
                    <a href="image-manager.php" class="text-white hover:text-gray-300 font-bold">Images</a>
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
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Image Manager</h2>
                <p class="text-gray-600">Manage your uploaded product images</p>
            </div>
            <a href="products.php?action=add" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Add New Product
            </a>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Images</h3>
                <p class="text-3xl font-bold text-blue-600"><?= count($all_files) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Images in Use</h3>
                <p class="text-3xl font-bold text-green-600"><?= count($product_images) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Unused Images</h3>
                <p class="text-3xl font-bold text-red-600"><?= count($unused_images) ?></p>
            </div>
        </div>

        <!-- Images in Use -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Images Currently in Use</h3>
            </div>
            <div class="p-6">
                <?php if (empty($product_images)): ?>
                    <p class="text-gray-500">No product images found.</p>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <?php foreach ($product_images as $image): ?>
                            <div class="relative group">
                                <img src="../assets/images/<?= htmlspecialchars($image) ?>" 
                                     alt="Product image" 
                                     class="w-full h-24 object-cover rounded border cursor-pointer hover:opacity-75"
                                     onclick="showImageModal('<?= htmlspecialchars($image) ?>')">
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 text-white text-xs p-1 rounded-b opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?= htmlspecialchars($image) ?>
                                </div>
                                <span class="absolute top-1 right-1 bg-green-500 text-white text-xs px-1 rounded">In Use</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Unused Images -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Unused Images</h3>
                <p class="text-sm text-gray-600">These images are not currently assigned to any products and can be safely deleted.</p>
            </div>
            <div class="p-6">
                <?php if (empty($unused_images)): ?>
                    <p class="text-gray-500">No unused images found. Great job keeping things clean!</p>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <?php foreach ($unused_images as $image): ?>
                            <div class="relative group">
                                <img src="../assets/images/<?= htmlspecialchars($image) ?>" 
                                     alt="Unused image" 
                                     class="w-full h-24 object-cover rounded border cursor-pointer hover:opacity-75"
                                     onclick="showImageModal('<?= htmlspecialchars($image) ?>')">
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 text-white text-xs p-1 rounded-b opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?= htmlspecialchars($image) ?>
                                </div>
                                <div class="absolute top-1 left-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="image_name" value="<?= htmlspecialchars($image) ?>">
                                        <button type="submit" name="delete_image" 
                                                class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600 w-full"
                                                onclick="return confirm('Are you sure you want to delete this image?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                                <span class="absolute top-1 right-1 bg-red-500 text-white text-xs px-1 rounded">Unused</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-4xl max-h-screen overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-800">Image Preview</h3>
                <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <img id="modalImage" src="" alt="Image preview" class="max-w-full max-h-96 object-contain rounded mx-auto block">
            <p id="modalFileName" class="text-sm text-gray-600 text-center mt-2"></p>
        </div>
    </div>

    <script>
        function showImageModal(imageName) {
            document.getElementById('modalImage').src = '../assets/images/' + imageName;
            document.getElementById('modalFileName').textContent = 'File: ' + imageName;
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
<?php
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$log_file = '../logs/emails.log';
$emails_content = '';
if (file_exists($log_file)) {
    $emails_content = file_get_contents($log_file);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Email Monitor - Diffindo Cakes & Bakes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .font-heading { font-family: 'Playfair Display', serif; }
        .font-body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 font-body">
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <h1 class="font-heading text-2xl font-bold text-gray-800">
                        <i class="fas fa-envelope text-pink-500 mr-2"></i>
                        Email Monitor
                    </h1>
                    <div class="flex space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                        </a>
                        <button onclick="refreshEmails()" class="bg-pink-500 text-white px-4 py-2 rounded-lg hover:bg-pink-600">
                            <i class="fas fa-refresh mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Email Log (Development Mode)
                    </h2>
                    <p class="text-gray-600 mb-6">
                        This shows all emails that would be sent to customers in production. 
                        In development mode, emails are logged here instead of being sent.
                    </p>

                    <?php if (empty($emails_content)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-500 mb-2">No Emails Yet</h3>
                            <p class="text-gray-400">Process an order to see email notifications here.</p>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                            <pre class="text-sm text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($emails_content) ?></pre>
                        </div>
                        
                        <div class="mt-4 flex justify-end space-x-2">
                            <button onclick="clearEmails()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                                <i class="fas fa-trash mr-1"></i> Clear Log
                            </button>
                            <button onclick="downloadLog()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                                <i class="fas fa-download mr-1"></i> Download Log
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Testing Email Functionality
                </h3>
                <div class="text-blue-700">
                    <p class="mb-2"><strong>To test emails:</strong></p>
                    <ol class="list-decimal list-inside space-y-1 ml-4">
                        <li>Place an order from a user account</li>
                        <li>Accept/reject the order from admin panel</li>
                        <li>Check this page to see the email that would be sent</li>
                        <li>Email content includes order details, customer info, and status updates</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshEmails() {
            location.reload();
        }

        function clearEmails() {
            if (confirm('Are you sure you want to clear the email log?')) {
                fetch('email-clear.php', { method: 'POST' })
                    .then(() => location.reload());
            }
        }

        function downloadLog() {
            window.open('email-download.php', '_blank');
        }

        // Auto-refresh every 30 seconds
        setInterval(refreshEmails, 30000);
    </script>
</body>
</html>
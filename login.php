<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: admin.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $loginSuccess = false;

        // 1. Try Database Login
        if (function_exists('getDBConnection')) {
            $conn = getDBConnection();
            if ($conn) {
                try {
                    $stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE username = ?");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password_hash'])) {
                        $loginSuccess = true;
                    }
                } catch (Exception $e) {
                    // DB error
                }
            }
        }

        // 2. Fallback to Static Credentials
        if (!$loginSuccess) {
            $staticUser = defined('ADMIN_USERNAME') ? ADMIN_USERNAME : 'admin';
            $staticPass = defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : 'sebas';
            
            if ($username === $staticUser && $password === $staticPass) {
                $loginSuccess = true;
            }
        }

        if ($loginSuccess) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: admin.php');
            exit;
        } else {
            $message = 'Username atau password salah!';
            $messageType = 'error';
        }
    } else {
        $message = 'Harap isi semua field!';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SIG Minahasa</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        
        <div class="bg-blue-900 p-8 pb-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-800 text-blue-200 mb-6 shadow-lg border border-blue-700">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Login Admin</h1>
            <p class="text-sm text-blue-200 mt-2 font-medium">Sistem Informasi Geografis Minahasa</p>
        </div>

        <?php if ($message): ?>
            <div class="px-8 pt-6">
                <div class="p-4 rounded-xl text-sm font-semibold flex items-center gap-3 <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                    <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5 shrink-0"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="p-8">
            <form method="POST" class="space-y-5">
                <div>
                    <label for="username" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="text" id="username" name="username" required 
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-medium placeholder-gray-400" 
                               placeholder="Masukkan username">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required 
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-medium placeholder-gray-400" 
                               placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900 transition-all duration-200 transform hover:-translate-y-0.5">
                    Masuk Dashboard
                </button>
            </form>

            <div class="mt-8 text-center border-t border-gray-100 pt-6">
                <a href="index.php" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-900 transition-colors group">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                    Kembali ke Peta Utama
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // SweetAlert for Errors
        <?php if ($message): ?>
            Swal.fire({
                icon: '<?php echo $messageType === "success" ? "success" : "error"; ?>',
                title: '<?php echo $messageType === "success" ? "Berhasil" : "Gagal Login"; ?>',
                text: '<?php echo $message; ?>',
                confirmButtonColor: '#1e3a8a', // Blue-900
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-2xl'
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>
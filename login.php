<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use root config (not includes/config.php)
include 'config.php'; 

$message = "";

// Handle POST BEFORE outputting any HTML to allow header() redirects
if(isset($_POST['login'])) {

    $identifier = trim($_POST['username']); // can be username or email
    $pass = $_POST['password'];

    $queryField = (strpos($identifier, '@') !== false) ? 'email' : 'username';
    if ($stmt = $conn->prepare("SELECT username, email, password, role FROM users WHERE $queryField = ? LIMIT 1")) {
        $stmt->bind_param('s', $identifier);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (!empty($row['password']) && password_verify($pass, $row['password'])) {
                $_SESSION['user'] = $row['username'] ?: ($row['email'] ?? '');
                $_SESSION['role'] = $row['role'] ?? 'customer';
                header('Location: ' . ($row['role'] === 'admin' ? 'admin/AdminDashboard.php' : 'shop.php'));
                exit();
            }
        }
        $stmt->close();
    }

    $message = strpos($identifier, '@') !== false ? "❌ Invalid email or password" : "❌ Invalid username or password";
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-4 p-4 border rounded bg-white shadow">
        <h3 class="text-center mb-3 fw-bold">Account Login</h3>

        <?php if($message): ?>
            <div class="alert alert-danger text-center py-1"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Email or Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" name="login" class="btn btn-gold w-100">Login</button>
        </form>
        <div class="text-center mt-3">
            <p class="mb-0">Need an account? <a href="/DrewCrew/register.php">Register here</a>.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

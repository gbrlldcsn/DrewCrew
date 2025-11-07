<?php
session_start();
include 'config.php';
include 'includes/validation.php';

$errors = [];

if (isset($_SESSION['user'])) {
    header('Location: /DrewCrew/home.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    validate_required($username, 'Username', $errors);
    validate_email($email, 'Email', $errors);
    validate_min_length($password, 6, 'Password', $errors);
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Check duplicates
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Username or email already exists.';
        }
        $stmt->close();

        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $role = 'customer';
            $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $username, $email, $hashed, $role);
            if ($stmt->execute()) {
                $_SESSION['user'] = $username;
                $_SESSION['role'] = $role;
                header('Location: /DrewCrew/shop.php');
                exit();
            } else {
                $errors[] = 'Failed to create account. Please try again.';
            }
            $stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<h1 class="fw-bold mb-4 text-center">Create an Account</h1>

<div class="row justify-content-center mb-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimum 6 characters.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-gold w-100">Create Account</button>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Already have an account? <a href="/DrewCrew/login.php">Log in</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>



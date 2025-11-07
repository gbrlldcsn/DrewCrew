<?php
include '../includes/auth.php';
require_admin();
include '../config.php';
include '../includes/validation.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    validate_required($username, 'Username', $errors);
    validate_email($email, 'Email', $errors);
    validate_min_length($password, 6, 'Password', $errors);
    if (!in_array($role, ['customer', 'admin'], true)) {
        $role = 'customer';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'A user with that username or email already exists.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $username, $email, $hashed, $role);
        if ($stmt->execute()) {
            $success = 'User account created successfully.';
        } else {
            $errors[] = 'Failed to create user.';
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<h2 class="fw-bold mb-4">Add New User</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" class="mb-4">
    <div class="mb-3">
        <label class="form-label fw-semibold">Username</label>
        <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required>
        <small class="text-muted">Minimum 6 characters.</small>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Role</label>
        <select name="role" class="form-select">
            <option value="customer" <?php echo (($_POST['role'] ?? '') === 'customer') ? 'selected' : ''; ?>>Customer</option>
            <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Create User</button>
    <a href="users.php" class="btn btn-secondary">Back to Users</a>
</form>

<?php include '../includes/footer.php'; ?>



<?php
include 'includes/auth.php';
require_login();
include 'config.php';

$username = $_SESSION['user'];
$message = '';
$messageType = 'success';

// Fetch current user data
$stmt = $conn->prepare('SELECT id, username, email, role, created_at FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$userData) {
    $message = 'Account not found.';
    $messageType = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account']) && $userData) {
    $userId = $userData['id'];

    $stmt = $conn->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) {
        $stmt->close();
        session_destroy();
        header('Location: /DrewCrew/home.php');
        exit();
    } else {
        $message = 'Failed to delete account.';
        $messageType = 'danger';
    }
    $stmt->close();
}

include 'includes/header.php';
?>

<h1 class="fw-bold mb-4 text-center">My Account</h1>

<div class="row justify-content-center mb-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($userData): ?>
                    <h4 class="fw-bold mb-3">Account Details</h4>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($userData['role'])); ?></p>
                    <?php if (!empty($userData['created_at'])): ?>
                        <p><strong>Member since:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($userData['created_at']))); ?></p>
                    <?php endif; ?>

                    <hr class="my-4">

                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                        <button type="submit" name="delete_account" class="btn btn-danger w-100">Delete My Account</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>



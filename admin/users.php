<?php
include '../includes/auth.php';
require_admin();
include '../config.php';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];

        // Prevent deleting own account accidentally
        $stmt = $conn->prepare('SELECT username FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userRow = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($userRow && $userRow['username'] === $_SESSION['user']) {
            $message = 'You cannot delete your own admin account.';
            $messageType = 'danger';
        } else {
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $message = 'User deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to delete user.';
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }
}

$users = $conn->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');

include '../includes/header.php';
?>

<h2 class="fw-bold mb-4">Manage Users</h2>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="add_user.php" class="btn btn-primary">Add New User</a>
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users && $users->num_rows > 0): ?>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($row['role'])); ?></td>
                        <td><?php echo $row['created_at'] ? htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))) : '-'; ?></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button class="btn btn-sm btn-danger" name="delete_user">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>



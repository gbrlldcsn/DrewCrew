<?php
include 'includes/auth.php';
require_login();
include 'config.php';

$username = $_SESSION['user'];
$role = $_SESSION['role'] ?? 'customer';

// Redirect admin to admin dashboard
if ($role === 'admin') {
    header('Location: /DrewCrew/admin/AdminDashboard.php');
    exit();
}

// Fetch current user data
$stmt = $conn->prepare('SELECT id, username, email, role, created_at FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$userData) {
    header('Location: /DrewCrew/login.php');
    exit();
}

// Get user's full name (if available) or use username
$displayName = $userData['username'];
$email = $userData['email'] ?? '';

// Get cart items
$cartItems = [];
$cartTotal = 0.0;

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];

if (!empty($cart)) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $types = str_repeat('i', count($cart));
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...array_keys($cart));
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pid = (int)$row['id'];
        $quantity = $cart[$pid] ?? 0;
        if ($quantity <= 0) continue;
        $row['quantity'] = $quantity;
        $row['subtotal'] = $quantity * (float)$row['price'];
        $cartItems[$pid] = $row;
        $cartTotal += $row['subtotal'];
    }
    $stmt->close();
}

function normalize_image_account(string $path): string {
    $path = str_replace('\\', '/', trim($path));
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path)) return $path;
    if (str_starts_with($path, '/DrewCrew/')) return $path;
    if (str_starts_with($path, '/assets/')) return '/DrewCrew' . $path;
    if (str_starts_with($path, 'assets/')) return '/DrewCrew/' . $path;
    if (str_starts_with($path, '/uploads/')) return '/DrewCrew' . $path;
    if (str_starts_with($path, 'uploads/')) return '/DrewCrew/' . $path;
    $doc = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    $normalized = str_replace('\\', '/', $path);
    if ($doc && str_starts_with($normalized, $doc)) {
        return '/' . ltrim(str_replace($doc, '', $normalized), '/');
    }
    return '/DrewCrew/uploads/' . $path;
}

include 'includes/header.php';
?>

<style>
.account-dashboard {
    max-width: 1200px;
    margin: 2rem auto;
}

.account-sidebar {
    padding-right: 3rem;
}

.account-sidebar h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #000;
}

.account-sidebar .sign-out-link {
    color: #666;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.3s;
}

.account-sidebar .sign-out-link:hover {
    color: #000;
}

.account-sidebar .section-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 3rem;
    margin-bottom: 1rem;
    color: #000;
}

.account-sidebar .no-orders {
    color: #666;
    font-size: 0.95rem;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 1rem;
}

.cart-item-details {
    flex: 1;
}

.cart-item-name {
    font-weight: 500;
    font-size: 0.95rem;
    color: #000;
    margin-bottom: 0.25rem;
}

.cart-item-info {
    font-size: 0.85rem;
    color: #666;
}

.cart-item-price {
    font-weight: 600;
    color: #000;
    font-size: 0.95rem;
}

.view-cart-link {
    display: inline-block;
    margin-top: 1rem;
    color: #000;
    text-decoration: underline;
    font-size: 0.95rem;
    transition: color 0.3s;
}

.view-cart-link:hover {
    color: #666;
}

.account-details {
    padding-left: 2rem;
}

.account-details h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #000;
}

.account-details .user-name {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #000;
}

.account-details .user-country {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.account-details .addresses-link {
    color: #000;
    text-decoration: underline;
    font-size: 0.95rem;
    transition: color 0.3s;
}

.account-details .addresses-link:hover {
    color: #666;
}

@media (max-width: 768px) {
    .account-sidebar {
        padding-right: 0;
        margin-bottom: 2rem;
    }
    
    .account-details {
        padding-left: 0;
    }
}
</style>

<div class="container account-dashboard">
    <div class="row">
        <!-- Left Column: Account Sidebar -->
        <div class="col-md-4 account-sidebar">
            <h2>Account</h2>
            <a href="/DrewCrew/logout.php" class="sign-out-link">Sign Out</a>
            
            <h2 class="section-title">Your Cart</h2>
            <?php if (empty($cartItems)): ?>
                <p class="no-orders">Your cart is empty</p>
            <?php else: ?>
                <div class="cart-items-list">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <?php $imgSrc = normalize_image_account($item['image'] ?? ''); ?>
                            <?php if ($imgSrc): ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="cart-item-image">
                            <?php else: ?>
                                <div class="cart-item-image bg-light d-flex align-items-center justify-content-center">
                                    <small class="text-muted">No Image</small>
                                </div>
                            <?php endif; ?>
                            <div class="cart-item-details">
                                <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-item-info">Qty: <?php echo (int)$item['quantity']; ?> × ₱<?php echo number_format((float)$item['price'], 2); ?></div>
                            </div>
                            <div class="cart-item-price">₱<?php echo number_format((float)$item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-total mt-3">
                    <div style="font-weight: 600; color: #000; margin-bottom: 0.5rem;">Total: ₱<?php echo number_format($cartTotal, 2); ?></div>
                </div>
                <a href="/DrewCrew/cart.php" class="view-cart-link">View Cart →</a>
            <?php endif; ?>
        </div>
        
        <!-- Right Column: Account Details -->
        <div class="col-md-8 account-details">
            <h2>Account Details</h2>
            <div class="user-name"><?php echo htmlspecialchars($displayName); ?></div>
            <div class="user-country">Philippines</div>
            <a href="#" class="addresses-link">Addresses (0)</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


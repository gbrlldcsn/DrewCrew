<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart =& $_SESSION['cart'];

function normalize_image_cart(string $path): string {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_id'])) {
        $productId = filter_var($_POST['remove_id'], FILTER_VALIDATE_INT);
        if ($productId && isset($cart[$productId])) {
            unset($cart[$productId]);
            $_SESSION['cart_alert'] = ['type' => 'info', 'message' => 'Item removed.'];
        }
        header('Location: cart.php');
        exit();
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        if ($productId) {
            $stmt = $conn->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
                $_SESSION['cart_alert'] = ['type' => 'success', 'message' => 'Product added to cart.'];
            } else {
                $_SESSION['cart_alert'] = ['type' => 'danger', 'message' => 'Product not found.'];
            }
            $stmt->close();
        }
        header('Location: cart.php');
        exit();
    }
    if ($action === 'update' && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $pid => $qty) {
            $pid = (int)$pid;
            $qty = max(0, (int)$qty);
            if ($qty <= 0) {
                unset($cart[$pid]);
            } else {
                $cart[$pid] = $qty;
            }
        }
        $_SESSION['cart_alert'] = ['type' => 'success', 'message' => 'Cart updated.'];
        header('Location: cart.php');
        exit();
    }
    if ($action === 'clear') {
        $cart = [];
        $_SESSION['cart_alert'] = ['type' => 'info', 'message' => 'Cart cleared.'];
        header('Location: cart.php');
        exit();
    }
}

$items = [];
$total = 0.0;

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
        $items[$pid] = $row;
        $total += $row['subtotal'];
    }
    $stmt->close();
}

$alert = $_SESSION['cart_alert'] ?? null;
unset($_SESSION['cart_alert']);

include __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h1 class="fw-bold mb-4">Your Cart</h1>

    <?php if ($alert): ?>
        <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?>">
            <?php echo htmlspecialchars($alert['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="alert alert-light">
            Your cart is empty. <a href="/DrewCrew/shop.php">Browse products</a> to add items.
        </div>
    <?php else: ?>
        <form method="POST" class="card shadow-sm p-3 mb-4">
            <input type="hidden" name="action" value="update">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th style="width:120px;">Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php $imgSrc = normalize_image_cart($item['image'] ?? ''); ?>
                                        <?php if ($imgSrc): ?>
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:64px; height:64px; object-fit:cover; border-radius:8px;" class="me-3">
                                        <?php endif; ?>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></div>
                                    </div>
                                </td>
                                <td>₱<?php echo number_format((float)$item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantities[<?php echo (int)$item['id']; ?>]" value="<?php echo (int)$item['quantity']; ?>" min="0" class="form-control form-control-sm">
                                </td>
                                <td>₱<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                                <td>
                                    <button type="submit" name="remove_id" value="<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <button type="submit" class="btn btn-outline-dark">Update Cart</button>
                </div>
                <div class="h4 mb-0">Total: ₱<?php echo number_format($total, 2); ?></div>
            </div>
        </form>
        <div class="d-flex justify-content-between">
            <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-outline-secondary" onclick="return confirm('Clear all items?');">Clear Cart</button>
            </form>
            <div>
                <a href="/DrewCrew/shop.php" class="btn btn-outline-dark me-2">Continue Shopping</a>
                <a href="#" class="btn btn-gold disabled">Checkout (coming soon)</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>



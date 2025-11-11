<?php
include '../includes/auth.php';
require_admin();
include '../config.php';

// Add stock column if it doesn't exist (MySQL 5.7+ supports IF NOT EXISTS)
try {
    $conn->query("ALTER TABLE products ADD COLUMN stock INT DEFAULT 0");
} catch (Exception $e) {
    // Column might already exist, ignore error
}

$message = '';
$messageType = 'success';

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    
    if ($productId && $stock !== false && $stock >= 0) {
        $stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
        $stmt->bind_param('ii', $stock, $productId);
        if ($stmt->execute()) {
            $message = 'Stock updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to update stock.';
            $messageType = 'danger';
        }
        $stmt->close();
    } else {
        $message = 'Invalid stock value.';
        $messageType = 'danger';
    }
}

// Handle bulk stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    $updates = $_POST['stocks'] ?? [];
    $successCount = 0;
    
    foreach ($updates as $productId => $stock) {
        $productId = (int)$productId;
        $stock = (int)$stock;
        
        if ($productId > 0 && $stock >= 0) {
            $stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
            $stmt->bind_param('ii', $stock, $productId);
            if ($stmt->execute()) {
                $successCount++;
            }
            $stmt->close();
        }
    }
    
    if ($successCount > 0) {
        $message = "Updated stock for $successCount product(s).";
        $messageType = 'success';
    }
}

// Get all products with stock
$products = $conn->query('SELECT id, name, price, stock FROM products ORDER BY name ASC');
?>

<?php include '../includes/header.php'; ?>

<h2 class="fw-bold mb-4">Manage Stock</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" id="stockForm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Current Stock</th>
                                <th>New Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <?php 
                                $currentStock = (int)($product['stock'] ?? 0);
                                $isOutOfStock = $currentStock <= 0;
                                ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>₱<?php echo number_format((float)$product['price'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $isOutOfStock ? 'bg-danger' : ($currentStock < 10 ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $currentStock; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="stocks[<?php echo $product['id']; ?>]" 
                                               class="form-control form-control-sm" 
                                               value="<?php echo $currentStock; ?>" 
                                               min="0" 
                                               style="width: 100px;">
                                    </td>
                                    <td>
                                        <?php if ($isOutOfStock): ?>
                                            <span class="badge bg-danger">Sold Out</span>
                                        <?php elseif ($currentStock < 10): ?>
                                            <span class="badge bg-warning">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 mb-4">
                    <button type="submit" name="bulk_update" class="btn btn-primary">Update All Stocks</button>
                    <a href="AdminDashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>


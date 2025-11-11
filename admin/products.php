<?php
include '../includes/auth.php';
require_admin();
include '../config.php';

$status = $_GET['status'] ?? '';
$alert = null;
if ($status === 'deleted') {
    $alert = ['type' => 'success', 'message' => 'Product deleted successfully.'];
} elseif ($status === 'notfound') {
    $alert = ['type' => 'warning', 'message' => 'Product not found or already removed.'];
} elseif ($status === 'error') {
    $alert = ['type' => 'danger', 'message' => 'Unable to delete product. Please try again.'];
}

$result = $conn->query('SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
?>
<?php include '../includes/header.php'; ?>

<h2>Products</h2>
<?php if ($alert): ?>
    <div class="alert alert-<?php echo $alert['type']; ?>">
        <?php echo htmlspecialchars($alert['message']); ?>
    </div>
<?php endif; ?>
<div class="d-flex gap-2 mb-3">
    <a href="add_product.php" class="btn btn-primary">Add Product</a>
    <a href="categorize_products.php" class="btn btn-outline-secondary" onclick="return confirm('Auto-categorize products by path/name?')">Auto Categorize</a>
    <?php if ($status === 'categorized'): ?>
        <span class="badge bg-success align-self-center">Categorization complete</span>
    <?php endif; ?>
    <?php if ($status === 'updated'): ?>
        <span class="badge bg-success align-self-center">Product updated</span>
    <?php endif; ?>
</div>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Image</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo htmlspecialchars($row['category_name']); ?></td>
<td><?php echo number_format($row['price'],2); ?></td>
<td>
    <?php 
    $stock = (int)($row['stock'] ?? 0);
    $stockClass = $stock <= 0 ? 'danger' : ($stock < 10 ? 'warning' : 'success');
    ?>
    <span class="badge bg-<?php echo $stockClass; ?>">
        <?php echo $stock; ?>
    </span>
    <?php if ($stock <= 0): ?>
        <span class="badge bg-danger ms-1">Sold Out</span>
    <?php endif; ?>
</td>
<td>
<?php if ($row['image']): ?>
<img src="../uploads/<?php echo $row['image']; ?>" width="80">
<?php else: ?>
<span class="text-muted">No Image</span>
<?php endif; ?>
</td>
<td>
<a class="btn btn-sm btn-warning" href="edit_product.php?id=<?php echo $row['id']; ?>">Edit</a>
<a class="btn btn-sm btn-danger" href="delete_product.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this product?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<?php include '../includes/footer.php'; ?>
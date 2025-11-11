<?php 
include '../includes/auth.php'; 
if($_SESSION['role'] !== 'admin') {
    header("Location: ../home.php");
    exit();
}
include '../config.php';

// stats
$total_products = $conn->query('SELECT COUNT(*) as c FROM products')->fetch_assoc()['c'];
$total_users = $conn->query('SELECT COUNT(*) as c FROM users')->fetch_assoc()['c'];
?>

<?php include '../includes/header.php'; ?>

<h2>Admin Dashboard</h2>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Products</h5>
            <h2><?php echo $total_products; ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Users</h5>
            <h2><?php echo $total_users; ?></h2>
        </div>
    </div>
</div>

<hr class="my-4">
<a href="products.php" class="btn btn-outline-dark">Manage Products</a>
<a href="add_product.php" class="btn btn-primary">Add Product</a>
<a href="manage_stocks.php" class="btn btn-outline-primary">Manage Stocks</a>
<a href="users.php" class="btn btn-secondary">Manage Users</a>

<?php include '../includes/footer.php'; ?>

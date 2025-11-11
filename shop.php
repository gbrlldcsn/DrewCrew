<?php
$cfg = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
if (!file_exists($cfg)) {
    $cfg = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'DrewCrew' . DIRECTORY_SEPARATOR . 'config.php';
}
require_once $cfg;
include __DIR__ . '/includes/header.php';

// Filters
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$size = trim($_GET['size'] ?? '');
$minPrice = $_GET['min'] ?? '';
$maxPrice = $_GET['max'] ?? '';

$where = [];
$params = [];
$types = '';

if ($q !== '') { $where[] = '(p.name LIKE CONCAT("%", ?, "%") OR p.description LIKE CONCAT("%", ?, "%"))'; $params[] = $q; $params[] = $q; $types .= 'ss'; }
if ($category !== '') {
    $where[] = '(c.category_name = ? OR p.image LIKE ?)';
    $params[] = $category;
    $params[] = '%/' . $category . '/%';
    $types .= 'ss';
}
if ($size !== '') { $where[] = 'p.size LIKE CONCAT("%", ?, "%")'; $params[] = $size; $types .= 's'; }
if ($minPrice !== '' && is_numeric($minPrice)) { $where[] = 'p.price >= ?'; $params[] = (float)$minPrice; $types .= 'd'; }
if ($maxPrice !== '' && is_numeric($maxPrice)) { $where[] = 'p.price <= ?'; $params[] = (float)$maxPrice; $types .= 'd'; }

$sql = 'SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY p.created_at DESC';

if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query('SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
}
?>

<h1 class="fw-bold mb-4 text-center">Shop</h1>

<form method="GET" class="card shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search products">
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">All</option>
                <option value="Tees" <?php echo ($category==='Tees')?'selected':''; ?>>Tees</option>
                <option value="Bottoms" <?php echo ($category==='Bottoms')?'selected':''; ?>>Bottoms</option>
                <option value="Accessories" <?php echo ($category==='Accessories')?'selected':''; ?>>Accessories</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Size</label>
            <input type="text" name="size" value="<?php echo htmlspecialchars($size); ?>" class="form-control" placeholder="e.g. M, L">
        </div>
        <div class="col-md-2">
            <label class="form-label">Min Price</label>
            <input type="number" step="0.01" name="min" value="<?php echo htmlspecialchars($minPrice); ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">Max Price</label>
            <input type="number" step="0.01" name="max" value="<?php echo htmlspecialchars($maxPrice); ?>" class="form-control">
        </div>
    </div>
    <div class="mt-3 d-flex gap-2">
        <button class="btn btn-gold" type="submit">Apply</button>
        <a href="/DrewCrew/shop.php" class="btn btn-outline-secondary">Reset</a>
    </div>
    <?php if (!empty($category) || !empty($q) || !empty($size) || $minPrice!=='' || $maxPrice!==''): ?>
        <div class="mt-2 small text-muted">Filters active.</div>
    <?php endif; ?>
</form>

<div class="row g-4 mb-5">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm h-100 product-card">
                    <?php 
                        $img = trim($product['image'] ?? '');
                        $src = '';
                        if ($img) {
                            $img = str_replace('\\', '/', $img);
                            if (preg_match('#^https?://#i', $img)) {
                                $src = $img;
                            } elseif (str_starts_with($img, '/DrewCrew/')) {
                                $src = $img;
                            } elseif (str_starts_with($img, '/assets/')) {
                                $src = '/DrewCrew' . $img;
                            } elseif (str_starts_with($img, 'assets/')) {
                                $src = '/DrewCrew/' . $img;
                            } elseif (str_starts_with($img, '/uploads/')) {
                                $src = '/DrewCrew' . $img;
                            } elseif (str_starts_with($img, 'uploads/')) {
                                $src = '/DrewCrew/' . $img;
                            } else {
                                // If absolute filesystem path, convert using document root
                                $doc = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
                                if ($doc && str_starts_with(str_replace('\\', '/', $img), $doc)) {
                                    $src = '/' . ltrim(str_replace($doc, '', str_replace('\\', '/', $img)), '/');
                                } else {
                                    $src = '/DrewCrew/uploads/' . $img;
                                }
                            }
                        }
                    ?>
                    <?php if (!empty($src)): ?>
                        <a href="/DrewCrew/product.php?id=<?php echo (int)$product['id']; ?>">
                            <img src="<?php echo htmlspecialchars($src); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 300px;">
                            <span class="text-muted">No Image</span>
                        </div>
                    <?php endif; ?>
                    <div class="product-caption">
                        <a href="/DrewCrew/product.php?id=<?php echo (int)$product['id']; ?>" class="text-decoration-none">
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        </a>
                        <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                        <?php 
                        $stock = (int)($product['stock'] ?? 0);
                        $isOutOfStock = $stock <= 0;
                        ?>
                        <div class="product-stock mb-2" style="font-size: 0.85rem;">
                            <?php if ($isOutOfStock): ?>
                                <span style="color: #ff4444; font-weight: 600;">SOLD OUT</span>
                            <?php else: ?>
                                <span style="color: #00ff00; font-weight: 600;">Stock: <?php echo $stock; ?></span>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="/DrewCrew/cart.php" class="mt-3">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-gold btn-sm w-100" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                                <?php echo $isOutOfStock ? 'Sold Out' : 'Add to Cart'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <p class="mb-0">No products available yet. Check back soon!</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>


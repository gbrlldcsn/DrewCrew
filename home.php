<?php include __DIR__ . '/includes/header.php'; ?>

<?php
$cfg = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
if (!file_exists($cfg)) {
    $cfg = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'DrewCrew' . DIRECTORY_SEPARATOR . 'config.php';
}
include $cfg;

function dc_normalize_image_url_home(string $path): string {
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
?>

<div class="text-center">
    <h1 class="fw-bold mb-4">Welcome to DrewCrew</h1>
    <p class="lead">Premium and clean style clothing for everyone.</p>
    <a href="shop.php" class="btn btn-gold btn-lg mt-3">Shop Now</a>
  </div>

<hr class="my-5">

<h2 class="fw-bold text-center mb-4">Featured Products</h2>

<?php
// Try ordering by created_at if column exists; otherwise fall back to id
$res = $conn->query('SELECT p.* FROM products p ORDER BY p.created_at DESC LIMIT 4');
if (!$res) {
    $res = $conn->query('SELECT p.* FROM products p ORDER BY p.id DESC LIMIT 4');
}
?>
<div class="row g-4 justify-content-center">
  <?php if ($res && $res->num_rows): ?>
    <?php while ($p = $res->fetch_assoc()): ?>
      <?php
        $img = trim($p['image'] ?? '');
        $src = $img ? dc_normalize_image_url_home($img) : '/DrewCrew/assets/products/accessories/accessories1.png';
      ?>
      <div class="col-md-3 col-sm-6">
        <div class="card h-100 shadow-sm product-card">
          <a href="/DrewCrew/product.php?id=<?php echo (int)$p['id']; ?>">
            <img src="<?php echo htmlspecialchars($src); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
          </a>
          <div class="product-caption">
            <a class="text-decoration-none" href="/DrewCrew/product.php?id=<?php echo (int)$p['id']; ?>">
                <div class="product-title"><?php echo htmlspecialchars($p['name']); ?></div>
            </a>
            <div class="product-price">₱<?php echo number_format((float)$p['price'], 2); ?></div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="alert alert-light text-center">No products yet...</div>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

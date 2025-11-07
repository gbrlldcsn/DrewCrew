<?php
$cfg = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$cfg = file_exists($cfg) ? $cfg : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'DrewCrew' . DIRECTORY_SEPARATOR . 'config.php';
require_once $cfg;
include __DIR__ . '/includes/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo '<div class="alert alert-danger container my-5">Invalid product.</div>';
    include 'includes/footer.php';
    exit();
}

$stmt = $conn->prepare('SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$product) {
    echo '<div class="alert alert-warning container my-5">Product not found.</div>';
    include 'includes/footer.php';
    exit();
}

$img = trim($product['image'] ?? '');
// Normalize various stored formats to a browser path
function dc_normalize_image_url(string $path): string {
    $path = str_replace('\\', '/', $path);
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path)) return $path; // external url
    // If full filesystem path, map to web path using document root
    if (preg_match('#^[A-Za-z]:/#', $path) || str_starts_with($path, $_SERVER['DOCUMENT_ROOT'] ?? '')) {
        $doc = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        $web = '/' . ltrim(str_replace($doc, '', $path), '/');
        return $web;
    }
    if (str_starts_with($path, '/DrewCrew/')) return $path;
    if (str_starts_with($path, '/assets/')) return '/DrewCrew' . $path;
    if (str_starts_with($path, 'assets/')) return '/DrewCrew/' . $path;
    if (str_starts_with($path, '/uploads/')) return '/DrewCrew' . $path;
    if (str_starts_with($path, 'uploads/')) return '/DrewCrew/' . $path;
    // default to uploads folder for bare filenames
    return '/DrewCrew/uploads/' . $path;
}

$src = $img !== '' ? dc_normalize_image_url($img) : '/DrewCrew/assets/products/accessories/accessories1.png';
?>

<div class="container my-5" style="background: transparent;">
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="ratio ratio-1x1 d-flex align-items-center justify-content-center" style="background: transparent; border: none;">
        <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width:100%; max-height:100%; object-fit:contain;">
      </div>
    </div>
    <div class="col-lg-6 text-light">
      <h2 class="fw-bold text-white"><?php echo htmlspecialchars($product['name']); ?></h2>
      <div class="mb-3 text-white-50">Category: <?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
      <div class="h4 text-primary mb-3">₱<?php echo number_format((float)$product['price'], 2); ?></div>
      <ul class="mb-4 text-white">
        <?php if (!empty($product['description'])): ?>
          <li><?php echo htmlspecialchars($product['description']); ?></li>
        <?php else: ?>
          <li>Premium materials</li>
          <li>Minimalist design</li>
          <li>Everyday comfort</li>
        <?php endif; ?>
      </ul>

      <form class="d-flex align-items-center gap-3" method="POST" action="/DrewCrew/cart.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
        <div class="input-group" style="width:140px;">
          <button class="btn btn-outline-secondary" type="button" onclick="var q=document.getElementById('qty'); q.value=Math.max(1, parseInt(q.value||'1')-1)">-</button>
          <input type="number" id="qty" name="quantity" value="1" min="1" class="form-control text-center">
          <button class="btn btn-outline-secondary" type="button" onclick="var q=document.getElementById('qty'); q.value=parseInt(q.value||'1')+1">+</button>
        </div>
        <button type="submit" class="btn btn-dark flex-grow-1">Add to Cart</button>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>



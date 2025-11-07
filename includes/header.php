<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
$role = $_SESSION['role'] ?? null;
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

// Determine prefix so assets path resolves correctly whether header is included from root or from /admin/
$script = $_SERVER['SCRIPT_NAME']; // e.g. /DrewCrew/home.php or /DrewCrew/admin/products.php
$asset_prefix = (strpos($script, '/admin/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>DrewCrew</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="<?php echo $asset_prefix; ?>assets/css/style.css">

  <style>
    .navbar-custom { background-color: rgba(255,255,255,0.85); backdrop-filter: blur(6px); padding-top: 0; padding-bottom: 0; overflow: visible; border-bottom: 1px solid rgba(0,0,0,0.06); }
    .navbar-custom .container { min-height: 56px; position: relative; display: flex; align-items: center; justify-content: space-between; }
    .navbar-brand, .nav-link { color: #000 !important; }
    .nav-link { padding: 0.5rem 1rem !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .nav-link:hover { color: #FFD700 !important; }
    .btn-gold { background-color: #FFD700; color:#000; font-weight:700; }
    .btn-gold:hover { background-color:#e6c200; }
    .drewcrew-logo { height:80px; width:auto; object-fit:contain; display:block; margin-top:-8px; margin-bottom:-8px; }
    @media (max-width:576px){ .drewcrew-logo{ height:56px; } }
    .icon-link img { height: 22px; width: 22px; display:inline-block; vertical-align:middle; }
    .navbar-nav-center { position: absolute; left: 50%; transform: translateX(-50%); display: flex; gap: 0.25rem; }
    .navbar-nav-right { margin-left: auto; }
    .left-tools { display: inline-flex; align-items: center; gap: 10px; }
    .cart-badge { position: absolute; top: 0; right: 0; transform: translate(45%, -45%); background-color: #FFD700; color:#000; font-size: 0.65rem; font-weight:700; border-radius: 999px; padding: 2px 6px; }
    @media (max-width: 991px) {
      .navbar-nav-center { position: static; transform: none; }
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <div class="left-tools">
      <!-- dropdown menu icon next to logo -->
      <div class="dropdown">
        <a class="nav-link dropdown-toggle p-0" href="#" id="categoryMenuLeft" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="/DrewCrew/assets/icons/menu.png" alt="Menu" style="height:24px; width:24px; display:inline-block;">
        </a>
        <ul class="dropdown-menu" aria-labelledby="categoryMenuLeft">
          <li><a class="dropdown-item" href="/DrewCrew/shop.php?category=tees">Tees</a></li>
          <li><a class="dropdown-item" href="/DrewCrew/shop.php?category=bottoms">Bottoms</a></li>
          <li><a class="dropdown-item" href="/DrewCrew/shop.php?category=accessories">Accessories</a></li>
        </ul>
      </div>

      <!-- logo: use computed prefix -->
      <a class="navbar-brand d-flex align-items-center" href="/DrewCrew/home.php">
        <img src="/DrewCrew/assets/img/DrewCrew.png" alt="DrewCrew" class="drewcrew-logo"
             onerror="this.style.display='none'">
      </a>
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
            style="border: none;">
      <img src="/DrewCrew/assets/icons/menu.png" alt="Menu" style="height:28px; width:28px; display:inline-block;">
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav navbar-nav-center">
        <li class="nav-item"><a class="nav-link" href="/DrewCrew/home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/DrewCrew/shop.php">Shop</a></li>
        
        <li class="nav-item"><a class="nav-link" href="/DrewCrew/how-to-order.php">How to Order</a></li>
        <li class="nav-item"><a class="nav-link" href="/DrewCrew/size-chart.php">Size Chart</a></li>
        <li class="nav-item"><a class="nav-link" href="/DrewCrew/contact.php">Contact</a></li>
      </ul>

      <ul class="navbar-nav navbar-nav-right">
        <?php if($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="/DrewCrew/admin/AdminDashboard.php">Admin</a></li>
        <?php endif; ?>

        <li class="nav-item position-relative">
          <a class="nav-link icon-link" href="/DrewCrew/cart.php" title="Cart">
            <img src="/DrewCrew/assets/icons/shopping-cart.png" alt="Cart">
          </a>
          <?php if($cartCount > 0): ?>
            <span class="cart-badge"><?php echo $cartCount; ?></span>
          <?php endif; ?>
        </li>

        <?php if($user): ?>
          <li class="nav-item">
            <a class="nav-link icon-link" href="/DrewCrew/account.php" title="My Account">
              <img src="/DrewCrew/assets/icons/person-fill.svg" alt="Account">
            </a>
          </li>
          <li class="nav-item"><a class="btn btn-gold" href="/DrewCrew/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link icon-link" href="/DrewCrew/login.php" title="Login">
              <img src="/DrewCrew/assets/icons/person-fill.svg" alt="Login">
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">

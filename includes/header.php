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
$is_admin_page = (strpos($script, '/admin/') !== false);
$is_login_page = (strpos($script, 'login.php') !== false);
$is_account_page = (strpos($script, 'account.php') !== false);
?>
<!DOCTYPE html>
<html lang="en"<?php echo $is_admin_page ? ' class="admin-page"' : ''; ?>>
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
    .nav-link { 
        padding: 0.5rem 1rem !important; 
        font-size: 0.95rem; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .nav-link:hover {
        color: #FFD700 !important;
    }
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: #FFD700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(-50%);
    }
    .nav-link:active::after {
        width: 80%;
    }
    .btn-gold { 
        background-color: #FFD700; 
        color:#000; 
        font-weight:700; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(255, 215, 0, 0.2);
    }
    .btn-gold:active {
        transform: translateY(1px);
        box-shadow: 0 1px 4px rgba(255, 215, 0, 0.3);
    }
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
<body<?php 
    $body_class = '';
    if ($is_admin_page) $body_class = 'admin-page';
    if ($is_login_page) $body_class = 'login-page';
    if ($is_account_page) $body_class = 'account-page';
    echo $body_class ? ' class="' . $body_class . '"' : '';
?>>
<?php if ($is_admin_page): ?>
<style>
html {
    height: 100% !important;
}
body.admin-page {
    height: 100% !important;
    min-height: 100vh !important;
    display: flex !important;
    flex-direction: column !important;
}
body.admin-page > nav {
    flex-shrink: 0 !important;
}
body.admin-page > .container.mt-4 {
    flex: 1 1 auto !important;
    min-height: calc(100vh - 150px) !important;
    padding-bottom: 3rem !important;
}
body.admin-page footer,
body.admin-page > footer,
body.admin-page .admin-footer {
    margin-top: auto !important;
    flex-shrink: 0 !important;
    width: 100% !important;
}
</style>
<?php endif; ?>
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
            <a class="nav-link icon-link" href="<?php echo ($role === 'admin') ? '/DrewCrew/admin/AdminDashboard.php' : '/DrewCrew/account.php'; ?>" title="<?php echo ($role === 'admin') ? 'Admin Dashboard' : 'My Account'; ?>">
              <img src="<?php echo $asset_prefix; ?>assets/icons/person-fill.svg" alt="<?php echo ($role === 'admin') ? 'Admin' : 'Account'; ?>">
            </a>
          </li>
          <li class="nav-item"><a class="btn btn-gold" href="/DrewCrew/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link icon-link" href="/DrewCrew/login.php" title="Login">
              <img src="<?php echo $asset_prefix; ?>assets/icons/person-fill.svg" alt="Login">
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">

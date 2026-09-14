<?php
$current = basename($_SERVER['PHP_SELF']);
// Detect if we're inside the admin folder
$inAdmin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$root    = $inAdmin ? '../' : '';
$cssDir  = $inAdmin ? '../css/' : 'css/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'E-Bike Registry') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $cssDir ?>style.css">
</head>
<body>

<nav class="main-nav">
  <a href="<?= $root ?>index.php" class="nav-logo">⚡ Tantiado's E-bike Registration</a>

  <div class="nav-links-wrap" id="navMenu">
    <ul class="nav-menu">
      <li><a href="<?= $root ?>index.php"         class="<?= $current==='index.php' && !$inAdmin ? 'active':'' ?>">Home</a></li>
      <li><a href="<?= $root ?>index.php#how"     >How It Works</a></li>
      <li><a href="<?= $root ?>register.php"      class="<?= $current==='register.php'     ? 'active':'' ?>">Register</a></li>
      <li><a href="<?= $root ?>check_status.php"  class="<?= $current==='check_status.php' ? 'active':'' ?>">Check Status</a></li>
      <li><a href="<?= $root ?>admin/index.php"   class="<?= $inAdmin                      ? 'active':'' ?>">Admin</a></li>
    </ul>
  </div>

  <div class="nav-right">
    <a href="<?= $root ?>register.php" class="btn btn-primary btn-sm">Register Now</a>
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$current = $_SERVER['SCRIPT_NAME']; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réservation en ligne</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg my-navbar-custom">
  <div class="container-fluid">
    
    <a class="navbar-brand" href="/reservation/index.php">Reservation</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($current, 'index.php') !== false) ? 'active' : ''; ?>" 
             href="/reservation/index.php">
            Accueil
          </a>
        </li>
       <li class="nav-item">
          <a class="nav-link <?php echo (strpos($current, 'contact.php') !== false) ? 'active' : ''; ?>"
             href="/reservation/contact.php">
            Contact
          </a>
        </li>

        <?php if (!isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current, 'register.php') !== false) ? 'active' : ''; ?>"
               href="/reservation/register.php">
               Inscription
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current, 'login.php') !== false) ? 'active' : ''; ?>"
               href="/reservation/login.php">
               Connexion
            </a>
          </li>
        <?php else: ?>
          <?php
            $stmtRole = $pdo->prepare("SELECT role FROM users WHERE id=?");
            $stmtRole->execute([$_SESSION['user_id']]);
            $role = $stmtRole->fetchColumn();
          ?>
          <?php if ($role === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo (strpos($current, 'admin') !== false) ? 'active' : ''; ?>"
                 href="/reservation/admin/appointments.php">
                 Administration
              </a>
            </li>
          <?php endif; ?>
          
          <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current, 'profile.php') !== false) ? 'active' : ''; ?>"
               href="/reservation/profile.php">
               Mon profil
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current, 'logout.php') !== false) ? 'active' : ''; ?>"
               href="/reservation/logout.php">
               Déconnexion
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4">

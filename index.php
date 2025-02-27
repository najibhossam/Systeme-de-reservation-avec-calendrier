<?php
require_once 'header.php';
require_once 'db.php';
$userId = $_SESSION['user_id'] ?? null;
?>

<div class="p-5 mb-4 bg-light rounded-3">
  <div class="container py-5">
    <?php if ($userId): ?>
      <?php
      $stmt = $pdo->prepare("SELECT firstname FROM users WHERE id = ?");
      $stmt->execute([$userId]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $firstname = $row['firstname'] ?? '';
      $hour = (int) date('H');
      if ($hour >= 5 && $hour < 18) {
          $greeting = "Bonjour";
      } else {
          $greeting = "Bonsoir";
      }
      ?>
      <h1 class="display-5 fw-bold"><?php echo "$greeting, $firstname!"; ?></h1>
      <p class="col-md-8 fs-4">Vous êtes déjà connecté.</p>
      <a class="btn btn-primary btn-lg" href="/reservation/profile.php">Continuer</a>
      <a class="btn btn-outline-primary btn-lg" href="/reservation/logout.php">Se déconnecter</a>
    <?php else: ?>
      <h1 class="display-5 fw-bold">Bienvenue sur notre plateforme de réservation</h1>
      <p class="col-md-8 fs-4">Veuillez vous inscrire ou vous connecter pour continuer.</p>
      <a class="btn btn-primary btn-lg" href="/reservation/register.php">S'inscrire</a>
      <a class="btn btn-secondary btn-lg" href="/reservation/login.php">Se connecter</a>
    <?php endif; ?>
  </div>
</div>

<?php
require_once 'footer.php';
?>

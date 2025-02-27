<?php
require_once 'header.php';
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtAppt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_datetime DESC");
$stmtAppt->execute([$userId]);
$appointments = $stmtAppt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-3">Mon profil</h2>

<div class="row">
  <div class="col-md-6 mb-3">
    <div class="card">
      <div class="card-header">Informations personnelles</div>
      <div class="card-body">
        <p><strong>Nom :</strong> <?php echo e($user['lastname']); ?></p>
        <p><strong>Prénom :</strong> <?php echo e($user['firstname']); ?></p>
        <p><strong>Email :</strong> <?php echo e($user['email']); ?></p>
        <p><strong>Adresse :</strong> <?php echo e($user['address']); ?></p>
        <p><strong>Téléphone :</strong> <?php echo e($user['phone']); ?></p>
        <p><strong>Date de naissance :</strong> <?php echo e($user['birthdate']); ?></p>
        <a href="update_profile.php" class="btn btn-warning">Modifier</a>
        <a href="delete_account.php" class="btn btn-danger" onclick="return confirm('Supprimer le compte ?')">Supprimer</a>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-3">
    <div class="card">
      <div class="card-header">Mes rendez-vous</div>
      <div class="card-body">
        <a href="appointments/schedule.php" class="btn btn-primary mb-3">Prendre un rendez-vous</a>
        <ul class="list-group">
          <?php foreach ($appointments as $appt): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>Le <strong><?php echo e($appt['appointment_datetime']); ?></strong></span>
              <a class="btn btn-sm btn-outline-danger"
                 href="appointments/cancel_appointment.php?id=<?php echo e($appt['id']); ?>"
                 onclick="return confirm('Voulez-vous annuler ce rendez-vous ?')">Annuler</a>
            </li>
          <?php endforeach; ?>
          <?php if (count($appointments) == 0): ?>
            <li class="list-group-item">Aucun rendez-vous.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php
require_once 'footer.php';
?>

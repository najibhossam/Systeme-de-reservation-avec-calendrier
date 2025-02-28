<?php
require_once 'header.php';
require_once 'db.php';
require_once 'csrf.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("<div class='alert alert-danger'>Token CSRF invalide.</div>");
    }

    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "<div class='alert alert-danger'>Tous les champs sont obligatoires.</div>";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "<div class='alert alert-danger'>Le nouveau mot de passe et sa confirmation ne correspondent pas.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $error = "<div class='alert alert-danger'>L'ancien mot de passe est incorrect.</div>";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmtUpd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtUpd->execute([$hashed, $userId]);
            $success = "<div class='alert alert-success'>Votre mot de passe a été mis à jour avec succès.</div>";
        }
    }
    $csrfToken = generateCsrfToken();
} else {
    $csrfToken = generateCsrfToken();
}
?>

<h2>Changer mon mot de passe</h2>

<?php
if (!empty($error)) {
    echo $error;
}
if (!empty($success)) {
    echo $success;
    echo '<script>
            setTimeout(function(){
              window.location.href = "profile.php";
            }, 5000);
          </script>';
    echo '<br><a href="profile.php" class="btn btn-success">Continuer</a>';
}
?>

<?php if (empty($success)) : ?>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
    <div class="mb-3">
        <label for="current_password" class="form-label">Ancien mot de passe</label>
        <input type="password" name="current_password" id="current_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="new_password" class="form-label">Nouveau mot de passe</label>
        <input type="password" name="new_password" id="new_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
</form>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

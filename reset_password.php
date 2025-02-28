<?php
require_once 'header.php';
require_once 'db.php';
require_once 'csrf.php';
require_once 'functions.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    die("<div class='alert alert-danger'>Token manquant.</div>");
}

$stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE reset_token = ?");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("<div class='alert alert-danger'>Token invalide ou expiré.</div>");
}
$userId = $row['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("<div class='alert alert-danger'>Token CSRF invalide.</div>");
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    if (empty($newPassword)) {
        $error = "<div class='alert alert-danger'>Le mot de passe ne peut pas être vide.</div>";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmtUpd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmtUpd->execute([$hashed, $userId]);
        $stmtDel = $pdo->prepare("DELETE FROM password_resets WHERE reset_token = ?");
        $stmtDel->execute([$token]);
        $success = "<div class='alert alert-success'>Mot de passe mis à jour ! Vous pouvez vous connecter.</div>";
    }
    $csrfToken = generateCsrfToken();
} else {
    $csrfToken = generateCsrfToken();
}
?>

<h2>Réinitialiser le mot de passe</h2>

<?php
if (isset($error)) {
    echo $error;
}
if (isset($success)) {
    echo $success;
    echo '<script>
            setTimeout(function(){
              window.location.href = "login.php";
            }, 5000);
          </script>';
    echo '<br><a href="login.php" class="btn btn-success">Continuer vers la connexion</a>';
}
?>

<?php if (!isset($success)) : ?>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
    <div class="mb-3">
        <label for="new_password" class="form-label">Nouveau mot de passe</label>
        <input type="password" name="new_password" id="new_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Valider</button>
</form>
<?php endif; ?>

<?php
require_once 'footer.php';
?>

<?php
require_once 'header.php';
require_once 'db.php';
require_once 'csrf.php';
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('/reservation/profile.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("<div class='alert alert-danger'>Token CSRF invalide.</div>");
    }
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_active'] == 1) {
            $_SESSION['user_id'] = $user['id'];
            redirect('profile.php');
        } else {
            echo "<div class='alert alert-danger'>Votre compte n'est pas activé. Vérifiez votre email.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Identifiants incorrects.</div>";
    }
    $csrfToken = generateCsrfToken();
} else {
    $csrfToken = generateCsrfToken();
}
?>

<h2 class="mb-3">Connexion</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

    <div class="mb-3">
        <label for="email" class="form-label">Email :</label>
        <input type="email" class="form-control" name="email" id="email" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe :</label>
        <input type="password" class="form-control" name="password" id="password" required>
    </div>

    <button type="submit" class="btn btn-primary">Se connecter</button>
    <a href="/reservation/forgot_password.php" class="btn btn-link">Mot de passe oublié ?</a>
</form>

<?php
require_once 'footer.php';
?>

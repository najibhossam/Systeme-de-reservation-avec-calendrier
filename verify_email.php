<?php
require_once 'header.php';
require_once 'db.php';
require_once 'functions.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT user_id FROM activation_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $userId = $row['user_id'];
        $stmtUpdate = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmtUpdate->execute([$userId]);
        $stmtDel = $pdo->prepare("DELETE FROM activation_tokens WHERE token = ?");
        $stmtDel->execute([$token]);

        echo "<div class='alert alert-success'>Votre compte est maintenant activé. Vous pouvez vous connecter.</div>";
        echo '<a class="btn btn-primary" href="login.php">Se connecter</a>';
    } else {
        echo "<div class='alert alert-danger'>Token invalide ou expiré.</div>";
    }
} else {
    echo "<div class='alert alert-warning'>Aucun token fourni.</div>";
}

require_once 'footer.php';
?>

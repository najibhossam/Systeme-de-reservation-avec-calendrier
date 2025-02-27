<?php
require_once 'header.php';
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

$stmtAppt = $pdo->prepare("DELETE FROM appointments WHERE user_id = ?");
$stmtAppt->execute([$userId]);

$stmtUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmtUser->execute([$userId]);

session_unset();
session_destroy();

echo "<div class='alert alert-info'>Votre compte a bien été supprimé.</div>";
echo '<a class="btn btn-primary" href="index.php">Retour à l\'accueil</a>';

require_once 'footer.php';
?>

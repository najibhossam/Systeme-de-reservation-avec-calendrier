<?php
require_once 'header.php';

session_unset();
session_destroy();

echo "<div class='alert alert-info'>Vous êtes déconnecté.</div>";
echo '<a class="btn btn-primary" href="index.php">Retour à l\'accueil</a>';

require_once 'footer.php';
?>

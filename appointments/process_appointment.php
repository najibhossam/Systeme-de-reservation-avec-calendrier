<?php
require_once '../header.php';
require_once '../db.php';
require_once '../functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/reservation/login.php');
}

if (!isset($_GET['datetime'])) {
    die("Pas de date/heure transmise.");
}

$datetimeStr = $_GET['datetime'];
try {
    $chosen = new DateTime($datetimeStr);
} catch (Exception $e) {
    die("Format de date invalide : " . $e->getMessage());
}

$now = new DateTime();
if ($chosen < $now) {
    die("<div class='alert alert-danger'>Impossible de réserver dans le passé.</div>");
}

$hour = (int) $chosen->format('H');
$minute = (int) $chosen->format('i');
if ($hour < 8 || $hour >= 24) {
    die("<div class='alert alert-danger'>Créneau hors plage (8h–minuit).</div>");
}
if ($minute !== 0 && $minute !== 30) {
    die("<div class='alert alert-danger'>Créneaux uniquement toutes les 30 minutes.</div>");
}

$stmtCheck = $pdo->prepare("SELECT id FROM appointments WHERE appointment_datetime=?");
$stmtCheck->execute([$chosen->format('Y-m-d H:i:s')]);
if ($stmtCheck->rowCount() > 0) {
    die("<div class='alert alert-danger'>Ce créneau est déjà pris.</div>");
}

$userId = $_SESSION['user_id'];
$stmtIns = $pdo->prepare("
    INSERT INTO appointments (user_id, appointment_datetime)
    VALUES (?, ?)
");
$stmtIns->execute([$userId, $chosen->format('Y-m-d H:i:s')]);

$stmtUser = $pdo->prepare("SELECT firstname, email FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$firstname = $user['firstname'];
$userEmail = $user['email'];

require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'mail.gmx.com';  
    $mail->SMTPAuth   = true;
    $mail->Username   = 'najibhossam@gmx.fr';
    $mail->Password   = '';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('najibhossam@gmx.fr', 'Reservation Bot');
    $mail->addAddress($userEmail, $firstname);
    $mail->isHTML(true);

    $mail->Subject = "Confirmation de votre rendez-vous";
    $dateFormattee = $chosen->format('d/m/Y H:i');
    $mail->Body = "
      Bonjour $firstname,<br><br>
      Votre rendez-vous est bien enregistré pour le <strong>$dateFormattee</strong>.<br>
      À bientôt !
    ";

    $mail->send();

    echo "<div class='alert alert-success'>Rendez-vous enregistré avec succès. Un email de confirmation vous a été envoyé.</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-warning'>Rendez-vous enregistré, mais erreur d'envoi mail : {$mail->ErrorInfo}</div>";
}

echo '<a href="/reservation/profile.php" class="btn btn-primary">Retour au profil</a>';

require_once '../footer.php';
?>

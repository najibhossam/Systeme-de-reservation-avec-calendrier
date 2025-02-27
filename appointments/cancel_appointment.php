<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
if (!isset($_SESSION['user_id'])) {
    redirect('/reservation/login.php');
}
$userId = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Aucun ID de rendez-vous.");
}
$stmt = $pdo->prepare("
    SELECT a.id, a.appointment_datetime, u.firstname, u.lastname, u.email
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$id, $userId]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    die("<div class='alert alert-danger'>Rendez-vous introuvable ou vous n'avez pas accès.</div>");
}
$stmtDel = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
$stmtDel->execute([$id]);
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

    $mail->setFrom('najibhossam@gmx.fr', 'Réservation');
    $mail->addAddress($appt['email'], $appt['firstname'].' '.$appt['lastname']);
    $mail->isHTML(true);

    $mail->Subject = 'Annulation de votre rendez-vous';
    $mail->Body = "
        Bonjour {$appt['firstname']},<br><br>
        Votre rendez-vous du <strong>{$appt['appointment_datetime']}</strong> vient d'être annulé.<br>
        Nous restons à votre disposition pour toute nouvelle réservation.
        <br><br>
        Cordialement,<br>L'équipe Réservation
    ";

    $mail->send();
    echo "<div class='alert alert-success'>
            Rendez-vous du <strong>{$appt['appointment_datetime']}</strong> annulé avec succès.<br>
            Un email de confirmation vous a été envoyé.
          </div>";
} catch (Exception $e) {
    echo "<div class='alert alert-warning'>
            Rendez-vous annulé, mais l'email n'a pas pu être envoyé : {$mail->ErrorInfo}
          </div>";
}

echo '<a href="/reservation/profile.php" class="btn btn-primary">Retour à mon profil</a>';

require_once __DIR__ . '/../footer.php';
?>

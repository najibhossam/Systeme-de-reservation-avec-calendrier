<?php
require_once '../header.php';
require_once '../db.php';
require_once '../functions.php';
if (!isset($_SESSION['user_id'])) {
    redirect('../login.php');
}

$stmtCheck = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtCheck->execute([$_SESSION['user_id']]);
$userRole = $stmtCheck->fetchColumn();
if ($userRole !== 'admin') {
    redirect('../non_autorise.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de rendez-vous non spécifié.");
}

$stmt = $pdo->prepare("
    SELECT a.id, a.user_id, a.appointment_datetime, u.email, u.firstname, u.lastname
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    die("Rendez-vous introuvable.");
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
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    
    $mail->setFrom('najibhossam@gmx.fr', 'Status Reservation');
    $mail->addAddress($appt['email'], $appt['firstname'].' '.$appt['lastname']);
    $mail->isHTML(true);

    $mail->Subject = 'Annulation de rendez-vous';
    $mail->Body = "
        Bonjour {$appt['firstname']},<br>
        Votre rendez-vous du <strong>{$appt['appointment_datetime']}</strong> a été annulé par l'administrateur.<br>
        Pour plus d'informations, veuillez nous contacter.
    ";

    $mail->send();
} catch (Exception $e) {
    echo "<div class='alert alert-warning'>Le rendez-vous est supprimé, mais l'email n'a pas pu être envoyé : " . $mail->ErrorInfo . "</div>";
}

echo "<div class='alert alert-success'>Rendez-vous supprimé avec succès. Un email a été envoyé à l'utilisateur.</div>";
echo '<a href="appointments.php" class="btn btn-primary">Retour à la liste des rendez-vous</a>';

require_once '../footer.php';
?>

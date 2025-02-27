<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    redirect('/reservation/login.php');
}

$stmtCheck = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtCheck->execute([$_SESSION['user_id']]);
$userRole = $stmtCheck->fetchColumn();
if ($userRole !== 'admin') {
    redirect('/reservation/non_autorise.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID manquant.");
}

$stmt = $pdo->prepare("
  SELECT a.*, u.firstname, u.lastname, u.email
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.id = ?
");
$stmt->execute([$id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    die("Rendez-vous introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newDatetimeStr = $_POST['datetime'];
    $newDatetime = DateTime::createFromFormat('Y-m-d\TH:i', $newDatetimeStr);
    
    if (!$newDatetime) {
        echo "<div class='alert alert-danger'>Format de date/heure invalide.</div>";
    } else {
        $now = new DateTime();
        if ($newDatetime < $now) {
            echo "<div class='alert alert-danger'>Impossible de reprogrammer dans le passé.</div>";
        } else {
            $hour = (int) $newDatetime->format('H');
            $minute = (int) $newDatetime->format('i');

            if ($hour < 8 || $hour >= 24) {
                echo "<div class='alert alert-danger'>Créneau hors plage (8h à minuit).</div>";
            } elseif ($minute !== 0 && $minute !== 30) {
                echo "<div class='alert alert-danger'>Créneaux disponibles uniquement toutes les 30 minutes.</div>";
            } else {
                $stmtCheckTime = $pdo->prepare("
                  SELECT id FROM appointments
                  WHERE appointment_datetime = ? AND id != ?
                ");
                $stmtCheckTime->execute([
                    $newDatetime->format('Y-m-d H:i:s'),
                    $id
                ]);
                if ($stmtCheckTime->rowCount() > 0) {
                    echo "<div class='alert alert-danger'>Ce créneau est déjà occupé.</div>";
                } else {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE appointments
                        SET appointment_datetime = ?
                        WHERE id = ?
                    ");
                    $stmtUpdate->execute([
                        $newDatetime->format('Y-m-d H:i:s'),
                        $id
                    ]);
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

                        $mail->setFrom('najibhossam@gmx.fr', 'Status Reservation');
                        $mail->addAddress($appt['email'], $appt['firstname'].' '.$appt['lastname']);
                        $mail->isHTML(true);

                        $mail->Subject = 'Modification de votre rendez-vous';
                        $mail->Body = "
                            Bonjour {$appt['firstname']},<br>
                            Votre rendez-vous a été reprogrammé au
                            <strong>".$newDatetime->format('d/m/Y H:i')."</strong>.<br>
                            Cordialement,<br>L'équipe
                        ";
                        $mail->send();

                        echo "<div class='alert alert-success'>Rendez-vous modifié avec succès. Un email a été envoyé à l'utilisateur.</div>";
                    } catch (Exception $e) {
                        echo "<div class='alert alert-warning'>Rendez-vous modifié, mais échec de l'envoi du mail : " . $mail->ErrorInfo . "</div>";
                    }
                }
            }
        }
    }
}
?>

<h2>Modifier le rendez-vous</h2>

<p>Rendez-vous actuel :
   <strong><?php echo htmlspecialchars($appt['appointment_datetime']); ?></strong>
   <br>Utilisateur :
   <strong><?php echo htmlspecialchars($appt['firstname']." ".$appt['lastname']); ?></strong>
</p>

<form method="post">
  <label for="datetime">Nouvelle date/heure :</label>
  <input type="datetime-local" name="datetime" id="datetime" class="form-control" required>
  <br>
  <button type="submit" class="btn btn-warning">Valider</button>
</form>

<a href="/reservation/admin/appointments.php" class="btn btn-secondary">Retour à la liste des Rendez-vous</a>

<?php
require_once __DIR__ . '/../footer.php';
?>

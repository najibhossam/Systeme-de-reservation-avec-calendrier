<?php
require_once 'header.php';
require_once 'db.php';
require_once 'csrf.php';
require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("<div class='alert alert-danger'>Token CSRF invalide.</div>");
    }

    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id, firstname FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<div class='alert alert-danger'>Aucun compte n’est lié à cet email.</div>";
    } else {
        $token = bin2hex(random_bytes(16));
        $stmtReset = $pdo->prepare("
            INSERT INTO password_resets (user_id, reset_token)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE reset_token = VALUES(reset_token), created_at = NOW()
        ");
        $stmtReset->execute([$user['id'], $token]);

        $resetLink = "http://localhost/reservation/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.gmx.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'najibhossam@gmx.fr';
            $mail->Password   = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom('najibhossam@gmx.fr', 'Site Reservation');
            $mail->addAddress($email, $user['firstname']);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "
                Bonjour {$user['firstname']},<br><br>
                Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :<br>
                <a href='$resetLink'>$resetLink</a><br><br>
                Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.
            ";

            $mail->send();
            echo "<div class='alert alert-success'>Un email de réinitialisation vous a été envoyé.</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Erreur d'envoi de l'email : {$mail->ErrorInfo}</div>";
        }
    }
    $csrfToken = generateCsrfToken();
} else {
    $csrfToken = generateCsrfToken();
}
?>

<h2>Mot de passe oublié</h2>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
    <div class="mb-3">
        <label for="email" class="form-label">Votre Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Réinitialiser</button>
</form>

<?php require_once 'footer.php'; ?>

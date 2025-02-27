<?php
require_once 'header.php';
require_once 'csrf.php';
require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("<div class='alert alert-danger'>Token CSRF invalide.</div>");
    }

    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='alert alert-danger'>Adresse email invalide.</div>";
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.gmx.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'najibhossam@gmx.fr';
            $mail->Password   = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom('najibhossam@gmx.fr', 'Support Reservation');
            $mail->addReplyTo($email, $name);
            $mail->addAddress('najibhossam@gmx.fr', 'Support Reservation'); 
            $mail->isHTML(true);
            $mail->Subject = "Contact depuis le site de reservation";
            $mailContent = "
                <h4>Demande de contact</h4>
                <p><strong>Nom :</strong> $name</p>
                <p><strong>Email :</strong> $email</p>
                <p><strong>Message :</strong><br>" . nl2br(e($message)) . "</p>
            ";
            $mail->Body = $mailContent;

            $mail->send();

            echo "<div class='alert alert-success'>Merci pour votre message. Nous vous répondrons dès que possible.</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Erreur d'envoi : " . $mail->ErrorInfo . "</div>";
        }
    }

    $csrfToken = generateCsrfToken();
    
} else {
    $csrfToken = generateCsrfToken();
}
?>

<h2 class="mb-3">Contact</h2>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Nom</label>
        <input type="text" class="form-control" name="name" id="name" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Votre Email</label>
        <input type="email" class="form-control" name="email" id="email" required>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" name="message" id="message" rows="5" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Envoyer</button>
</form>

<?php
require_once 'footer.php';
?>

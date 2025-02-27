<?php

require_once 'db.php';
require_once 'csrf.php';
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('/reservation/profile.php');
}

require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("<div class='alert alert-danger'>Token CSRF invalide.</div>");
    }

    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $birthdate = $_POST['birthdate'];
    $address   = trim($_POST['address']);
    $phone     = trim($_POST['phone']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];

    if (empty($firstname) || empty($lastname) || empty($birthdate) ||
        empty($address)   || empty($phone)    || empty($email)     || empty($password)) {
        echo "<div class='alert alert-danger'>Tous les champs sont obligatoires.</div>";
        $csrfToken = generateCsrfToken();
    }
    else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<div class='alert alert-danger'>Adresse email invalide.</div>";
            $csrfToken = generateCsrfToken();
        } else {
            $today = new DateTime();
            $birth = DateTime::createFromFormat('Y-m-d', $birthdate);

            if (!$birth) {
                echo "<div class='alert alert-danger'>Date de naissance invalide.</div>";
                $csrfToken = generateCsrfToken();
            } else {
                $ageInterval = $birth->diff($today);
                $age = $ageInterval->y;

                if ($age < 18) {
                    echo "<div class='alert alert-danger'>Vous devez avoir au moins 18 ans pour vous inscrire.</div>";
                    $csrfToken = generateCsrfToken();
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->rowCount() > 0) {
                        echo "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
                        $csrfToken = generateCsrfToken();
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                        $stmtInsert = $pdo->prepare("
                            INSERT INTO users (firstname, lastname, birthdate, address, phone, email, password, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                        ");
                        $stmtInsert->execute([
                            $firstname, $lastname, $birthdate, $address, $phone, $email, $hashedPassword
                        ]);

                        $userId = $pdo->lastInsertId();

                        $token = bin2hex(random_bytes(16));
                        $stmtToken = $pdo->prepare("INSERT INTO activation_tokens (user_id, token) VALUES (?, ?)");
                        $stmtToken->execute([$userId, $token]);

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

                            $mail->setFrom('najibhossam@gmx.fr', 'Site Reservation');
                            $mail->addAddress($email, $firstname . ' ' . $lastname);
                            $mail->isHTML(true);

                            $mail->Subject = 'Activation de votre compte';
                            $activationLink = "http://localhost/reservation/verify_email.php?token=$token";

                            $mailContent = "
                                <h3>Bonjour {$firstname},</h3>
                                <p>Merci de vous être inscrit. Pour activer votre compte, cliquez sur le lien ci-dessous :</p>
                                <p><a href='$activationLink'>$activationLink</a></p>
                                <p>À bientôt!</p>
                            ";
                            $mail->Body = $mailContent;

                            $mail->send();

                            echo "<div class='alert alert-success'>Inscription réussie ! Un email d'activation vous a été envoyé.</div>";
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>Erreur d'envoi de l'email : " . $mail->ErrorInfo . "</div>";
                        }

                        $csrfToken = generateCsrfToken();
                    }
                }
            }
        }
    }
}
else {
    $csrfToken = generateCsrfToken();
}

?>
<h2 class="mb-3">Inscription</h2>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

    <div class="mb-3">
        <label for="firstname" class="form-label">Prénom</label>
        <input type="text" class="form-control" name="firstname" id="firstname" required>
    </div>

    <div class="mb-3">
        <label for="lastname" class="form-label">Nom</label>
        <input type="text" class="form-control" name="lastname" id="lastname" required>
    </div>

    <div class="mb-3">
        <label for="birthdate" class="form-label">Date de naissance</label>
        <input type="date" class="form-control" name="birthdate" id="birthdate" required>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Adresse postale</label>
        <input type="text" class="form-control" name="address" id="address" required>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Téléphone</label>
        <input type="text" class="form-control" name="phone" id="phone" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Adresse Email</label>
        <input type="email" class="form-control" name="email" id="email" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" name="password" id="password" required>
    </div>

    <button type="submit" class="btn btn-primary">S'inscrire</button>
</form>

<?php require_once 'footer.php'; ?>

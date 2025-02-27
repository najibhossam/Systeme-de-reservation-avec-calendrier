<?php
require_once 'header.php';
require_once 'db.php';
require_once 'csrf.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}
$userId = $_SESSION['user_id'];

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

    if (empty($firstname) || empty($lastname) || empty($birthdate) ||
        empty($address)   || empty($phone)    || empty($email)) {
        echo "<div class='alert alert-danger'>Tous les champs sont obligatoires.</div>";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $csrfToken = generateCsrfToken();
    }
    else {
        $today = new DateTime();
        $birth = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$birth) {
            echo "<div class='alert alert-danger'>Date de naissance invalide.</div>";
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $csrfToken = generateCsrfToken();
        } else {
            $ageInterval = $birth->diff($today);
            $age = $ageInterval->y;

            if ($age < 18) {
                echo "<div class='alert alert-danger'>Vous devez avoir au moins 18 ans.</div>";
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                $csrfToken = generateCsrfToken();
            } else {
                $stmtCurrent = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmtCurrent->execute([$userId]);
                $currentUser = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

                if ($email !== $currentUser['email']) {
                    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmtCheck->execute([$email, $userId]);
                    if ($stmtCheck->rowCount() > 0) {
                        echo "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
                    } else {
                        $stmtUpdate = $pdo->prepare("
                            UPDATE users
                            SET firstname=?, lastname=?, birthdate=?, address=?, phone=?, email=?
                            WHERE id=?
                        ");
                        $stmtUpdate->execute([$firstname, $lastname, $birthdate, $address, $phone, $email, $userId]);
                        echo "<div class='alert alert-success'>Profil mis à jour (email modifié)!</div>";
                    }
                } else {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE users
                        SET firstname=?, lastname=?, birthdate=?, address=?, phone=?
                        WHERE id=?
                    ");
                    $stmtUpdate->execute([$firstname, $lastname, $birthdate, $address, $phone, $userId]);
                    echo "<div class='alert alert-success'>Profil mis à jour!</div>";
                }
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $csrfToken = generateCsrfToken();
            }
        }
    }
}
else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $csrfToken = generateCsrfToken();
}
?>

<h2 class="mb-3">Modifier mon profil</h2>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

    <div class="mb-3">
        <label for="firstname" class="form-label">Prénom</label>
        <input type="text" class="form-control" name="firstname" id="firstname"
               value="<?php echo e($user['firstname']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="lastname" class="form-label">Nom</label>
        <input type="text" class="form-control" name="lastname" id="lastname"
               value="<?php echo e($user['lastname']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="birthdate" class="form-label">Date de naissance</label>
        <input type="date" class="form-control" name="birthdate" id="birthdate"
               value="<?php echo e($user['birthdate']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Adresse postale</label>
        <input type="text" class="form-control" name="address" id="address"
               value="<?php echo e($user['address']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Téléphone</label>
        <input type="text" class="form-control" name="phone" id="phone"
               value="<?php echo e($user['phone']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Adresse Email</label>
        <input type="email" class="form-control" name="email" id="email"
               value="<?php echo e($user['email']); ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Enregistrer</button>
</form>

<?php
require_once 'footer.php';
?>

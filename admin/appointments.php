<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
if (!isset($_SESSION['user_id'])) {
    redirect('/reservation/login.php');
}
$stmtRole = $pdo->prepare("SELECT role FROM users WHERE id=?");
$stmtRole->execute([$_SESSION['user_id']]);
$role = $stmtRole->fetchColumn();
if ($role !== 'admin') {
    redirect('/reservation/non_autorise.php');
}
$stmtAppts = $pdo->query("
    SELECT a.id, a.appointment_datetime, u.firstname, u.lastname, u.email
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    ORDER BY appointment_datetime
");
$appointments = $stmtAppts->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mb-4">Gestion des Rendez-vous</h1>
<table class="table table-striped table-hover">
  <thead class="table-dark">
    <tr>
      <th>Identifiant</th>
      <th>Date & Heure</th>
      <th>Utilisateur</th>
      <th>Email</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($appointments as $appt): ?>
      <tr>
        <td><?php echo $appt['id']; ?></td>
        <td><?php echo htmlspecialchars($appt['appointment_datetime']); ?></td>
        <td><?php echo htmlspecialchars($appt['firstname'].' '.$appt['lastname']); ?></td>
        <td><?php echo htmlspecialchars($appt['email']); ?></td>
        <td>
          <a href="/reservation/admin/edit_appointment.php?id=<?php echo $appt['id']; ?>"
             class="btn btn-sm btn-warning">
             Modifier
          </a>
          <a href="/reservation/admin/delete_appointment.php?id=<?php echo $appt['id']; ?>"
             class="btn btn-sm btn-danger"
             onclick="return confirm('Voulez-vous annuler ce rendez-vous ?')">
             Annuler
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (count($appointments) === 0): ?>
      <tr><td colspan="5" class="text-center">Aucun rendez-vous.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php
require_once __DIR__ . '/../footer.php';
?>

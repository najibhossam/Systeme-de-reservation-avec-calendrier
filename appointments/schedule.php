<?php
require_once '../header.php';
require_once '../db.php';
require_once '../functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/reservation/login.php');
}

$stmt = $pdo->query("SELECT appointment_datetime FROM appointments");
$events = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $datetime = $row['appointment_datetime'];

    $events[] = [
      'title' => 'CRÉNEAU OCCUPÉ',
      'start' => $datetime,
      'color' => 'red',
      'textColor' => 'white',
    ];
}
?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<h2>Prendre un rendez-vous</h2>
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var events = <?php echo json_encode($events); ?>;

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    slotDuration: '00:30:00',
    slotMinTime: '08:00:00',
    slotMaxTime: '24:00:00',
    nowIndicator: true,
    allDaySlot: false,
    validRange: {
      start: new Date()
    },
    events: events,
    eventClick: function(info) {
      alert("Ce créneau est déjà occupé.");
      info.jsEvent.preventDefault();
    },

    dateClick: function(info) {
      if (confirm("Voulez-vous réserver le créneau : " + info.dateStr + " ?")) {
        window.location.href = "process_appointment.php?datetime=" + encodeURIComponent(info.dateStr);
      }
    }
  });

  calendar.render();
});
</script>

<?php
require_once '../footer.php';
?>

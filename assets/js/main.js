document.addEventListener("DOMContentLoaded", function() {
    console.log("Page chargée, script JS opérationnel!");
      const alerts = document.querySelectorAll(".alert");
    alerts.forEach(alert => {
      setTimeout(() => {
        alert.style.transition = "opacity 1s";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 1000);
      }, 5000);
    });
  });
  
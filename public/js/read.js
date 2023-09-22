     document.addEventListener("DOMContentLoaded", function () {
    const notifications = document.querySelectorAll(".notification");
    console.log('coucou')
    notifications.forEach(notification => {
      const notificationId = notification.getAttribute("data-notification-id");
      const isDefaultNotification = notification.textContent.includes("Offre en cours de traitement");

      notification.addEventListener("click", function () {
        if (!notification.classList.contains("read") && !isDefaultNotification) {
          // Envoyer une requête AJAX pour marquer l'offre comme lue
          fetch(`/marquer-offre-lue/${notificationId}`, {
            method: "POST",
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              "Content-Type": "application/json"
            }
          });

          // Marquer la notification comme lue visuellement
          notification.classList.remove("bold");
        }
      });
    });
  });
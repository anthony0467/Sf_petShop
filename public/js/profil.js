//onglet notification

document.addEventListener("DOMContentLoaded", function () {
        const tabs = document.querySelectorAll(".tab");
        const tabContents = document.querySelectorAll(".tab-content");

        tabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                // Masquer tous les contenus des onglets
                tabContents.forEach((content) => {
                    content.style.display = "none";
                });

                // Afficher le contenu de l'onglet correspondant
                const tabId = tab.getAttribute("id").replace("tab-", "");
                document.getElementById(`content-${tabId}`).style.display = "block";
            });
        });

        // Afficher initialement le contenu du premier onglet
        tabs[0].click();
    });


// notif read

    
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
 

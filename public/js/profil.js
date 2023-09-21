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
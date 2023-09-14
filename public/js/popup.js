$(document).ready(function() {
    // Cibler le lien
    $('#lien-popup').click(function(e) {
        e.preventDefault(); // Empêcher le lien de naviguer vers une nouvelle page
        // Afficher la popup (fenêtre modale)
        $('#popup').show();
    });

    // Fermer la popup en cliquant sur un bouton "Fermer"
    $('#fermer-popup').click(function() {
        $('#popup').hide();
    });
});
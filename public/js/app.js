// requete ajax function recherche produit
$(document).ready(function() {
    $('#search-form').submit(function(event) {
        event.preventDefault();
      
        // Récupérer les données du formulaire
        var mots = $('[name="search_produit[mots]"]').val();
        var categorie = $('[name="search_produit[categorie]"]').val();
    
        // Construire l'URL de la requête AJAX avec les paramètres de recherche
        var url = '/search?mots=' + encodeURIComponent(mots) + '&categorie=' + encodeURIComponent(categorie);
        
        // Envoyer les données du formulaire via AJAX à la route 'search_results'
        $.ajax({
            type: 'GET',
            url: url,
            data: $(this).serialize(),
            dataType: 'json',
            success: function(data) {
                // Mettre à jour la zone d'affichage des résultats avec le contenu HTML
                $('#search-results').html(data.result);

                // Faire apparaître les éléments un par un avec un décalage
                var searchResults = $('#search-results li').hide();
                searchResults.each(function(index) {
                    var $element = $(this);
                    setTimeout(function() {
                        $element.fadeIn(400);
                    }, 350 * index);
                });
            },
            error: function() {
                console.log('Une erreur s\'est produite lors de la recherche.');
            }
        });
    });
});

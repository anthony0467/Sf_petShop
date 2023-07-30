// requete ajax function recherche produit
$(document).ready(function() {
    $('#search-form').submit(function(event) {
        event.preventDefault();
      
        // Récupérer les données du formulaire
        let mots = $('[name="search_produit[mots]"]').val();
        let categorie = $('[name="search_produit[categorie]"]').val();
    
        // Construire l'URL de la requête AJAX avec les paramètres de recherche
        let url = '/search?mots=' + encodeURIComponent(mots) + '&categorie=' + encodeURIComponent(categorie);
        
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
                let searchResults = $('#search-results li').hide();
                searchResults.each(function(index) {
                    let $element = $(this);
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


// variable pour vérifier si c'est la première mise à jour
let isFirstLoad = true;

// Requête AJAX pour la pagination des produits
$(document).ready(function() {
    $(document).on('click', '.pagination a', function(event) {
        event.preventDefault();
        let url = '/pagination?page=' + $(this).text();
        console.log(url);
        // Envoyer les données de la requête AJAX à la route 'app_pagination'
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data) {
                console.log('data: ', data);
                // Mettre à jour la zone d'affichage des résultats avec le contenu HTML
                $('#article-container').fadeOut(400, function() {
                    $(this).html(data.resultProduct).fadeIn(400);
                });
                console.log('result :', data.resultProduct)

                // La première fois, on n'ajoute pas de fondu
                if (isFirstLoad) {
                    isFirstLoad = false;
                }
            },
            error: function() {
                console.log('Une erreur s\'est produite lors de la pagination.');
            }
        });
    });
});


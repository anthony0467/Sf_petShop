// variable pour vérifier si c'est la première mise à jour
let isFirstLoad = true;

// Requête AJAX pour la pagination des produits
$(document).ready(function() {
    $(document).on('click', '.pagination a', function(event) {
        event.preventDefault();
        //let url = '/pagination?page=' + $(this).text();
        let url = $(this).attr('href');

           // Vérifier si "pagination" est déjà présent dans l'URL
           if (url.indexOf('/pagination') !== 0) {
            // Ajouter "pagination" au chemin de l'URL
            url = '/pagination' + url;
        }
        
        console.log(url)
        //let url = '/pagination?page=' + page;
       // console.log(url);
        // Envoyer les données de la requête AJAX à la route 'app_pagination'
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data) {
                // Mettre à jour la zone d'affichage des résultats avec le contenu HTML
                $('#avis-container').fadeOut(400, function() {
                    $(this).html(data.resultProduct).fadeIn(400);
                });

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
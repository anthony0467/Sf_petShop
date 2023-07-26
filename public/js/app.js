$(document).ready(function() {
    $('#search-form').submit(function(event) {
        event.preventDefault(); // Empêche le rechargement de la page
      console.log('Formulaire de recherche soumis !');
      // Récupérer les données du formulaire
    //   var formData = $(this).serialize()
      // Envoyer les données du formulaire via AJAX à la route 'search_results'
      $.ajax({
        type: 'GET',
        url: '{{ path("search_results") }}', // URL vers la route AJAX que vous avez créée
        data: $(this).serialize(),
        success: function(data) {
            console.log(data)
          // Mettre à jour la zone d'affichage des résultats avec les données reçues
          $('#search-results').html(data.result);
            // $('#search-results').empty().prepend(data.result)
            
         
        },
        error: function() {
          console.log('Une erreur s\'est produite lors de la recherche.');
        }
      });
    });
  });
$(document).ready(function() {
    
    $('#evenement_localisation').selectize({
        valueField: 'value',
        labelField: 'label',
        searchField: 'label',
        // Pas possible de creer de nouveaux champs
        create: false,
        // Pas de surbrillance pour les correspondances dans la liste
        highlight: false,
        // 1 choix max
        maxItems: 1,
        // Fermeture select apres choix
        closeAfterSelect: true,
        // Temps entre chaque requete
        loadThrottle: 250, 
        // Texte d'attente requete
        loadingClass: "Recherche...",
        placeholder: "Votre adresse...",
        // Personnalisation du rendu
        
        render: {
            option: function(item, escape){
                //console.log(item)
                return '<div>' + escape(item.label) + '</div>'
            }
        },
        // Quand on selectionne une adresse, remplissage de CP et ville
        onChange: function(value) {
            if(value) {
                var selectedAdress = this.options[value];
                //console.log(selectedAdress)
                var codePostal = selectedAdress.postcode;
                var ville = selectedAdress.city

                $('#evenement_cp').val(codePostal)
                $('#evenement_ville').val(ville)
            }
            // Suppression de CP et ville si on suppr l'adresse
            else{
                $('#evenement_cp').val('')
                $('#evenement_ville').val('')
            }
        },
    
        // 
        load: function(query, callback) {
            // Si l'utilisateur n'a rien écrit ou si moins de 4 caractères
            if (!query.length) return callback();
            if (query.length < 4) return callback();
        
            //Requete AJAX vers GEOGOUV
            $.ajax({
                url:'https://api-adresse.data.gouv.fr/search/',
                type: 'GET',
                dataType: 'json',
                data: 
                {
                    q: query,
                    limit: 10
                },
                // En cas d'erreur : rien a afficher
                error: function() 
                {
                    callback();
                },
                // En cas de succes on retourne l'adresse, le nom, le cp et la ville
                success: function(res) 
                {
                    callback(res.features.map(function(feature)
                    {   
                        //console.log(res.features)
                            return {
                                value: feature.properties.name,
                                label: feature.properties.label,
                                postcode : feature.properties.postcode,
                                city : feature.properties.city
                            };
                    })); 

                }
            });
        }
    });
    
});
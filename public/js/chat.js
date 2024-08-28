$(document).ready(function() {
    $('#chat_form').on('submit', function(event) {
        event.preventDefault(); // Empêche l'envoi standard du formulaire

        // Récupère les données du formulaire
        var formData = $(this).serialize(); // Sérialise les données du formulaire  en string

        $.ajax({
            url: $(this).attr('action'), // L'URL d'action du formulaire
            type: $(this).attr('method'), // La méthode (POST)
            data: formData,
            dataType: 'json', // Le type de données attendu en réponse
            success: function(response) {
                // Insère la réponse dans la div avec la classe "response"
                $('.response').html('<p>' + response.response + '</p>');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Gérer les erreurs
                $('.response').html('<p>Une erreur est survenue : ' + textStatus + '</p>');
            }
        });
    });
});

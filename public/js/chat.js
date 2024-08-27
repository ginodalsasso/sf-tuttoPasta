// var csrfToken = $('meta[name="csrf-token"]').attr("content"); // Récupère le token csrf dans le head du template

// $(document).ready(function () {
//     $("#chat_form").on("submit", function (event) {
//         // event.preventDefault(); // Empêche la soumission normale du formulaire
//         sendMessage();
//     });
// });

// function sendMessage() {
//     const message = $("#chat_message").val(); // Récupère le message

//     $.ajax({
//         url: "/chat",
//         method: "POST",
//         data: { message: message },
//         headers: {
//             "X-CSRF-Token": csrfToken,
//             "X-Requested-With": "XMLHttpRequest",
//         },
//         success: function (data) {
//             console.log(data); // Vérification de la réponse
//             if (data.response) {
//                 $(".response").html(
//                     "<h3>Response:</h3><p>" + data.response + "</p>"
//                 );
//             } else {
//                 alert(
//                     "Erreur lors de la requête avec Mistral. Veuillez réessayer."
//                 );
//             }
//         },
//         error: function (jqXHR, textStatus, errorThrown) {
//             console.error(
//                 "Erreur lors de l'envoi du message :",
//                 textStatus,
//                 errorThrown
//             );
//         },
//     });
// }

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

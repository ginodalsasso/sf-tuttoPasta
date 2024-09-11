
var csrfToken = $('meta[name="csrf-token"]').attr('content');  // Récupère le token csrf dans le head du template


$(document).ready(function () {

    // Met le token CSRF dans l'input caché du formulaire
    $('input[name="csrf"]').val(csrfToken);
    
    // Suppression d'un message
    $("#delete_message_link").on('click', function (event) {
        if (!confirm("Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.")) {
            event.preventDefault();
        }    
    });

});

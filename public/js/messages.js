
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

    // Messages d'erreurs UI
    $("#message_envoyer").on("click", function (event) {
        $(".error_msg").text("");
        $(".data").removeClass("input_invalid");
        let isValid = true;

        $(".data").each(function () {
            if ($(this).val().trim() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });

        const title = $("#message_title").val().trim();
        if (title.length < 5 || title.length > 255) {
            $("#title_error").text("Le titre de votre message doit contenir entre 5 et 255 caractères !");
            $("#message_title").addClass("input_invalid");
            isValid = false;
        }
    
        const content = $("#message_content").val().trim();
        if (content.length < 5) {
            $("#content_error").text("Le contenu de votre message doit contenir au moins 5 caractères !");
            $("#message_content").addClass("input_invalid");
            isValid = false;
        }

        if (isValid) {
            $("#register_save").submit();
        } else {
            event.preventDefault();
        }
    });

});

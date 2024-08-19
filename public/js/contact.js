    // Messages d'erreurs UI
    $("#contact_form_save").on("click", function (event) {
        $(".error_msg").text("");
        $(".data").removeClass("input_invalid");

        let isValid = true;

        $(".data").each(function () {
            if ($(this).val() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });

        const name = $("#contact_form_name").val().trim();
        if (name === "" || name.length < 2 || name.length > 50) {
            $("#firstName_error").text("Le nom est invalide et doit contenir entre 2 et 50 caractères");
            $("#contact_form_name").addClass("input_invalid");
            isValid = false;
        }

        const email = $("#contact_form_email").val().trim();
        if (email === "" || !validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            $("#contact_form_email").addClass("input_invalid");
            isValid = false;
        }

        const subject = $("#contact_form_message").val();
        if (subject === "" || subject.length < 10) {
            $("#subject_error").text("Le subject est invalide et doit contenir au minimum 10 caractères");
            $("#contact_form_subject").addClass("input_invalid");
            isValid = false;
        }

        const message = $("#contact_form_message").val();
        if (message === "" || message.length < 10) {
            $("#message_error").text("Le message est invalide et doit contenir au minimum 10 caractères");
            $("#contact_form_message").addClass("input_invalid");
            isValid = false;
        }

        if (isValid) {
            $("#contact_form").submit();
        }
    });
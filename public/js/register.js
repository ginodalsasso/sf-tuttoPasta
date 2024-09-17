$(document).ready(function () {
    // Messages d'erreurs UI
    $("#register_save").on("click", function (event) {
        $(".error_msg").text("");
        $(".data").removeClass("input_invalid");
        let isValid = true;

        $(".data").each(function () {
            if ($(this).val().trim() === "") {
                $(this).addClass("input_invalid");
                isValid = false;
            }
        });

        const name = $("#registration_form_username").val().trim();
        if (!validateUsername(name)) {
            $("#pseudo_error").text("Pseudo invalide : doit commencer par une lettre, contenir 3-50 caractères, et n'inclure que lettres, chiffres, tirets et underscores.");
            $("#registration_form_username").addClass("input_invalid");
            isValid = false;
        }

        const email = $("#registration_form_email").val().trim();
        if (email === "" || !validateEmail(email)) {
            $("#email_error").text("L'email est invalide !");
            $("#registration_form_email").addClass("input_invalid");
            isValid = false;
        }

        const password1 = $("#registration_form_plainPassword_first").val().trim();
        const password2 = $("#registration_form_plainPassword_second").val().trim();

        if (password1 === "" || password2 === "") {
            $("#password_error").text("Les deux champs de mot de passe doivent être remplis !");
            $("#registration_form_plainPassword_first").addClass("input_invalid");
            $("#registration_form_plainPassword_second").addClass("input_invalid");
            isValid = false;
        } else if (password1 !== password2) {
            $("#password_error").text("Les mots de passe ne correspondent pas !");
            $("#registration_form_plainPassword_first").addClass("input_invalid");
            $("#registration_form_plainPassword_second").addClass("input_invalid");
            isValid = false;
        } else if (!validatePassword(password1)) {
            let errorMessage = "Le mot de passe est invalide et doit contenir :";
            if (!/(?=.*?[A-Z])/.test(password1)) {
                errorMessage += "<br>- Au moins une lettre majuscule";
            }
            if (!/(?=.*?[a-z])/.test(password1)) {
                errorMessage += "<br>- Au moins une lettre minuscule";
            }
            if (!/(?=.*?[0-9])/.test(password1)) {
                errorMessage += "<br>- Au moins un chiffre";
            }
            if (!/(?=.*?[#?!@$%^&*-])/.test(password1)) {
                errorMessage += "<br>- Au moins un caractère spécial (#?!@$%^&*-)";
            }
            if (!/.{13,}/.test(password1)) {
                errorMessage += "<br>- Au moins 13 caractères";
            }
            $("#password_error").html(errorMessage);
            $("#registration_form_plainPassword_first").addClass("input_invalid");
            $("#registration_form_plainPassword_second").addClass("input_invalid");
            isValid = false;
        }

        if (isValid) {
            $("#register_save").submit();
        } else {
            event.preventDefault();
        }
    });
    
    $('#toggle_password').on('change', function () {
        var inputPassword = $('#registration_form_plainPassword_first, #registration_form_plainPassword_second');
        if (inputPassword.attr('type') === "password") {
            inputPassword.attr('type', 'text');
        } else {
            inputPassword.attr('type', 'password');
        }
    });
});
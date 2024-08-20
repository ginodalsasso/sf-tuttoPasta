// $(document).ready(function () {
//     // Messages d'erreurs UI
//     // Vue Login
//     $("#login_save").on("click", function (event) {
//         $(".error_msg").text("");
//         $(".data").removeClass("input_invalid");
//         let isValid = true;

//         $(".data").each(function () {
//             if ($(this).val().trim() === "") {
//                 $(this).addClass("input_invalid");
//                 isValid = false;
//             }
//         });
 
//         const email = $("#username").val().trim();
//         if (email === "" || !validateEmail(email)) {
//             $("#email_error").text("L'email est invalide !");
//             $("#username").addClass("input_invalid");
//             isValid = false;
//         }

//         const password = $("#password").val().trim();

//         if (password === "") {
//             $("#password_error").text("Le mot de passe est invalide !");
//             $("#password").addClass("input_invalid");
//             isValid = false;
//         } else if (!validatePassword(password)) {
//             let errorMessage = "Le mot de passe est invalide et doit contenir :";
//             if (!/(?=.*?[A-Z])/.test(password)) {
//                 errorMessage += "<br>- Au moins une lettre majuscule";
//             }
//             if (!/(?=.*?[a-z])/.test(password)) {
//                 errorMessage += "<br>- Au moins une lettre minuscule";
//             }
//             if (!/(?=.*?[0-9])/.test(password)) {
//                 errorMessage += "<br>- Au moins un chiffre";
//             }
//             if (!/(?=.*?[#?!@$%^&*-])/.test(password)) {
//                 errorMessage += "<br>- Au moins un caractère spécial (#?!@$%^&*-)";
//             }
//             if (!/.{13,}/.test(password)) {
//                 errorMessage += "<br>- Au moins 13 caractères";
//             }
//             $("#password_error").html(errorMessage);
//             $("#password").addClass("input_invalid");
//             isValid = false;
//         }
//         if (isValid) {
//             $("#login_save").submit();
//         } else {
//             event.preventDefault();
//         }
//     });

//     // Vue Reset password - email
//     $("#validate_email").on("click", function (event) {
//         $(".error_msg").text("");
//         $(".data").removeClass("input_invalid");
//         let isValid = true;

//         $(".data").each(function () {
//             if ($(this).val().trim() === "") {
//                 $(this).addClass("input_invalid");
//                 isValid = false;
//             }
//         });


//         const email = $("#reset_password_request_form_email").val().trim();
//         if (email === "" || !validateEmail(email)) {
//             $("#email_error").text("L'email est invalide !");
//             $("#reset_password_request_form_email").addClass("input_invalid");
//             isValid = false;
//         }

//         if (isValid) {
//             $("#validate_email").submit();
//         } else {
//             event.preventDefault();
//         }
//     });

//     // Vue Reset password - mot de passes confirmation
//     $("#pass_confirm").on("click", function (event) {
//         $(".error_msg").text("");
//         $(".data").removeClass("input_invalid");
//         let isValid = true;

//         $(".data").each(function () {
//             if ($(this).val().trim() === "") {
//                 $(this).addClass("input_invalid");
//                 isValid = false;
//             }
//         });
        
//         const password1 = $("#change_password_form_plainPassword_first").val().trim();
//         const password2 = $("#change_password_form_plainPassword_second").val().trim();

//         if (password1 === "" || password2 === "") {
//             $("#password_error").text("Les deux champs de mot de passe doivent être remplis !");
//             $("#change_password_form_plainPassword_first").addClass("input_invalid");
//             $("#change_password_form_plainPassword_second").addClass("input_invalid");
//             isValid = false;
//         } else if (password1 !== password2) {
//             $("#password_error").text("Les mots de passe ne correspondent pas !");
//             $("#change_password_form_plainPassword_first").addClass("input_invalid");
//             $("#change_password_form_plainPassword_second").addClass("input_invalid");
//             isValid = false;
//         } else if (!validatePassword(password1)) {
//             let errorMessage = "Le mot de passe est invalide et doit contenir :";
//             if (!/(?=.*?[A-Z])/.test(password1)) {
//                 errorMessage += "<br>- Au moins une lettre majuscule";
//             }
//             if (!/(?=.*?[a-z])/.test(password1)) {
//                 errorMessage += "<br>- Au moins une lettre minuscule";
//             }
//             if (!/(?=.*?[0-9])/.test(password1)) {
//                 errorMessage += "<br>- Au moins un chiffre";
//             }
//             if (!/(?=.*?[#?!@$%^&*-])/.test(password1)) {
//                 errorMessage += "<br>- Au moins un caractère spécial (#?!@$%^&*-)";
//             }
//             if (!/.{13,}/.test(password1)) {
//                 errorMessage += "<br>- Au moins 13 caractères";
//             }
//             $("#password_error").html(errorMessage);
//             $("#change_password_form_plainPassword_first").addClass("input_invalid");
//             $("#change_password_form_plainPassword_first").addClass("input_invalid");
//             isValid = false;
//         }
//         if (isValid) {
//             $("#pass_confirm").submit();
//         } else {
//             event.preventDefault();
//         }
//     });
// });

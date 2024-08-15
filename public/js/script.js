var csrfToken = $('meta[name="csrf-token"]').attr('content');


function validateEmail(email) {
    const emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test(email);
}


function validatePassword(password) {
    const passwordReg = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{13,}$/;
    return passwordReg.test(password);
}

function validateUsername(username) {
    const usernameReg = /^[a-zA-Z][a-zA-Z0-9_-]{2,49}$/;
    return usernameReg.test(username);
}

// Fonction pour échapper les caractères spéciaux en HTML
function escapeHtml(unsafe) {
    if (unsafe === undefined || unsafe === null) {
        // Si c'est le cas, retourne une chaîne vide pour éviter les erreurs
        return '';
    }
    return unsafe
        // Convertit la valeur en chaîne de caractères et remplace les caractères spéciaux par leurs équivalents HTML
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


$(document).ready(function() {
    // Evenement click burger menu
    $('.burger-menu').click(function() {
        $('#nav_container').addClass('active');
        $('.burger-menu').hide();
        $('.close_burger').show();
        $('body').css('overflow', 'hidden'); // Désactive le scroll
    });
    // Evenement fermeture du burger menu
    $('.close_burger').click(function() {
        $('#nav_container').removeClass('active');
        $('.burger-menu').show();
        $('.close_burger').hide();
        $('body').css('overflow', 'auto'); // Active le scroll
    });

    // Affiche la navbar au scroll vers le haut
    var $navbar = $('#header_container');
    var lastScrollTop = 0;
    $(window).on('scroll', function() {
        var scrollTop = $(this).scrollTop();

        if (scrollTop > lastScrollTop) {
            $navbar.css('top', '-150px'); // Cache la navbar
        } else {
            $navbar.css('top', '-30px'); // Affiche la navbar
        }
        lastScrollTop = scrollTop;
    });

});

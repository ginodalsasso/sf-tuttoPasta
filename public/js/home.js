

gsap.registerPlugin(ScrollTrigger);

const introSection = document.getElementById("introduction_section");
const introImage = introSection.querySelector("img");
const introContent = introSection.querySelector(".introduction_content");

// Positionne l'image à gauche, en dehors de l'écran
gsap.set(introImage, { xPercent: -100, opacity: 0 });

// Positionne le contenu à droite, en dehors de l'écran
gsap.set(introContent, { xPercent: 100, opacity: 0 });

// Animation pour faire apparaître l'image
gsap.to(introImage, {
    xPercent: 0,   // Ramène l'image à sa position d'origine
    opacity: 1,    // Rend l'image visible
    duration: 2,
    ease: "power2.out", // animation fluide
    delay: 0.2,
    scrollTrigger: {
        trigger: "#h1_page_title", // Déclenche l'animation 
        start: "top top",
        toggleActions: "play none none none", // Joue l'animation à l'arrivée
    }
});


// Animation pour faire apparaître le contenu
gsap.to(introContent, {
    xPercent: 0,   // Ramène le contenu à sa position d'origine
    opacity: 1,    // Rend le contenu visible
    duration: 2, 
    ease: "power2.out", // Easing pour une animation fluide
    delay: 0.4,
    scrollTrigger: {
        trigger: "#h1_page_title", // Déclenche l'animation 
        start: "top top",
        toggleActions: "play none none none", // Joue l'animation à l'arrivée
    }     
});


// Animation pour les éléments de la section "Guides"
gsap.from(".guide_content .badges_guide", {
    scrollTrigger: {
        trigger: ".guide_content .badges_guide",
        toggleActions: "play none none none",
        start: "top 80%",
    },
    x: -100,  // Déplacement depuis la gauche
    opacity: 0,
    ease: "power3.out",  // Glissement fluide
    duration: 1, 
    stagger: 0.3 // Délai entre chaque élément
});


gsap.from(".expertise_cards .expertise_card", {
    scrollTrigger: {
        trigger: ".expertise_cards .expertise_card",
        toggleActions: "play none none none",
        start: "top 80%", 
    },
    x: -100,
    opacity: 0,
    scale: 0,
    ease: "elastic.out(0.2, 0.5)",
    duration: 1,
    stagger: 0.5 // Délai entre chaque élément
});


$(document).ready(function () {
    
    //_______________________________GESTION DES COULEURS ALEATOIRES________________________________
    // Variable de couleur pour les H2 des cards articles
    var colors = [
        "var(--pink-color)",
        "var(--red-color)",
        "var(--blue-color)",
        "var(--green-color)",
    ];

    var stickerClasses = [
        "stickers_pink",
        "stickers_red",
        "stickers_blue",
        "stickers_green",
    ];

    var buttonClasses = [
        "full_button_pink",
        "full_button_red",
        "full_button_blue",
        "full_button_green",
    ];


    // Couleur aléatoire pour chaque élément de la classe .badges_guide i
    $(".badges_guide i").each(function (index) {
        $(this).css("color", colors[index % colors.length]);
    });


    // Couleur aléatoire pour chaque h3 de la section expertise_cards
    $(".expertise_card h3").each(function (index) {
        $(this).css("color", colors[index % colors.length]);

    });


    // Couleur aléatoire pour chaque élément de la classe service_cards_header
    $(".service_cards").each(function (index) {
        var color = colors[index % colors.length];
        var stickerClass = stickerClasses[index % stickerClasses.length];
        var buttonClass = buttonClasses[index % buttonClasses.length];

        $(this).find(".service_cards_header").css("color", color); // Change la couleur du H2

        $(this).find(".stickers_price").addClass(stickerClass); // Ajoute la classe de sticker
        $(this).find(".service_button").addClass(buttonClass); // Ajoute la classe de button
    });



    //_______________________________GESTION DES OFFRES DE PRIX (SERVICES)________________________________
    // Variable pour les offres de prix
    var stepMappings = [
        {
            next: "#next_to_site_services",
            back: "#back_to_identite_service",
            currentStep: "#identite_service",
            nextStep: "#site_services",
            currentStepId: "#step1",
            nextStepId: "#step2"
        },
        {
            next: "#next_to_presta_services",
            back: "#back_to_site_services",
            currentStep: "#site_services",
            nextStep: "#presta_a_la_carte",
            currentStepId: "#step2",
            nextStepId: "#step3"
        }
    ];

    // Initialisation des étapes des offres de prix
    $("#step1").css({ 
        "background-color": "white", 
        "color": "black",
        "width": "fit-content",
    });


    // Gestion des clics sur les boutons Suivant
    stepMappings.forEach(function(mapping) {
        $(mapping.next).on("click", function () {
            $(mapping.currentStep).hide(); // Masquer l'étape actuelle
            $(mapping.nextStep).show(); // Afficher l'étape suivante
            $(mapping.currentStepId).removeAttr("style");  // Supprimer les styles de l'étape actuelle
            $(mapping.nextStepId).css({ // Ajouter les styles à l'étape suivante
                "background-color": "white",
                "color": "black",
                "width": "fit-content",
            });
        });


        // Gestion du clic sur le bouton Précédent
        $(mapping.back).on("click", function () {
            $(mapping.nextStep).hide();
            $(mapping.currentStep).show();
            $(mapping.nextStepId).removeAttr("style");
            $(mapping.currentStepId).css({
                "background-color": "white",
                "color": "black",
                "width": "fit-content",
            });
        });
    });

    //_______________________________GESTION DES CHECKBOXES ET LABELS SUR LE FORMULAIRE D'OFFRES________________________________
    // Fonction pour gérer le changement d'état des checkboxes
    function handleCheckboxChange() {
        // Ajouter ou supprimer la classe checked à l'élément suivant
        $(this).next('label').toggleClass(this.checked);
    }


    // Fonction pour gérer le clic sur les labels
    function handleLabelClick(e) {
        e.preventDefault();
        var $checkbox = $(this).prev('input[type="checkbox"]');
        $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
    }


    // Gestion des checkboxes et des labels
    $('#identite_visuelle, #services_site_internet, #presta_a_la_carte').each(function() {
        $(this).find('input[type="checkbox"]').on('change', handleCheckboxChange); // Gestion des changements de checkbox
        $(this).find('label').on('click', handleLabelClick); // Gestion des clics sur les labels
    });


    //_______________________________FAQ Icones________________________________
    $("#faq summary").click(function(){
        // Change l'icone de la balise <summary> lorsqu'on clique dessus
        var icon = $(this).find("i");
        if(icon.hasClass("fa-arrow-down")) {
            icon.removeClass("fa-arrow-down").addClass("fa-arrow-right");
        } else {
            icon.removeClass("fa-arrow-up").addClass("fa-arrow-down");
        }
    });
    
});

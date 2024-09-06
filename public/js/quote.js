$(document).ready(function() {   
    // Suppression d'un devis
    $(document).on('click', '.delete_quote', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('id');
        
        if (confirm("Êtes-vous sûr de vouloir supprimer ce devis ?")) {
            deleteQuote(quoteId, csrfToken);
        }
    });

    // Archivage d'un devis
    $(document).on('click', '.archive_quote', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('id');
        
        if (confirm("Êtes-vous sûr de vouloir archiver ce devis ?")) {
            archiveQuote(quoteId, csrfToken);
        }
    });

    // Devis terminé
    $(document).on('click', '.completed_quote', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('id');
        
        if (confirm("Êtes-vous sûr de vouloir finaliser ce devis ?")) {
            completedQuote(quoteId, csrfToken);
        }
    });

    // Selection du service
    handleServiceSelection();

});

//___________________________________Edit quote Services_______________________________________
// Fonction pour gérer la sélection des services
function handleServiceSelection() {
    $("#quote_services").on("change","input[type='checkbox']", function (e) {
        const $input = $(this);
        const $label = $input.next('label');
        const service = $input.val();
        // console.log(service);

        if ($input.is(":checked")) {
            $label.addClass("showRadioClass");
            $("#selectedService").val(service);
        } else {
            $label.removeClass("showRadioClass");
            $("#selectedService").val('');
        }
    });
}

//___________________________________Liste des devis_______________________________________
// selectionner les td avec l'attribut data-etat
$('.quotes_table td[data-etat]').each(function() {
    // Récupère la valeur de l'attribut data-etat
    var dataEtat = $(this).attr('data-etat');
    
    // Apply styles based on the data-etat value
    if (dataEtat === 'En attente') {
        $(this).css('background-color', '#fbfb8f');
    } else if (dataEtat === 'En cours') {
        $(this).css('background-color', '#6C6CFF');
    } else if (dataEtat === 'Payé') {
        $(this).css('background-color', '#27B474');
    } else {
        $(this).css('background-color', '#b6b6b6');
    }
});

//___________________________________Suppression devis_______________________________________
function deleteQuote(quoteId, csrfToken) {
    var url = `/admin/quote/${quoteId}/delete`;
    
    $.ajax({
        url: url,
        method: 'DELETE', 
        headers: {
            'X-CSRF-TOKEN': csrfToken  // Ajout du token CSRF dans les headers
        },
        success: function(data) {
            if (data.success) {
                // Supprimer le devis
                $('#quote-' + quoteId).remove();
            } else {
                alert(data.message || "Erreur lors de la suppression du devis. Veuillez réessayer.");
                console.log(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la suppression du devis :", textStatus, errorThrown);
            alert("Une erreur est survenue lors de la suppression du devis. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}

//___________________________________Archivage devis_______________________________________
function archiveQuote(quoteId, csrfToken) {
    var url = `/admin/quote/${quoteId}/archive`;
    
    $.ajax({
        url: url,
        method: 'POST', 
        headers: {
            'X-CSRF-TOKEN': csrfToken  // Ajout du token CSRF dans les headers
        },
        success: function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || "Erreur lors du changement d'état du devis. Veuillez réessayer.");
                console.log(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors du changement d'état du devis :", textStatus, errorThrown);
            alert("Une erreur est survenue lors du changement d'état du devis. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}

//___________________________________Devis terminé_______________________________________
function completedQuote(quoteId, csrfToken) {
    var url = `/admin/quote/${quoteId}/completed`;
    
    $.ajax({
        url: url,
        method: 'POST', 
        headers: {
            'X-CSRF-TOKEN': csrfToken  // Ajout du token CSRF dans les headers
        },
        success: function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || "Erreur lors du changement d'état du devis. Veuillez réessayer.");
                console.log(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors du changement d'état du devis :", textStatus, errorThrown);
            alert("Une erreur est survenue lors du changement d'état du devis. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}
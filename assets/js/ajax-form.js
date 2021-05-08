$(function() {

    // Get the form.
    var form = $('#contact-form');

    // Get the messages div.
    var formMessages = $('#form-messages');

    // Set up an event listener for the contact form.
    $(form).submit(function(e) {
        // Stop the browser from submitting the form.
        e.preventDefault();

        // Serialize the form data.
        var formData = $(form).serialize();
        
        // Submit the form using AJAX.
        $.ajax({
            type: 'POST',
            url: $(form).attr('action'),
            data: formData
        })
        .done(function(response) {
            if (response!='') {
                $(formMessages).removeClass('alert alert-danger');
                $(formMessages).addClass('alert alert-success');

                $(formMessages).text(response);

                $('#cname').val('');
                $('#cemail').val('');
                $('#cphone').val('');
                $('#czipcode').val('');
                $('#cmessage').val('');
            } else {
                $(formMessages).removeClass('alert alert-success');
                $(formMessages).addClass('alert alert-danger');
                $(formMessages).text('Complete la información en inténtelo nuevamente');
            }
        })
        .fail(function(data) {
            // Make sure that the formMessages div has the 'alter-danger' class.
            $(formMessages).removeClass('alert alert-success');
            $(formMessages).addClass('alert alert-danger');

            // Set the message text.
            if (data.responseText !== '') {
                $(formMessages).text(data.responseText);
            } else {
                $(formMessages).text('Ups! ha ocurrido un error, no se puedo enviar su mensaje.');
            }
        });

    });

});

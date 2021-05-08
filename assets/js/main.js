$(document).ready(function() {

/* === modal == */
$('#mensaje-temp').modal();
/* ======= Scrollspy ======= */
$('body').scrollspy({ target: '#topbar', offset: 100});

/* ======= ScrollTo ======= */
$('.scrollto').on('click', function(e){

    //store hash
    var target = this.hash;

    e.preventDefault();

            $('body').scrollTo(target, 800, {offset: -50, 'axis':'y'});
    //Collapse mobile menu after clicking
            if ($('.navbar-collapse').hasClass('in')){
                    $('.navbar-collapse').removeClass('in').addClass('collapse');
            }

    }); 

    /* ======= Fixed Header animation ======= */   
$(window).on('scroll load', function() {

     if ($(window).scrollTop() > 50 ) {
         $('#topbar').addClass('topbar-scrolled');
     }
     else {
         $('#topbar').removeClass('topbar-scrolled');             
     }
}); 


/* ======= Owl Carousel ======= */    
/* Ref: https://github.com/OwlCarousel2/OwlCarousel2 */
$('#reviews-carousel').owlCarousel({
    loop: true,
    autoplay: true,
    autoplayTimeout: 6000,
    nav:false,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:2
        },
        1000:{
            items:4
        }
    }
});

/* ======= jQuery Responsive equal heights plugin ======= */
/* Ref: https://github.com/liabru/jquery-match-height */

 $('#services .item-desc').matchHeight(); 
 $('#reviews .quote').matchHeight(); 


/* ======= jQuery form validator ======= */ 
/* Ref: http://jqueryvalidation.org/documentation/ */   
$("#contact-form").validate({
    messages: {

        name: {
                required: 'Por favor ingrese su nombre' //You can customise this message
            },
            email: {
                required: 'Ingrese una dirección de correo válida' //You can customise this message
            },	
            phone: {
                required: 'Ingrese su número telefónico' //You can customise this message
            },	
            message: {
                required: 'Por favor escriba información sobre el motivo de su contacto' //You can customise this message
            }
        }

    });
$("#resetpass-form").validate({
    rules: {
        cedula : {required : true }
    },
    messages : {
        cedula : {required : 'Ingrese su número de cedula' }
    },
    submitHandler : function(form) {
        $.ajax({
            type : 'POST',
            url  : 'login.php',
            dataType: 'json',
            data: $(form).serialize(),
            beforeSend: function() {
                $("#resetpass-form").find("button").button('loading');
                $('#resetpass-messages').removeClass('alert alert-danger');
                $('#resetpass-messages').html('');
            }
        })
        .done(function(obj) {
             if (obj.suceed == false) {
                $('#resetpass-messages').removeClass('alert alert-success');
                $('#resetpass-messages').addClass('alert alert-danger');
                $('#resetpass-messages').text(obj.error);
                $("#resetpass-form").find("button").button('reset');
            } else {
                $('#resetpass-messages').removeClass('alert alert-danger');
                $('#resetpass-messages').addClass('alert alert-success');
                $('#resetpass-messages').text(obj.success);
                $("#resetpass-form").find("button").button('reset');
            }
        })
        .fail(function(data) {
            $('#resetpass-messages').removeClass('alert alert-success');
            $('#resetpass-messages').addClass('alert alert-danger');
            $('#resetpass-messages').text(data);
            console.log('error ' + data);
            $("#resetpass-form").find("button").button('reset');
        });
    },
    errorPlacement : function(error, element) {
    error.insertBefore(element.parent());
    }
});
$("#login-form").validate({
rules : {
cedula : {
required : true
},
password : {
required : true,
minlength : 4,
maxlength : 20
}
},
messages : {
cedula : {
required : 'Ingrese su número de cédula'
},
password : {
required : 'Ingrese su clave de acceso',
minlength: 'Deben ser mínimo 4 caracteres',
maxlength:  'No pueden ser más de 20 caracteres'
}
},
submitHandler: function(form) {
    $.ajax({
        type: 'POST',
        url: 'login.php',
        dataType: 'json',
        data: $(form).serialize(),
        beforeSend: function() {
            $("#login-form").find("button").button('loading');
            $('#login-messages').removeClass('alert alert-danger');
        }
    })
    .done(function(obj) {
        if (obj.suceed == false) {
            $('#login-messages').removeClass('alert alert-success').addClass('alert alert-danger');
            $("#login-form").find("button").button('reset');
            $('#login-messages').text(obj.error);
            
        } else {
            
            $('#login-messages').removeClass('alert alert-danger');
            $('#login-messages').addClass('alert alert-success');
            $('#login-messages').text('Propietario verificado con éxito. Lo estamos redireccionando.....');
            console.log("redireccionando...")
            location.href = $(form).attr('data-destination');
        }
        
    })
    .fail(function(data) {
        $('#login-messages').removeClass('alert alert-success');
        $('#login-messages').addClass('alert alert-danger');
        $('#login-messages').text(data);
        console.log('error' + data);
        $("#login-form").find("button").button('reset');
    });
},
errorPlacement : function(error, element) {
error.insertBefore(element.parent());
}
});

/* ======= Toggle between Signup & Login & ResetPass Modals ======= */ 
$('#signup-link').on('click', function(e) {
    $('#login-modal').modal('toggle');
    $('#signup-modal').modal();

    e.preventDefault();
});

$('#login-link').on('click', function(e) {
    $('#signup-modal').modal('toggle');
    $('#login-modal').modal();

    e.preventDefault();
});

$('#back-to-login-link').on('click', function(e) {
    $('#resetpass-modal').modal('toggle');
    $('#login-modal').modal();

    e.preventDefault();
});

$('#resetpass-link').on('click', function(e) {
    $('#login-modal').modal('hide');
    e.preventDefault();
});
});
//11055962
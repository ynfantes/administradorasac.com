   var dp = jQuery;
   dp(document).ready(function() {
       dp('.ch-color li a').click(function(event) {
           val = dp(this).attr('data-color');
           dp("#clscheme").attr("href", "css/scheme/" + val + ".css");
       });
       dp('.cos-trigger').click(function(event) {
           value = dp('.cos-wrapper').css('left') === '-280px' ? 0 : '-280px';
           dp('.cos-wrapper').animate({
               left: value,
               duration: 750
           });
       });
       dp('.sac-en-linea, .btn-outline').click(function(event) {
           value = dp('.cos-wrapper').css('left') === '-280px' ? 0 : '-280px';
           dp('.cos-wrapper').animate({
               left: value,
               duration: 750
           });
       });
       dp('.ch-color li').click(function(event) {
           dp('.ch-color li').removeClass('aktif');
           dp(this).addClass('aktif');
       });

   });

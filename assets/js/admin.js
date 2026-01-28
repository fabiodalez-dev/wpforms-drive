/**
 * Script per la pagina di amministrazione
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Funzione per copiare negli appunti
        $('.wpforms-gdrive-copy-button').on('click', function(e) {
            e.preventDefault();

            var $input = $(this).prev('input');
            $input.select();
            document.execCommand('copy');

            // Feedback visivo
            var originalText = $(this).text();
            $(this).text('Copiato!');

            setTimeout(function() {
                $('.wpforms-gdrive-copy-button').text(originalText);
            }, 2000);
        });

        // Conferma prima di disconnettere
        $('a[href*="action=disconnect"]').on('click', function(e) {
            if (!confirm('Sei sicuro di voler disconnettere l\'account Google Drive?')) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-dismiss notices dopo 5 secondi
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut();
        }, 5000);
    });

})(jQuery);

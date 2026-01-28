/**
 * Script per la pagina di amministrazione
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Funzione per copiare negli appunti
        $('.wpforms-gdrive-copy-button').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $input = $button.prev('input');
            var textToCopy = $input.val();

            // Usa l'API moderna clipboard se disponibile
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    showCopyFeedback($button);
                }).catch(function() {
                    fallbackCopy($input, $button);
                });
            } else {
                fallbackCopy($input, $button);
            }
        });

        function fallbackCopy($input, $button) {
            $input.select();
            document.execCommand('copy');
            showCopyFeedback($button);
        }

        function showCopyFeedback($button) {
            var originalText = $button.text();
            $button.text('Copiato!');
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        }

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

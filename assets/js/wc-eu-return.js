/* global jQuery */
( function ( $ ) {
    'use strict';

    $( function () {
        var $form = $( '#wc-eu-return-form' );
        if ( ! $form.length ) {
            return;
        }

        $form.on( 'submit', function ( e ) {
            var valid = true;
            var errors = [];

            $form.find( '.wcer-field-error' ).remove();
            $form.find( '.wcer-input-error' ).removeClass( 'wcer-input-error' );

            // Produkt
            var $product = $form.find( '#product_id' );
            if ( ! $product.val() ) {
                errors.push( { field: $product, msg: $product.closest( '.form-row' ).find( 'label' ).text().replace( '*', '' ).trim() } );
                valid = false;
            }

            // Powód
            var $reason = $form.find( '#return_reason' );
            if ( ! $reason.val() ) {
                errors.push( { field: $reason, msg: $reason.closest( '.form-row' ).find( 'label' ).text().replace( '*', '' ).trim() } );
                valid = false;
            }

            // Checkbox
            if ( ! $form.find( '#confirm_policy' ).is( ':checked' ) ) {
                errors.push( { field: $form.find( '#confirm_policy' ).closest( '.form-row' ), msg: null } );
                valid = false;
            }

            if ( ! valid ) {
                e.preventDefault();
                errors.forEach( function ( err ) {
                    if ( err.msg ) {
                        err.field.addClass( 'wcer-input-error' );
                        err.field.after(
                            $( '<span>' )
                                .addClass( 'wcer-field-error' )
                                .text( err.msg + ' — pole wymagane' )
                        );
                    } else {
                        err.field.addClass( 'wcer-input-error' );
                    }
                } );

                $( 'html, body' ).animate(
                    { scrollTop: $form.find( '.wcer-input-error' ).first().offset().top - 100 },
                    300
                );
            }
        } );
    } );
}( jQuery ) );

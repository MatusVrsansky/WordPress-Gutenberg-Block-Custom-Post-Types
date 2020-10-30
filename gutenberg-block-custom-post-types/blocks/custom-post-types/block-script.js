( function( $ ) {
    function init() {
        // Do something.
        console.log('ideee')
    }

    // Run when a document ready on the front end.
    $( document ).ready( init );

    // Run when a block preview is done loading.
    $( document ).on( 'mb_blocks_preview/gutenberg-block-custom-post-types', console.log('hhh'), init );
} )( jQuery );
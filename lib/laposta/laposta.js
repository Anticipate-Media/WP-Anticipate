( function( $ ) {
    function init() {
        jQuery('.lapostasubmit').on(
            'click',
            (ee)=>{
                ee.preventDefault();  
                var data = {
                    'action': 'laposta_submit',
                };
                var form = jQuery(ee.currentTarget).parents( "form" );
                jQuery('input',form).each((i,e)=>{
                    if(jQuery(e).attr('type')=='checkbox'){
                        if(e.checked) {
                            data[jQuery(e).attr('name')] = jQuery(e).val();
                        }                       
                    }
                    if(jQuery(e).attr('type')=='text'){
                        data[jQuery(e).attr('name')] = jQuery(e).val();
                    }
                    if(jQuery(e).attr('type')=='hidden'){
                        data[jQuery(e).attr('name')] = jQuery(e).val();
                    }
                });
        
                jQuery.post(window.anticipateajaxurl, data, function(response) {
                    if(response.success){
                        jQuery('.lapostamessage',form).html(response.data).removeClass('error');
                        jQuery('label, input',form).hide();
                    } else {
                        jQuery('.lapostamessage',form).html(response.data[0].message).addClass('error');
                    }
                });                
                return false;
        });
    }

    // Run when a document ready on the front end.
    $( document ).ready( init );

    // Run when a block preview is done loading.
    $( document ).on( 'mb_blocks_preview/laposta', init );
} )( jQuery );



jQuery(document).ready(function( $ ){

    $('#universal_password_form').on('submit', function( e ){

        e.preventDefault();

        $.ajax({
            url: _client_access.ajaxurl,
            type: "POST",
            data: {
                action: 'universalPasswordAjax',
                password: $('#universal_password_pass').val(),
                security: $('#universal_password_security').val()
            },
            success: function( msg ){

                if ( msg.code ){
                    alert( msg.description );
                    location.reload();
                } else {
                    alert( msg.description );
                }
            }
        });
    });

});
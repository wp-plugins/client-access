<?php

/**
 *
 * @since 1.0.0
 * @param
 * @return
 */
function client_access_scripts(){
    wp_enqueue_script( 'client-access-script', CLIENT_ACCESS_URL . 'assets/javascripts/script.js', array('jquery') );
    wp_enqueue_style( 'client-access-style', CLIENT_ACCESS_URL . 'assets/stylesheets/style.css' );
    wp_localize_script( 'client-access-script', '_client_access', apply_filters( CLIENT_ACCESS_NAMESPACE . '_localized_filter', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ) ) );
}
add_action( 'wp_enqueue_scripts', 'client_access_scripts' );


function client_access_settings_page_title( $title, $namespace ){

    return 'Client Access';

}
add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE . '_page_title', 'client_access_settings_page_title', 15, 2 );


function client_access_settings_menu_title( $title, $namespace ){

    return 'Client Access';

}
add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE . '_menu_title', 'client_access_settings_menu_title', 15, 2 );


function client_access_settings_footer_content( $content ){

    return sprintf( '%s | v%s | <a href="%s" target="_blank">%s</a>',
        __( 'Thank you for using Client Access', CLIENT_ACCESS_NAMESPACE ),
        CLIENT_ACCESS_VERSION,
        esc_url( 'http://support.zanematthew.com/forum/client-access/'),
        __( 'Support', CLIENT_ACCESS_NAMESPACE )
        );

}
add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE . '_footer', 'client_access_settings_footer_content', 15, 2 );
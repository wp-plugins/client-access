<?php

/**
 * Plugin Name: Client Access
 * Plugin URI: http://zanematthew.com/products/client-access/
 * Description: Let your client see their work in progress.
 * Version: 1.0.0
 * Author: Zane Matthew Kolnik
 * Author URI: http://zanematthew.com
 * Author Email: support@zanematthew.com
 * License: GPLv2 or later
 */

define( 'CLIENT_ACCESS_URL', plugin_dir_url( __FILE__ ) );
define( 'CLIENT_ACCESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLIENT_ACCESS_NAMESPACE', 'client_access' );
define( 'CLIENT_ACCESS_VERSION', '1.0.0' );
define( 'CLIENT_ACCESS_PLUGIN_FILE', __FILE__ );
define( 'CLIENT_ACCESS_PRODUCT_NAME', 'Client Access' ); // Must match download title in EDD store!
define( 'CLIENT_ACCESS_AUTHOR', 'Zane Matthew' );

require CLIENT_ACCESS_PATH . 'lib/lumber/lumber.php';
require CLIENT_ACCESS_PATH . 'lib/quilt/quilt.php';
require CLIENT_ACCESS_PATH . 'lib/zm-welcome/zm-welcome.php';

require CLIENT_ACCESS_PATH . 'settings.php';

require CLIENT_ACCESS_PATH . 'src/AllowByRole/AllowByRole.php';
require CLIENT_ACCESS_PATH . 'src/AllowByIp/AllowByIp.php';
require CLIENT_ACCESS_PATH . 'src/AllowByUniversalPassword/AllowByUniversalPassword.php';


function client_access_init(){

    global $client_access_settings_obj;
    $client_access_settings_obj = new Quilt(
        CLIENT_ACCESS_NAMESPACE,
        array(),
        'plugin'
    );

    global $client_access_settings;
    $client_access_settings = $client_access_settings_obj->getSaneOptions();

    do_action( CLIENT_ACCESS_NAMESPACE . '_init' );

    if ( basename( $_SERVER['PHP_SELF'] ) == 'wp-login.php'
        || is_admin()
        // || is_user_logged_in()
        ){
        return;
    }


    if ( ! empty( $client_access_settings['client_access_enabled'] ) ){
        $access_granted = false;
    } else {
        $access_granted = apply_filters( 'client_access_access_granted', true );
    }

    if ( ! $access_granted ){

        // Remove un-needed scripts/meta from the wp head
        add_filter( 'show_recent_comments_widget_style', '__return_null' );
        remove_action( 'wp_head', array( 'WP_Widget_Recent_Comments', 'recent_comments_style' ), 99 );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'rsd_link' );

        client_access_load_template();
    }

}
add_action( 'init', 'client_access_init' );


/**
 * locate_template() returns path to file
 * if either the child theme or the parent theme have overridden the template
 * If neither the child nor parent theme have overridden the template,
 * we load the template from the 'templates' sub-directory of the directory this file is in
 */
function client_access_load_template(){
    if ( $overridden_template = locate_template( 'client-access.php' ) ) {
        load_template( $overridden_template );
        die();
    } else {
        load_template( dirname( __FILE__ ) . '/templates/client-access.php' );
        die();
    }
}


/**
 * Searches a string of text for certain "tags", replaces the tags
 * with the given value.
 *
 * @since 1.0.0
 * @param   $string     The value to replace tags from
 * @param   $tags       The default tags used contained key => value
 * @return  $string     The new string with replaced tags
 */
function client_access_template_tags( $string=null, $default_tags=null ){

    $message = str_replace( array_keys( $default_tags ), $default_tags, nl2br( $string ) );
    $message = wp_kses_decode_entities( $message,
        array(
            'code' => array(),
            'br' => array(),
            'a' => array()
            )
        );

    return $message;
}


/**
 * Manging of version numbers when plugin is activated.
 *
 * Add Upgraded From Option
 * Bail if activating from network, or bulk
 * Add the transient to redirect
 */
function client_access_activation() {

    $current_version = get_option( CLIENT_ACCESS_NAMESPACE . '_version' );
    if ( $current_version ) {
        update_option( CLIENT_ACCESS_NAMESPACE . '_version_upgraded_from', $current_version );
    }

    update_option( CLIENT_ACCESS_NAMESPACE . '_version', CLIENT_ACCESS_VERSION );

    if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
        return;
    }

    set_transient( '_' . CLIENT_ACCESS_NAMESPACE . '_activation_redirect', true, 30 );
}
register_activation_hook( CLIENT_ACCESS_PLUGIN_FILE, 'client_access_activation' );


/**
 * Manging of version numbers when plugin is activated
 */
function client_access_deactivate() {

    delete_option( CLIENT_ACCESS_NAMESPACE . '_version', CLIENT_ACCESS_VERSION );

}
register_deactivation_hook( CLIENT_ACCESS_PLUGIN_FILE, 'client_access_deactivate' );


function client_access_plugins_loaded(){

    load_plugin_textdomain( CLIENT_ACCESS_NAMESPACE, false, plugin_basename(dirname(__FILE__)) . '/languages' );

    $welcome_pages = new ZMWelcome(

    // Our Tabs
    array(
        'about-client-access' => array(
            'page_title' => 'Getting Started'
        )
        // ,
        // 'update-client-access' => array(
        //     'page_title' => 'Updates'
        // )
    ),

    // Paths and dir
    array(
        'dir_path' => plugin_dir_path( __FILE__ ) . 'welcome',
        'dir_url' => plugin_dir_url( __FILE__ ) . 'welcome'
    ),

    // Product info
    array(
        'slug' => CLIENT_ACCESS_NAMESPACE,
        'current_version' => CLIENT_ACCESS_VERSION,
        // 'previous_version' => get_option( CLIENT_ACCESS_NAMESPACE . '_version_upgraded_from'),
        'text_domain' => CLIENT_ACCESS_NAMESPACE,
        'start_slug' => 'about-client-access',
        // 'update_slug' => 'update-client-access',
        'name' => CLIENT_ACCESS_PRODUCT_NAME
    ) );

}
add_action( 'plugins_loaded', 'client_access_plugins_loaded' );
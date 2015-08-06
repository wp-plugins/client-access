<?php

/**
 * This class will contain ALL functionality for the "Allow By Role" section.
 * Including any CSS, JS files, settings, or additional templates, etc.
 */
Class AllowByRole {

    public function __construct(){

        $this->prefix = 'allow_by_role';

        add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE. '_settings', array( &$this, 'settings') );
        add_filter( 'client_access_access_granted', array( &$this, 'accessGranted' ) );

        add_action( 'client_access_init', array( &$this, 'init' ) );

    }


    /**
     * Filters the default settings, adding the additional settings below.
     *
     * @since 1.0.0
     */
    public function settings( $current_settings ){

        $settings[ $this->prefix ] = array(
            'title' => __('Allow by Role', CLIENT_ACCESS_NAMESPACE ),
            'fields' => array(
                array(
                    'id' => $this->prefix . '_enabled',
                    'title' => __('Enable', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'checkbox',
                    'desc' => __('By checking this option, only the allowed roles will have access to this website.',  CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_default_message',
                    'title' => __( 'Default Message', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'textarea',
                    'std' => __( 'Only logged in users with the following role(s): {roles} are allowed.', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __( 'You may use the following tags {roles}.', CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_footer_message',
                    'title' => __( 'Footer Message', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'textarea',
                    'std' => __( '{site_name} is a private site, open to only certain roles.', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __( 'Displayed at the bottom of the page for unauthorized users. You may use the following tags {site_name}, {roles}.', CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_allowed_roles',
                    'title' => __('Allowed Roles', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __('Please select the allowed roles (administrators are always allowed).',  CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'checkboxes',
                    'options' => $this->getRoles()
                    )
            )
        );


        return array_merge( $current_settings, $settings );

    }


    /**
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function init(){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled'] )
            && $this->isRoleNotAllowed() ){

            add_action( 'client_access_main_content', array( &$this, 'mainContent' ) );
            add_action( 'client_access_footer_content', array( &$this, 'footerContent' ) );

        }
    }


    public function accessGranted( $access ){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled'] ) && $this->isRoleNotAllowed()

            || ! empty( $client_access_settings[ $this->prefix . '_enabled'] ) && ! is_user_logged_in()
            ){
            return false;
        } else {
            return $access;
        }

    }


    /**
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function isRoleNotAllowed(){

        $allowed_roles = $this->getAllowedRoles();

        $current_user = wp_get_current_user();

        if ( empty( $current_user->roles[0] ) ){
            $allowed = false;
        } elseif ( $current_user->roles[0] == 'administrator' ) {
            $allowed = false;
        } else {
            $allowed = ! in_array( $current_user->roles[0], $allowed_roles ) ? true : false;
        }

        return $allowed;
    }


    public function getAllowedRoles(){

        global $client_access_settings;

        return empty( $client_access_settings[ $this->prefix . '_allowed_roles'] ) ? null : $client_access_settings[ $this->prefix . '_allowed_roles'];

    }



    public function getAllowedRolesText(){

        return implode( ', ', $this->getAllowedRoles() );

    }


    /**
     * Filter the main content displayed when a visitor is not allowed.
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function mainContent(){

        global $client_access_settings;

        $content = client_access_template_tags( $client_access_settings[ $this->prefix . '_default_message'], array(
            '{roles}' => '<strong>' . $this->getAllowedRolesText() . '</strong>'
        ) );

        echo '<p>' . $content . '</p>';

    }


    /**
     * Filter the footer content displayed when a visitor is not allowed.
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function footerContent(){

        global $client_access_settings;

        $content = client_access_template_tags( $client_access_settings[ $this->prefix . '_footer_message'], array(
            '{site_name}' => get_bloginfo( 'name' )
        ) );

        echo '<li>' . $content . '</li>';

    }


    public function getRoles(){

        if ( ! function_exists( 'get_editable_roles' ) ){
            require_once(ABSPATH.'wp-admin/includes/user.php' );
        }

        $editable_roles = array_reverse( get_editable_roles() );

        foreach ( $editable_roles as $role => $details ) {

            if ( $role != 'administrator' ){
                $roles[ esc_attr( $role ) ] = translate_user_role( $details['name'] );
            }

        }

        return $roles;

    }

}
new AllowByRole();
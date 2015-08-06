<?php

/**
 * This class will contain ALL functionality for the "Allow By IP" section.
 * Including any CSS, JS files, settings, or additional templates, etc.
 */
Class AllowByIp {

    public function __construct(){

        $this->prefix = 'allow_by_ip';

        add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE. '_settings', array( &$this, 'settings') );

        add_action( 'client_access_init', array( &$this, 'clientAccessInit' ) );
        add_filter( 'client_access_access_granted', array( &$this, 'accessGranted' ) );

        add_action( 'client_access_add_ip_address', array( &$this, 'addIpAddress' ), 15, 2 );

    }


    /**
     * Filters the default settings, adding the additional settings below.
     *
     * @since 1.0.0
     */
    public function settings( $current_settings ){

        $settings[ $this->prefix ] = array(
            'title' => __('Allow by IP', CLIENT_ACCESS_NAMESPACE ),
            'fields' => array(
                array(
                    'id' => $this->prefix . '_enabled',
                    'title' => __('Enable', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'checkbox',
                    'desc' => __('By checking this option, only the allowed IPs will have access to this website',  CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_default_message',
                    'title' => __( 'Default Message', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'textarea',
                    'std' => __( 'Your IP {ip_address} address is not allowed.', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __( 'You may use the following tags {ip_address}.', CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_footer_message',
                    'title' => __( 'Footer Message', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'textarea',
                    'std' => __( 'IP Address: {ip_address}', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __( 'Displayed at the bottom of the page for unauthorized users. You may use the following tags {ip_address}.', CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_allowed_ips',
                    'title' => __('Allowed IPs', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __('Enter each IP address on a new line. Note, you will need to add your current IP address: ' . $_SERVER['REMOTE_ADDR'],  CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'ips'
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
    public function clientAccessInit(){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled'] )
            && $this->isIpNotAllowed() ){
            add_action( 'client_access_main_content', array( &$this, 'mainContent' ) );
            add_action( 'client_access_footer_content', array( &$this, 'footerContent' ) );
        }
    }


    public function accessGranted( $access ){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled'] ) && $this->isIpNotAllowed() ){
            return false;
        } else {
            return $access;
        }

    }


    public function isIpAllowed( $current_ip=null ){
        $allowed_ips = $this->getAllowedIps();

        if ( empty( $allowed_ips ) )
            return false;

        $ip = empty( $current_ip ) ? $_SERVER['REMOTE_ADDR'] : $current_ip;
        return in_array( $ip, $allowed_ips ) ? true : false;
    }


    /**
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function isIpNotAllowed( $current_ip=null ){

        $allowed_ips = $this->getAllowedIps();

        if ( empty( $allowed_ips ) )
            return true;

        $ip = empty( $current_ip ) ? $_SERVER['REMOTE_ADDR'] : $current_ip;

        $allowed = ! in_array( $ip, $allowed_ips ) ? true : false;

        return $allowed;
    }


    /**
     *
     * @since   1.0.0
     * @return  $ips(array)     An array of valid IP address
     */
    public function getAllowedIps(){

        global $client_access_settings;

        if ( empty( $client_access_settings[ $this->prefix . '_allowed_ips' ] ) )
            return false;

        $values = array_values( array_filter( explode( PHP_EOL, trim( $client_access_settings[ $this->prefix . '_allowed_ips' ] ) ), 'trim' ) );

        $ips = array();

        // Sanitize our IP address
        foreach( $values as $value ){

            $ip = trim( $value );
            $valid_ip = ( filter_var( $ip, FILTER_VALIDATE_IP ) ) ? $ip : false;

            if ( $valid_ip )
                $ips[] = $valid_ip;
        }

        return $ips;
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
            '{ip_address}' => '<code>' . $_SERVER['REMOTE_ADDR'] . '</code>'
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
            '{ip_address}' => $_SERVER['REMOTE_ADDR']
        ) );

        echo '<li>' . $content . '</li>';

    }


    /**
     * Adds an IP address to the allowed IP address list
     *
     * @since 1.0.0
     * @param   $ip         The IP address to add
     * @param   $comment    The comment
     * @return  $added      bool
     */
    public function addIpAddress( $ip=null, $comment=null ){

        if ( $comment && $ip ){

            global $client_access_settings;

            $client_access_settings[ $this->prefix . '_allowed_ips' ] .= PHP_EOL . $comment . PHP_EOL . $ip;

            $added = update_option( CLIENT_ACCESS_NAMESPACE, $client_access_settings );

        } else {

            $added = false;

        }

        return $added;

    }

}
new AllowByIp();
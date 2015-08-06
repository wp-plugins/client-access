<?php

/**
 * This class will contain ALL functionality for the "Allow By Universal Password" section.
 * Including any CSS, JS files, settings, or additional templates, etc.
 */
Class AllowByUniversalPassword {

    public function __construct(){

        $this->asset_url = plugin_dir_url( __FILE__ ) . 'assets/';
        $this->prefix = 'universal_password';

        add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE . '_settings', array( &$this, 'settings') );
        add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE . '_universal_password_value_sanitize', array( &$this, 'passwordValueSanitize' ) );
        add_filter( 'quilt_' . CLIENT_ACCESS_NAMESPACE . '_universal_password_enabled_sanitize', array( &$this, 'enabledSanitize' ) );

        add_action( CLIENT_ACCESS_NAMESPACE . '_init', array( &$this, 'init' ) );

        add_filter( CLIENT_ACCESS_NAMESPACE . '_access_granted', array( &$this, 'accessGranted' ) );
        add_filter( CLIENT_ACCESS_NAMESPACE . '_notice_message_tags_filter', array( &$this, 'filterNoticeTags' ) );

    }


    /**
     * Filters the default settings, adding the additional settings below.
     *
     * @since 1.0.0
     */
    public function settings( $current_settings ){

        $settings[ $this->prefix ] = array(
            'title' => __('Universal Password', CLIENT_ACCESS_NAMESPACE ),
            'fields' => array(
                array(
                    'id' => $this->prefix . '_enabled',
                    'title' => __('Enable', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'checkbox'
                    ),
                array(
                    'id' => $this->prefix . '_value',
                    'title' => __('Universal Password',  CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'fancyText',
                    'desc' => __('Your entire site will be blocked by single password.', CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_password_expires',
                    'title' => __('Expires', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __('Please use the following format: 01-Jan, 14, 2015, 01:55. Once this time is reached the site will be accessible WITHOUT the password.',  CLIENT_ACCESS_NAMESPACE  ),
                    'type' => 'touchtime',
                    'std' => array(
                        'month' => 'MM',
                        'day' => 'DD',
                        'year' => 'YYYY',
                        'hour' => 'HH',
                        'minute' => 'MM',
                        )
                    ),
                array(
                    'id' => $this->prefix . '_default_message',
                    'title' => __( 'Default Message', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'textarea',
                    'std' => __( 'A password is required.', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __( 'You may use the following tags {site_name}.', CLIENT_ACCESS_NAMESPACE )
                    ),
                array(
                    'id' => $this->prefix . '_footer_message',
                    'title' => __( 'Footer Message', CLIENT_ACCESS_NAMESPACE ),
                    'type' => 'textarea',
                    'std' => __( '{site_name} is password protected.', CLIENT_ACCESS_NAMESPACE ),
                    'desc' => __( 'Displayed at the bottom of the page for unauthorized users. You may use the following tags {site_name}.', CLIENT_ACCESS_NAMESPACE )
                    )
                )
        );

        return array_merge( $current_settings, $settings );

    }


    /**
     * Hook into the main plugin init
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function init(){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled'] ) ){

            // wp_ajax_* needs to be added here
            add_action( 'wp_ajax_nopriv_universalPasswordAjax', array( &$this, 'universalPasswordAjax' ) );
            add_action( 'wp_ajax_universalPasswordAjax', array( &$this, 'universalPasswordAjax' ) );

            $this->maybeStartSession();

            if ( ! empty( $_SESSION['client_access_settings']['password'] )
                && $this->isValidPassword( $_SESSION['client_access_settings']['password'] )
                && ! $this->isPasswordExpired() ) {
                return;
            }

            add_action( 'client_access_main_content', array( &$this, 'mainContent' ) );
            add_action( 'client_access_footer_content', array( &$this, 'footerContent' ) );
            add_action( 'wp_enqueue_scripts', array( &$this, 'enqueueScripts' ) );

        } else {

            $this->maybeEndSession();

        }

    }


    /**
     * Filter the access granted
     *
     * @since   1.0.0
     * @param   $access     (bool)  Current access
     * @return  $access     (bool)  The access
     */
    public function accessGranted( $access ){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled' ] ) ){

            $this->maybeStartSession();

            if ( $this->isPasswordExpired() ){

                return false;

            }

            if ( ! empty( $_SESSION['client_access_settings']['password'] )
                && $this->isValidPassword( $_SESSION['client_access_settings']['password'] ) ){

                return true;
            }

        } else {

            $this->maybeEndSession();

            return $access;
        }

    }


    /**
     * Determine if the current password is expired
     *
     * @since   1.0.0
     * @return  $access     (bool)  The status
     */
    public function isPasswordExpired(){

        global $client_access_settings;

        $month = $client_access_settings[ $this->prefix . '_password_expires' ]['month'];
        $day = $client_access_settings[ $this->prefix . '_password_expires' ]['day'];
        $year = $client_access_settings[ $this->prefix . '_password_expires' ]['year'];
        $hour = $client_access_settings[ $this->prefix . '_password_expires' ]['hour'];
        $minute = $client_access_settings[ $this->prefix . '_password_expires' ]['minute'];

        $time = "{$year}-{$month}-{$day} {$hour}:{$minute}";
        $time = strtotime( $time );

        $time_adj = current_time('timestamp');

        if ( $time < $time_adj && $time !== false ){
            $expired = true;
        } else {
            $expired = false;
        }

        return $expired;

    }


    /**
     * Intercept the AJAX request
     *
     * @since   1.0.0
     * @return  JSON status
     */
    public function universalPasswordAjax(){

        check_ajax_referer( 'universal_password_action', 'security' );

        if ( $this->maybeSetPassword( esc_attr( $_POST['password'] ) ) ){
            $msg = array(
                'code' => true,
                'description' => __( 'One moment while we log you in.', CLIENT_ACCESS_NAMESPACE )
                );
        } else {
            $msg = array(
                'code' => false,
                'description' => __( 'Invalid password.', CLIENT_ACCESS_NAMESPACE )
                );
        }

        wp_send_json( $msg );

    }


    /**
     * Load the needed JS and CSS
     *
     * @since 1.0.0
     */
    public function enqueueScripts(){

        $scripts = array(
            array(
                'handle' => $this->prefix . '-scripts',
                'src' => $this->asset_url . 'javascripts/scripts.js',
                'deps' => array( 'jquery' ),
                'ver' => '1',
                'in_footer' => true
                )
            );

        foreach( $scripts as $script ){
            wp_enqueue_script( $script['handle'], $script['src'], $script['deps'], $script['ver'], $script['in_footer'] );
        }

    }


    /**
     * Start session if it is has not started
     *
     * @since 1.0.0
     */
    public function maybeStartSession(){

        if ( ! session_id() ) {
            session_start();
        }

    }


    /**
     * Starts session if it is not available, and destroys the session for block by
     * password.
     *
     * @since 1.0.0
     * @param
     * @return
     */
    public function maybeEndSession(){

        $this->maybeStartSession();
        unset( $_SESSION['client_access_settings'][ $this->prefix . '_enabled'] );
        unset( $_SESSION['client_access_settings']['password'] );
        session_destroy();

    }


    /**
     * Sets the password in session.
     *
     * @since   1.0.0
     * @param   $password (string)  The password to set
     * @return  (bool)              Boolean on if password was set or not.
     */
    public function maybeSetPassword( $password=null ){

        if ( $this->isValidPassword( $password ) ){
            $this->maybeStartSession();
            $_SESSION['client_access_settings']['password'] = $password;
            return true;
        } else {
            return false;
        }

    }


    /**
     * Check if the password matches that in settings.
     *
     * @since   1.0.0
     * @param   $password(string)   The password to check
     * @return  (bool)              True if password is correct, false if it is not
     */
    public function isValidPassword( $password=null ){

        global $client_access_settings;
        return ( $password == $client_access_settings[ $this->prefix . '_value'] ) ? true : false;

    }


    /**
     * Reset the current password if it has changed.
     *
     * @since   1.0.0
     * @param   $value(string)  The password
     * @return  $value(string)  The password
     */
    public function passwordValueSanitize( $value ){

        $value = esc_attr( $value );

        global $client_access_settings;
        if ( ! empty( $client_access_settings[ $this->prefix . '_value'] ) && $value !== $client_access_settings[ $this->prefix . '_value'] ){
            $this->maybeStartSession();
            $_SESSION['client_access_settings']['password'] = false;
        }

        return $value;

    }


    /**
     * Sanitize filter to start the session if universal password is enabled.
     *
     * @since   1.0.0
     * @param   $value  The to sanitize
     * @return  $value  The value that is sanitized
     */
    public function enabledSanitize( $value ){

        $this->maybeStartSession();
        $_SESSION['client_access_settings'][ $this->prefix . '_enabled'] = true;

        return $value;

    }


    /**
     * Filter the main content to show the form
     *
     * @since 1.0.0
     */
    public function mainContent(){

        global $client_access_settings;

        $content = client_access_template_tags( $client_access_settings[ $this->prefix . '_default_message'], array(
            '{site_name}' => '<a href="' . home_url() . '">' . get_bloginfo( 'name' ) . '</a>'
        ) ); ?>
        <p><?php echo $content; ?></p>
        <form method="POST" action="#" id="<?php echo $this->prefix; ?>_form" class="<?php echo $this->prefix; ?>_form">
            <div class="form-row">
                <?php wp_nonce_field( 'universal_password_action', 'universal_password_security' ); ?>
                <input type="text" name="password" id="<?php echo $this->prefix; ?>_pass" class="<?php echo $this->prefix; ?>_password" required />
                <input type="submit" name="submit_password" value="<?php _e('Submit', CLIENT_ACCESS_NAMESPACE ); ?>" class="<?php echo $this->prefix; ?>_submit" />
            </div>
        </form>
        <?php
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
            '{site_name}' => '<a href="' . home_url() . '">' . get_bloginfo( 'name' ) . '</a>'
        ) );

        echo '<li>' . $content . '</li>';

    }


    /**
     * Filter the notice tags and add our custom tag
     *
     * @since   1.0.0
     * @param   $tags     The current tags
     * @return  $tags     The combined tags
     */
    public function filterNoticeTags( $tags ){

        global $client_access_settings;

        if ( ! empty( $client_access_settings[ $this->prefix . '_enabled'] ) ){
            $tags = array_merge( $tags, array(
                '{password}' => $client_access_settings[ $this->prefix . '_value']
            ) );
        }

        return $tags;
    }

}
new AllowByUniversalPassword();
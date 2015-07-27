<?php

/**
 * An easy class for making a plugin welcome page
 *
 * Usage: Include the file
 * Call the class with the following parameters:
 * @param $pages (array)
 * @param $template_path
 * @param $plugin_info (array)
 * @note Each slug is translated to the a template file, i.e., $slug = 'about-page', is found in
 * $dir_path . 'about-page.php'
 *
 * $welcome_pages = new ZMWelcome(
 *
 *    // Our Tabs
 *    array(
 *        'about-client-access' => array(
 *            'page_title' => 'Getting Started'
 *        )
 *        // ,
 *        // 'update-client-access' => array(
 *        //     'page_title' => 'Updates'
 *        // )
 *    ),
 *
 *    // Paths and dir
 *    array(
 *        'dir_path' => plugin_dir_path( __FILE__ ) . 'welcome',
 *        'dir_url' => plugin_dir_url( __FILE__ ) . 'welcome'
 *    ),
 *
 *    // Product info
 *    array(
 *        'slug' => CLIENT_ACCESS_NAMESPACE,
 *        'current_version' => CLIENT_ACCESS_VERSION,
 *        'previous_version' => get_option( CLIENT_ACCESS_NAMESPACE . '_version_upgraded_from'),
 *        'text_domain' => CLIENT_ACCESS_NAMESPACE,
 *        'start_slug' => 'about-client-access',
 *        // 'update_slug' => 'update-client-access',
 *        'name' => CLIENT_ACCESS_PRODUCT_NAME
 *    ) );
 */

if ( ! class_exists( 'ZMWelcome' ) ) :

class ZMWelcome {


    public $minimum_capability = 'manage_options';


    /**
     * Get things started
     *
     * @since 1.1
     */
    public function __construct( $pages=array(), $paths=array(), $plugin_info=array() ){

        $this->pages = $pages;

        $this->dir_path = $paths['dir_path'];
        $this->dir_url = trailingslashit( $paths['dir_url'] );
        $this->previous_version = empty( $plugin_info['previous_version'] ) ? null : $plugin_info['previous_version'];
        $this->current_version = $plugin_info['current_version'];
        $this->plugin_text_domain = $plugin_info['text_domain'];
        $this->activation_redirect = '_' . $plugin_info['slug'] . '_activation_redirect';
        $this->name = $plugin_info['name'];
        $this->start_slug = $plugin_info['start_slug'];
        $this->update_slug = empty( $plugin_info['update_slug'] ) ? null : $plugin_info['update_slug'];

        add_action( 'admin_menu', array( $this, 'admin_menus') );
        add_action( 'admin_head', array( $this, 'admin_head' ) );
        add_action( 'admin_init', array( $this, 'welcome_init' ), 1, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );
    }


    /**
     * Navigation tabs
     *
     * @access public
     * @since 1.1
     * @return void
     */
    public function display_tabs( $admin_pages=null, $selected=null ){ ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach( $admin_pages as $slug => $pages ) : ?>
                <a class="nav-tab <?php echo $selected == $slug ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => $slug ), 'index.php' ) ) ); ?>"><?php _e( $this->pages[ $slug ]['page_title'], $this->plugin_text_domain ); ?></a>
            <?php endforeach; ?>
        </h2>
        <?php
    }


    /**
     * Register the Dashboard Pages which are later hidden but these pages
     * are used to render the pages
     *
     * @access public
     * @since 1.1
     * @return void
     */
    public function admin_menus(){

        foreach( $this->pages as $slug => $value ){
            add_dashboard_page(
                $this->pages[ $slug ]['page_title'],
                null,
                $this->minimum_capability,
                $slug,
                array( $this, 'display_page' )
            );
        }

    }


    /**
     * Display our header, tabs, pages and footer
     *
     * @note Each page is derived via the $template_path . $slug . '.php';
     */
    public function display_page(){

        $slug = isset( $_GET['page'] ) ? $_GET['page'] : $this->pages[0];
        $tab_content = $this->dir_path . '/' . $slug . '.php';

        ?>
        <div class="wrap about-wrap">
            <?php $this->get_template_part( 'header' ); ?>
            <?php $this->display_tabs( $this->pages, isset( $_GET['page'] ) ? $_GET['page'] : 'getting-started' ); ?>
            <?php if ( file_exists( $tab_content ) ) : ?>
                <?php load_template( $tab_content ); ?>
            <?php endif; ?>
        </div>
        <?php
    }


    /**
     * Hide individual dashboard pages from the admin menu
     *
     * @access public
     * @since 1.1
     * @return void
     */
    public function admin_head(){
        foreach( $this->pages as $slug => $pages ){
            remove_submenu_page( 'index.php', $slug );
        }
    }


    /**
     * Sends user to the start page on first activation, as well as each time the
     * plugin is upgraded to a new version
     *
     * @access public
     * @since 1.1
     * @return void
     */
    public function welcome_init(){

        // Bail if no activation redirect
        if ( ! get_transient( $this->activation_redirect ) ){
            return;
        }

        // Delete the redirect transient
        delete_transient( $this->activation_redirect );

        // Bail if activating from network, or bulk
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ){
            return;
        }

        if( ! $this->previous_version ){ // First time install
            wp_safe_redirect( admin_url( 'index.php?page=' . $this->start_slug ) ); exit;
        } else { // Update
            wp_safe_redirect( admin_url( 'index.php?page=' . $this->update_slug ) ); exit;
        }

    }


    public function admin_scripts(){
        wp_enqueue_style( 'zm-welcome-admin', $this->dir_url . 'assets/stylesheets/welcome.css' );
    }


    public function get_template_part( $part=null ){

        $template_path = $this->dir_path . '/' . $part . '.php';

        $default['header'] = '<div class="welcome-badge"><img src="' . $this->dir_url . 'assets/images/icon-256x256.png" style="border: 1px solid #ccc;" /></div><h1>' . $this->name . '</h1><div class="about-text">' . sprintf( '%s %s!', __( 'Thank you for installing', $this->plugin_text_domain ), $this->name ) . '</div>'; ?>

        <?php if ( file_exists( $template_path ) ) : ?>
            <?php load_template( $template_path ); ?>
        <?php else : ?>
            <?php echo $default[ $part ]; ?>
        <?php endif; ?>

    <?php }
}
endif;
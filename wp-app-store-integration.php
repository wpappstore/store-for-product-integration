<?php
/*
WP App Store for Product Integration
http://wpappstore.com/
Version: 0.1

The following code is intended for developers to include in their themes/plugins
to add a mini version of the WP App Store to their product.

License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

require 'classes/wp-app-store.php';

if ( !class_exists( 'Tribe_WP_App_Store_Integration' ) ) :

class Tribe_WP_App_Store_Integration extends Tribe_WP_App_Store {
    
    public $parent_sku = 'events-calendar-pro:personal';
    
	public $affiliate_id = '5555';
    
    public $parent_slug = 'edit.php?post_type=tribe_events';

    public $addons_menu_title = 'Event Addons';

    function __construct() {
		// Stop if the user doesn't have access to install themes
		if ( ! current_user_can( 'install_themes' ) ) {
			return;
		}
        
        $this->slug = 'tribe-app-store';
        
        if ( is_multisite() ) {
			$this->admin_url = network_admin_url( $this->get_base_url() );
		}
		else {
			$this->admin_url = admin_url( $this->get_base_url() );
		}
        
		$this->home_url = $this->admin_url;
        $this->install_url = $this->home_url . '&wpas-do=install';
        $this->upgrade_url = $this->home_url . '&wpas-do=upgrade';
        
        if ( defined( 'WPAS_API_URL' ) ) {
            $this->api_url = WPAS_API_URL;
        }
        else {
            $this->api_url = 'https://wpappstore.com/api/client';
        }
        
        $this->cdn_url = 'http://cdn.wpappstore.com';
        
        add_action( 'admin_init', array( $this, 'handle_request' ) );
        
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        
        // Plugin upgrade hooks
        add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

        $this->output['head_js'] = "
            WPAPPSTORE.INTEGRATED = true;
            WPAPPSTORE.PARENT_SKU = '" . addslashes( $this->parent_sku ) . "';
            WPAPPSTORE.CLIENT_BASE_URL = '" . addslashes( $this->get_base_url() ) . "';
            WPAPPSTORE.CLIENT_INSTALL_URL = '" . addslashes( $this->get_client_install_url() ) . "';
        ";

        // Here you can pass a custom variable through the purchase process,
        // to be received in the sale postback
        $this->output['head_js'] .= "
            WPAPPSTORE.SP_CUSTOM = 'purplemonkey';
        ";
    }
    
    function admin_menu() {
        // Add submenu item to Appearance
        add_submenu_page( $this->parent_slug, $this->addons_menu_title, $this->addons_menu_title, 'install_themes', $this->slug, array( $this, 'render_page' ) );

        add_action( 'admin_print_styles', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_head', array( $this, 'admin_head' ) );
    }
    
    function api_url() {
        $qs = remove_query_arg( 'page', $_SERVER['QUERY_STRING'] );
        $qs = add_query_arg( 'wpas-page', urlencode( $_GET['page'] ), $qs );
        
        if ( !isset( $_GET['wpas-client-base-url'] ) ) {
            $qs = add_query_arg( 'wpas-client-base-url', urlencode( $this->get_base_url() ), $qs );
        }
        
        if ( !isset( $_GET['wpas-integrated'] ) ) {
            $qs = add_query_arg( 'wpas-integrated', '1', $qs );
        }

        if ( isset( $_GET['wpas-do'] ) && $_GET['wpas-do'] ) {
            return $this->api_url . '/?' . ltrim( $qs, '?' );
        }

        if ( !isset( $_GET['wpas-action'] ) ) {
            $qs = add_query_arg( 'wpas-action', 'addons', $qs );
        }
        
        if ( ( !isset( $_GET['wpas-action'] ) || 'addons' == $_GET['wpas-action'] ) && !isset( $_GET['wpas-sku'] ) ) {
            $qs = add_query_arg( 'wpas-sku', urlencode( $this->parent_sku ), $qs );
        }

        return $this->api_url . '/?' . ltrim( $qs, '?' );
    }
    
    function get_client_install_url() {
        return 'update.php?action=install-plugin&plugin=wp-app-store&_wpnonce=' . urlencode( wp_create_nonce( 'install-plugin_wp-app-store' ) );
    }
    
    function get_base_url() {
        return add_query_arg( array( 'page' => $this->slug ), $this->parent_slug );
    }
    
    function body_after() {
        return "
            <script>
            jQuery('#wp-app-store > .header .title').prepend('\
                <div id=\"icon-edit\" class=\"icon32 icon32-posts-tribe_events\"><br></div>\
                <h2>" . $this->addons_menu_title . "</h2>\
            ');
            </script>
        ";
    }
    
	function get_affiliate_id() {
        return $this->affiliate_id;
	}

    // Install plugin
    function plugins_api( $api, $action, $args ) {
        if (
            'plugin_information' != $action || false !== $api
            || !isset( $args->slug ) || 'wp-app-store' != $args->slug
        ) return $api;

        $upgrade = $this->get_client_upgrade_data();
        $menu = $this->get_menu();

        if ( !$upgrade ) return $api;
		
		// Add affiliate ID to WP settings if it's not already set by another
		// theme or plugin
		if ( $this->affiliate_id && !get_site_transient( 'wpas_affiliate_id' ) ) {
			set_site_transient( 'wpas_affiliate_id', $this->affiliate_id );
		}
        
        $api = new stdClass();
        $api->name = $menu['title'];
        $api->version = $upgrade['version'];
        $api->download_link = $upgrade['download_url'];
        return $api;
    }
}

function tribe_wp_app_store_init() {
	new Tribe_WP_App_Store_Integration();
}

add_action( 'init', 'tribe_wp_app_store_init' );

endif;

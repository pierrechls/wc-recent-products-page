<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpgenie.org
 * @since      1.0.0
 *
 * @package    Woocommerce_recentproducts_page
 * @subpackage Woocommerce_recentproducts_page/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_recentproducts_page
 * @subpackage Woocommerce_recentproducts_page/public
 * @author     Your Name <email@example.com>
 */
class Woocommerce_recentproducts_page_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}


	public function template_loader( $template ) {


		$find = array( 'woocommerce.php' );
		$file = '';

		if ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'recentproducts' ) ) ) {

			$file 	= 'archive-product.php';
			$find[] = $file;
			$find[] = WC()->template_path() . $file;

		}

		if ( !empty($file) ) {

			$template = locate_template( array_unique( $find ) );

			if ( ! $template || WC_TEMPLATE_DEBUG_MODE ) {
				$template = WC()->plugin_path() . '/templates/' . $file;
			}
		}


		return $template;
	}


	public function pre_get_posts( $q ) {

		if(!$q->query)
			return;
		$recentproducts_page_id = $this->get_main_wpml_id(wc_get_page_id( 'recentproducts' ));

		if($recentproducts_page_id != -1 ){


			if( is_page( $recentproducts_page_id ) ){

				$q->set( 'post_type', 'product' );
				$q->set( 'orderby', 'date' );
				$q->set( 'order', 'DESC' );
				$q->set( 'page', '' );
				$q->set( 'pagename', '' );

				// Fix conditional Functions
				$q->is_archive           = true;
				$q->is_post_type_archive = true;
				$q->is_singular          = false;
				$q->is_page              = false;
				$q->is_recent_products_page         = true;

				add_filter( 'woocommerce_is_filtered' , array($this, 'add_is_filtered'), 99); // hack for displaying when Shop Page Display is set to show categories

				// Fix WP SEO
					if ( class_exists( 'WPSEO_Meta' ) ) {
							add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
							add_filter( 'wpseo_metakey', array( $this, 'wpseo_metakey' ) );
							add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );
					}


				}
		}
	}

	public function mod_woocommerce_product_query($q, $WC_Query){

		global $wp_query;

		if($wp_query->is_recent_products_page) {
			$meta_query = WC()->query->get_meta_query();
		}
	}

	public function woocommerce_page_title($title) {

		global $wp_query;

		$recentproducts_page_id = $this->get_main_wpml_id(wc_get_page_id( 'recentproducts' ));


		if($wp_query->is_recent_products_page) {

			$page_title   = get_the_title( $recentproducts_page_id );

			return $page_title;

		}

		return $title;

	}

	public function woocommerce_get_breadcrumb($crumbs, $WC_Breadcrumb){

		global $wp_query;

		if($wp_query->is_recent_products_page) {

			$recentproducts_page_id = $this->get_main_wpml_id(wc_get_page_id( 'recentproducts' ));
			$crumbs[1] = array(get_the_title( $recentproducts_page_id ), get_permalink( $recentproducts_page_id )	);
		}

		return $crumbs;

	}

	 /**
     * Get main product id for multilanguage purpose
     *
     * @access public
     * @return int
     *
     */

    public static function get_main_wpml_id($id){

        global $sitepress;

        if (function_exists('icl_object_id')) { // Polylang with use of WPML compatibility mode
            $id = icl_object_id($id,'page',false);
        }


        return $id;

    }

    /**
     * Set is filtered is true to skip displaying categories only on page
     *
     * @access public
     * @return bolean
     *
     */

    function add_is_filtered($id){

        return true;

    }


    /**
     * Change title for recentproducts page

     */
    function change_page_title( $title ) {

				    global $wp_query;

				    if (!is_woocommerce() OR !$wp_query->is_recent_products_page) {
					return $title;
						}


					$title = get_the_title($this->get_main_wpml_id(wc_get_page_id( 'recentproducts' )));

				    return $title;
			}


	/**
	 *
     * Fix active class in nav for auction  page.
	 *
	 * @param array $menu_items
	 * @return array
                 *
	 */
	function nav_menu_item_classes($menu_items) {

		global $wp_query;

		if (!is_woocommerce() OR !$wp_query->is_recent_products_page) {
			return $menu_items;
		}

		$recentproducts_page = (int) $this->get_main_wpml_id(wc_get_page_id( 'recentproducts' ));

		foreach ((array) $menu_items as $key => $menu_item) {

			$classes = (array) $menu_item->classes;

			// Unset active class for blog page

			$menu_items[$key]->current = false;

			if (in_array('current_page_parent', $classes)) {
				unset($classes[array_search('current_page_parent', $classes)]);
			}

			if (in_array('current-menu-item', $classes)) {
				unset($classes[array_search('current-menu-item', $classes)]);
			}

			// Set active state if this is the shop page link
			if ($recentproducts_page == $menu_item->object_id && 'page' === $menu_item->object) {
				$menu_items[$key]->current = true;
				$classes[] = 'current-menu-item';
				$classes[] = 'current_page_item';

			}

			$menu_items[$key]->classes = array_unique($classes);

		}

		return $menu_items;
	}

	 /**
     * Translate recentproducts page url
     */
    function translate_ls_recentproducts_url($languages, $debug_mode = false) {
        global $sitepress;
        global $wp_query;



		$recentproducts_page = (int) wc_get_page_id( 'recentproducts' );


        foreach ($languages as $language) {
            // shop page
            // obsolete?
            if ($wp_query->is_recent_products_page || $debug_mode ) {


                    $sitepress->switch_lang($language['language_code']);
                    $url = get_permalink( apply_filters( 'translate_object_id', $recentproducts_page, 'page', true, $language['language_code']) );
                    $sitepress->switch_lang();
	                $languages[$language['language_code']]['url'] = $url;

            }
        }

        return $languages;
    }


	/**
	 * Register Widgets.
	 *
	 * @since
	 */
	function wc_register_widgets() {
		register_widget( 'WC_RecentProducts_Page_Widget_Layered_Nav' );
	}


	/**
	 * WP SEO meta description.
	 *
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_metadesc() {
		return WPSEO_Meta::get_value( 'metadesc', wc_get_page_id( 'recentproducts' ) );
	}

	/**
	 * WP SEO meta key.
	 *
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_metakey() {
		return WPSEO_Meta::get_value( 'metakey', wc_get_page_id( 'recentproducts' ) );
	}

	/**
	 * WP SEO title.
	 *
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_title() {
		return WPSEO_Meta::get_value( 'title', wc_get_page_id( 'recentproducts' ) );
	}


}

if ( ! function_exists( 'is_woocommerce_recent_products_page' ) ) :
function is_woocommerce_recent_products_page(){
	global $wp_query;

    if ( ! isset( $wp_query ) ) {
        _doing_it_wrong( __FUNCTION__, __( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' );
        return false;
    }

    return $wp_query->is_recent_products_page;
}
endif;

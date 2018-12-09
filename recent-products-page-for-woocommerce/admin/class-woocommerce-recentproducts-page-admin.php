<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpgenie.org
 * @since      1.0.0
 *
 * @package    Woocommerce_recentproducts_page
 * @subpackage Woocommerce_recentproducts_page/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_recentproducts_page
 * @subpackage Woocommerce_recentproducts_page/admin
 * @author     Your Name <email@example.com>
 */
class Woocommerce_recentproducts_page_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function add_recent_productspage_on_option_page($settings){


		$recentproductspageid = array('title'    => __( 'RecentProducts Page', 'wc_recentproducts_page' ),
								
								'id'       => 'woocommerce_recentproducts_page_id',
								'type'     => 'single_select_page',
								'default'  => '',
								'class'    => 'wc-enhanced-select-nostd',
								'css'      => 'min-width:300px;',
								'desc_tip' => __( 'This sets the recentproducts page of your shop - this is where your product recent products archive will be.', 'wc_recentproducts_page' ),
								);
		

		$modifsettings = 	array_slice($settings, 0, 2, true) ;
		array_push($modifsettings,$recentproductspageid);			
	
		return array_merge  ($modifsettings ,array_slice($settings, 2, count($settings), true));

	}

}
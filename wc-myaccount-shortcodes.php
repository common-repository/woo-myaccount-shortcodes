<?php

/**
 * Plugin Name: WC Myaccount Shortcodes
 * Version: 1.5
 * Description: This addon provides you woocommerce myaccount page shortcodes e.g. current user downloads, orders, account details etc.
 * Author: Bilal Malik
 * Author URI: https://www.fiverr.com/users/bilalmalik349
 * Text Domain: wc_myaccount_sc
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if( ! defined( 'ABSPATH' ) ) exit;

class WCAS {

	public static $instance;
	const VERSION = '1.0';

	public static function instance() {
		if( is_null( self::$instance ) && ! self::$instance instanceof WCAS ) {
			self::$instance = new WCAS;
			self::$instance->constants();
			self::$instance->hooks();
		}
	}

	/**
	 * Create constants
	 */
	public function constants() {
		define( 'WCAS_URL', plugins_url( '', __FILE__ ) . '/' );
		define( 'WCAS_ASSETS', WCAS_URL . 'assets/' );
		define( 'WCAS', 'wc_myaccount_sc' );
	}

	/**
	 * Create hooks
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'wcas_scripts' ] );
		add_shortcode( 'woo_downloads', [ $this, 'wcas_downloads' ] );
		add_shortcode( 'woo_address', [ $this, 'wcas_address' ] );
		add_shortcode( 'woo_orders', [ $this, 'wcas_orders' ] );
		add_shortcode( 'woo_ac_details', [ $this, 'wcas_acc_details' ] );
		add_action( 'admin_menu', [ $this, 'wcas_admin_menu' ] );
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wcas_links' ] );
		add_filter( 'woocommerce_get_endpoint_url', [ $this, 'wcas_filter_order_pagination_link' ], 10, 4 );
		add_filter( 'woocommerce_get_endpoint_url', [ $this, 'wcas_filter_edit_address_link' ], 10, 4 );
		/*add_action( 'woocommerce_account_content', function() {
			$user = wp_get_current_user();

		}, 5 );*/
		add_action( 'init', function() {
			// var_dump( wc_get_endpoint_url( 'edit-address','billing' ) );
			// var_dump( wc_get_account_endpoint_url( 'edit-address' ) );
			// $user = wp_get_current_user();
		} );
	}

	public function wcas_filter_edit_address_link( $url, $endpoint, $value, $permalink ) {

		global $post;

		if( ! $post || stripos( $post->post_content, '[woo_address]' ) === false ) return $url; 
		
		if( $endpoint == 'edit-address' && function_exists( 'wc_get_account_endpoint_url' ) ) {
			$url = wc_get_page_permalink( 'myaccount' ) . $endpoint . '/' . $value;
		}

		return $url;
	}

	public function wcas_filter_order_pagination_link( $url, $endpoint, $value, $permalink ) {

		global $post;

		if( ! $post || stripos( $post->post_content, '[woo_orders]' ) === false ) return $url; 

		$url = stripos( get_permalink(), '?' ) === false ? get_permalink() . '?woo_order=' . $value : get_permalink() . '&woo_order=' . $value;

		return $url;
	}

	public function wcas_links( $links ) {

		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=wcsc-menu' ) ) . '">' . __( 'Shortcodes', 'textdomain' ) . '</a>'
		), $links );

		return $links;
	}

	/**
	 * WCAS admin scripts
	 */
	public function wcas_scripts() {
		wp_enqueue_style( 'wcas-style', WCAS_ASSETS . 'css/wcas-style.css', self::VERSION, [], false );
	}

	/**
	 * WCAS admin menu
	 */
	public function wcas_admin_menu() {
		// add_menu_page( 'WC Account Shortcodes', 'WC Account Shortcodes', 'manage_options', 'wcsc-menu', [ $this, 'wcas_menu_page' ] );
		add_submenu_page( 'woocommerce', 'WC Account Shortcodes', 'Account Shortcodes', 'manage_options', 'wcsc-menu', [ $this, 'wcas_menu_page' ] );
	}

	/**
	 * WCAS menu page
	 */
	public function wcas_menu_page() { ?>
		<div class="wcas-wrapper">
			<div class="wcas-title">
				<h3><?php _e( 'Shortcodes', WCAS ); ?></h3>
			</div>
			<div class="wcas-shortcodes">
				<div class="wcas-box">
					<code class="wcas-sc">[woo_downloads]</code>
				</div>
				<div class="wcas-box-info">
					<span class="dashicons dashicons-info-outline"></span>
					<i class="wcas-sc-info"><?php _e( 'Shows the WC downloads of logged in user', WCAS ); ?></i>
				</div>
				<div class="wcas-box">
						<code class="wcas-sc">[woo_address]</code>
				</div>
				<div class="wcas-box-info">
					<span class="dashicons dashicons-info-outline"></span>
					<i class="wcas-sc-info"><?php _e( 'Shows the WC address of logged in user', WCAS ); ?></i>
				</div>
				<div class="wcas-box">
						<code class="wcas-sc">[woo_orders]</code>
				</div>
				<div class="wcas-box-info">
					<span class="dashicons dashicons-info-outline"></span>
					<i class="wcas-sc-info"><?php _e( 'Shows the WC orders of logged in user', WCAS ); ?></i>
				</div>
				<div class="wcas-box">
						<code class="wcas-sc">[woo_ac_details]</code>
				</div>
				<div class="wcas-box-info">
					<span class="dashicons dashicons-info-outline"></span>
					<i class="wcas-sc-info"><?php _e( 'Shows the WC account details of logged in user', WCAS ); ?></i>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Woocommerce myaccount details page
	 */
	public function wcas_acc_details() {
		// include_once WC_ABSPATH . 'templates\myaccount\form-edit-account.php';
		if( function_exists( 'wc_get_template' ) ) {
			wc_get_template( 'myaccount/form-edit-account.php', [ 'user' => wp_get_current_user() ] );
		}
	}

	/**
	 * Woocommerce myaccount orders page
	 */
	public function wcas_orders() {
		// include_once WC_ABSPATH . 'templates\myaccount\orders.php';
		if( function_exists( 'woocommerce_account_orders' ) ) {
			$page = isset( $_GET['woo_order'] ) ? $_GET['woo_order'] : 1;
			woocommerce_account_orders( $page );
		}
	}

	/**
	 * Woocommerce myaccount address page
	 */
	public function wcas_address() {
		// include_once WC_ABSPATH . 'templates\myaccount\form-edit-address.php';
		if( function_exists( 'wc_get_template' ) ) {
			wc_get_template( 'myaccount/my-address.php', [ 'load_address' => 'billing' ] );
		}
	}

	/**
	 * Woocommerce myaccount downloads page
	 */
	public function wcas_downloads() {
		// include_once WC_ABSPATH . 'templates\myaccount\downloads.php';
		if( function_exists( 'wc_get_template' ) ) {
			wc_get_template( 'myaccount/downloads.php' );
		}
	}
}

if( ! function_exists( 'wcas_error_msg' ) ) {
	function wcas_error_msg() {
		deactivate_plugins( __FILE__ );
		return _e( '<div class="notice notice-error is-dismissible" style="padding: 10px"><b>WC MyAccount Shortcodes</b> addon requires <b>Woocommerce</b> plugin to be activated.</div>' );
	}	
}

if( ! function_exists( 'check_requirements' ) ) {
	function check_requirements() {
		if( ! class_exists( 'WooCommerce' ) ) {
			return add_action( 'admin_notices', 'wcas_error_msg' );
		}
		return WCAS::instance();
	}	
}
add_action( 'plugins_loaded', 'check_requirements' );
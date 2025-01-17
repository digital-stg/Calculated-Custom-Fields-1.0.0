<?php

/**
 * The admin-specific functionality of the plugin.
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://digital-stg.com 
 * @since      1.0.0
 *
 * @package    Calculated_Custom_Fields
 * @subpackage Calculated_Custom_Fields/admin
 * @author     Digital STG <contact@digital-stg.com>
 */

class Calculated_Custom_Fields_Admin

{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string   $plugin_name    
	 */

	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version   
	 */

	private $version;

	//Initialize the class and set its properties. Admin side hooks and filters.
	public function __construct($calculated_custom_fields, $version)
	{
		$this->plugin_name = $calculated_custom_fields;
		$this->version = $version;
		
		add_filter('manage_edit-product_columns', array($this,'ccf_new_columns_in_admin_product_table'), 30);
		add_action('manage_product_posts_custom_column', array($this,'ccf_admin_new_columns_content'), 30);

		add_filter('woocommerce_product_data_tabs', array($this, 'ccf_woocommerce_create_product_data_tab'), 30);
		
		add_action('woocommerce_product_data_panels', array($this, 'ccf_admin_tabs_contents'), 30);
		add_filter('upload_mimes', array($this, 'allowing_more_mimes_types'), 10, 3);
		add_action('admin_init', [$this, 'cpm_settings']);
		if (is_admin()) {add_action('woocommerce_process_product_meta', array($this, 'ccf_options_save_and_delete'), 30);}	
	}

	//Register the stylesheets for the admin area
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/calculated-custom-fields-admin.css', array(), $this->version, 'all');
		//Admin fontawesome
		$admin_handle = 'admin_css';
		$admin_fontawsome = plugin_dir_url(__FILE__) . '/assets/fontawesome/css/all.css';
		wp_enqueue_style($admin_handle, $admin_fontawsome);
	}

	// Register the JavaScript for the admin area.
	 public function enqueue_scripts()
	{
		$screen = get_current_screen();
		if ($screen->id === 'product') {
			wp_enqueue_script($this->plugin_name . 'admin_js', plugin_dir_url(__FILE__) . 'js/admin_side.js', array('jquery'), $this->version, false);
			// Getting PHP values and pass them to JS File -> wp_add_inline_script() 
			wp_register_script($this->plugin_name . 'admin_js_inline_script', '', array("jquery"), '', true);
			wp_enqueue_script($this->plugin_name . 'admin_js_inline_script');
		}

		$post = get_post();
		if($post) {
		$product = wc_get_product($post->ID);
		if (!empty($product)) {
			// Getting PHP values and pass them to JS File
			require_once plugin_dir_path(__FILE__) . 'data/admin-inline-script.php';
		}};
	}

	// Create a new tab in the WooCommerce product data tab
	function ccf_woocommerce_create_product_data_tab($tab)
	{
		$tab['Admin_product_menu'] = [
			'label' => __('Calculated Custom Fields', 'calculated-custom-fields'),
			'target' => 'custom_produts_manager',
			'class' => ['tabs_custom'],
			'priority' => 25
		];
		return $tab;
	}


	//Product data panel tabs display
	function ccf_admin_tabs_contents()
	{require plugin_dir_path(__FILE__) . 'partials/calculated-custom-fields-admin-display.php';}

	//mimes types
	function allowing_more_mimes_types($mimes)
	{
		$mimes['ttf']   = 'font/ttf';
		$mimes['woff']  = 'font/woff';
		$mimes['woff2'] = 'font/woff2';
		$mimes['epub'] = 'application/epub+zip';
		$mimes['json'] = 'application/json';
		$mimes['jsonld'] = 'application/ld+json';
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		//unset($mimes['gif']);

		return $mimes;
	}

	//Save and delete options data
	function ccf_options_save_and_delete($post_id)

	{
		if ($_POST) {

			$i = 1;
			do {
				if ($_POST['op' . $i . '_option_submit'] || $_POST['op' . $i . '_delete-submit']) {
					$option_identificator = "op" . $i;
					require_once plugin_dir_path(__FILE__) . 'data/save_delete_template.php';
				}
				$i++;
			} while ($i <= 2);
		}
	}

	function cpm_settings()
	{
		if (get_option('stgccf_quantity_related') === false) {
			add_option('stgccf_quantity_related', 'true', '', 'yes');
		}
		if (get_option('stgccf_free_option_text') === false) {
			add_option('stgccf_free_option_text', __('Free option', 'calculated-custom-fields'), '', 'yes');
		}
		if (get_option('stgccf_cost_option_text') === false) {
			add_option('stgccf_cost_option_text', __('Option cost : ', 'calculated-custom-fields'), '', 'yes');
		}
		if (get_option('stgccf_add_to_cart_text') === false) {
			add_option('stgccf_add_to_cart_text', __('Customize', 'calculated-custom-fields'), '', 'yes');
		}
		if (get_option('stgccf_option_required_text') === false) {
			add_option('stgccf_option_required_text', __('Option required', 'calculated-custom-fields'), '', 'yes');
		}

		if ($_POST && $_POST['save_settings']) {

			update_option('stgccf_quantity_related', 'yes');
			if (isset($_POST['free_option_text']) && !empty($_POST['free_option_text'])) {
				update_option('stgccf_free_option_text', sanitize_text_field($_POST['free_option_text']));
			}
			if (isset($_POST['cost_option_text']) && !empty($_POST['cost_option_text'])) {
				update_option('stgccf_cost_option_text', sanitize_text_field($_POST['cost_option_text']));
			}
			if (isset($_POST['add_to_cart_text']) && !empty($_POST['add_to_cart_text'])) {
				update_option('stgccf_add_to_cart_text', sanitize_text_field($_POST['add_to_cart_text']));
			}
			if (isset($_POST['option_required_text']) && !empty($_POST['option_required_text'])) {
				update_option('stgccf_option_required_text', sanitize_text_field($_POST['option_required_text']));
			}
		}

		if ($_POST && $_POST['remove_settings']) {
			update_option('stgccf_quantity_related', 'yes');
			update_option('stgccf_free_option_text', __('Free option', 'calculated-custom-fields'));
			update_option('stgccf_cost_option_text', __('Option cost', 'calculated-custom-fields'));
			update_option('stgccf_add_to_cart_text', __('Customize', 'calculated-custom-fields'));
			update_option('stgccf_option_required_text', __('Option required', 'calculated-custom-fields'));
		}


	}

	//Add column in product admin table 
	function ccf_new_columns_in_admin_product_table($columns) {
		
		$columns['CCF'] = 'Fields'; 
		return $columns;
	}

	function ccf_admin_new_columns_content($columns)
	{

		if ($columns == 'CCF') {

			$post = get_post();
			$product = wc_get_product($post->ID);
			$active_option = 0;
			$i = 1;

			do {
				$option_identificator = 'op' . $i;
				if ($product->get_meta($option_identificator . '_field_type')) {
					$active_option++;
				}
				$i++;
			} while ($i <= 2);

			if ($active_option > 0) : echo $active_option;
			endif;
		}
	}

 
}
		

<?php
/**
 * Plugin Name: 3D Configurator with Three.js
 * Description: Advanced plugin to use model(GLB) into WooCOmmerce products
 * Version: 1.0
 * Author: Developer
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$active_plugins = get_option('active_plugins');
if (!is_array($active_plugins) || !in_array('woocommerce/woocommerce.php', $active_plugins)) {
	add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>WooCommerce is required</strong> for the 3DConfigurator Woo ThreeJs plugin to work. Please install and activate <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>.</p></div>';
    });
    add_action( 'admin_init', function() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    });

    return;
}
$plugin_path = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
$plugin_data = file_get_contents($plugin_path);
if (preg_match('/^.*Version:\s*(.*)$/mi', $plugin_data, $matches)) {
	$woocommerce_version = trim($matches[1]);
	preg_match('/^\d+\.\d+\.\d+/', $woocommerce_version, $matches);
	$base_version = $matches[0] ?? $woocommerce_version;
	// echo $woocommerce_version; exit;
	if (version_compare($base_version, '9.0.0', '<')) {
		add_action('admin_notices', function () use ($woocommerce_version) {
			echo '<div class="notice notice-error"><p><strong>WooCommerce version 9.0.0 or higher is required</strong> for the 3DConfigurator Woo ThreeJs plugin. ';
			echo 'Your current version: <strong>' . esc_html($woocommerce_version ?: 'Unknown') . '</strong>. Please update WooCommerce.</p></div>';
		});
	
		add_action('admin_init', function () {
			deactivate_plugins(plugin_basename(__FILE__));
			if (isset($_GET['activate'])) {
				unset($_GET['activate']);
			}
		});
	
		return;
	}
}

define('PLUGIN_VERSION', date('YmdHis'));
define('PLUGIN_PATH', plugin_dir_url(__FILE__));

//class Configurator_3d {
class Configurator_3d {

    public function __construct() {	
		add_filter('post_class',  array($this,'remove_wp_block_class_on_woocommerce_pages'));
		// add_filter('wp',  array($this,'custom_remove_cart_hooks'));
		// add_action('wp', 'custom_remove_cart_hooks');
		$license_key = get_option('my_plugin_license_key');
		if ($this->my_plugin_validate_license($license_key)) {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_script'));
			add_filter( 'template_include', array($this,'force_wc_template_for_block_themes'), 99 );
			add_filter('post_class',  array($this,'remove_wp_block_class_on_woocommerce_pages'));


			// add_action( 'wp_enqueue_scripts', 'enqueue_threejs_cart_scripts' );
			add_filter('woocommerce_single_product_image_thumbnail_html', array($this,'custom_gallery_image_html'), 10);
			add_filter('woocommerce_product_class', array($this,'add_configurator_product_class'), 10, 2);
			add_action( 'woocommerce_single_product_summary', array($this,'configurator_summery_part'), 30);
			add_action( 'woocommerce_before_add_to_cart_quantity', array($this,'show_parts_and_materials'), 10);	
			add_filter( 'woocommerce_get_price_html',  array($this,'sv_change_product_html'), 10, 2 );
			add_action( 'woocommerce_before_single_product_summary', array($this,'move_product_name_next_to_image_single_product'), 5);

		}
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'),10);
		
		//product type
		add_action('init', array($this,'register_custom_product_type'));
		add_filter('product_type_selector', array($this,'add_custom_product_type'));

		// Step 1 - Adding a custom tab to the Products Metabox
		add_filter( 'woocommerce_product_data_tabs', array($this,'add_3d_configurator_product_data_tab'), 99 , 1 );
		add_action( 'woocommerce_product_data_panels', array($this,'add_3d_configurator_product_data_fields') );
		add_action( 'woocommerce_process_product_meta',  array($this,'shipping_costs_process_product_meta_fields_save' ));
		
		
		
		
		add_action( 'admin_footer', array( $this, 'custom_product_type_js' ) );
		
		//Save data in cart item
		add_filter( 'woocommerce_add_cart_item_data', array($this,'add_configuration_data'), 10, 3 );
		//Display data in cart page
		add_filter( 'woocommerce_get_item_data', array($this,'display_cart_item_custom_meta_data'), 10, 2 );

		//validate customization item before add to cart
		add_filter( 'woocommerce_add_to_cart_validation', array($this,'validate_for_customize_items'), 1, 5 );

		//Set Hidden field for custom price
		add_action( 'woocommerce_before_add_to_cart_button', array($this,'custom_hidden_product_price'), 11, 0 );

		//chaneg Price Display

		//Override price with custom configuration
		add_action( 'woocommerce_before_calculate_totals', array($this,'update_custom_price'), 1, 1 );

		add_filter( 'woocommerce_loop_add_to_cart_link',array($this,'replace_loop_add_to_cart_button'), 10, 2 );

		add_action( 'wp_ajax_nopriv_my_upload_image',array($this,'my_upload_image') );
		add_action( 'wp_ajax_my_upload_image',array($this,'my_upload_image') );
		add_action( 'wp_ajax_nopriv_get_module_files',array($this,'get_module_files_callback') );
		add_action( 'wp_ajax_get_module_files',array($this,'get_module_files_callback') );
		add_action( 'wp_ajax_nopriv_get_upload_module_files',array($this,'get_upload_module_files_callback') );
		add_action( 'wp_ajax_get_upload_module_files',array($this,'get_upload_module_files_callback') );
		//Save 3d configuration data on order
		add_action( 'woocommerce_add_order_item_meta',array($this,'add_product_custom_field_to_order_item_meta'), 9, 3);
		add_filter( 'woocommerce_cart_item_thumbnail', [ $this, 'custom_cart_item_thumbnail' ], 10, 3 );
		add_action( 'woocommerce_after_single_product', [ $this, 'display_cart_items_on_product_page'], 20 );

		
		add_action( 'wp_ajax_nopriv_get_materials_threejs',array($this,'get_materials_threejs') );
		add_action( 'wp_ajax_get_materials_threejs',array($this,'get_materials_threejs') );
		add_filter( 'body_class', array($this,'add_product_type_body_class'));

		add_action('admin_init', array($this,'my_plugin_register_settings'));
	

		//add_action('admin_menu', array($this, 'configurator_sidebar_menu'));
		add_action('admin_menu', array($this,'my_plugin_add_settings_page'));
		require_once plugin_dir_path(__FILE__) . 'CriteriaOptionsCPT.php';
    }
	function force_wc_template_for_block_themes( $template ) {
		if ( is_plugin_active( 'woocommerce/woocommerce.php' )) {
			if ( wp_is_block_theme() ) {
				// For Shop page (Archive Products)
				if ( is_shop() ) {
					$theme_template = WP_PLUGIN_DIR . '/woocommerce/templates/archive-product.php';
					$plugin_template = WP_PLUGIN_DIR . '/woocommerce/templates/archive-product.php';
	
					// If theme template exists, return it. Otherwise, use the correct plugin template.
					return file_exists( $theme_template ) ? $theme_template : $plugin_template;
				}
	
				// For Single Product page
				if ( is_product() ) {
					$theme_template = WP_PLUGIN_DIR . '/woocommerce/templates/single-product.php';
					$plugin_template = WP_PLUGIN_DIR . '/woocommerce/templates/single-product.php';
	
					// If theme template exists, return it. Otherwise, use the correct plugin template.
					return file_exists( $theme_template ) ? $theme_template : $plugin_template;
				}
			}
		}
		return $template;
	}
	

	
	function remove_wp_block_class_on_woocommerce_pages( $classes ) {
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			$key = array_search('wp-block-post', $classes);
			if ( false !== $key ) {
				unset($classes[$key]);
			}
		}
		return $classes;
	}	
	// function custom_remove_cart_hooks() {
	// 	if (is_cart()) {
	// 		remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10);
	// 	}
	// }


	public function my_plugin_add_settings_page() {
		add_options_page(
			'3D Configurator Licence',
			'Configurator (3D)',
			'manage_options',
			'configurator-settings',
			array($this,'my_plugin_render_settings_page')
		);
	}

	public function my_plugin_render_settings_page() {
		?>
    <div class="wrap">
        <h1>Configuration Setting</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#tab1" class="nav-tab nav-tab-active">Licence</a>
			<?php 
				$license_key = get_option('my_plugin_license_key'); 
				if($license_key !== '' && $this->my_plugin_validate_license($license_key)){ ?>
            	<a href="#tab2" class="nav-tab">Configurator setting</a>
			<?php } ?>
        </h2>
        <div id="tab1-content" class="tab-content" style="display: block;">
            <form method="post" action="options.php">
			<?php
				settings_fields('license_settings');
				do_settings_sections('license_settings');
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">License Key</th>
						<td><input type="text" name="my_plugin_license_key" value="<?php echo esc_attr(get_option('my_plugin_license_key')); ?>" /> 
						<?php 
							
							if($license_key == ''){
								echo '<br /><span style="color:grey"><strong>Please enter a Licence Key.</strong></span>';
							}else{
								if ($this->my_plugin_validate_license($license_key)) {
									echo '<br /><span style="color:green"><strong>Valid Licence Key!</strong></span>';
								}elseif($this->my_plugin_validate_license($license_key) == 0){
									echo '<br /><span style="color:red"><strong>Invalid Licence Key!</strong></span>';
								}
							}
						?>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
            </form>
        </div>
		
        <div id="tab2-content" class="tab-content" style="display: none;">
            
			<form method="post" action="options.php">
			<?php
				settings_fields('configurator_settins');
				do_settings_sections('configurator_settins');
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Project Name</th>
						<td><input type="text" name="project_name" value="<?php echo esc_attr(get_option('project_name')); ?>" /> 
						
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
            </form>
        </div>
    </div>

    <script>
         jQuery(document).ready(function($) {
            $('.nav-tab').click(function(e) {
                e.preventDefault();

                // Remove active class from all tabs and hide all tab contents
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').hide();

                // Add active class to the clicked tab and show its content
                $(this).addClass('nav-tab-active');
                $($(this).attr('href') + '-content').show();
            });
        });
    </script>
    <style>
        .tab-content {
            margin-top: 20px;
        }
    </style>
    <?php
	}

	protected function my_plugin_validate_license($license_key) {
		error_log( 'my_plugin_validate_license called functions.' );
		 	$activate = 1;
		
		return $activate;
	}
	
	public function my_plugin_register_settings() {
		register_setting('license_settings', 'my_plugin_license_key');
		register_setting('configurator_settins', 'project_name');
	}	
	
	public function register_custom_product_type() {
        // Ensure WC_Product_Configurator is included when required
			if (!class_exists('WC_Product_Configurator_Threejs')) {
				require_once plugin_dir_path(__FILE__) . 'class-wc-product-configurator-threejs.php';
			}
    }
	
	// Add to product type selector
	public function add_custom_product_type($types){
		$license_key = get_option('my_plugin_license_key');
		if ($this->my_plugin_validate_license($license_key)) {
			$types['threedium_module_threejs'] = __( 'Configurator', 'text-domain' );
		}else{
			$types['configurator'] = __('Configuration (Pro*)');	
		}
		
		return $types;
	}
	function add_configurator_product_class($class_name, $product_type) {
		if ($product_type == 'threedium_module_threejs') {
			$class_name = 'WC_Product_Configurator_Threejs';
		}
		return $class_name;
	}
	
	public function add_product_type_body_class( $classes ) {
		if ( is_product() ) {
			global $product, $post;
			$product = wc_get_product($post->ID);
			if($product->get_type() == 'threedium_module_threejs'){
					$product_id = $this->getProductID();
					$custom_3DID = $this->getProductMeta('_3d_ID_baby');
					if($custom_3DID != '' ){
						$classes[] = 'product-type-configurator';
					}								
				$classes[] = 'product-type-configurator';
			}
		}
		return $classes;
	}

	public function custom_product_type_js() {
        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function(){
                // Show/hide custom tab based on product type
                jQuery('.product_data_tabs .configurator-3d_tab').hide();
                jQuery('.product_data_tabs .mesh-collection_tab').hide();
				jQuery('.product_data_tabs .threedium_module-3d_tab').hide();
				jQuery('.product_data_tabs .baby-loan-mesh-collection_tab').hide();
                // var custom_product_type = 'configurator';
				var custom_baby_loan_type = 'threedium_module_threejs';
                jQuery('#product-type').change(function(){
                   	if(jQuery(this).val() == custom_baby_loan_type ){
						jQuery('.product_data_tabs .configurator-3d_tab').hide();
                        jQuery('.product_data_tabs .mesh-collection_tab').hide();
						jQuery('.product_data_tabs .threedium_module-3d_tab').show();
                        jQuery('.product_data_tabs .baby-loan-mesh-collection_tab').show();
                        jQuery('.panel').hide();
                        jQuery('#custom_product_data').show();

					} else{
                        jQuery('.product_data_tabs .configurator-3d_tab').hide();
                        jQuery('.product_data_tabs .mesh-collection_tab').hide();
						jQuery('.product_data_tabs .baby-loan-mesh-collection_tab').hide();
						jQuery('.product_data_tabs .threedium_module-3d_tab').hide();
                        jQuery('#custom_product_data').hide();
                    }
                }).change(); // Trigger change to set initial state

                // Show/hide custom tab for existing products
               	if(jQuery('#product-type').val() == custom_baby_loan_type ){
					jQuery('.product_data_tabs .configurator-3d_tab').hide();
					jQuery('.product_data_tabs .mesh-collection_tab').hide();
					jQuery('.product_data_tabs .threedium_module-3d_tab').show();
					jQuery('.product_data_tabs .baby-loan-mesh-collection_tab').show();	
                    jQuery('.panel').hide();
                    jQuery('#custom_product_data').show();
				}else{
					jQuery('.product_data_tabs .threedium_module-3d_tab').hide();
					jQuery('.product_data_tabs .baby-loan-mesh-collection_tab').hide();	
                    jQuery('.product_data_tabs .configurator-3d_tab').hide();
                    jQuery('.product_data_tabs .mesh-collection_tab').hide();
                    jQuery('#custom_product_data').hide();
                } 
            });
        </script>
        <?php
    }

	
	public function move_product_name_next_to_image_single_product() {
		global $product, $post;	
		$product = wc_get_product($post->ID);
		if($product->get_type() == 'configurator'){
			if (is_product()) {
				global $product;
				?>
				<div class="product-title">
					<h1 class="product_title entry-title"><?php echo $product->get_title(); ?></h1>
				</div>
				<?php
			}
		}
	}
	public function get_materials_threejs(){
		$material_json = $_POST['matrial'];
		$postID = $_POST['post_id'];
		if (get_post_meta($postID, '_threejs_mesh_collection', true)) {
			delete_post_meta($postID, '_threejs_mesh_collection');
		}
		$mesh_collection = array();
		foreach($material_json as $key => $value){
				$mesh_collection[$value]['name'] = $value ;
				$mesh_collection[$value]['image_id'] = '';
				$mesh_collection[$value]['price'] = 0; 
			}
		
		$mesh_collection = json_encode($mesh_collection);
		
		update_post_meta( $postID, '_threejs_mesh_collection', $mesh_collection );
		wp_send_json_success(array(
			'message' => 'Default mesh collection created and added to form.',
			'mesh_collection' => $mesh_collection
		));
		wp_die();
	}
	function configurator_sidebar_menu() {
		add_options_page( 'Configurator Plugin Configurations', 'Configurator Plugin Page', 'manage_options', 'configurator-plugin-pages', array($this,'configurator_plugin_settings_page') );
	}

	// Configurations Setting
	public function configurator_plugin_settings_page() {
		?>
		<form action="options.php" method="post">
			<?php 
			settings_fields( '3d_configurator_setting' );
			do_settings_sections( 'configurator_plugin' ); ?>
			<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
		</form>
		<?php
	}
	
	public function enqueue_admin_scripts(){
			// echo "fdssdf";exit;
		wp_enqueue_script('jquery');
		wp_enqueue_media();
		wp_enqueue_style('sweetalert-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css');
		wp_enqueue_script('sweetalert-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js', ['jquery'], '4.1.0', true);
        

	    wp_enqueue_script('3d-configurator', plugin_dir_url(__FILE__) . 'admin/js/admin-script.js', array('jquery'), PLUGIN_VERSION);
	    wp_enqueue_style('3d-configurator-css', plugin_dir_url(__FILE__) . 'admin/css/admin-style.css');
		wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
		wp_enqueue_style('3d-configurator-css-compile', plugin_dir_url(__FILE__) . 'admin/css/style.css',array(), PLUGIN_VERSION);
		wp_enqueue_script('threedium-deviceCheck', 'https://cdn.threedium.co.uk/deviceCheck/v1.0/script.js', array('jquery'));
		wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js', [], null, true);
		wp_enqueue_script('three-js', 'https://cdn.jsdelivr.net/npm/three@0.134.0/build/three.min.js', array(), null, true);
		wp_enqueue_script('orbit-controls', 'https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/controls/OrbitControls.js', array('three-js'), null, true);
		wp_enqueue_script('gltf-loader', 'https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/loaders/GLTFLoader.js', array('three-js'), null, true);

		// wp_enqueue_script('OrbitControls-js', 'https://cdn.skypack.dev/three@0.134.0/examples/jsm/controls/OrbitControls.js', array(), null, true);
		// wp_enqueue_script('GLTFLoader-js', 'https://cdn.skypack.dev/three@0.134.0/examples/jsm/loaders/GLTFLoader.js', array(), null, true);
		wp_enqueue_script('resumable-js', 'https://cdnjs.cloudflare.com/ajax/libs/resumable.js/1.0.3/resumable.js', [], null, true);
		$unlimited_js_version = "2.11.3";
		$screen = get_current_screen();
		$custom_data = array(
			'adminURL' => admin_url( 'admin-ajax.php' ),
			'siteUrl' => site_url(),
		);
		$proName = get_option( '3d_configurator_setting' );
		
		$check = $this->check_3Ddata_admin();
		if(!empty($check['available']) && $check['available']){
			global $post;
			$custom_data['custom_3DName'] = $check['custom_3DName'];
			$custom_data['custom_3DID'] = $check['custom_3DID'];
			$custom_data['pro_name'] = $check['pro_name'];
			$custom_data['configured_data'] = get_post_meta( $post->ID, 'product_configurator_data', true);
			$custom_data['post_id'] = get_the_ID();
		}else{
			$custom_data['custom_3DName'] = "";
			$custom_data['custom_3DID'] = "";
			$custom_data['pro_name'] = (isset($proName['project_name']))? $proName['project_name'] : '' ;
			$custom_data['configured_data'] = "";
			$custom_data['post_id'] = get_the_ID();
		}
		wp_localize_script('3d-configurator', 'adminScripData', $custom_data);
		
		$license_key = get_option('my_plugin_license_key');
		if (!$this->my_plugin_validate_license($license_key)) {
			wp_add_inline_script('jquery', "
				jQuery(document).ready(function($) {
					// Disable the specific product type
					$('#product-type option[value=\"configurator\"]').prop('disabled', true);
				});
			");
		}
	}

	public function add_plugin_setting_page(){
		
	}
	
	public function add_3d_configurator_product_data_tab( $product_data_tabs ) {
		global $post; 
		$product_id = $post->ID;
		$baby_loan_esh_collection = get_post_meta( $product_id, '_threejs_mesh_collection', true); 
		$product = wc_get_product($post->ID);
		if($baby_loan_esh_collection == ''){
			$hide_tab_baby = array('when_data_not');
		}else{
			$hide_tab_baby = array('');
		}
		$license_key = get_option('my_plugin_license_key');
		if ($this->my_plugin_validate_license($license_key)) {
			$product_data_tabs['baby-loan-mesh-collection'] = array(
				'label' => __( 'Mesh Collection', 'woocommerce' ), // translatable
				'target' => 'threejs_mesh_collection', 
				'class' => array_merge(array('show_if_configurator'), $hide_tab_baby),		
			);
			$product_data_tabs['threedium_module-3d'] = array(
				'label' => __( 'Threedium Module', 'woocommerce' ), // translatable
				'target' => 'Threedium_product_data', // translatable			
				'class' => array('show_if_configurator'),
			);
			if ($product && $product->get_type() === 'threedium_module_threejs') {			
				$product_data_tabs['threedium_module-3d']['class'][] = 'active';
			}
		}

		return $product_data_tabs;
	}
	public function shipping_costs_process_product_meta_fields_save( $post_id ){
		// echo "<pre>"; print_r($_POST['original_publish']); exit;
		$configuratorData = array();
		if (isset($_POST['_product_type'])) {
			update_post_meta($post_id, '_product_type', sanitize_text_field($_POST['_product_type']));
		}
		if (isset($_POST['product-type'])) {
			update_post_meta($post_id, 'product-type', sanitize_text_field($_POST['product-type']));
		}
		if ($_POST['product-type'] == "threedium_module_threejs"){
			if(isset($_POST['baby_mesh']) && !empty($_POST['baby_mesh'])){
				$mesh_collection = json_encode($_POST['baby_mesh']);
				update_post_meta( $post_id, '_threejs_mesh_collection', $mesh_collection );
			}
			if ( isset($_POST['_regular_price_baby']) ) {
				update_post_meta($post_id, '_regular_price', esc_attr($_POST['_regular_price_baby']));
				update_post_meta($post_id, '_price', esc_attr($_POST['_regular_price_baby']));
			}
			
				if(isset($_POST['baby_parts']) && !empty($_POST['baby_parts'])){
					$partArray = array();
					$name = 0;
					foreach($_POST['baby_parts'] as $part => $partArrayVal){
						$check = $_POST['baby_option_name'][$name];
						if(!empty($check) && $check != ''){
							$partArray[$check] = array();
							
							foreach($partArrayVal['label'] as $key => $value){
								$partArray[$check][$key]['lable'] =  $value;
								$partArray[$check][$key]['price'] =  $partArrayVal['price'][$key];
								$partArray[$check][$key]['icon'] =  $partArrayVal['icon'][$key];
								$partArray[$check][$key]['parts'] =  $partArrayVal['part'][$key];
								$partArray[$check][$key]['logic_status'] =  $partArrayVal['logic_status'][$key];
								$partArray[$check][$key]['logic'] =  (!empty($partArrayVal['logic'][$value])) ? $partArrayVal['logic'][$value] : '' ;
								
							}
						$partArray[$check]['image'] = $partArrayVal['image'];
						$partArray[$check]['material'] = $partArrayVal['material'];
						$partArray[$check]['show_hide_parts_change'] = (isset($partArrayVal['show_hide_parts_change']) && $partArrayVal['show_hide_parts_change'] === 'on') ? 1 : 0;
						}
						$name++;
					}
					$configuratorData['baby_parts'] = $partArray; 
					$configuratorData['baby_parts_display_name'] = $_POST['baby_option_name']; 
				}

				if(isset($_POST['text_parts']) && !empty($_POST['text_parts'])){
					$textPartArray = array();
					$name = 0;
					foreach($_POST['text_parts'] as $part => $partArrayVal){
						$check = $_POST['text_option_name'][$name];
						if(!empty($check) && $check != ''){
							$textPartArray[$check] = array();
							$textPartArray[$check]['option_icon'] = $partArrayVal['option_icon'];
							$textPartArray[$check]['material'] = $partArrayVal['material'];
							$textPartArray[$check]['text_color'] = $partArrayVal['text_color'];
						}
						$name++;
					}
					$configuratorData['text_parts'] = $textPartArray; 
					$configuratorData['text_parts_display_name'] = $_POST['text_option_name']; 
				}	
				if(isset($_POST['color_parts']) && !empty($_POST['color_parts'])){
					$colorPartArray = array();
					$name = 0;
					foreach($_POST['color_parts'] as $part => $partArrayVal){
						$check = $_POST['color_option_name'][$name];
						if(!empty($check) && $check != ''){
							$colorPartArray[$check] = array();
							$colorPartArray[$check]['option_icon'] = $partArrayVal['option_icon'];
							$colorPartArray[$check]['pattern'] = $partArrayVal['pattern'];
							$colorPartArray[$check]['part'] = $partArrayVal['part'];
							$colorPartArray[$check]['pattern_part'] = $partArrayVal['pattern_part'];
							$colorPartArray[$check]['gold_part'] = $partArrayVal['gold_part'];
							$colorPartArray[$check]['match_with_card'] = isset($partArrayVal['match_with_card'][0]) ? $partArrayVal['match_with_card'][0] : 0;
							$colorPartArray[$check]['embossing_debossing_effect'] = (isset($partArrayVal['embossing_debossing_effect']) && $partArrayVal['embossing_debossing_effect'] === 'on') ? 1 : 0;
							$colorPartArray[$check]['match_color_part_name'] =  !empty($partArrayVal['match_color_part_name'])
							? $partArrayVal['match_color_part_name']
							: "";
							$colorPartArray[$check]['match_with_label'] =  !empty($partArrayVal['match_with_label'])
							? $partArrayVal['match_with_label']
							: "";
						}
						$name++;
					}
					$configuratorData['color_parts'] = $colorPartArray; 
					$configuratorData['color_parts_display_name'] = $_POST['color_option_name']; 
				}
				$configuratorData['paper_types'] = $_POST['paper_types'] ?? [];
				$configuratorData['grampage'] = $_POST['grampage'] ?? [];
				$configuratorData['quantity'] = $_POST['quantity'] ?? [];
				$configuratorData['finishing'] = $_POST['finishing'] ?? [];
				if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
					if( isset( $_POST['threedium_module_data'] )) {
						update_post_meta( $post_id, 'threedium_module_data', esc_attr( $_POST['threedium_module_data'] ) );
					}
				} else {
					if( isset( $_POST['threedium_module_data'] )) {
						update_post_meta( $post_id, 'threedium_module_data', esc_attr( $_POST['threedium_module_data'] ) );
					}
				}
				update_post_meta( $post_id, 'threedium_module_data', esc_attr( $_POST['threedium_module_data'] ) );
				if( !empty( $configuratorData ) ) {
					update_post_meta( $post_id, 'threejs_product_configurator_data', json_encode( $configuratorData ) );
				}else{
					update_post_meta( $post_id, 'threejs_product_configurator_data', "" );
				}
				if( isset( $_POST['_3d_ID_baby'] )) {
					update_post_meta( $post_id, '_3d_ID_baby', esc_attr( $_POST['_3d_ID_baby'] ) );
				}
				if( isset( $_POST['_regular_price_baby'] )) {
					update_post_meta( $post_id, '_regular_price_baby', esc_attr( $_POST['_regular_price_baby'] ) );
				}
				$save_draf_check = isset($_POST['save']) ? $_POST['save'] : 'Publish';
				if (
					isset($_POST['original_publish']) &&
					$_POST['original_publish'] === 'Publish' &&
					$save_draf_check === 'Publish'
				) {
						wp_update_post( [
							'ID'          => $post_id,
							'post_status' => 'publish',
						] );
				}	
			}	
	}
	public function add_3d_configurator_product_data_fields() {
		global $post;
		$license_key = get_option('my_plugin_license_key');
		if($this->my_plugin_validate_license($license_key)){
			require_once(plugin_dir_path( file: __FILE__ ). 'admin/templates/perfix_configurator_data_new.php');
		}
	}
	
	public function enqueue_custom_script() {
		// First, hook into the main content area.
		add_action('woocommerce_single_product_summary', array($this, 'check_product_type_and_enqueue'));
		
		
	
		// Enqueue cart-specific scripts
		if (is_cart()) {
			wp_enqueue_script('threedium-deviceCheck', 'https://cdn.threedium.co.uk/deviceCheck/v1.0/script.js', array('jquery'));
			wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js', [], null, true);
			wp_enqueue_script('orbit-controls', 'https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/controls/OrbitControls.js', array('three-js'), null, true);
			wp_enqueue_script('gltf-loader', 'https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/loaders/GLTFLoader.js', array('three-js'), null, true);
		}
	}
	function check_product_type_and_enqueue() {
		global $product, $post;	
		$product = wc_get_product($post->ID);
		$unlimited_js_version = "2.11.3";

		if(!is_cart()){
			// echo"asdasd";exit;
		if($product->get_type() == 'threedium_module_threejs'){
			$check = $this->check_3Ddata();
			wp_enqueue_script('custom-js', PLUGIN_PATH . 'public/js/custom.js', array('jquery'));
			
			if(!empty($check['available']) && $check['available'] || is_cart() ){
				global $post;
				if (is_product()) {
					global $product, $post;	
					$product = wc_get_product($post->ID);
					$js_version = get_post_meta($product->get_id(), '_3d_project_js_version', true);
					if($js_version){
						$unlimited_js_version = $js_version;
					}else{
						$unlimited_js_version = $unlimited_js_version;
					}
				}
				if(!is_cart()){
					// echo "asdasdsss"; 	exit;
					wp_enqueue_script('threedium-deviceCheck', 'https://cdn.threedium.co.uk/deviceCheck/v1.0/script.js', array('jquery'));
					wp_enqueue_script('3d-configurator', PLUGIN_PATH . 'public/js/3d-configurator.js', array('jquery'),PLUGIN_VERSION);
					wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'));
					wp_enqueue_style('3d-configurator', PLUGIN_PATH . 'public/css/3d-configurator.css', array());
					wp_enqueue_style('select2-css','https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array());
					wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js', [], null, true);
					wp_enqueue_script('three-js', 'https://cdn.jsdelivr.net/npm/three@0.134.0/build/three.min.js', array(), null, true);
					wp_enqueue_script('orbit-controls', 'https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/controls/OrbitControls.js', array('three-js'), null, true);
					wp_enqueue_script('gltf-loader', 'https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/loaders/GLTFLoader.js', array('three-js'), null, true);
					wp_enqueue_style('font-css','https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap', array());
				}
				$threedium_data = get_post_meta($post->ID, 'threedium_module_data', true);
				// Localize the script with a variable
				$custom_data = array(
					'custom_3DName' => '',
					'custom_3DID' => '',
					'pro_name' => '',
					'threedium_module_data' => $threedium_data,
					'poroduct_type'=> $product->get_type(),
					'adminURL' => admin_url( 'admin-ajax.php' ),
					'siteUrl' => site_url(),
					'configured_data' => get_post_meta( $post->ID, 'threejs_product_configurator_data', true),
					'priceSymbol' => get_woocommerce_currency_symbol(),
					
				);
				wp_localize_script('3d-configurator', 'productData', $custom_data);
			}
		}
		}
		
    }
	
	public function custom_gallery_image_html($html) {
		global $product, $post;	
		$product = wc_get_product($post->ID);
		$check = $this->check_3Ddata();
		if($product->get_type() == 'threedium_module_threejs'){
			if($check['available']){
				global $post;
				if(get_post_meta( $post->ID, 'threejs_product_configurator_data', true)){
					$html = $this->get3DBaby_html();
				}
			}else{
				$html = $html;
			}

		}
		return $html;
	}

	private function check_3Ddata_admin(){
		if ( function_exists('get_current_screen')) {  
			$screen = get_current_screen();
			if($screen->post_type == 'product' && isset($_GET['action']) && $_GET['action'] === 'edit'){
				global $post;
				//echo "Product Type:---".$product->get_type();
       			$id = $post->ID;
				$product = wc_get_product($id);
				$product_type = $product ? $product->get_type() : '';

				if($product_type == "threedium_module_threejs"){
					$model_data['available'] = false; 
					return $model_data;
				} else {
					$model_data['available'] = false; 
					return $model_data;
				}
			}else{
				$model_data['available'] = false; 
				return $model_data;
			}
		}
	}

	private function check_3Ddata(){
		if (is_product()) {
			global $post;
			$id = $post->ID;
			$product = wc_get_product($id);
			$product_type = $product ? $product->get_type() : '';
			if($product_type == "threedium_module_threejs"){
				$model_data['available'] = true; 
				return $model_data;
			} else  {
				$model_data['available'] = false; 
				return $model_data;
			}
		}
	}

	private function getProductID(){
		$product_id = get_the_ID();
		return $product_id;
	}
	
	private function getProductMeta($meta = ''){
		$productMeta = get_post_meta($this->getProductID(), $meta, true);
		return $productMeta;
	}
	private function get3DBaby_html(){
		// global $product, $post;
		// echo "<pre>"; print_r(plugin_dir_path( __FILE__ )	); exit;
		$html = '';
        $html .= '<canvas id="renderCanvas" style="width: 100% !important; height: 78vh !important;"></canvas>';
		$html .= '<div class="loadingContent" id="loadingContent"><span id="loading-message">Loading 3D Model</span>';
			$html .= '<div class="loading">';
				$html .= '<div id="loadingBar" class="bar"></div>';
			$html .= '</div>';
        $html .= '</div>';
        return $html;
	}

	/* Show 3d configurators option on product details page */	
	public function show_parts_and_materials(){
// 		define('WP_DEBUG', true);
// define('WP_DEBUG_LOG', true);
// define('WP_DEBUG_DISPLAY', true);
		global $product, $post;	
		$product = wc_get_product($post->ID);
		
		if($product->get_type() == 'threedium_module_threejs'){
			if (is_product()) {			
				$proName =  $this->getProductMeta('threedium_module_data');
				if($proName != ""){					
					$product_id = $this->getProductID();
					$threedium_module_data = $this->getProductMeta('threedium_module_data');
					$custom_3DID = $this->getProductMeta('_3d_ID_baby');	
					
				
					if($threedium_module_data != ''){
						require_once(plugin_dir_path( __FILE__ ). 'public/templates/threejs_product_data_custom.php');
						?>						
						<script>
						jQuery(document).ready(function(){
							jQuery("#toggle").click(function() {
								console.log('3d Toggle Click');
								jQuery(this).toggleClass("on");
								jQuery('.summary.entry-summary').toggleClass("active");
								jQuery("#menu").slideToggle();
							});
						});
						</script>
						<?php
						if ( $product->get_type() == 'threedium_module_threejs' ) {
							$product_configurator_data = get_post_meta( $product->get_id(), 'threejs_product_configurator_data', true);

							if($product_configurator_data){
								echo '<div><span class="amount custom">'.get_woocommerce_currency_symbol().''.number_format((float)$product->get_price(), 2, '.', '').'</span></div>';
							}
						}
					}
				}
			}
			
		}
	}
	public function configurator_summery_part(){
		global $product, $post;	
		$product = wc_get_product($post->ID);
		if($product->get_type() == 'threedium_module_threejs'){

			if ( ! $product->is_purchasable() ) { return; }
			echo wc_get_stock_html( $product ); // WPCS: XSS ok.
			if ( $product->is_in_stock() ) : 
				do_action( 'woocommerce_before_add_to_cart_form' ); 
				?>
				<form class="cart 123456" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
					<?php 
						do_action( 'woocommerce_before_add_to_cart_button' );
						do_action( 'woocommerce_before_add_to_cart_quantity' );
						woocommerce_quantity_input( 
							array(
								'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
								'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
								'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
							)
						);
						do_action( 'woocommerce_after_add_to_cart_quantity' );
					?>
					<div class="button-section">
						<button type="button" class="button-section-button button">CANCEL</button>
						<button type="button" class="button-section-button button-section-done">DONE</button>
					</div>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
					<button type="button" name="Apply" style="margin-top: 10px; display: none;" class="apply_data button alt">Apply</button>
					<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
				</form>
				<script>
					jQuery(document).ready(function () {
						if (window.innerWidth <= 1024 && jQuery('.d3-data-tabli.active:visible').length === 0) {
							jQuery(".button-section-done")
								.removeClass("button-section-done")
								.addClass("button-section-finish")
								.text("Finish Customisation");
							jQuery(".button-section-button").not(".button-section-finish").hide();
						}
					});
				</script>
				<?php 
				do_action( 'woocommerce_after_add_to_cart_form' );
			endif;
		} 
	}
	
	/* Add Selected 3d configurator option in cart */
	public function add_configuration_data( $cart_item_data, $product_id, $variation_id ) {
		global $product;
		// echo "<pre>"; print_r($_POST['main_data_for_custom']);
		$_product = wc_get_product($product_id);
			if (!$_product->is_type('threedium_module_threejs') ) {			
				return $cart_item_data;
			}
		$dataArray = array();
		if(isset($_POST['is_config_data'])){
			$dataArray['is_config_data'] = 1;
			foreach($_POST['display'] as $key => $data){
				$parts_raw = isset($data['part_all_value']) ? $data['part_all_value'] : '[]';
				$parts_cleaned = is_string($parts_raw) ? stripslashes($parts_raw) : '[]';
				$change_show_hide_fun = isset($data['change_show_hide_fun']) ? $data['change_show_hide_fun'] : '';
				$cart_item_data['configured_data'][] = array("name" => $data['name'], "type" => $data['type'], "price" => $data['price'], "materials" => $data['materials'],"material_price" => $data['material_price'], "parts" => json_decode($parts_cleaned, true) ?? [],"change_show_hide_fun" => $change_show_hide_fun ?? '');
			}
			if(isset($_POST['pro_update_price'])){
				$cart_item_data['custom_data']['pro_update_price'] = $_REQUEST['pro_update_price'];
			}
			if(isset($_POST['pro_image_cus'])){
				// $cart_item_data['configured_data'][pro_image_cus] =  $_POST['pro_image_cus'];
				$cart_item_data['custom_data']['custom_image_url'] = $_POST['pro_image_cus'];
			}
			if(isset($_POST['prod_data_3d'])){
				$cart_item_data['custom_data']['prod_data_3d'] = $_POST['prod_data_3d'];
			}
		}else{
		}
		if (isset($_POST['main_data_for_custom'])) {
			$mainDataForCustom = json_decode(stripslashes($_POST['main_data_for_custom']), true);
		
			if (json_last_error() === JSON_ERROR_NONE) {
				$cart_item_data['custom_data']['main_data_for_custom'] = [];
		
				foreach ($mainDataForCustom as $option) {
					$optionName = $option['option_name'] ?? 'unknown_option';
					$data = [];
		
					if (!empty($option['text_data']['overlays'])) {
						$data['text_data'] = $option['text_data'];
					}
		
					if (!empty($option['color']['color_section']) || !empty($option['color']['pattern_section'])) {
						$data['color'] = $option['color'];
					}
		
					if ($optionName == 'text_effects') {
						if (!empty($option['text_effects']['text_effects'])) {
							$data['text_effects'] = $option['text_effects'];
						} else {
							$data['text_effects'] = "No text effect selected";
						}
					}
					if ($optionName == 'criteria_options') {
						if (!empty($option['criteria_options']['criteria_options'])) {
							$data['criteria_options'] = $option['criteria_options'];
						} else {
							$data['criteria_options'] = "No get any options";
						}
					}
					if (!empty($data)) {
						$cart_item_data['custom_data']['main_data_for_custom'][$optionName] = $data;
					}
				}
			} else {
				error_log("JSON Decode Error: " . json_last_error_msg());
			}
		}
		// echo "<pre>"; print_r($cart_item_data); exit;
		return $cart_item_data;
	}

	/* Show Selected 3d configurator data on cart page */
	public function display_cart_item_custom_meta_data( $cart_data, $cart_item ) {
		if ( ! isset( $cart_item['configured_data'] ) && ! isset( $cart_item['custom_data']['main_data_for_custom'] ) ) {
			return $cart_data;
		}
	
		$item_data = [];// initialize it once
	
		//Process configured_data
		if ( ! empty( $cart_item['configured_data'] ) ) {
			foreach ( $cart_item['configured_data'] as $key => $value ) {
				if(!empty($value['type']) && !empty($value['materials'])){
					$item_data[] = array(
						'key'   => $value['name'],
						'value' => $value['type'] . ' ($' . $value['price'] . ') - ' . $value['materials'] . ' ($' . $value['material_price'] . ')',
					);
				}
			}
		}
	
		// Process custom_data
		if( ! empty( $cart_item['custom_data']['main_data_for_custom'] ) ){
		
	
			foreach ($cart_item['custom_data']['main_data_for_custom'] as $key => $value) {
			$output = '<div class="cart-item-meta">';
			if (!empty($value['text_data']['overlays'])) {
				$output .= '<p><strong>Text Parts:</strong> ' . ($value['text_data']['parts'][0] ?? 'N/A') . '</p>';
				$output .= '<p><strong>Text Color:</strong> ' . ($value['text_data']['text_color'] ?? 'N/A') . '</p>';
	
				foreach ($value['text_data']['overlays'] as $overlay) {
					$output .= '<p><strong> Overlay Name:</strong> ' . ($overlay['name'] ?? 'N/A') . '</p>';
	
					if (!empty($overlay['entries'])) {
						$output .= '<p><strong> Overlay Entries:</strong></p>';
						$i = 1;
						foreach ($overlay['entries'] as $entry) {
							$output .= '<li> ' . $i . '. ' . ($entry['name'] ?? 'N/A') . ' = ' . (!empty(trim($entry['text'])) ? $entry['text'] : 'N/A') . '</li>';
							$i++;
						}
					}
				}
			}
	
			if (!empty($value['color'])) {
				$output .= '<p><strong> Color Section:</strong></p>';
				if (!empty($value['color']['color_section'])) {
					foreach ($value['color']['color_section'] as $color) {
						$output .= '<span> ' . ($color['section'] ?? 'N/A') . ' = ' . ($color['value'] ?? 'N/A') . '</span><br>';
					}
				}
	
				$output .= '<p><strong> Pattern Section:</strong></p>';
				if (!empty($value['color']['pattern_section'])) {
					foreach ($value['color']['pattern_section'] as $pattern) {
						$output .= '<span> ' . ($pattern['section'] ?? 'N/A') . ' = ' . ($pattern['value'] ?? 'N/A') . '</span><br>';
					}
				}
			}
	
			if ($key === 'text_effects' && !empty($value['text_effects']['text_effects'])) {
				$output .= '<p><strong> Text Effects:</strong></p>';
				foreach ($value['text_effects']['text_effects'] as $effect) {
					$output .= '<span> ' . ($effect['value'] ?? 'None') . '</span><br>';
				}
			}
			if ($key === 'criteria_options' && !empty($value['criteria_options']['criteria_options'])) {
				$output .= '<p><strong> Criteria Options :</strong></p>';
				foreach ($value['criteria_options']['criteria_options'] as $effect) {
					$output .= '<span> ' . ($effect['section'] ?? 'N/A') . ' = ' . ($effect['value'] ?? 'N/A') . '</span><br>';
				}
			}
	
			$output .= '</div>';
		
			$item_data[] = [
				'key'   => ucfirst(str_replace('_', ' ', $key)), 
				'value' => $output,
			];
		}
		}
		
	
		return $item_data;
	}
	
	
	/* check if all option selected */
	public function validate_for_customize_items($passed, $product_id, $quantity, $variation_id = '', $variations = '' ){

		
		$_product = wc_get_product( $product_id );
		if(isset($_POST['is_config_data'])){
				if($_product->get_parent_id() == 0){
					$post_id = $_product->get_id();
				}else{
					$post_id = $_product->get_parent_id();
				}
				$product_configurator_data = (array)json_decode(get_post_meta( $post_id, 'product_configurator_data', true));
				$allParts = array_keys($product_configurator_data);
				array_pop($allParts);

			$already_in_cart = false;
    
			foreach( WC()->cart->get_cart() as $key => $item ){
				if( $item['product_id'] == $product_id ){
					$already_in_cart = true;
					$existing_product_key = $key;
					break;
				}
			}

			if( $already_in_cart  ){
				WC()->cart->remove_cart_item($existing_product_key);
			}
		}

		return $passed;
	}

	public function sv_change_product_html( $price_html, $product ) {
		// Ensure that the product is valid
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return $price_html; // Return original price HTML if the product is invalid
		}
	
		// Check for product types and modify price HTML
		if ( $product->get_type() == 'threedium_module_threejs' ) {
			return ''; // Return empty price HTML for 'threedium_module' type
		}
	
		// Return the original price HTML for other product types
		return $price_html;
	}
	

	public function custom_hidden_product_price() {		
		global $product;
		$prod_data = array(
			"project" => get_post_meta($product->get_id(), '_3d_project_name', true) ? get_post_meta($product->get_id(), '_3d_project_name', true) : get_option( '3d_configurator_setting',true),
			"c3d_name" => get_post_meta($product->get_id(), '_3d_name', true),
			"c3d_id"	  => get_post_meta($product->get_id(), '_3d_ID', true)
		);
		$final_data = json_encode($prod_data);
		echo '<input type="hidden" name="pro_update_price" value="'.$product->get_price().'" class="pro-updated-price">
			<input type="hidden" name="pro_actual_price" value="'.$product->get_price().'" class="pro-actual-price">
			<input type="hidden" id="pro_image_cus" name="pro_image_cus">
			<input type="hidden" id="main_data_for_custom" name="main_data_for_custom">
			<input type="hidden" id="prod_data_3d" name="prod_data_3d" value='.$final_data.'>';
	}

	public function update_custom_price($cart_object ){
		 foreach ( $cart_object->get_cart() as $item_values ) {

			##  Get cart item data
			$item_id = $item_values['data']->id; // Product ID
			$original_price = $item_values['data']->price; // Product original price

			## Get your custom fields values

			if(isset($item_values['custom_data']['pro_update_price'])){

				$price1 = $item_values['custom_data']['pro_update_price'];
	
				// CALCULATION FOR EACH ITEM:
				## Make HERE your own calculation 
				$new_price = $price1 ;
	
				## Set the new item price in cart
				$item_values['data']->set_price($new_price);
			}
		}
	}

	public function replace_loop_add_to_cart_button( $button, $product  ) {
		if( $this->check3dProduct($product) ===  true){
			$button_text = __( "Choose Configuration", "woocommerce" );
			$button = '<a class="button product_type_simple wp-element-button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
		}
		return $button;
	}

	private function check3dProduct($product){		
		if( ($product->is_type('simple') || $product->is_type('configurator')) && get_post_meta( $product->get_id(), 'product_configurator_data', true)){
			return true;
		}
		return false;
	}

	public function my_upload_image(){

		$file['name'] = $_FILES['data']['name'];
		$file['tmp_name'] = $_FILES['data']['tmp_name'];
		$file['type'] = $_FILES['data']['type'];
		$file['size'] = $_FILES['data']['size'];
		$file['error'] = $_FILES['data']['error'];
		$filename = $this->upload_file_post($file);
		if($filename['error'] == 0){
			wp_send_json($filename);
			exit();
		}else{
			$result['status'] = 0;
			$result['msg'] = "msg";
			$result['url'] = "";
			wp_send_json($result);
			exit();
		}
		wp_send_json($result);
		wp_die();

	}
	function get_module_files_callback() {
		$plugin_path = plugin_dir_path(__FILE__); // Get the current plugin directory
		$directory = $plugin_path . 'module_files/'; // Adjust the folder inside your plugin
		$files = [];
	
		if (is_dir($directory)) {
			$fileList = scandir($directory);
			foreach ($fileList as $file) {
				if ($file !== "." && $file !== ".." && pathinfo($file, PATHINFO_EXTENSION) === 'glb') {
					$files[] = $file;
				}
			}
		}
	
		wp_send_json_success($files);
	}
	
	function get_upload_module_files_callback() {
		if (!isset($_FILES['file_upload'])) {
			wp_send_json_error(['message' => 'No file uploaded.']);
			return;
		}
	
		$upload_dir = WP_CONTENT_DIR . '/uploads/woo-threejs-module/';
	
		if (!is_dir($upload_dir)) {
			if (!mkdir($upload_dir, 0755, true)) {
				wp_send_json_error(['message' => 'Failed to create upload directory.']);
				return;
			}
		}
	
		$uploaded_file = $_FILES['file_upload'];
	
		if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error(['message' => 'An error occurred during the file upload.']);
			return;
		}
	
		$original_name = sanitize_file_name($_POST['filename'] ?? 'unknown.glb');
		$filename_only = pathinfo($original_name, PATHINFO_EXTENSION);

		$file_extension = preg_replace('/[\s\(\)]+/', '_', $filename_only);
		
		if ($file_extension !== 'glb') {
			wp_send_json_error(['message' => 'Only .glb files are allowed.']);
			return;
		}
	
		$destination = $upload_dir . $original_name;
	
		if (file_exists($destination)) {
			error_log("⚠ File already exists. Overwriting content...");
			$file_contents = file_get_contents($uploaded_file['tmp_name']);
			if (file_put_contents($destination, $file_contents) !== false) {
				wp_send_json_success([
					'message' => 'File uploaded and overwritten successfully.',
					'file_url' => $destination
				]);
			} else {
				wp_send_json_error(['message' => 'There was an error overwriting the file.']);
			}
		} else {
			if (move_uploaded_file($uploaded_file['tmp_name'], $destination)) {
				wp_send_json_success([
					'message' => 'File uploaded successfully.',
					'file_url' => $destination
				]);
			} else {
				wp_send_json_error(['message' => 'There was an error uploading the file.']);
			}
		}
	}

	public function upload_file_post($file){
		$uploadedFile = $file;
		//Get the uploaded file information
		$name_of_uploaded_file = basename($uploadedFile['name']);
		//get the file extension of the file
		$type_of_uploaded_file = substr($name_of_uploaded_file, strrpos($name_of_uploaded_file, '.') + 1);
		$size_of_uploaded_file = $uploadedFile["size"] / 1024; //size in KBs
		//Settings
		$max_allowed_file_size  = 2000; // size in KB
		$allowed_extensions     = array("jpg", "jpeg", "png", "doc","docx");
		$upload_overrides       = array( 'test_form' => false );

		//Validations
		if($size_of_uploaded_file > $max_allowed_file_size)
		{
			$result['error'] = 1;
			$result['msg'] = "Please upload max 2MB file";
		}

		//------ Validate the file extension 
		$allowed_ext = false;

		for($i = 0; $i <sizeof($allowed_extensions); $i++)
		{
			if(strcasecmp($allowed_extensions[$i], $type_of_uploaded_file) == 0)
			{
				$allowed_ext = true;
			}
		}

		$folderPath = plugin_dir_path( __FILE__ )."/public/images";
		if (!file_exists($folderPath)) {
			mkdir($folderPath, 0777, true);
		}
		//mkdir($folderPath, 0755, true);
		$fileName = ""; 
		$path_parts = pathinfo($uploadedFile["name"]);
		$filename = time().".png";
		$filetype = $uploadedFile['type'];
		$target_path = $folderPath. "/" . $filename;
		if(move_uploaded_file($uploadedFile['tmp_name'], $target_path)) {
			$fileName = $filename;
		}
		chmod("{$target_path}", 0755);

		if($fileName != "") {
			$result['error'] = 0;
			$result['msg'] = "File Uloaded Successfully";
			$result['url'] = $fileName;
		}

		return $result;
	}

	public function add_product_custom_field_to_order_item_meta( $item_id, $item_values, $item_key  ) {
		if(isset($item_values['configured_data'])){
			foreach( $item_values['configured_data'] as $key => $value){
				wc_update_order_item_meta( $item_id, $value['type'], $value['materials'] );
			}	
			wc_update_order_item_meta( $item_id,'_config_data_custom', $item_values['configured_data'] );
			wc_update_order_item_meta( $item_id,'_main_config_data_custom', $item_values['custom_data']['main_data_for_custom'] );

		}

	}
	public function custom_cart_item_thumbnail( $product_thumbnail, $cart_item, $cart_item_key ) {
	
		$selected_parts_array = [];
	
		if (isset($cart_item['configured_data']) && is_array($cart_item['configured_data'])) {
			foreach ($cart_item['configured_data'] as $index => $selected_parts) {
				if (!empty($selected_parts['parts'])) {
					$selected_parts_array[$index] = [
						'parts' => $selected_parts['parts'],
						'materials' => $selected_parts['materials'] ?? ''
					];
				}
				
			}
		}
		$selected_parts_json = json_encode($selected_parts_array);
		// $show_hide_parts_json = json_encode($show_hide_parts_array);
		// echo "<pre>"; print_r($cart_item['configured_data']); exit;
		$selected_parts_flat = [];
		foreach ($selected_parts_array as $entry) {
			if (!empty($entry['parts']) && is_array($entry['parts'])) {
				$selected_parts_flat = array_merge($selected_parts_flat, $entry['parts']);
			}
		}
		$selected_parts_flat = array_unique($selected_parts_flat);

		$product     = $cart_item['data'];
		$product_id  = $product->get_id();
		$threedium_data = get_post_meta( $product_id, 'threedium_module_data', true );
		$product_configurator_data = get_post_meta( $product_id, 'threejs_product_configurator_data', true );
		$parts = json_decode($product_configurator_data); 
		// $parts = json_decode($product_configurator_data);
		$all_parts = [];
		$show_hide_parts_array = []; 
		if (isset($parts->baby_parts) && !empty($parts->baby_parts)) {
			foreach ($parts->baby_parts as $partData) {
				// echo "<pre>"; print_r($partData	); exit;
				if (isset($partData) && !empty($partData)) {
					foreach ($partData as $value) {
						if (isset($value->parts) && is_array($value->parts)) {
							$all_parts = array_merge($all_parts, $value->parts);
						}
						if (isset($partData->show_hide_parts_change) && $partData->show_hide_parts_change != 1) {
							foreach ($value->parts as $part_name) {
								$show_hide_parts_array[] = $part_name;
							}
						}
					}
				}
			}
		}
		$main_data = $cart_item['custom_data']['main_data_for_custom'] ?? [];
		$parts_array = [];
	
		// Recursively find all matching keys
		$findSections = function($array, $target_keys = ['color_section', 'pattern_section']) use (&$findSections) {
			$results = [];
	
			if (!is_array($array)) return $results;
	
			foreach ($array as $key => $value) {
				if (in_array($key, $target_keys) && is_array($value)) {
					$results[] = $value;
				} elseif (is_array($value)) {
					$results = array_merge($results, $findSections($value, $target_keys));
				}
			}
	
			return $results;
		};
	
		// Build parts array
		$build_parts_array = function($sections) use (&$parts_array) {
			if (!is_array($sections) || empty($sections)) return;
	
			$temp = [
				'parts'     => null,
				'color'     => '',
				'matrirel'  => ''
			];
	
			foreach ($sections as $item) {
				if (!isset($item['section'], $item['value'])) continue;
	
				$section = strtolower($item['section']);
				$value = $item['value'];
	
				if (strpos($section, 'parts') !== false && $section != 'match part' && $section != 'match color part') {
					$temp['parts'] = $value;
				}
	
				if (strpos($section, 'color') !== false && strpos($section, 'part') === false) {
					$temp['color'] = $value;
				}
	
				if (strpos($section, 'material') !== false) {
					$temp['matrirel'] = $value;
				}
	
				if ($section === 'match color part' && isset($value)) {
					foreach ($parts_array as $prev) {
						if (isset($prev['parts']) && $prev['parts'] === $value) {
							$temp['color'] = $prev['color'] ?? '';
							break;
						}
					}
				}
			}
	
			if (!empty($temp['parts'])) {
				$parts_array[] = $temp;
			}
		};
	
		// 🔍 Find all `color_section` and `pattern_section` arrays dynamically
		$section_groups = $findSections($main_data);
	
		foreach ($section_groups as $group) {
			$build_parts_array($group);
		}
	
		// // Optionally remove duplicates
		$all_parts = array_unique($all_parts);
		$non_selected_parts_old = array_diff($all_parts, $selected_parts_flat);
		$non_selected_parts = array_diff($non_selected_parts_old, $show_hide_parts_array);
		$non_selected_parts_json = json_encode(array_values($non_selected_parts));
		$custom_config = json_encode($parts_array);
		$product_url = get_permalink($product_id);
		// echo "<pre>"; print_r($show_hide_parts_array); exit;
	
		// Ensure path is not empty
		if ( empty( $threedium_data ) ) {
			return $product_thumbnail;
		}
	
		// Build full model path (relative to site root)
		$model_path  = "/wp-content/uploads/woo-threejs-module/" . $threedium_data;
		$canvas_id   = 'three-canvas-' . esc_attr( $cart_item_key );
	
		ob_start();
		?>
		<div class="custom-cart-thumbnail">
			<canvas id="<?php echo esc_attr( $canvas_id ); ?>" width="300" height="300" style="border:1px solid #ccc;"></canvas>
		</div>
		<div class="edit-product-button">
		<a href="<?php echo esc_url($product_url . (strpos($product_url, '?') !== false ? '&' : '?') . 'edit_config_3d'); ?>" class="button">Edit Product</a>
    	</div>
			<script>
				window.threeModelsQueue = window.threeModelsQueue || [];
				window.threeModelsQueue.push({
					canvasId: '<?php echo esc_js($canvas_id); ?>',
					modelPath: window.location.origin + '<?php echo esc_url($model_path); ?>',
					materials: <?php echo $selected_parts_json; ?>,
					hiddenParts: <?php echo $non_selected_parts_json; ?>,
					customConfig: <?php echo $custom_config; ?>
				});

				if (!window.initThreeJS) {
					window.initThreeJS = true;

					window.addEventListener('DOMContentLoaded', function () {
						const interval = setInterval(() => {
							if (typeof THREE !== 'undefined' && THREE.GLTFLoader && THREE.OrbitControls) {
								clearInterval(interval);

								const initThreeModel = function ({ canvasId, modelPath, materials , hiddenParts , customConfig}) {
									const canvas = document.getElementById(canvasId);
									canvas.addEventListener('click', function (e) {
											e.preventDefault();
											e.stopPropagation();
										});
										canvas.addEventListener('mousedown', function (e) {
											e.preventDefault();
											e.stopPropagation();
										});
										canvas.addEventListener('wheel', function (e) {
											e.preventDefault();
											e.stopPropagation();
										}, { passive: false });
										canvas.addEventListener('touchstart', function (e) {
											e.preventDefault();
											e.stopPropagation();
										}, { passive: false });

										canvas.addEventListener('touchmove', function (e) {
											e.preventDefault();
											e.stopPropagation();
										}, { passive: false });
															
											document.addEventListener("click", function (event) {
												if (!canvas.contains(event.target)) {
													document.body.style.overflow = "auto";
												}
											});

									const originalMaterials = new Map();
									const selectedParts = new Map();
									const namedMaterials = new Map();
									const scene = new THREE.Scene();
									scene.background = new THREE.Color(0xffffff);

									const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
									renderer.physicallyCorrectLights = true;
									renderer.shadowMap.enabled = true;
									renderer.shadowMap.type = THREE.PCFSoftShadowMap;
									function setRendererSize() {
										renderer.setSize(canvas.clientWidth, canvas.clientHeight);
										renderer.setPixelRatio(window.devicePixelRatio);
									}
									setRendererSize();

									

									const camera = new THREE.PerspectiveCamera(55, canvas.clientWidth / canvas.clientHeight, 0.1, 100);
									camera.position.set(0, 2, 10);

									const controls = new THREE.OrbitControls(camera, renderer.domElement);
									controls.enableDamping = true;
									controls.dampingFactor = 0.05;
									controls.screenSpacePanning = false;
									controls.minDistance = 2;
									controls.maxDistance = 30;
								
									scene.children.forEach((child) => {
										if (child.isLight) {
											scene.remove(child);
										}
									});
										// 1️⃣ Ambient Light - Softens the shadows, increases base brightness
										const ambientLight = new THREE.AmbientLight(0xffffff, 2); // Increased intensity
										scene.add(ambientLight);
									
										// 2️⃣ Hemisphere Light - Mimics natural outdoor lighting
										const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 2);
										hemiLight.position.set(0, 20, 0);
										scene.add(hemiLight);
									
										// 3️⃣ Directional Light - Bright overhead light (like the Sun)
										const directionalLight = new THREE.DirectionalLight(0xffffff, 4);
										directionalLight.position.set(5, 10, 5);
										directionalLight.castShadow = true;
										scene.add(directionalLight);
									
										// 4️⃣ Additional Directional Light for more clarity
										const directionalLight2 = new THREE.DirectionalLight(0xffffff, 3);
										directionalLight2.position.set(-5, 10, -5);
										scene.add(directionalLight2);
									
										// 5️⃣ Spotlight for dramatic highlights
										const spotLight = new THREE.SpotLight(0xffffff, 5);
										spotLight.position.set(0, 10, 10);
										spotLight.angle = Math.PI / 4;
										spotLight.penumbra = 0.5;
										spotLight.decay = 2;
										spotLight.distance = 50;
										scene.add(spotLight);

								

									// window.addEventListener('resize', function () {
									// 	const width = canvas.clientWidth;
									// 	const height = canvas.clientHeight;
									// 	renderer.setSize(width, height);
									// 	camera.aspect = width / height;
									// 	camera.updateProjectionMatrix();
									// });

									// scene.add(new THREE.AmbientLight(0xffffff, 2));
									// scene.add(new THREE.HemisphereLight(0xffffff, 0x444444, 2));
									// const dirLight = new THREE.DirectionalLight(0xffffff, 4);
									// dirLight.position.set(5, 10, 5);
									// scene.add(dirLight);

									const loader = new THREE.GLTFLoader();
									loader.load(modelPath, function (gltf) {
										const model = gltf.scene;
										scene.add(model);

										model.traverse((child) => {
											if (child.isMesh) {
												child.castShadow = true;
												child.receiveShadow = true;
												if (child.material) {
													child.material.needsUpdate = true;
													child.material.metalness = 0.3;
													child.material.roughness = 0.2;
												}
												if (hiddenParts.includes(child.name)) {
													child.visible = false;
													console.log(`❌ Hidden mesh: ${child.name}`);
													return;
												}
												

												const matArray = Array.isArray(child.material) ? child.material : [child.material];
												matArray.forEach((mat) => {
													if (mat?.name && !originalMaterials.has(mat.name)) {
														originalMaterials.set(mat.name, mat);
													}
													if (mat?.name && !namedMaterials.has(mat.name)) {
														namedMaterials.set(mat.name, mat.clone());
													}
												});
											}
										});

										if (Array.isArray(materials)) {
											materials.forEach((item) => {
												const partNames = item.parts;
												const matName = item.materials;
												if (!Array.isArray(partNames) || !matName) return;

												const originalMat = originalMaterials.get(matName);
												if (!originalMat) {
													console.warn(`❌ Material '${matName}' not found.`);
													return;
												}

												partNames.forEach((partName) => {
													model.traverse((child) => {
														if (child.isMesh && child.name === partName) {
															// ✅ Clone only if material is already applied somewhere else
															const newMat = originalMat.clone();
															child.material = originalMat;
															console.log(`✔ Applied clone of '${matName}' to '${child.name}'`);
														}
													});
												});
											});
										}
										// if (three_config_input === "value_if_set") {
											if (Array.isArray(customConfig)) {
												customConfig.forEach((item) => {
													const partName = item.parts;
													const matName = item.material;
													const hexColor = item.color;
										
													if (!partName) {
														console.warn("❗ Skipping item due to missing part name:", item);
														return;
													}
										
													model.traverse((child) => {
														if (!child.isMesh || child.name !== partName) return;
										
														let materialToApply = null;
										
														// Step 1: Try to get named material
														if (matName) {
															const namedMat = namedMaterials.get(matName);
															if (namedMat) {
																// Always clone named material to avoid sharing
																materialToApply = namedMat.clone();
																materialToApply._isCloned = true;
																console.log(`✔ Cloned & applied '${matName}' to '${child.name}'`);
															} else {
																console.warn(`❌ Material '${matName}' not found for '${child.name}'`);
															}
														}
										
														// Step 2: If no named material, clone current or use fallback
														if (!materialToApply) {
															if (child.material) {
																materialToApply = child.material.clone();
																materialToApply._isCloned = true;
																console.log(`🧱 Cloned existing material for '${child.name}'`);
															} else {
																// Ultimate fallback: default MeshStandardMaterial
																materialToApply = new THREE.MeshStandardMaterial({ color: 0xffffff });
																console.log(`⚪ Used default material for '${child.name}'`);
															}
														}
										
														// Step 3: Assign final cloned material
														child.material = materialToApply;
										
														// Step 4: Apply hex color
														if (hexColor && materialToApply.color) {
															try {
																const colorChange = new THREE.Color(hexColor);
																colorChange.convertSRGBToLinear();
										
																if (child.material.map) child.material.map = null;
										
																materialToApply.color.copy(colorChange);
																materialToApply.needsUpdate = true;
										
																	console.log(`🎨 Applied color '${hexColor}' to '${child.name}'`);
																} catch (err) {
																	console.error(`💥 Invalid color '${hexColor}' for '${child.name}':`, err);
																}
															}
														});
													});
												}
											// }


										scaleModelToFit(model);
										animate();
									});

									function animate() {
										requestAnimationFrame(animate);
										controls.update();
										renderer.render(scene, camera);
									}
								
									function scaleModelToFit(model) {
										const boundingBox = new THREE.Box3().setFromObject(model);
										const modelSize = boundingBox.getSize(new THREE.Vector3());
										const maxSize = 10;
										const scaleFactor = maxSize / Math.max(modelSize.x, modelSize.y, modelSize.z);
										model.scale.set(scaleFactor, scaleFactor, scaleFactor);
									}
								};

								(window.threeModelsQueue || []).forEach(initThreeModel);
							}
						}, 100);
					});
				}
			</script>



		<?php
		return ob_get_clean();
	}

	function display_cart_items_on_product_page() {
		$cart_items = WC()->cart->get_cart();
		if ( !empty( $cart_items ) ) {
			// echo "<pre>"; print_r($cart_items); exit;
			foreach ($cart_items as $cart_item_key => $cart_item) {
		// echo "<pre>"; print_r($cart_item['configured_data']); exit;

				$selected_parts_array = [];
				// $show_hide_parts_array = [];
			if (isset($cart_item['configured_data']) && is_array($cart_item['configured_data'])) {
				foreach ($cart_item['configured_data'] as $index => $selected_parts) {
					if (!empty($selected_parts['parts'])) {
						$selected_parts_array[$index] = [
							'parts' => $selected_parts['parts'],
							'materials' => $selected_parts['materials'] ?? ''
						];
					}
					// if (isset($selected_parts['change_show_hide_fun']) && $selected_parts['change_show_hide_fun'] == 1) {
					// 	foreach ($selected_parts['parts'] as $part_name) {
					// 		$show_hide_parts_array[] = $part_name;
					// 	}
					// }
				}
			}
			$selected_parts_json = json_encode($selected_parts_array);
			$selected_parts_flat = [];
			foreach ($selected_parts_array as $entry) {
				if (!empty($entry['parts']) && is_array($entry['parts'])) {
					$selected_parts_flat = array_merge($selected_parts_flat, $entry['parts']);
				}
			}
			$selected_parts_flat = array_unique($selected_parts_flat);
	
			$product     = $cart_item['data'];
			$product_id  = $product->get_id();
			$threedium_data = get_post_meta( $product_id, 'threedium_module_data', true );
			$product_configurator_data = get_post_meta( $product_id, 'threejs_product_configurator_data', true );
			$parts = json_decode($product_configurator_data); 
			// $parts = json_decode($product_configurator_data);
			$all_parts = [];
			$show_hide_parts_array = []; 
			if (isset($parts->baby_parts) && !empty($parts->baby_parts)) {
				foreach ($parts->baby_parts as $partData) {
					// echo "<pre>dd"; print_r($partData	); exit;
					if (isset($partData) && !empty($partData)) {
						foreach ($partData as $value) {
							if (isset($value->parts) && is_array($value->parts)) {
								$all_parts = array_merge($all_parts, $value->parts);
							}
							if (isset($partData->show_hide_parts_change) && $partData->show_hide_parts_change != 1) {
								foreach ($value->parts as $part_name) {
									$show_hide_parts_array[] = $part_name;
								}
							}
						}
					}
				}
			}

			$main_data = $cart_item['custom_data']['main_data_for_custom'] ?? [];
			$parts_array = [];
		
			// Recursively find all matching keys
			$findSections = function($array, $target_keys = ['color_section', 'pattern_section']) use (&$findSections) {
				$results = [];
		
				if (!is_array($array)) return $results;
		
				foreach ($array as $key => $value) {
					if (in_array($key, $target_keys) && is_array($value)) {
						$results[] = $value;
					} elseif (is_array($value)) {
						$results = array_merge($results, $findSections($value, $target_keys));
					}
				}
		
				return $results;
			};
		
			// Build parts array
			$build_parts_array = function($sections) use (&$parts_array) {
				if (!is_array($sections) || empty($sections)) return;
		
				$temp = [
					'parts'     => null,
					'color'     => '',
					'matrirel'  => ''
				];
		
				foreach ($sections as $item) {
					if (!isset($item['section'], $item['value'])) continue;
		
					$section = strtolower($item['section']);
					$value = $item['value'];
		
					if (strpos($section, 'parts') !== false && $section != 'match part' && $section != 'match color part') {
						$temp['parts'] = $value;
					}
		
					if (strpos($section, 'color') !== false && strpos($section, 'part') === false) {
						$temp['color'] = $value;
					}
		
					if (strpos($section, 'material') !== false) {
						$temp['matrirel'] = $value;
					}
		
					if ($section === 'match color part' && isset($value)) {
						foreach ($parts_array as $prev) {
							if (isset($prev['parts']) && $prev['parts'] === $value) {
								$temp['color'] = $prev['color'] ?? '';
								break;
							}
						}
					}
				}
		
				if (!empty($temp['parts'])) {
					$parts_array[] = $temp;
				}
			};
		
			// 🔍 Find all `color_section` and `pattern_section` arrays dynamically
			$section_groups = $findSections($main_data);
		
			foreach ($section_groups as $group) {
				$build_parts_array($group);
			}
		
			// // Optionally remove duplicates
			$all_parts = array_unique($all_parts);
			$non_selected_parts = array_diff($all_parts, $selected_parts_flat);
			$non_selected_parts_old = array_diff($all_parts, $selected_parts_flat);
			$non_selected_parts = array_diff($non_selected_parts_old, $show_hide_parts_array);
			$non_selected_parts_json = json_encode(array_values($non_selected_parts));
			$custom_config = json_encode($parts_array);
			// $parts = json_decode($product_configurator_data);

			
			?>
			<input type="hidden" id="selected_parts_json" value='<?php echo esc_js($selected_parts_json); ?>'>
            <input type="hidden" id="non_selected_parts_json" value='<?php echo esc_js($non_selected_parts_json); ?>'>
            <input type="hidden" id="custom_config" value='<?php echo esc_js($custom_config); ?>'>
			
			<?php 
		}
		?>
			<input type="hidden" id="edit_config_all_data" value="<?php echo htmlspecialchars(json_encode($cart_item), ENT_QUOTES, 'UTF-8'); ?>">
		<?php
	}

	}	  
	
}
// Instantiate the plugin class
$configurator_3d = new Configurator_3d();

<?php
// Block direct access to file
defined('ABSPATH') or die('Not Authorized!');

class Poka_Filter_Product {
    
    public $product_cateogry = 'product_cat';

    public function __construct() {
        define( 'PRODUCTS_CATEGORY_TAXONOMY', 'product_cat' );

        // Define path and URL to the ACF plugin.
        define( 'PFP_ACF_PATH', PFP_DIRECTORY_PATH . '/vendor/advanced-custom-fields/' );
        define( 'PFP_ACF_URL', PFP_DIRECTORY_URL . '/vendor/advanced-custom-fields/' );
        include_once( PFP_ACF_PATH . 'acf.php' );
        
        // Customize the url setting to fix incorrect asset URLs & Path.
        add_filter('acf/settings/url', array($this, 'acf_settings_url'));
        add_filter('acf/settings/show_admin', array($this, 'acf_settings_show_admin'));
        
        
        // Plugin Actions
        add_action('init', array($this, 'plugin_init'));
        //add_filter( 'loop_shop_post_in', array($this,'ob_add_categories_filter', 5, 1 ) );
        
        add_action('after_setup_theme', function () {
            // Register Widget
            include_once( PFP_DIRECTORY_PATH . '/include/widget.php' );
        });
        
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
    }
    
    public function acf_settings_url( $url ) {
        return PFP_ACF_URL;
    }
    public function acf_settings_show_admin( $show_admin ) {
        return false;
    }

    public static function plugin_uninstall() {
        
    }

    /**
     * Plugin activation function
     * called when the plugin is activated
     * @method plugin_activate
     */
    public function plugin_activate() {
        
    }

    /**
     * Plugin deactivate function
     * is called during plugin deactivation
     * @method plugin_deactivate
     */
    public function plugin_deactivate() {
        
    }

    /**
     * Plugin init function
     * init the plugin textDomain
     * @method plugin_init
     */
    public function plugin_init() {
        // before all load plugin text domain
        load_plugin_textDomain(PFP_TEXT_DOMAIN, false, dirname(PFP_DIRECTORY_BASENAME) . '/languages');
        // acf register
        if (function_exists('acf_add_local_field_group')):
            acf_add_local_field_group(array(
                'key' => 'group_poka_filter_product',
                'title' => 'Tuỳ chọn Lọc sản phẩm',
                'fields' => array(
                    array(
                        'key' => 'field_is_main_category_product',
                        'label' => 'Main Category Product',
                        'name' => 'is_main_category_product',
                        'type' => 'true_false',
                        'instructions' => 'Check If Category is parent category',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '',
                        'default_value' => 0,
                        'ui' => 0,
                        'ui_on_text' => '',
                        'ui_off_text' => '',
                    ),
                    array(
                        'key' => 'field_in_group_filter',
                        'label' => 'In Group Filter',
                        'name' => 'in_group_filter',
                        'type' => 'text',
                        'instructions' => 'What is box filter, that is this category in. Ex: Categories Core I3, Core I5, Core I7 will belong to "CPU Filter"',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_is_main_category_product',
                                    'operator' => '!=',
                                    'value' => '1',
                                ),
                            ),
                        ),
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => 'CPU Filter',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'taxonomy',
                            'operator' => '==',
                            'value' => 'product_cat',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ));

        endif;
    }

    public function ob_add_categories_filter( $filtered_posts ) {
	global $_chosen_attributes;

        $taxonomy = wc_sanitize_taxonomy_name(PRODUCTS_CATEGORY_TAXONOMY);
        $name = 'filter_' . PRODUCTS_CATEGORY_TAXONOMY;
        $query_type_name = 'query_type_' . PRODUCTS_CATEGORY_TAXONOMY;

        if (!empty($_GET[$name]) && taxonomy_exists($taxonomy)) {

            $_chosen_attributes[$taxonomy]['terms'] = explode(',', $_GET[$name]);

            if (empty($_GET[$query_type_name]) || !in_array(strtolower($_GET[$query_type_name]), array(
                        'and',
                        'or'
                    ))
            ) {
                $_chosen_attributes[$taxonomy]['query_type'] = apply_filters('woocommerce_layered_nav_default_query_type', 'and');
            } else {
                $_chosen_attributes[$taxonomy]['query_type'] = strtolower($_GET[$query_type_name]);
            }
        }

        return $filtered_posts;
    }

    function wp_enqueue_scripts() {
        wp_register_style('poka-filter-proudct-style', PFP_DIRECTORY_URL . '/assets/dist/css/user-style.css');
        //wp_enqueue_script('poka-filter-product-script', PFP_DIRECTORY_URL . '/assets/dist/js/user-script.js', array('jquery'), '1.0', true);
        
    }
    
    
}

new Poka_Filter_Product;

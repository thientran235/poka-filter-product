<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Poka Widget Filter Product
 *
 * @author   Thien Tran
 * @category Widgets
 * @version  1.0
 * @extends  Poka_Widget_Filter_Product
 */
class Poka_Widget_Filter_Product extends WC_Widget {
    
    public $cat_ancestors;
    public $current_cat;
    
    public function __construct() {
        $this->widget_cssclass    = 'woocommerce poka_filter_product';
        $this->widget_description = __( 'A list box of product categories.', PFP_TEXT_DOMAIN );
        $this->widget_id          = 'poka_filter_product';
        $this->widget_name        = __( 'Poka Filter Product', PFP_TEXT_DOMAIN );
        
        $attribute_array = array();
        $category_taxonomy = get_taxonomies(array('name' => PRODUCTS_CATEGORY_TAXONOMY), 'objects');
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        if ($category_taxonomy) {
            $category_taxonomy = array_pop($category_taxonomy);
            $attribute_array[PRODUCTS_CATEGORY_TAXONOMY] = $category_taxonomy->label;
        }
        if ($attribute_taxonomies) {
            foreach ($attribute_taxonomies as $tax) {
                $attribute_key = wc_attribute_taxonomy_name($tax->attribute_name);
                if (taxonomy_exists($attribute_key)) {
                    $attribute_array[$attribute_key] = $tax->attribute_label;
                }
            }
        }

        $this->settings = array(
            'title' => array(
                'type' => 'text',
                'std' => __('Filter by', PFP_TEXT_DOMAIN),
                'label' => __('Title', PFP_TEXT_DOMAIN)
            ),
            'orderby' => array(
                'type' => 'select',
                'std' => 'name',
                'label' => __('Order by', 'woocommerce'),
                'options' => array(
                    'order' => __('Category order', 'woocommerce'),
                    'name' => __('Name', 'woocommerce'),
                    'id' => __('ID', 'woocommerce'),
                ),
            ),
            'query_type' => array(
                'type' => 'select',
                'std' => 'and',
                'label' => __('Query type', PFP_TEXT_DOMAIN),
                'options' => array(
                    'and' => __('AND', PFP_TEXT_DOMAIN),
                    'or' => __('OR', PFP_TEXT_DOMAIN)
                )
            ),
            'hide_empty' => array(
                'type' => 'checkbox',
                'std' => 0,
                'label' => __('Hide empty categories', 'woocommerce'),
            ),
            'count' => array(
                'type' => 'checkbox',
                'std' => 0,
                'label' => __('Show product counts', 'woocommerce'),
            ),
        );

        parent::__construct();
    }
    /**
     * Widget Display Function.
     *
     * Added ability to hide the widget if its filter attribute is a category
     * and the current page is category archive
     * Changed the way it constructs the filters so that it uses the value
     * instead of recreating the attribute id.
     *
     * @param array $args
     * @param array $instance
     *
     * @return void
     */
    public function widget($args, $instance) {
        global $_chosen_attributes;

        if (!is_post_type_archive('product') && !is_tax(get_object_taxonomies('product'))) {
            return;
        }
        
        wp_enqueue_style('poka-filter-proudct-style');
        
        $count        = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];
        $current_term = is_tax() ? get_queried_object()->term_id : '';
        $current_tax  = is_tax() ? get_queried_object()->taxonomy : '';
        $query_type   = isset( $instance['query_type'] ) ? $instance['query_type'] : $this->settings['query_type']['std'];
        $hide_empty   = isset( $instance['hide_empty'] ) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
        
        $get_terms_args = array('hide_empty' => $hide_empty, 'show_count'   => $count );
        
        $orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->settings['orderby']['std'];
        switch ($orderby) {
            case 'name' :
                $get_terms_args['orderby'] = 'slug';
                $get_terms_args['menu_order'] = false;
                break;
            case 'id' :
                $get_terms_args['orderby'] = 'id';
                $get_terms_args['order'] = 'ASC';
                $get_terms_args['menu_order'] = false;
                break;
            case 'menu_order' :
                $get_terms_args['menu_order'] = 'ASC';
                break;
        }
        
        $is_query_terms = false;
        if(is_product_category()) {
            $is_main_category_product = get_field('is_main_category_product', 'product_cat_'.$current_term);
            if(!$is_main_category_product) {
                $child_term = get_term( $current_term, PRODUCTS_CATEGORY_TAXONOMY );
                if(get_field('is_main_category_product', 'product_cat_'.$child_term->parent)) {
                    $get_terms_args['parent'] = $child_term->parent;
                } else {
                    $get_terms_args['parent'] = $current_term;
                    $terms = get_terms(PRODUCTS_CATEGORY_TAXONOMY, $get_terms_args);
                    if (count($terms) > 0) {
                        $is_query_terms = true;
                    } else {
                        $get_terms_args['parent'] = $child_term->parent;
                    }
                }
            } else
                $get_terms_args['parent'] = $current_term;
        } else {
            $get_terms_args['parent'] = 0;
        }
        
        if(!$is_query_terms)
            $terms = get_terms(PRODUCTS_CATEGORY_TAXONOMY, $get_terms_args);
        
        if (0 < count($terms)) {

            ob_start();

            $in_group_args = array();
            $in_group_args['others'] = array();
            foreach ($terms as $term) {
                $in_group_filter = get_field('in_group_filter', 'product_cat_'.$term->term_id);
                if(!$in_group_filter) {
                    $in_group_args['others'][] = $term;
                } elseif(isset($in_group_args[$in_group_filter])) {
                    $in_group_args[$in_group_filter][] = $term;
                } else {
                    $in_group_args[$in_group_filter] = array();
                    $in_group_args[$in_group_filter][] = $term;
                }
            }
            
            foreach ($in_group_args as $title => $in_group_arg) {
                if(empty($in_group_arg))
                    continue;
                if($title !== 'others') {
                    $instance['title'] = $title;
                }
                $this->widget_start($args, $instance);
                echo '<ul>';
                foreach ($in_group_arg as $term) {
                    if ($current_term == $term->term_id) {
                        $icon = '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="check-square" class="svg-inline--fa fa-check-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zm0 400H48V80h352v352zm-35.864-241.724L191.547 361.48c-4.705 4.667-12.303 4.637-16.97-.068l-90.781-91.516c-4.667-4.705-4.637-12.303.069-16.971l22.719-22.536c4.705-4.667 12.303-4.637 16.97.069l59.792 60.277 141.352-140.216c4.705-4.667 12.303-4.637 16.97.068l22.536 22.718c4.667 4.706 4.637 12.304-.068 16.971z"></path></svg>';
                    } else {
                        $icon = '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="square" class="svg-inline--fa fa-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-6 400H54c-3.3 0-6-2.7-6-6V86c0-3.3 2.7-6 6-6h340c3.3 0 6 2.7 6 6v340c0 3.3-2.7 6-6 6z"></path></svg>';
                    }
                    $link = get_term_link($term);
                    if (isset($_GET['min_price'])) {
                        $link = add_query_arg('min_price', $_GET['min_price'], $link);
                    }
                    if (isset($_GET['max_price'])) {
                        $link = add_query_arg('max_price', $_GET['max_price'], $link);
                    }
                    if (isset($_GET['orderby'])) {
                        $link = add_query_arg('orderby', $_GET['orderby'], $link);
                    }
                    if (get_search_query()) {
                        $link = add_query_arg('s', get_search_query(), $link);
                    }
                    echo '<li >';
                    echo '<a href="' . esc_url(apply_filters('woocommerce_layered_nav_link', $link)) . '">';
                    echo $icon . '<span>' .$term->name . '</span>';
                    echo '</a>';
                    echo ' <small class="count">' . $term->count . '</small></li>';
                }
                echo '</ul>';
                $this->widget_end($args);
            }
            
            echo ob_get_clean();
        }
    }

}
add_action( 'widgets_init', 'poka_filter_product_widget' );

function poka_filter_product_widget() {
    register_widget( 'Poka_Widget_Filter_Product' );
}


<?php
/**
 * Plugin Name: BMP Gallery
 * Plugin URI:  
 * Text Domain: bmp_gallery
 * Description: Blah Blah Blah
 * Version:     1.0.0
 * Author:      BroadMediaPartner
 * Author URI:  http://www.broadmediapartner.com
 * License:     LGPL
 */


define('BMP_GALLERY_VERSION', '1.0' . time());


add_action('wp_enqueue_scripts', 'bmp_gallery_enqueue_scripts');

function bmp_gallery_enqueue_scripts() {

    wp_enqueue_script('imagesloaded', plugins_url('/assets/plugins/imagesloaded/imagesloaded.pkgd.min.js', __FILE__), array('jquery'), BMP_GALLERY_VERSION, true);
    wp_enqueue_script('isotope', plugins_url('/assets/plugins/isotope/isotope.pkgd.min.js', __FILE__), array('jquery'), BMP_GALLERY_VERSION, true);

    wp_enqueue_script('bmp-gallery', plugins_url('/assets/js/gallery.js', __FILE__), array('jquery', 'isotope', 'imagesloaded'), BMP_GALLERY_VERSION, true);

    wp_enqueue_style('bmp-gallery', plugins_url('/assets/css/gallery.css', __FILE__), null, BMP_GALLERY_VERSION);

}


/**
 * Setup Taxonomies
 * Creates 'attachment_tag' and 'attachment_category' taxonomies.
 * Enhance via filter `ct_attachment_taxonomies`
 *
 * @uses    register_taxonomy, apply_filters
 * @since   1.0.0
 * @return  void
 */
function setup_taxonomies() {

    $labels = array(
        'name'              => _x('Media Categories', 'taxonomy general name', 'bmp_gallery'),
        'singular_name'     => _x('Media Category', 'taxonomy singular name', 'bmp_gallery'),
        'search_items'      => __('Search Media Categories', 'bmp_gallery'),
        'all_items'         => __('All Media Categories', 'bmp_gallery'),
        'parent_item'       => __('Parent Media Category', 'bmp_gallery'),
        'parent_item_colon' => __('Parent Media Category:', 'bmp_gallery'),
        'edit_item'         => __('Edit Media Category', 'bmp_gallery'),
        'update_item'       => __('Update Media Category', 'bmp_gallery'),
        'add_new_item'      => __('Add New Media Category', 'bmp_gallery'),
        'new_item_name'     => __('New Media Category Name', 'bmp_gallery'),
        'menu_name'         => __('Media Categories', 'bmp_gallery'),
    );

    $args = array(
        'hierarchical'      => TRUE,
        'labels'            => $labels,
        'show_ui'           => TRUE,
        'show_admin_column' => TRUE,
        'query_var'         => TRUE,
        'rewrite'           => TRUE,
    );

    register_taxonomy('attachment_category', 'attachment',  $args );

}

add_action('init', 'setup_taxonomies');



 /** Add a category filter to images */
function ct_add_image_category_filter() {
    $screen = get_current_screen();
    if ( 'upload' == $screen->id ) {
        $dropdown_options = array( 'show_option_all' => __( 'View all categories', 'ct' ), 'hide_empty' => false, 'hierarchical' => true, 'orderby' => 'name', );
        wp_dropdown_categories( $dropdown_options );
    }
}
add_action( 'restrict_manage_posts', 'ct_add_image_category_filter' );



add_shortcode('bmp_gallery', 'bmp_gallery_shortcode');


function bmp_gallery_bool($value){
    return ($value == 'yes' || $value == '1');
}


function bmp_gallery_shortcode($atts, $content = null) {

    $atts = shortcode_atts(array(
       'cat' => '',
       'menu_pos' => 'center',
       'limit' => 20,
       'gap' => 0,
    ), $atts, 'bmp_gallery');

    $output = '';

    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => $atts['limit'],
        'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png'
    );



    $classes = array();
    $classes[] = 'bmp-gallery';
    $classes[] = 'menu-' . $atts['menu_pos'];



    $query = new WP_Query($args);

    $output .= '<div class="' . join(' ', $classes) . '" data-gap="' . intval($atts['gap']) . '">';

    $cats = explode(',', $atts['cat']);

    $output .= '<ul class="bmp-gallery-filters">';
    $output .= '<li>';
    $output .= '<a data-filter="" href="#">' . __('All', 'bmp_gallery') . '</a>';
    $output .= '</li>';

    foreach($cats as $cat){

        if(!empty($cat)){
            $output .= '<li>';
            $output .= '<a data-filter="' . $cat . '" href="#">' . $cat . '</a>';
            $output .= '</li>';
        }

    }

    $output .= '</ul>';



    $output .= '<div class="bmp-gallery-list-wrapper">';
    $output .= '<ul class="bmp-gallery-list">';
    $output .= '';

    if ($query->have_posts()) {

        while ($query->have_posts()) {

            $query->the_post();

            $categories = wp_get_post_terms(get_the_ID(), 'attachment_category');

            $classes = array();
            $classes[] = 'bmp-gallery-item';

            foreach($categories as $category){
                $classes[] = 'category-' . $category->slug;
            }


            $image_source = wp_get_attachment_image_src(get_the_ID(), 'full');
            $image_thumbnail = wp_get_attachment_image(get_the_ID(), 'medium');

            $output .= '<li';
            $output .= ' data-source="' . $image_source[0] . '"';
            $output .= ' class="' . join(' ', $classes) . '">';

            $output .= $image_thumbnail;
            //$output .= '<img src="'.$image_thumbnail . '" />';


            $output .= '</li>';

        }

    }

    $query->reset_postdata();

    $output .= '</ul>';
    $output .= '</div>';
    $output .= '</div>';

    $output .= '';

    return $output;



}



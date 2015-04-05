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

    wp_enqueue_script('tos', plugins_url('/assets/plugins/tos/js/jquery.tosrus.min.all.js', __FILE__), array('jquery'), BMP_GALLERY_VERSION, true);
    wp_enqueue_style('tos', plugins_url('/assets/plugins/tos/css/jquery.tosrus.all.css', __FILE__), null, BMP_GALLERY_VERSION);

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
    return ($value == 'yes' || $value == 'on' || $value == '1');
}


function bmp_gallery_hex2rgb($hex, $alpha = '0.4') {
    $hex = str_replace("#", "", $hex);

    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    $rgb = array($r, $g, $b);
    return 'rgba(' . join(', ', $rgb) . ', ' . $alpha .')';
}


function bmp_gallery_shortcode($atts, $content = null) {

    $atts = shortcode_atts(array(
        'cat'           => '',
        'menu_show'     => '1',
        'menu_pos'      => 'center',
        'lightbox'      => 'yes',
        'limit'         => 20,
        'border_size'   => 5,
        'border_color'  => '#fff',
        'overlay_color' => '#fff',
        'menu_color'    => '#fff',
        'menu_bg'       => '#000',
        'menu_gap'      => 4,
        'hover_data'    => 'yes',
        'bg'            => '#eee',
        'desc_color'    => '#000',
        'gap'           => 10,
        'style'         => 'normal',
        'size'          => 'medium'
    ), $atts, 'bmp_gallery');

    $output = '';

    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => $atts['limit'],
        'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png'
    );

    $categories = array();

    $atts['cat'] = array_map('sanitize_title', explode(',', $atts['cat']));

    foreach($atts['cat'] as $category){

        if($term = get_term_by('slug', $category, 'attachment_category')){
            $categories[$term->term_id] = $term;
        }

    }

    if(!empty($categories)){
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'attachment_category',
                'field'    => 'term_id',
                'terms'    => array_keys($categories)
            )
        );
    }

    $atts['menu_gap'] = min($atts['menu_gap'], 100);

    $classes[] = 'bmp-gallery';
    $classes[] = 'menu-' . $atts['menu_pos'];
    $classes[] = bmp_gallery_bool($atts['menu_show']) ? 'menu-show' : '';
    $classes[] = 'size-' . $atts['size'];
    $classes[] = 'style-' . $atts['style'];

    $attributes = array();
    $attributes['class'] = join(' ', $classes);
    $attributes['id'] = 'bmp-' . substr(md5(mt_rand(0, PHP_INT_MAX)), 0, 6);
    $attributes['data-gap'] = intval($atts['gap']);
    $attributes['data-border-color'] = $atts['border_color'];
    $attributes['data-lightbox'] = bmp_gallery_bool($atts['lightbox']) ? 'yes' : 'no';
    $attributes['data-desc-color'] = $atts['desc_color'];
    $attributes['data-menu-color'] = $atts['menu_color'];
    $attributes['data-menu-bg'] = $atts['menu_bg'];
    $attributes['data-menu-gap'] = $atts['menu_gap'];
    $attributes['data-bg'] = $atts['bg'];
    $attributes['data-border-size'] = $atts['border_size'];
    $attributes['data-overlay-color'] = bmp_gallery_hex2rgb($atts['overlay_color']);

    $thumb_size = 'medium';

    if($atts['size'] == 'large' || ($atts['style'] == 'squared' && in_array($atts['size'], array('medium', 'large')))){
        $thumb_size = 'large';
    }


    foreach($attributes as $attribute => $value){
        $attributes[$attribute] = $attribute . '="' . $value . '"';
    }

    $query = new WP_Query($args);

    $output .= '<div ' . join(' ', $attributes) . '>';
    $output .= '<ul class="bmp-gallery-filters">';
    $output .= '<li>';
    $output .= '<a data-filter="" href="#">' . __('All', 'bmp_gallery') . '</a>';
    $output .= '</li>';

    foreach($categories as $category){

        if(!empty($category)){
            $output .= '<li>';
            $output .= '<a data-filter="' . $category->slug . '" href="#">' . $category->name . '</a>';
            $output .= '</li>';
        }

    }

    $output .= '</ul>';

    $output .= '<div class="bmp-gallery-list-wrapper">';
    $output .= '<ul class="bmp-gallery-list">';

    foreach($query->posts as $post){

        $category_terms = wp_get_post_terms($post->ID, 'attachment_category');

        $classes = array();
        $classes[] = 'bmp-gallery-item';

        foreach($category_terms as $category_term){
            $classes[] = 'category-' . $category_term->slug;
        }

        $image_source = wp_get_attachment_image_src($post->ID, 'full');

        $output .= '<li data-source="' . $image_source[0] . '" class="' . join(' ', $classes) . '">';

        $output .= '<a class="image-wrap" href="' . $image_source[0] . '">';
        $output .= '<figure>';

        $output .= wp_get_attachment_image($post->ID, $thumb_size);

        $output .= '<div class="image-overlay">';
        
        if(bmp_gallery_bool($atts['hover_data'])){
            $output .= '<h3>' . $post->post_title . '</h3>';
            $output .= '<h4>' . $post->post_content . '</h4>';
        }

        $output .= '</div>';

        $output .= '</figure>';
        $output .= '</a>';
        $output .= '</li>';

    }

    $output .= '</ul>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;



}



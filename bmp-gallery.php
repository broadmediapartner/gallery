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

        $attachment_taxonomies = array();


        // Tags
        $labels = array(
            'name'              => _x( 'Media Tags', 'taxonomy general name', 'attachment_taxonomies' ),
            'singular_name'     => _x( 'Media Tag', 'taxonomy singular name', 'attachment_taxonomies' ),
            'search_items'      => __( 'Search Media Tags', 'attachment_taxonomies' ),
            'all_items'         => __( 'All Media Tags', 'attachment_taxonomies' ),
            'parent_item'       => __( 'Parent Media Tag', 'attachment_taxonomies' ),
            'parent_item_colon' => __( 'Parent Media Tag:', 'attachment_taxonomies' ),
            'edit_item'         => __( 'Edit Media Tag', 'attachment_taxonomies' ), 
            'update_item'       => __( 'Update Media Tag', 'attachment_taxonomies' ),
            'add_new_item'      => __( 'Add New Media Tag', 'attachment_taxonomies' ),
            'new_item_name'     => __( 'New Media Tag Name', 'attachment_taxonomies' ),
            'menu_name'         => __( 'Media Tags', 'attachment_taxonomies' ),
        );

        $args = array(
            'hierarchical' => FALSE,
            'labels'       => $labels,
            'show_ui'      => TRUE,
            'show_admin_column' => TRUE,
            'query_var'    => TRUE,
            'rewrite'      => TRUE,
        );


        $attachment_taxonomies[] = array(
            'taxonomy'  => 'attachment_tag',
            'post_type' => 'attachment',
            'args'      => $args
        );


        // Categories
        $labels = array(
            'name'              => _x( 'Media Categories', 'taxonomy general name', 'attachment_taxonomies' ),
            'singular_name'     => _x( 'Media Category', 'taxonomy singular name', 'attachment_taxonomies' ),
            'search_items'      => __( 'Search Media Categories', 'attachment_taxonomies' ),
            'all_items'         => __( 'All Media Categories', 'attachment_taxonomies' ),
            'parent_item'       => __( 'Parent Media Category', 'attachment_taxonomies' ),
            'parent_item_colon' => __( 'Parent Media Category:', 'attachment_taxonomies' ),
            'edit_item'         => __( 'Edit Media Category', 'attachment_taxonomies' ), 
            'update_item'       => __( 'Update Media Category', 'attachment_taxonomies' ),
            'add_new_item'      => __( 'Add New Media Category', 'attachment_taxonomies' ),
            'new_item_name'     => __( 'New Media Category Name', 'attachment_taxonomies' ),
            'menu_name'         => __( 'Media Categories', 'attachment_taxonomies' ),
        );

        $args = array(
            'hierarchical' => TRUE,
            'labels'       => $labels,
            'show_ui'      => TRUE,
            'show_admin_column' => TRUE,
            'query_var'    => TRUE,
            'rewrite'      => TRUE,
        );

        $attachment_taxonomies[] = array(
            'taxonomy'  => 'attachment_category',
            'taxonomy'  => 'category',
            'post_type' => 'attachment',
            'args'      => $args
        );

        $attachment_taxonomies = apply_filters( 'ct_attachment_taxonomies', $attachment_taxonomies );

        foreach ( $attachment_taxonomies as $attachment_taxonomy ) {
            register_taxonomy(
                $attachment_taxonomy['taxonomy'],
                $attachment_taxonomy['post_type'],
                $attachment_taxonomy['args']
            );
        }

    }
add_action( 'init', 'setup_taxonomies');

 /** Add a category filter to images */
function ct_add_image_category_filter() {
    $screen = get_current_screen();
    if ( 'upload' == $screen->id ) {
        $dropdown_options = array( 'show_option_all' => __( 'View all categories', 'ct' ), 'hide_empty' => false, 'hierarchical' => true, 'orderby' => 'name', );
        wp_dropdown_categories( $dropdown_options );
    }
}
add_action( 'restrict_manage_posts', 'ct_add_image_category_filter' );




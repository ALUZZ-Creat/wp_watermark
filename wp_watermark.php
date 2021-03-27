<?php

/*
Plugin Name:watermark
Plugin URI:
Description: to add water mark
Version: 1.0
Author: aluzz
Author URI:
*/


require_once __DIR__ . '/vendor/autoload.php';
require 'require-plugins.php';

function watermark($dir)
{
    $mpdf = new \Mpdf\Mpdf();
    
    $watermark_image = get_template_directory().'/../../plugins/wp-watermark/assets/aa.png';
    $mpdf->SetWatermarkImage($watermark_image,0.3,'D','p');
    $mpdf->showWatermarkImage = true;
    $pagecount = $mpdf->SetSourceFile($dir);
    for ($i=1; $i<=$pagecount; $i++)
    {
        $mpdf->AddPage();
        $tplId = $mpdf->importPage($i);
        $mpdf->useTemplate($tplId);
    }
    $mpdf->Output($dir);
}

add_action( 'init', 'gov_order_register_post_type' );
function gov_order_register_post_type() {
    $args = [
        'label'  => esc_html__( 'Orders', 'text-domain' ),
        'labels' => [
            'menu_name'          => esc_html__( 'Orders', 'digital-repository' ),
            'name_admin_bar'     => esc_html__( 'Order', 'digital-repository' ),
            'add_new'            => esc_html__( 'Add Order', 'digital-repository' ),
            'add_new_item'       => esc_html__( 'Add new Order', 'digital-repository' ),
            'new_item'           => esc_html__( 'New Order', 'digital-repository' ),
            'edit_item'          => esc_html__( 'Edit Order', 'digital-repository' ),
            'view_item'          => esc_html__( 'View Order', 'digital-repository' ),
            'update_item'        => esc_html__( 'View Order', 'digital-repository' ),
            'all_items'          => esc_html__( 'All Orders', 'digital-repository' ),
            'search_items'       => esc_html__( 'Search Orders', 'digital-repository' ),
            'parent_item_colon'  => esc_html__( 'Parent Order', 'digital-repository' ),
            'not_found'          => esc_html__( 'No Orders found', 'digital-repository' ),
            'not_found_in_trash' => esc_html__( 'No Orders found in Trash', 'digital-repository' ),
            'name'               => esc_html__( 'Orders', 'digital-repository' ),
            'singular_name'      => esc_html__( 'Order', 'digital-repository' ),
        ],
        'public'              => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'show_in_rest'        => true,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite_no_front'    => false,
        'show_in_menu'        => true,
        'supports' => [
            'title',
            'editor',
            'thumbnail',
        ],
        'taxonomies' => [
            'category',
            'tag',
        ],
        
        'rewrite' => true
    ];

    register_post_type( 'gov_order', $args );
}

// function wporg_custom_post_type() {
//     register_post_type('gov_order',
//         array(
//             'labels'      => array(
//                 'name'          => __('Orders'),
//                 'singular_name' => __('Order'),
//             ),
//                 'public'      => true,
//                 'has_archive' => true,
//                 'show_in_rest' => true,
//                 'template' => array(
//                     array( 'core/paragraph', array(
//                         'placeholder' => 'Add a root-level paragraph',
//                     ) ),
//                     array( 'core/columns', array(), array(
//                         array( 'core/column', array(), array(
//                             array( 'core/file', array() ),
//                             array( 'core/block', array() ),
//                         ) ),
//                         array( 'core/column', array(), array(
//                             array( 'core/paragraph', array(
//                                 'placeholder' => 'Add a inner paragraph'
//                             ) ),
//                         ) ),
//                     ) )
//                 )
//             )
        
//     );
// }
// add_action('init', 'wporg_custom_post_type');


function cpt_from_attachment($attachment_ID)
{          
    global $current_user;
    get_currentuserinfo();

    $attachment_post = get_post( $attachment_ID );
    $type = get_post_mime_type( $attachment_ID );
    $get_post_type = get_post_type( $attachment_post->post_parent );

    if ($get_post_type == 'gov_order')
    {
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

        $dir = explode('wp-content',$attachment_post->guid);

        $path = get_template_directory()."/../..".$dir[1];

        watermark($path);
    }
    
}
add_action("add_attachment", 'cpt_from_attachment');


add_filter( 'rwmb_meta_boxes', 'prefix_register_meta_boxes' );
function prefix_register_meta_boxes( $meta_boxes ) {

    $post_id = isset($_GET['post'])?$_GET['post']:false;

    $meta_boxes[] = array(
        'title'      => 'Order Information',
        'post_types' => 'gov_order',
        'context'    => 'side',
        'fields' => array(
            array(
                'name'  => 'Order ID',
                'desc'  => 'Order No.: {Month}/{Year}',
                'id'    => 'gov_order_id',
                'type'  => 'text',
                'required'  => true,
            ),
            array(
                'id'               => 'file',
                'name'             => 'File',
                'type'             => 'file',

                // Delete file from Media Library when remove it from post meta?
                // Note: it might affect other posts if you use same file for multiple posts
                'force_delete'     => false,

                // Maximum file uploads.
                'max_file_uploads' => 1,
                'required'  => true,

            ),
            array(
                'id'      => 'keywords',
                'name'    => 'Keywords',
                'type'    => 'text_list',
                'clone' => true,

                // Options: array of Placeholder => Label for text boxes
                // Number of options are not limited
                'options' => array(
                    'Keyword'      => '',
                ),
            ),
        ),
        'validation' => array(
            'rules' => array(
                'gov_order_id' => array(
                    'remote' => admin_url( 'admin-ajax.php?action=check_order_exists&post_id='.$post_id ),
                ),
            ),
            'messages' => [
                'gov_order_id' => [
                    'remote'  => 'Order No. must be unique',
                ],
            ],
        ),
    );

    // Add more meta boxes if you want
    // $meta_boxes[] = ...

    return $meta_boxes;
}


add_filter('use_block_editor_for_post_type', 'prefix_disable_gutenberg', 10, 2);
function prefix_disable_gutenberg($current_status, $post_type)
{
    // Use your post type key instead of 'product'
    if ($post_type === 'gov_order') return false;
    return $current_status;
}


add_action( 'wp_ajax_check_order_exists', 'remote_validation' );

function remote_validation() {
    $rd_args = array(
        'post_type' => 'gov_order',
        'meta_query' => array(
            array(
                'key'     => 'gov_order_id',
                'value'   => $_GET['gov_order_id'],
                'compare' => '=',
            ),
        ),
        'post__not_in' => array( $_GET['post_id'] )
    );
 
    $rd_query = new WP_Query( $rd_args );

    if ($rd_query->found_posts) {
        echo wp_json_encode(false);
    } else {
        echo wp_json_encode(true);
    }

    exit;
}

add_filter( 'template_include', 'gov_order_page_template', 99 );
function gov_order_page_template( $template ) {
    if ( is_single() && 'gov_order' == get_post_type()  ) {
       return plugin_dir_path(__FILE__) . 'govorder-page-template.php';
    }
    return $template;
}
?>
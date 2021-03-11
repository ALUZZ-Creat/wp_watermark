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
function watermark($dir)
{
    $mpdf = new \Mpdf\Mpdf();
    //print_r($mpdf);exit;png
    $mpdf->SetWatermarkImage('wp-content/plugins/wp-watermark/assets/aa.png',0.3,'D','p');
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

function wporg_custom_post_type() {
    register_post_type('gov_order',
        array(
            'labels'      => array(
                'name'          => __('Orders'),
                'singular_name' => __('Order'),
            ),
                'public'      => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'template' => array(
                    array( 'core/paragraph', array(
                        'placeholder' => 'Add a root-level paragraph',
                    ) ),
                    array( 'core/columns', array(), array(
                        array( 'core/column', array(), array(
                            array( 'core/file', array() ),
                            array( 'core/block', array() ),
                        ) ),
                        array( 'core/column', array(), array(
                            array( 'core/paragraph', array(
                                'placeholder' => 'Add a inner paragraph'
                            ) ),
                        ) ),
                    ) )
                )
            )
        
    );
}
add_action('init', 'wporg_custom_post_type');


function cpt_from_attachment($attachment_ID)
{          
    global $current_user;
    get_currentuserinfo();

    $attachment_post = get_post( $attachment_ID );
    $type = get_post_mime_type( $attachment_ID );
    $get_post_type = get_post_type( $attachment_post->post_parent );

    if ($get_post_type == 'gov_order')
    {
        $dir = explode('wp-content',$attachment_post->guid);
        // print_r('wp-content'.$dir[1]);exit;
    	watermark('wp-content'.$dir[1]);
    }
    
}
add_action("add_attachment", 'cpt_from_attachment');
?>
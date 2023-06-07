<?php
/*
Plugin Name: DW Hivebrite Endpoint
Plugin URI: https://github.com/Dantolos/DW_Hivebrite_Endpoint
Description: Custom API Endpoint, to prepare post data for Hivebrite.
Version: 1.80
Author: Aaron Giaimo
Author URI: https://github.com/Dantolos/
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


//https://demenzjournal.com/?wppusher-hook&token=4cfea2921a6b126277f559783717d280e1917fed420c0e48d84cc12b791de732&package=RFdfSGl2ZWJyaXRlX0VuZHBvaW50L2luZGV4LnBocA==

//CALLBACK DATA
function article_api($request) {
    $after = $request->get_param( 'after' );
    $before = $request->get_param( 'before' );
    //specificid
    $spezid = isset($_GET['id']) ? explode(",", $_GET['id']) : array();
    
    //stylesheet
    $css_file_path = __DIR__.'/style.css';
    $css_string = '<style>'.file_get_contents($css_file_path).'</style>';

    

    //query
    $args = array(
        'post__in'=> $spezid,
        'post_type' => 'post',
        'posts_per_page' => isset($_GET['per_page']) ? $_GET['per_page'] : -1, // Add per_page parameter
        'paged' => isset($_GET['page']) ? $_GET['page'] : 1, 
        'date_query' => array(
            array(
                'after' => $after ? $after : '',
                //'before' => $before ? $before : 'today',
                'inclusive' => true,
            ),
        ),
    );

    $query = new WP_Query( $args );
    $posts = $query->get_posts();

    $data = array();

    foreach ( $posts as $post ) {
        $published = str_replace( ' ', 'T', $post->post_date);
        $updated = str_replace( ' ', 'T', $post->post_date);
      
        
        //if(get_field('', $post->ID) == false){ continue; }
        $blocks = parse_blocks($post->post_content);
        $clearBlock = '';

        // LEAD einbinden
        $lead = '<p><strong>'.get_field('lead', $post->ID).'</strong></p>';

        $teaser = '';

        $alternativeRender = $post->post_content;

        foreach ( $blocks as $block ) {
            switch ($block['blockName']) {

                //Heading
                case 'core/heading':
                    
                    $clearBlock .= '<div class="dj-block-heading">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;

                //Paragraph
                case 'core/paragraph':
                    $clearBlock .= '<div class="dj-block-paragraph">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;
                    
                //Bilder
                case 'core/image':
                    $imageURL = wp_get_attachment_image_url($block['attrs']['id'] );
                    $clearBlock .= '<div class="dj-block-image">';
                    $clearBlock .= '<img src="'.$imageURL.'" />';
                    $clearBlock .= '</div>';
                    break;

                //List
                case 'core/list':
                    $clearBlock .= '<div class="dj-block-list">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;

                //Separator
                case 'core/separator':
                    $clearBlock .= '<div class="dj-block-separator">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;


                //Teaser
                case 'alzheimer/teaser':
                    //$clearBlock .= '<div class="dj-block-teaser">';
                    //$clearBlock .= $block['innerHTML'];
                    //$clearBlock .= '</div>';
                    $clearBlock .= '';
                    break;

                //Filet
                case 'alzheimer/filet':
                    $clearBlock .= '<div class="dj-block-filet">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;

                //Quote
                case 'alzheimer/quote':
                    $clearBlock .= '<div class="dj-block-quote">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;

                //Call to Action
                case 'alzheimer/calltoaction':
                    $clearBlock .= '<div class="dj-block-cta">';
                    $clearBlock .= $block['innerHTML'];
                    $clearBlock .= '</div>';
                    break;

                //Wiki Teaser
                case 'demenzwiki/teaser':
                    $teaser = render_block( $block );
                    $clearBlock .= '<div class="dj-block-dw-teaser">';
                    $clearBlock .= $teaser;
                    $clearBlock .= '</div>';
                    
                    break;

                //Newsletter
                case 'alzheimer/newsletter':
                    $clearBlock .= '';
                    break;

                //Fundraising
                case 'alzheimer/fundraising':
                    $clearBlock .= '';
                    break;
                
                default:
                $teaser = render_block($block);
                    $clearBlock .= $block['innerHTML'];
                    break;    
            }
        }

        $updated = the_date($post->ID);

        $thumbnail_id = get_post_thumbnail_id( $post->ID ); // Abrufen der ID des Thumbnail-Bildes
        $thumbnail_size = 'medium_large'; // Hier den Namen der benutzerdefinierten Bildgröße angeben
        $thumbnail_url = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size ); 
        $featured_image = ( is_array($thumbnail_url) ) ? $thumbnail_url[0] : wp_get_attachment_image_src( $thumbnail_id );
  

        $data[] = array(
            'id' => $post->ID,
            'published' => $published,
            'updated' => $updated,
            'title' => $post->post_title,
            'teaser' => $teaser,
            'featured_image' => $featured_image,
            //'content' => $css_string.$lead.$clearBlock,
            //'blocks' => $clearBlock,//TO DELETE
            'style' => $css_string,//TO DELETE
            //'raw' => parse_blocks($post->post_content), //TO DELETE
            'alternative' => $css_string.$lead.$alternativeRender
        );
    }
    return rest_ensure_response( $data );
}

//HOOK
add_action( 'rest_api_init', function () {
    register_rest_route( 'sud/v1', '/article/', array(
        'methods' => 'GET',
        'callback' => 'article_api',
        'args' => array(
            'after' => array(
                'type' => 'string',
                'description' => 'Limit results to those after a certain date and time (ISO8601 format)',
                'required' => false,
            ),
            'before' => array(
                'type' => 'string',
                'description' => 'Limit results to those before a certain date and time (ISO8601 format)',
                'required' => false,
            ),
        ),
    ) );
} );

?>
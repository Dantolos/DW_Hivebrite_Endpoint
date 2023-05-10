<?php
/*
Plugin Name: DW Hivebrite Endpoint
Plugin URI: https://github.com/Dantolos/DW_Hivebrite_Endpoint
Description: Custom API Endpoint, to prepare post data for Hivebrite.
Version: 1.75
Author: Aaron Giaimo
Author URI: https://github.com/Dantolos/
License: GPL2
*/

//CALLBACK DATA
function article_api($request) {
    $after = $request->get_param( 'after' );
    $before = $request->get_param( 'before' );

    //stylesheet
    $css_file_path = __DIR__.'/style.css';
    $css_string = '<style>'.file_get_contents($css_file_path).'</style>';

    //query
    $args = array(
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
                    $imageURL = wp_get_attachment_image_url($block['attrs']['id']);
                    $clearBlock .= '<div class="dj-block-image">';
                    $clearBlock .= '<img src="'.$imageURL.'" />';
                    $clearBlock .= '</div>';
                    break;

                //List
                case 'core/list':
                    $clearBlock .= '<div class="dj-block-list">';
                    $clearBlock .= $imageURL;
                    $clearBlock .= '</div>';
                    break;

                //Separator
                case 'core/separator':
                    $clearBlock .= '<div class="dj-block-separator">';
                    $clearBlock .= $imageURL;
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
                    $clearBlock .= render_block( $block );
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
                    $clearBlock .= $block['innerHTML'];
                    break;
                    
            }
        }

        $updated = the_date($post->ID);

        $data[] = array(
            'id' => $post->ID,
            'published' => $published,
            'updated' => $updated,
            'title' => $post->post_title,
            'teaser' => $teaser,
            'featured_image' => get_the_post_thumbnail_url( $post->ID ),
            'content' => $css_string.$lead.$clearBlock,
            //'blocks' => $clearBlock,//TO DELETE
            'style' => $css_string,//TO DELETE
            'raw' => parse_blocks($post->post_content), //TO DELETE
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
<?php

namespace DW;

class generate_html 
{
     public $htmlBlock;
 
     public function  __construct($block) {
          $this->create_html_elements($block);
          return  $this->htmlBlock;
     }
     

     public function create_html_elements($block){
          
          switch ($block['blockName']) {
          
               //Heading
               case 'core/heading':
                    $this->htmlBlock .= '<div class="dj-block-heading">';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
          
               //Paragraph
               case 'core/paragraph':
                    $this->htmlBlock .= '<div class="dj-block-paragraph">';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
               
               //Bilder
               case 'core/image':
               $imageURL = wp_get_attachment_image_url($block['attrs']['id'] );
                    $this->htmlBlock .= '<div class="dj-block-image">';
                    //$this->htmlBlock .= '<img src="'.$imageURL.'" />';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
          
               //List
               case 'core/list':
                    $this->htmlBlock .= '<div class="dj-block-list">';
                    //$this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '<ul>'; 
                    if(is_array($block['innerBlocks']) || is_object($block['innerBlocks'])){
                         foreach($block['innerBlocks'] as $listItem){
                              $this->htmlBlock .= $listItem['innerHTML'];
                         }
                    }
                    $this->htmlBlock .= '</ul>';
                    $this->htmlBlock .= '</div>';
                    break;
          
               //Separator
               case 'core/separator':
                    $this->htmlBlock .= '<div class="dj-block-separator">';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
          
          
               //Teaser
               case 'alzheimer/teaser':
                    //$this->htmlBlock .= '<div class="dj-block-teaser">';
                    //$this->htmlBlock .= $block['innerHTML'];
                    //$this->htmlBlock .= '</div>';
                    $this->htmlBlock .= '';
                    break;
          
               //Filet
               case 'alzheimer/filet':
                    $this->htmlBlock .= '<div class="dj-block-filet">';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
          
               //Quote
               case 'alzheimer/quote':
                    $this->htmlBlock .= '<div class="dj-block-quote">';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
          
               //Call to Action
               case 'alzheimer/calltoaction':
                    $this->htmlBlock .= '<div class="dj-block-cta">';
                    $this->htmlBlock .= $block['innerHTML'];
                    $this->htmlBlock .= '</div>';
                    break;
          
               //Wiki Teaser
               case 'demenzwiki/teaser':
                    $teaser = render_block( $block );
                    $this->htmlBlock .= '<div class="dj-block-dw-teaser">';
                    $this->htmlBlock .= $teaser;
                    $this->htmlBlock .= '</div>';
               
                    break;
          
               //Newsletter
               case 'alzheimer/newsletter':
                    $this->htmlBlock .= '';
                    break;
          
               //Fundraising
               case 'alzheimer/fundraising':
                    $this->htmlBlock .= '';
                    break;
          
               default:
                    $teaser = render_block($block);
                    $this->htmlBlock .= $block['innerHTML'];
                    break;    
          }
     }
}
<?php
class J_PostList {
	private $query_object;

  private $html;

  private $html_list_tag = "ul";
  private $html_list_id;
  private $html_list_class;
  private $html_post_format;

	public function __construct( $query = '', $options = null ) {
		if( is_a( $query, 'WP_Query') ) {
      $this->query_object = $query;
    } else {
      $this->query_object = new J_WP_Query( $query );
    }
    
    if( ! is_null( $options ) && is_array( $options ) && isset( $options['post_format'] ) ) {
      $this->html_post_format = $options['post_format'];
    } 

    if( ! is_null( $options ) && is_array( $options ) && isset( $options['list_class'] ) ) {
      $this->html_list_class = $options['list_class'];
    } 
	}

	public function __destruct() {
		wp_reset_postdata();
	}

	public function max_num_pages() {
		return $this->query_object->max_num_pages;
	}

  private function add_to_html_output( $string ) {
    $this->html .= $string;
  }

  // View-y functions
  
  public function to_html() {
    if( $this->have_posts() ) {
      $this->add_to_html_output( $this->list_to_html( $list ) );
    } else {
      $this->add_to_html_output( $this->no_posts_message() );
    }
    return $this->html;
  }

  public function pagination_to_html( $paged = null ) {
  	//TODO: refactor the crap out of this, obviously... maybe accept another lambda for formatting pagination?
  	if( is_null( $paged ) ) $paged = $this->query_object->query_vars['paged'];
  	if ($this->max_num_pages() > 1) : ?>
  	  <nav id="post-nav">
  	    <ul class="pager">
  	      <?php if ($next_link = $this->get_next_posts_link( $paged, __('&larr; See More', 'roots'))) : ?>
  	         <li class="previous"><?php echo $next_link; ?></li>
  	      <?php else: ?>
  	        <li class="previous disabled"><a><?php _e('&larr; See More', 'roots'); ?></a></li>
  	      <?php endif; ?>
  	      <?php if ($previous_link = $this->get_previous_posts_link( $paged, __('See More &rarr;', 'roots'))) : ?>
  	        <li class="next"><?php echo $previous_link; ?></li>
  	      <?php else: ?>
  	        <li class="next disabled"><a><?php _e('See More &rarr;', 'roots'); ?></a></li>
  	      <?php endif; ?>
  	    </ul>
  	  </nav>
  	<?php endif;
  }

  public function get_next_posts_link( $paged, $label = null, $max_page = 0 ) {
  	if ( !$max_page )
      $max_page = $this->max_num_pages();

  	if ( !$paged )
      $paged = 1;

  	$nextpage = intval($paged) + 1;

  	if ( null === $label )
      $label = __( 'Next Page &raquo;' );

  	if ( ( $nextpage <= $max_page ) ) {
      $attr = apply_filters( 'next_posts_link_attributes', '' );
      return '<a href="' . next_posts( $max_page, false ) . "\" $attr>" . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) . '</a>';
  	}
  }

  public function get_previous_posts_link( $paged, $label = null ) {
    if ( null === $label )
      $label = __( '&laquo; Previous Page' );

    if ( !is_single() && $paged > 1 ) {
      $attr = apply_filters( 'previous_posts_link_attributes', '' );
      return '<a href="' . previous_posts( false ) . "\" $attr>". preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) .'</a>';
    }
  }

  private function list_to_html() {

    return $this->html_open_list_tag() . $this->list_items_to_html() . $this->html_close_list_tag();
  }

  private function list_items_to_html( ) {
    $return = null;
    while ( $this->have_posts() ):
      $return .= $this->the_post(); global $post; $post = new J_Post( $post ); 
      $return .= $this->list_item_to_html( $post );
    endwhile;

    return $return;
  }

  private function list_item_to_html( $list_item ) {
    return $list_item->to_html( $this->html_post_format );
  }

  private function html_open_list_tag() {
    $html_attributes = array();
    if( ! is_null( $this->html_list_id ) ) $html_attributes['id'] = $this->html_list_id;
    if( ! is_null( $this->html_list_class ) ) $html_attributes['class'] = $this->html_list_class;
    return $this->html_open_tag( $this->html_list_tag, $html_attributes );
  }

  private function html_close_list_tag() {
    return $this->html_close_tag( $this->html_list_tag );
  }

  private function html_open_list_item_tag( $html_attributes = null ) {
    return $this->html_open_tag( 'li', $html_attributes );
  }

  private function html_close_list_item_tag() {
    return $this->html_close_tag( 'li' );
  }

  private function html_open_tag( $tag, $html_attributes ) {
    return "<$tag "
      . ((isset($html_attributes['class'])) ? "class='{$html_attributes['class']}' " : "")
      . ((isset($html_attributes['id'])) ? "id='{$html_attributes['id']}' " : "")
      . ">";
  }

  private function html_close_tag( $tag ) {
    return "</$tag>";
  }

  private function no_posts_message() {
    return "<div class='alert'>No items could be found.</div>";
  }


  // Just for Decoupling

	private function have_posts() {
		return $this->query_object->have_posts();
	}

	private function the_post() {
		return $this->query_object->the_post();
	}
}


//Usage
// $li_format = function( $item ) {
//   // set a return value
//   return sprintf( "<a href='%s' title='%s'>%s</a><p>%s</p>", $item->permalink(), $item->post_title, $item->post_title, $item->get_meta('special-title', true) );
//   // or return the result of an existing function
//   //return J_PostFormats::linked_title_and_content($item);
// };

// $post_list = new J_PostList( "author=1", $li_format );
// echo $post_list->to_html();
// unset($post_list);


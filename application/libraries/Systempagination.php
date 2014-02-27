<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Systempagination {
	
	var $base_url = '';
	var $prefix = ''; // A custom prefix added to the path.
	var $suffix = ''; // A custom suffix added to the path.
	

	var $total_rows = 0; 
	var $per_page = 10;  
	var $num_links = 5;  
	var $cur_page = 0; 
	var $use_page_numbers = FALSE;  
	var $first_link = '&lsaquo; First';
	var $next_link = '下一页';
	var $prev_link = '上一页';
	var $last_link = 'Last &rsaquo;';
	var $uri_segment = 4;
	var $full_tag_open = '';
	var $full_tag_close = '';
	var $first_tag_open = '';
	var $first_tag_close = '&nbsp;';
	var $last_tag_open = '&nbsp;';
	var $last_tag_close = '';
	var $first_url = ''; // Alternative URL for the First Page.
	var $cur_tag_open = '&nbsp;<strong>';
	var $cur_tag_close = '</strong>';
	var $next_tag_open = '&nbsp;';
	var $next_tag_close = '&nbsp;';
	var $prev_tag_open = '&nbsp;';
	var $prev_tag_close = '';
	var $num_tag_open = '&nbsp;';
	var $num_tag_close = '';
	var $page_query_string = FALSE;
	var $query_string_segment = 'per_page';
	var $display_pages = TRUE;
	var $anchor_class = '';
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct($params = array()) { 
		$this->full_tag_open = '<div id="page">';
		$this->full_tag_close = '</div>';
		$this->first_link = '首页';
		$this->first_tag_open = ' ';
		$this->first_tag_close = ' ';
		$this->last_link = '尾页';
		$this->last_tag_open = ' ';
		$this->last_tag_close = ' ';
		$this->cur_tag_open = '<b>';
		$this->cur_tag_close = '</b> ';
		$this->query_string = ''; 
		if (count ( $params ) > 0) {
			$this->initialize ( $params );
		}
		
		if ($this->anchor_class != '') {
			$this->anchor_class = 'class="' . $this->anchor_class . '" ';
		}
		 
	}
	
	// --------------------------------------------------------------------
	

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array()) {
		if (count ( $params ) > 0) {
			foreach ( $params as $key => $val ) {
				if (isset ( $this->$key )) {
					$this->$key = $val;
				}
			}
		}
	}
	 
	function create_links() {
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 or $this->per_page == 0) {
			return '';
		}
		
		// Calculate the total number of pages
		$num_pages = ceil ( $this->total_rows / $this->per_page );
		
		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1) {
			return '';
		}
		
		// Determine the current page number.
		$CI = & get_instance ();
		
		if ($CI->config->item ( 'enable_query_strings' ) === TRUE or $this->page_query_string === TRUE) {
			if ($CI->input->get ( $this->query_string_segment ) != 0) {
				$this->cur_page = $CI->input->get ( $this->query_string_segment );
				
				// Prep the current page - no funny business!
				$this->cur_page = ( int ) $this->cur_page;
			}
		} else {
			if ($CI->uri->segment ( $this->uri_segment ) != 0) {
				$this->cur_page = $CI->uri->segment ( $this->uri_segment );
				
				// Prep the current page - no funny business!
				$this->cur_page = ( int ) $this->cur_page;
			}
		}
		
		$this->num_links = ( int ) $this->num_links;
		
		if ($this->num_links < 1) {
			show_error ( 'Your number of links must be a positive number.' );
		}
		
		if (! is_numeric ( $this->cur_page )) {
			$this->cur_page = 0;
		}
		
		if ($this->cur_page > $this->total_rows) {
			$this->cur_page = ($num_pages - 1) * $this->per_page;
		}
		
		$uri_page_number = $this->cur_page;
		$this->cur_page = floor ( ($this->cur_page / $this->per_page) + 1 );
		
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;
		
		if ($CI->config->item ( 'enable_query_strings' ) === TRUE or $this->page_query_string === TRUE) {
			$this->base_url = rtrim ( $this->base_url ) . '&amp;' . $this->query_string_segment . '=';
		} else {
			$this->base_url = rtrim ( $this->base_url, '/' ) . '/';
		}
		
		$output = '';
		
		if ($this->first_link !== FALSE and $this->cur_page > ($this->num_links + 1)) {
			$first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
			$output .= $this->first_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ( $first_url ) . $this->query_string . '">' . $this->first_link . '</a>' . $this->first_tag_close;
		}
		
		if ($this->prev_link !== FALSE and $this->cur_page != 1) {
			$i = $uri_page_number - $this->per_page;
			
			if ($i == 0 && $this->first_url != '') {
				$output .= $this->prev_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ( $CI->config->item ( 'system' ).$this->first_url ) . $this->query_string . '">' . $this->prev_link . '</a>' . $this->prev_tag_close;
			} else {
				$i = ($i == 0) ? '' : $this->prefix . $i . $this->suffix;
				$output .= $this->prev_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ( $CI->config->item ( 'system' ).$this->base_url . $i ) . $this->query_string . '">' . $this->prev_link . '</a>' . $this->prev_tag_close;
			}
		
		}
		
		if ($this->display_pages !== FALSE) {
			for($loop = $start - 1; $loop <= $end; $loop ++) {
				$i = ($loop * $this->per_page) - $this->per_page;
				
				if ($i >= 0) {
					if ($this->cur_page == $loop) {
						$output .= $this->cur_tag_open . $loop . $this->cur_tag_close; // Current page
					} else {
						$n = ($i == 0) ? '' : $i;
						
						if ($n == '' && $this->first_url != '') {
							$output .= $this->num_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ($CI->config->item ( 'system' ). $this->first_url ) . $this->query_string . '">' . $loop . '</a>' . $this->num_tag_close;
						} else {
							$n = ($n == '') ? '' : $this->prefix . $n . $this->suffix;
							
							$output .= $this->num_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ( $CI->config->item ( 'system' ).$this->base_url . $n ) . $this->query_string . '">' . $loop . '</a>' . $this->num_tag_close;
						}
					}
				}
			}
		}
		
		if ($this->next_link !== FALSE and $this->cur_page < $num_pages) {
			$output .= $this->next_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ( $CI->config->item ( 'system' ).$this->base_url . $this->prefix . ($this->cur_page * $this->per_page) ) . $this->suffix . $this->query_string . '">' . $this->next_link . '</a>' . $this->next_tag_close;
		}
		
		if ($this->last_link !== FALSE and ($this->cur_page + $this->num_links) < $num_pages) {
			$i = (($num_pages * $this->per_page) - $this->per_page);
			$output .= $this->last_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->site_url ( $CI->config->item ( 'system' ).$this->base_url . $this->prefix . $i ) . $this->suffix . $this->query_string . '">' . $this->last_link . '</a>' . $this->last_tag_close;
		}
		
		$output = preg_replace ( "#([^:])//+#", "\\1/", $output );
		$output = $this->full_tag_open . $output . $this->full_tag_close;
		
		return $output;
	}
	function site_url($str){
		return site_url('admin/'.$str);
	}
} 
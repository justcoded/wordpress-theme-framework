<?php

/**
 * Alias for get_stylesheet_directory_uri() . / . $path
 *
 * @param string  $path path to be appended to the theme uri.
 * @param boolean $echo print or return.
 *
 * @return string
 */
function assets( $path, $echo = true ) {
	$path = get_stylesheet_directory_uri() . '/assets/' . ltrim( $path, '/' );
	if ( $echo ) {
		echo esc_attr( $path );
	}

	return $path;
}

/**
 * Alias for get_template_directory_uri() . / . $path
 *
 * @param string  $path path to be appended to the parent theme uri.
 * @param boolean $echo print or return.
 *
 * @return string
 */
function parent_assets( $path, $echo = true ) {
	$path = get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
	if ( $echo ) {
		echo esc_attr( $path );
	}

	return $path;
}

/**
 * Alias for plugins_url with pre-defined second parameter
 * Mostly used to set paths for assets
 *
 * @param string $path asset or callback path.
 *
 * @return string
 */
function jtf_plugin_url( $path ) {
	return plugins_url( $path, dirname( __FILE__ ) );
}

/**
 * Return the next posts page link for custom post type loop.
 *
 * @param WP_Query $wp_query Query to use in pagination checks.
 * @param string   $label Content for link text.
 * @param string   $load_more_attr attribute for default load more link. Required options: data-selector="(./#)name", additional options: data-container="(./#)name".
 *
 * @return string|void HTML-formatted next posts page link.
 */
function cpt_next_posts_link( WP_Query $wp_query, $label, $load_more_attr = '' ) {
	$paged    = $wp_query->query_vars['paged'];
	$max_page = $wp_query->max_num_pages;

	// on default post list we have empty $paged, so need to set into 1.
	if ( ! $paged ) {
		$paged = $wp_query->query_vars['paged'] = 1;
	}

	$nextpage = intval( $paged ) + 1;

	if ( null === $label ) {
		$label = __( 'Next Page &raquo;' );
	}

	if ( $nextpage <= $max_page ) {
		$attr = apply_filters( 'next_posts_link_attributes', '' );
		$attr = apply_filters( 'jtf_next_posts_link_attributes', $attr );
		if ( ! empty( $load_more_attr ) ) {
			if ( ! empty( $attr ) && strpos( $attr, 'class=' ) !== false ) {
				$attr = preg_replace( '/class="/i', 'class="jtf-load-more ', $attr );
			} else {
				$attr .= ' class="jtf-load-more"';
			}
			$load_more_attr .= ' data-pagecount="' . $max_page . '"';
		}
		$load_more_attr = apply_filters( 'jtf_load_more_attributes', $load_more_attr );

		return '<a href="' . next_posts( $max_page, false ) . "\" $attr $load_more_attr>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) . '</a>';
	}
}

/**
 * Return the previous posts page link for custom post type loop.
 *
 * @param WP_Query $wp_query Query to use in pagination checks.
 * @param string   $label Optional. Previous page link text.
 *
 * @return string|void HTML-formatted previous page link.
 */
function cpt_prev_posts_link( WP_Query $wp_query, $label = null ) {
	$paged = $wp_query->query_vars['paged'];

	if ( null === $label ) {
		$label = __( '&laquo; Previous Page' );
	}

	if ( $paged > 1 ) {
		$attr = apply_filters( 'previous_posts_link_attributes', '' );
		$attr = apply_filters( 'jtf_previous_posts_link_attributes', $attr );

		return '<a href="' . previous_posts( false ) . "\" $attr>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) . '</a>';
	}
}


if ( ! function_exists( 'pa' ) ) {
	/**
	 * Debug function
	 *
	 * @param mixed   $mixed Variable to be printed.
	 * @param boolean $stop Stop script execution.
	 */
	function pa( $mixed, $stop = false ) {
		$ar    = debug_backtrace();
		$key   = pathinfo( $ar[0]['file'] );
		$key   = $key['basename'] . ':' . $ar[0]['line'];
		$print = array( $key => $mixed );
		echo( '<pre>' . htmlentities( print_r( $print, 1 ) ) . '</pre>' );
		if ( ! empty( $stop ) ) {
			exit();
		}
	}
}

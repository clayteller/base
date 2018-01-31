<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @uses Advanced Custom Fields Pro
 *
 * @package Base
 * @since 1.0.1
 */

 /**
 * Include files
 */
 foreach ( glob( plugin_dir_path( __FILE__ ) . "theme-functions/*.php" ) as $file ) {
 	require $file;
 }

 /**
  * Set responsive image sizes for entry featured images.
  *
  * @uses $entries_count
  *
  * @param  string       $sizes
  * @param  array|string $size
  * @return string Sizes attribute.
  */
function base_entry_image_responsive_sizes( $sizes, $size ) {
	if ( is_front_page() || is_archive()  ) {
		global $entries_count;

		if ( 2 == $entries_count ) {
			$viewport_width = '49vw';
		} elseif ( 3 == $entries_count ) {
			$viewport_width = '32vw';
		} elseif ( 4 == $entries_count ) {
			$viewport_width = '24vw';
		// Otherwise, bail
		} else {
			return;
		}

		return '(max-width: 649px) 100vw, ' . $viewport_width;
	}
}
add_filter( 'wp_calculate_image_sizes', 'base_entry_image_responsive_sizes', 10 , 2 );

/**
 * Add a 'Read more' link to the excerpt.
 */
function base_excerpt( $excerpt ) {
	return $excerpt . ' <a href="' . get_permalink() . '" class="more-link">' . __( 'Read more', 'base' ) . '</a>';
}
add_filter( 'get_the_excerpt', 'base_excerpt' );

/**
 * Change excerpt length.
 */
function base_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'base_excerpt_length', 999 );

/**
 * Change excerpt ellispis.
 */
function base_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'base_excerpt_more' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @uses Advanced Custom Fields Pro
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function base_body_classes( $classes ) {
   global $post;
	$show_subscribe = get_field( 'show_subscribe', 'option' );

	// Adds a class of 'hfeed' to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

   // Adds a class for pages
	if ( is_page() ) {
		$classes[] = 'page-' . $post->post_name;
	}

	// Adds a class of 'two-column' to two-column pages.
	if ( base_show_sidebar() || is_page( 'contact' ) ) {
		$classes[] = 'two-column';
	}

	// Adds a class of 'has-subscribe' to pages with subscribe form.
	if ( $show_subscribe ) {
		$classes[] = 'has-subscribe';
	}

	return $classes;
}
add_filter( 'body_class', 'base_body_classes' );

/**
 * Are we displaying a sidebar on this page?
 *
 * @return bool True if 'show_sidebar' custom field is true.
 */
function base_show_sidebar() {
	return get_field( 'show_sidebar' );
}

/**
* Add inline CSS to display a background image.
*
* @param string $css_class          CSS class of element being assigned a background image.
* @param string $field_name         Field name of background image custom field.
* @param bool   $field_name_post_id Optional. Post id of custom field.
* @return string Inline CSS rules.
*/
function base_get_background_image_css( $css_class, $field_name, $field_name_post_id = false ) {
	// If there's no 'page' background image, use the 'site' background image
	$image = get_field( $field_name, $field_name_post_id ) ? get_field( $field_name, $field_name_post_id ) : get_field( $field_name, 'option' );

	// Bail if there's no image
	if ( ! $image ) return;

	// Image size for small screens
	$image_url = $image['sizes'][ 'medium_large' ];
	// Image size for desktop screens
	$image_url_desktop = $image['url'];

	return "
	.{$css_class} {
		background-image: url({$image_url});
	}
	@media (min-width: 768px) {
		.{$css_class} {
			background-image: url({$image_url_desktop});
		}
	}";
}

/**
 * Reads SVG icon file and returns it as a string.
 *
 * @param string $name Name of icon file.
 * @return string SVG code.
 */
function base_svg_icon( $name ) {
	// Use @ to suppress warning if no file exists
	$file = @file_get_contents( THEME_PATH . '/icons/' . $name . '.svg' );

	// Bail if there's no file
	if ( ! $file ) return;

	return $file;
}

/**
* Add a category icon to the category list.
*
* @uses base_add_string()
* @uses base_svg_icon()
*/
function base_add_category_icon( $category_list ) {
   // If not single, don't add category icon.
   if ( ! is_single() ) return $category_list;

   return base_add_string( $category_list, base_svg_icon( 'folder' ), '<li' );
}
add_filter( 'the_category', 'base_add_category_icon' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function base_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'base_pingback_header' );

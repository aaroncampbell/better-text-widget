<?php
/**
 * Plugin Name: Better Text Widget
 * Plugin URI: http://ran.ge/wordpress-plugin/better-text-widget/
 * Description: Improves text widget by adding a class to each instance based off title
 * Version: 1.1.1
 * Author: Aaron D. Campbell
 * Author URI: http://ran.ge/
 * License: GPLv2 or later
 */

function better_text_widget_widgets_init() {
	class wpBetterTextWidget extends WP_Widget_Text {
		function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$additional_class = sanitize_title_with_dashes( $title );
			$args['before_widget'] = str_replace( 'widget_text', 'widget_text ' . $additional_class, $args['before_widget'] );
			parent::widget( $args, $instance );
		}
	}

	register_widget( 'wpBetterTextWidget' );
}
add_action( 'widgets_init', 'better_text_widget_widgets_init' );

/**
 * Convert existing Better Text Widgets to regular text widgets
 */
function better_text_widgets_convert_widgets() {
	$better_text_widget = get_option( 'better_text_widget' );
	if ( ! empty( $better_text_widget ) ) {
		$replace_array = array();
		$widget_text = get_option( 'widget_text' );

		$id = max( array_keys( $widget_text ) );

		foreach ( $better_text_widget as $btw_id => $btw ) {
			$widget_text[++$id] = $btw;
			$replace_array['better-text-' . $btw_id] = 'text-' . $id;
		}

		$sidebars_widgets = get_option( 'sidebars_widgets' );
		foreach ( $sidebars_widgets as $sidebar => &$widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as &$w ) {
					$w = str_replace( array_keys( $replace_array ), $replace_array, $w );
				}
			}
		}
		update_option( 'sidebars_widgets', $sidebars_widgets );
		update_option( 'widget_text', $widget_text );
		update_option( 'better_text_widget', '' );
	}
}
add_action( 'init', 'better_text_widgets_convert_widgets' );

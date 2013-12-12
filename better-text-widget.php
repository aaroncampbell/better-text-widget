<?php
/**
 * Plugin Name: Better Text Widget
 * Plugin URI: http://ran.ge/wordpress-plugin/better-text-widget/
 * Description: Improves text widget by adding a class to each instance based off title
 * Version: 1.1.0
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

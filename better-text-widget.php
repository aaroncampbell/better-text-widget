<?php
/**
 * Plugin Name: Better Text Widget
 * Plugin URI: http://xavisys.com/2009/04/wordpress-text-widget
 * Description: An improved text widget that adds a class to each instance (based off title)
 * Version: 1.0.0
 * Author: Aaron D. Campbell
 * Author URI: http://xavisys.com/
 */

/*  Copyright 2006  Aaron D. Campbell  (email : wp_plugins@xavisys.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/**
 * wpBetterTextWidget is the class that handles ALL of the plugin functionality.
 * It helps us avoid name collisions
 * http://codex.wordpress.org/Writing_a_Plugin#Avoiding_Function_Name_Collisions
 */

class wpBetterTextWidget
{
	/**
	 * Displays the better text widge
	 *
	 * @param array $args - Widget Settings
	 * @param array|int $widget_args - Widget Number
	 */
	public function display($args, $widget_args = 1) {
		extract( $args, EXTR_SKIP );
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('better_text_widget');
		if ( !isset($options[$number]) )
			return;

		$title = apply_filters('widget_title', $options[$number]['title']);
		$text = apply_filters( 'widget_text', $options[$number]['text'] );
	?>
			<?php echo $before_widget; ?>
				<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
				<div class="textwidget"><?php echo $text; ?></div>
			<?php echo $after_widget; ?>
	<?php
	}

	/**
	 * Sets up admin forms to manage widgets
	 *
	 * @param array|int $widget_args - Widget Number
	 */
	public function control($widget_args) {
		global $wp_registered_widgets;
		static $updated = false;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('better_text_widget');
		if ( !is_array($options) )
			$options = array();

		if ( !$updated && !empty($_POST['sidebar']) ) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( $this_sidebar as $_widget_id ) {
				if ( array( $this, 'display' ) == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "better-text-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed.
						unset($options[$widget_number]);
				}
			}

			foreach ( (array) $_POST['better-text-widget'] as $widget_number => $widget_text ) {
				if ( !isset($widget_text['text']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;
				$title = strip_tags(stripslashes($widget_text['title']));
				if ( current_user_can('unfiltered_html') )
					$text = stripslashes( $widget_text['text'] );
				else
					$text = stripslashes(wp_filter_post_kses( $widget_text['text'] ));
				$options[$widget_number] = compact( 'title', 'text' );
			}

			update_option('better_text_widget', $options);
			$updated = true;
		}

		if ( -1 == $number ) {
			$title = '';
			$text = '';
			$number = '%i%';
		} else {
			$title = attribute_escape($options[$number]['title']);
			$text = format_to_edit($options[$number]['text']);
		}
	?>
			<p>
				<input class="widefat" id="better-text-title-<?php echo $number; ?>" name="better-text-widget[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
				<textarea class="widefat" rows="16" cols="20" id="better-text-text-<?php echo $number; ?>" name="better-text-widget[<?php echo $number; ?>][text]"><?php echo $text; ?></textarea>
				<input type="hidden" name="better-text-widget[<?php echo $number; ?>][submit]" value="1" />
			</p>
	<?php
	}

	/**
	 * Registers widget in such a way as to allow multiple instances of it
	 *
	 * @see wp-includes/widgets.php
	 */
	public function register() {
		if ( !$options = get_option('better_text_widget') )
			$options = array();
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML with classname'));
		$control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'better-text');
		$name = __('Better Text');

		$id = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['title']) || !isset($options[$o]['text']) )
				continue;
			$id = "better-text-$o"; // Never never never translate an id
			$ops = $widget_ops;
			$ops['classname'] .= ' ' . sanitize_title_with_dashes($options[$o]['title']);
			wp_register_sidebar_widget($id, $name, array( $this, 'display' ), $ops, array( 'number' => $o ));
			wp_register_widget_control($id, $name, array( $this, 'control' ), $control_ops, array( 'number' => $o ));
		}

		// If there are none, we register the widget's existance with a generic template
		if ( !$id ) {
			wp_register_sidebar_widget( 'better-text-1', $name, array( $this, 'display' ), $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'better-text-1', $name, array( $this, 'control' ), $control_ops, array( 'number' => -1 ) );
		}
	}
}
// Instantiate our class
$wpBetterTextWidget = new wpBetterTextWidget();

/**
 * Add filters and actions
 */
add_action('widgets_init', array($wpBetterTextWidget, 'register'));

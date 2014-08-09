<?php
/*
 * Plugin Name: Frontend Widget Editor
 * Plugin URI: trepmal.com
 * Description:
 * Version: 0.0.0
 * Author: Kailey Lampert
 * Author URI: kaileylampert.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * TextDomain: fwe
 * DomainPath:
 * Network:
 */

$frontend_widget_editor = new Frontend_Widget_Editor;

class Frontend_Widget_Editor {

	function __construct() {
		add_action( 'dynamic_sidebar',            array( $this, 'dynamic_sidebar' ) );
		add_action( 'wp_ajax_fwe_widget_edit',    array( $this, 'widget_edit' ) );
		add_action( 'wp_ajax_fwe_refresh_widget', array( $this, 'refresh_widget' ) );
	}

	function dynamic_sidebar( $widget ) {
		if ( is_admin() ) return;
		if ( ! current_user_can( 'edit_theme_options' ) ) return;

		wp_enqueue_script( 'widget-editor', plugins_url( 'widget-editor.js', __FILE__ ), array('jquery'), 1, true );
		wp_enqueue_style( 'widget-editor', plugins_url( 'widget-editor.css', __FILE__ ) );

		echo "<a href='#' class='widget-edit' data-widget-id='{$widget['id']}'>". __( 'Edit this', 'fwe' ) ."</a>";
	}

	function widget_edit() {
		ob_start();
		global $sidebars_widgets, $wp_registered_widgets, $wp_registered_widget_controls;

		$widget_id = $_POST['widget_id'];
		$widget = $wp_registered_widgets[ $widget_id ];

		foreach ( $sidebars_widgets as $sidebar_id => $sidebar_widgets ) {
			if ( in_array( $widget_id, $sidebar_widgets ) ) {
				$sidebar = $sidebar_id;
				$position = array_search( $widget_id, $sidebar_widgets ) + 1;
				break;
			}
		}


		echo '<div class="widget-edit-wrapper">';
		echo '<div class="widget-edit-inner">';

		echo "<a href='#' class='widget-edit-close' title=". __('Close', 'fwe') .">&times;</a>";

		echo '<form class="widget-edit-form" action="'. admin_url( 'widgets.php' ) .'" method="post">';
		call_user_func_array( $wp_registered_widget_controls[ $widget_id ]['callback'], $wp_registered_widget_controls[ $widget_id ]['params'] );
		echo '<input type="hidden" name="sidebar" class="sidebar" value="'. esc_attr($sidebar) .'" />';
		echo '<input type="hidden" name="'. esc_attr($sidebar) .'_position" class="sidebar-position" value="'. esc_attr($position) .'" />';
		echo '<input type="hidden" name="widget-id" class="widget-id" value="'. esc_attr($widget_id) .'" />';
		echo '<input type="hidden" name="id_base" class="id_base" value="'. esc_attr($widget['callback'][0]->id_base) .'" />';
		echo '<input type="hidden" name="multi_number" class="multi_number" value="'. esc_attr($wp_registered_widget_controls[ $widget_id ]['params'][0]['number']) .'" />';
		wp_nonce_field( 'save-sidebar-widgets', 'savewidgets' );
		echo '<input type="submit" name="savewidget" id="savewidget" class="button button-primary alignright" value="'. __( 'Save Widget', 'fwe' ) .'"  /></form>';
		echo '</div>';
		echo '</div>';

		$html = ob_get_clean();
		wp_send_json_success( $html );
	}

	function refresh_widget() {
		$widget_id = $_POST['widget_id'];
		$sidebar_id = $_POST['sidebar_id'];
		global $wp_registered_widgets, $wp_registered_sidebars;

		ob_start();
		$id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;
		$number = preg_replace( "#^{$id_base}-#", '', $widget_id );
		$instances = get_option( $wp_registered_widgets[ $widget_id ]['callback'][0]->option_name );
		$instance = $instances[ $number ];
		the_widget( get_class( $wp_registered_widgets[ $widget_id ]['callback'][0] ), $instance, $wp_registered_sidebars[$sidebar_id] );
		$html = ob_get_clean();
		wp_send_json_success( $html );
	}

}

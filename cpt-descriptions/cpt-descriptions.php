<?php
/*
Plugin Name: CPT Descriptions
Version: 0.2
Plugin URI: http://vanpop.com/cpt-descriptions/
Description: Adds a place to enter a description for your custom post types which you can display anywhere in your template.
Author: Evan Stein
Author URI: http://vanpop.com/
License: GPL v3

CPT Descriptions
Copyright (C) 2013, Evan Stein - admin@vanpop.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

add_action( 'admin_init', 'post_type_desc_register_settings' );
add_action( 'admin_menu' , 'post_type_desc_enable_pages' );

function cptd_get_post_types() {
  $args = array(
    'public'   => true,
    '_builtin' => false
  );
  $post_types = apply_filters( 'cptd_post_types', get_post_types( $args ) );

  return $post_types;
}

function post_type_desc_register_settings() {
  $post_types = cptd_get_post_types();

  foreach ( $post_types as $post_type ) {
    if( post_type_exists( $post_type ) ) {
      // Register settings and call sanitation functions
      register_setting( 'post_type_desc' . $post_type, 'post_type_desc_' . $post_type, 'post_type_desc_validate_options' );
    }
  }
}

function post_type_desc_enable_pages() {
  $post_types = cptd_get_post_types();

  foreach ( $post_types as $post_type ) {
    if( post_type_exists( $post_type ) ) {
      $args = array(
        'name' => $post_type,
      );
      $post_type_info = get_post_types( $args, 'objects' );

      if ( $post_type == 'post' ) {
        // for historical reasons, the builtin 'post' post-type doesn't use an edit.php slug
        add_submenu_page( 'edit.php', $post_type_info[$post_type]->labels->name . ' Custom Post Type Description', 'Description', 'edit_posts', urlencode( $post_type_info[$post_type]->name ) . '-description', 'post_type_desc_page' );
      } else {
        add_submenu_page( 'edit.php?post_type=' . $post_type, $post_type_info[$post_type]->labels->name . ' Custom Post Type Description', 'Description', 'edit_posts', urlencode( $post_type_info[$post_type]->name ) . '-description', 'post_type_desc_page' );
      }
    }
  }
}

function post_type_desc_page() {
  global $post_type_desc;
  $screen = get_current_screen();
  $post_type = $screen->post_type;
  $args = array(
    'name' => $post_type,
  );
  $post_type_info = get_post_types( $args, 'objects' );

  if ( ! isset( $_REQUEST['settings-updated'] ) )
    $_REQUEST['settings-updated'] = false; // This checks whether the form has just been submitted. ?>

<div class="wrap">
  <?php  screen_icon(); echo "<h2>" . __( 'Description of the ' ) . $post_type_info[$post_type]->labels->name . " Custom Post Type</h2>"; ?>

  <?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
    <div class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
  <?php endif; // If the form has just been submitted, this shows the notification ?>

    <form method="post" action="options.php">
      <?php $settings = get_option( 'post_type_desc_'.$post_type, $post_type_desc ); ?>
      <?php settings_fields( 'post_type_desc'.$post_type ); ?>
      <?php
      $editor_settings = array(
        'textarea_name' => 'post_type_desc_' . $post_type . '[description]',
        'textarea_rows' => 15,
        'tabindex' => 1,
        'media_buttons' => false,
      );
      wp_editor( stripslashes( $settings['description'] ), 'description', $editor_settings );
      ?>

        <p class="submit"><input type="submit" class="button-primary" value="Save Description" /></p>
    </form>
</div>
<?php
}

function post_type_desc_validate_options( $input ) {
  // strip all tags from the text field, to avoid vulnerablilties like XSS
  $input['description'] = wp_kses_post( $input['description'] );
  return $input;
}

function the_post_type_description( $post_type = '' ) {
  if ( '' == $post_type )
    global $post_type;
  $post_type_x = get_option( 'post_type_desc_' . $post_type );
  echo wpautop( $post_type_x['description'] );
}

function get_post_type_description( $post_type = '' ) {
  if ( '' == $post_type )
    global $post_type;
  $post_type_x = get_option( 'post_type_desc_' . $post_type );
  return wpautop( $post_type_x['description'] );
}

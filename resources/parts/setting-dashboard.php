<?php

defined( 'ABSPATH' ) || exit;

$plugin_prefix = self::PLUGIN_PREFIX;

if( ! empty( $_POST ) && ! empty( $_POST['setting']['header'] ) && ! empty( $_POST['setting']['footer'] ) && check_admin_referer( $plugin_prefix . 'setting', $plugin_prefix . 'nonce' ) && current_user_can( 'activate_plugins' ) ) {

  $post_setting = array(

    $plugin_prefix . 'header_hide_update' => stripslashes_deep( $_POST['setting']['header']['hide_update'] ),

    $plugin_prefix . 'footer_text'        => stripslashes_deep( $_POST['setting']['footer']['text'] ),

  );

  try {
    $option = $this->option;
    $option = wp_parse_args( $post_setting, $option );
    $this->option = $option;
    update_option( self::PLUGIN_OPTION, $option );

    add_settings_error( $plugin_prefix . 'error', $plugin_prefix . 'error', __( 'Saved', 'jr_translation' ), 'updated' );
  } catch (Exception $e) {
    add_settings_error( $plugin_prefix . 'error', $plugin_prefix . 'error', $e->getMessage(), 'error' );
  }

  settings_errors( $plugin_prefix . 'error' );

}

//--------------------------------------------------------------------------------------------------
//  Admin Page
//--------------------------------------------------------------------------------------------------

?>
<div class="wrap">
  <h2><?php echo __( 'Dashboard', 'jr_translation' ) . ' ' . __( 'Customize', 'jr_translation' ); ?></h2>
  <form action="" method="post">
<?php

  $form_tables = array(

    array(
      'heading' => __( 'Header', 'jr_translation' ),
      'row' => array(
        array(
          'type'  => 'checkbox',
          'label' => __( 'Hide update information', 'jr_translation' ),
          'name'  => array( 'header', 'hide_update' ),
        ),
      ),
    ),

    array(
      'heading' => __( 'Footer', 'jr_translation' ),
      'row' => array(
        array(
          'type'  => 'textarea',
          'label' => __( 'Footer text', 'jr_translation' ),
          'name'  => array( 'footer', 'text' ),
        ),
      ),
    ),

  );
  self::_render_form_tabls( $form_tables, $plugin_prefix );

  wp_nonce_field( $plugin_prefix . 'setting', $plugin_prefix . 'nonce' );
  echo( PHP_EOL );

  submit_button();
  echo( PHP_EOL );

?>
  </form>
</div>
<?php

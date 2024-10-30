<?php

defined( 'ABSPATH' ) || exit;

$plugin_prefix = self::PLUGIN_PREFIX;

if( ! empty( $_POST ) && ! empty( $_POST['setting'] ) && check_admin_referer( $plugin_prefix . 'setting', $plugin_prefix . 'nonce' ) && current_user_can( 'activate_plugins' ) ) {

  $post_setting = array();

  $count = 0;
  foreach( $_POST['setting'] as $value ) {
    if( empty( $value['view'] ) && empty( $value['title'] ) && empty( $value['content'] ) )
      continue;

    if( isset( $value['delete'] ) && boolval( $value['delete'] ) )
      continue;

    $count++;

    $post_setting[$plugin_prefix.$count.'_view']    = stripslashes_deep( $value['view'] );
    $post_setting[$plugin_prefix.$count.'_title']   = stripslashes_deep( $value['title'] );
    $post_setting[$plugin_prefix.$count.'_content'] = stripslashes_deep( $value['content'] );
  }
  $post_setting[$plugin_prefix.'max_content'] = stripslashes_deep( $count );

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

$option = $this->option;

//--------------------------------------------------------------------------------------------------
//  Admin Page
//--------------------------------------------------------------------------------------------------

?>
<div class="wrap">
  <h2><?php echo __( 'Dashboard', 'jr_translation' ) . ' ' . __( 'Add widgets', 'jr_translation' ); ?></h2>
  <form action="" method="post">
<?php
  $max_content = isset( $option[$plugin_prefix.'max_content'] ) ? intval( $option[$plugin_prefix.'max_content'] ) : 0;
  for( $i = 1; $i <= $max_content; $i++ ) {
    if( empty( $option[$plugin_prefix.$i.'_view'] ) && empty( $option[$plugin_prefix.$i.'_title'] ) && empty( $option[$plugin_prefix.$i.'_content'] ) )
      continue;
?>
    <h3><?php echo __( 'Dashboard', 'jr_translation' ), ' ', $i; ?></h3>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'view', '_', $i; ?>"><?php _e( 'Enabled', 'jr_translation' ); ?></label></th>
          <td>
            <input type="hidden" name="setting[<?php echo $i; ?>][view]" value="0">
            <input type="checkbox" name="setting[<?php echo $i; ?>][view]" id="<?php echo 'view', '_', $i; ?>" value="1"<?php if( ! empty( $option[$plugin_prefix.$i.'_view'] ) ) echo ' checked'; ?>>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'title', '_', $i; ?>"><?php _e( 'Title', 'jr_translation' ); ?></label></th>
          <td><input type="text" name="setting[<?php echo $i; ?>][title]" id="<?php echo 'title', '_', $i; ?>" value="<?php if( ! empty( $option[$plugin_prefix.$i.'_title'] ) ) echo esc_textarea( $option[$plugin_prefix.$i.'_title'] ); ?>" size="50"></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'content', '_', $i; ?>"><?php _e( 'Content', 'jr_translation' ); ?></label></th>
          <td><textarea name="setting[<?php echo( $i ); ?>][content]" id="<?php echo 'content', '_', $i; ?>" cols="100" rows="10" ><?php if( ! empty( $option[$plugin_prefix.$i.'_content'] ) ) echo esc_textarea( $option[$plugin_prefix.$i.'_content'] ); ?></textarea></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'delete', '_', $i; ?>"><?php _e( 'Delete', 'jr_translation' ); ?></label></th>
          <td>
            <input type="hidden" name="setting[<?php echo $i; ?>][delete]" value="0">
            <input type="checkbox" name="setting[<?php echo $i; ?>][delete]" id="<?php echo 'delete', '_', $i; ?>" value="1">
          </td>
        </tr>
      </tbody>
    </table>
<?php } $i = $max_content + 1; ?>
    <h3><?php echo __( 'Dashboard', 'jr_translation' ), ' ', $i; ?></h3>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'view', '_', $i; ?>"><?php _e( 'Enabled', 'jr_translation' ); ?></label></th>
          <td>
            <input type="hidden" name="setting[<?php echo $i; ?>][view]" value="0">
            <input type="checkbox" name="setting[<?php echo $i; ?>][view]" id="<?php echo 'view', '_', $i; ?>" value="1">
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'title', '_', $i; ?>"><?php _e( 'Title', 'jr_translation' ); ?></label></th>
          <td><input type="text" name="setting[<?php echo $i; ?>][title]" id="<?php echo 'title', '_', $i; ?>" value="" size="50"></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo 'content', '_', $i; ?>"><?php _e( 'Content', 'jr_translation' ); ?></label></th>
          <td><textarea name="setting[<?php echo( $i ); ?>][content]" id="<?php echo 'content', '_', $i; ?>" cols="100" rows="10" ></textarea></td>
        </tr>
      </tbody>
    </table>
<?php
  wp_nonce_field( $plugin_prefix . 'setting', $plugin_prefix . 'nonce' );
  echo( PHP_EOL );

  submit_button();
  echo( PHP_EOL );
?>
  </form>
</div>
<?php

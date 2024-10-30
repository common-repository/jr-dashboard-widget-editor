<?php

/*
 * Version:     0.3.0
 *
 * Plugin Name: JR : Dashboard Widget Editor
 * Plugin URI:
 *
 * Description: You can add widgets to the dashboard.
 *
 * Author:      Jack Russell
 * Author URI:  https://tekuaru.jack-russell.jp/
 *
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: jr_translation
 * Domain Path: /resources/languages/
 *
 * Requires PHP:  7.2
 *
 */

defined( 'ABSPATH' ) || exit;

//====================================================================================================================================================
//  Class 定義
//====================================================================================================================================================

if( ! class_exists( 'jr_dashboard_widget_editor' ) ) {
  class jr_dashboard_widget_editor {

    const PLUGIN_SLUG   = 'jr-bootstrap_plugin';
    const PLUGIN_OPTION = 'jr_bootstrap_plugin_option';

    const PLUGIN_PREFIX = 'dashboard_widget_editor_';

    protected $option = array();

    function __construct() {

      add_action( 'init', array( $this, 'load_textdomain' ) );

      $plugin_prefix = self::PLUGIN_PREFIX;

      $option_get = get_option( self::PLUGIN_OPTION );
      $this->option = wp_parse_args( $option_get, self::defaults() );
      $option = $this->option;

      // -----------------------------------------------

      add_action( 'admin_menu', array( $this, 'add_pages' ) );

      add_action( 'admin_init', array( $this, 'the_hide_update' ) );

      add_action( 'wp_dashboard_setup', array( $this, 'the_widget' ) );

      add_action( 'admin_footer_text', array( $this, 'the_footer_text' ) );

    }

//--------------------------------------------------------------------------------------------------
//  基本設定 : デフォルト値
//--------------------------------------------------------------------------------------------------

    static function defaults() {

      $plugin_prefix = self::PLUGIN_PREFIX;

      $defaults = array(

        $plugin_prefix . 'header_hide_update' => false,

        $plugin_prefix . 'footer_text'        => '',

      );

      return $defaults;
    }

//--------------------------------------------------------------------------------------------------
//  基本設定 : プラグイン用のMOファイルをロード
//--------------------------------------------------------------------------------------------------

    function load_textdomain() {
      load_plugin_textdomain( 'jr_translation', false, plugin_basename( dirname( __FILE__ ) ) . '/resources/languages' );
    }

//--------------------------------------------------------------------------------------------------
//  管理画面 : 設定
//--------------------------------------------------------------------------------------------------

    function add_pages() {
      global $menu;

      $plugin_slug = self::PLUGIN_SLUG;

      $menu_switch = false;
      foreach( $menu as $sub_menu ) {
        if( in_array( $plugin_slug , $sub_menu ) ) {
          $menu_switch = true;
          break;
        }
      }

      if( $menu_switch ) {
        add_submenu_page(
          $plugin_slug,
          __( 'Dashboard', 'jr_translation' ) . ' ' . __( 'Customize', 'jr_translation' ),
          __( 'Dashboard', 'jr_translation' ) . '<br>' . __( '- ', 'jr_translation' ) . __( 'Customize', 'jr_translation' ),
          'activate_plugins',
          $plugin_slug . '-dashboard',
          function() {
            require_once( implode( DIRECTORY_SEPARATOR, array( 'resources', 'parts', 'setting-dashboard.php' ) ) );
          }
        );
        add_submenu_page(
          $plugin_slug,
          __( 'Dashboard', 'jr_translation' ) . ' ' . __( 'Add widgets', 'jr_translation' ),
          __( 'Dashboard', 'jr_translation' ) . '<br>' . __( '- ', 'jr_translation' ) . __( 'Add widgets', 'jr_translation' ),
          'activate_plugins',
          $plugin_slug . '-dashboard_widget',
          function() {
            require_once( implode( DIRECTORY_SEPARATOR, array( 'resources', 'parts', 'setting-widget.php' ) ) );
          }
        );
      } else {
        add_options_page(
          __( 'Dashboard', 'jr_translation' ) . ' ' . __( 'Customize', 'jr_translation' ),
          __( 'Dashboard', 'jr_translation' ) . '<br>' . __( '- ', 'jr_translation' ) . __( 'Customize', 'jr_translation' ),
          'activate_plugins',
          $plugin_slug . '-dashboard',
          function() {
            require_once( implode( DIRECTORY_SEPARATOR, array( 'resources', 'parts', 'setting-dashboard.php' ) ) );
          }
        );
        add_options_page(
          __( 'Dashboard', 'jr_translation' ) . ' ' . __( 'Add widgets', 'jr_translation' ),
          __( 'Dashboard', 'jr_translation' ) . '<br>' . __( '- ', 'jr_translation' ) . __( 'Add widgets', 'jr_translation' ),
          'activate_plugins',
          $plugin_slug . '-dashboard_widget',
          function() {
            require_once( implode( DIRECTORY_SEPARATOR, array( 'resources', 'parts', 'setting-widget.php' ) ) );
          }
        );
      }
    }

//--------------------------------------------------------------------------------------------------
//  管理画面 : Dashboard : header
//--------------------------------------------------------------------------------------------------

    function the_hide_update() {
      $plugin_prefix = self::PLUGIN_PREFIX;

      $option = $this->option;

      if( $option[$plugin_prefix.'header_hide_update'] ) {
        remove_action( 'admin_notices',         'update_nag', 3 );
        remove_action( 'network_admin_notices', 'update_nag', 3 );
      }
    }

//--------------------------------------------------------------------------------------------------
//  管理画面 : Dashboard : widget
//--------------------------------------------------------------------------------------------------

    function the_widget() {
      $plugin_prefix = self::PLUGIN_PREFIX;

      $option = $this->option;

      $max_content = isset( $option[$plugin_prefix.'max_content'] ) ? intval( $option[$plugin_prefix.'max_content'] ) : 0;
      for( $i = 1; $i <= $max_content; $i++ ) {
        if( empty( $option[$plugin_prefix.$i.'_view'] ) && empty( $option[$plugin_prefix.$i.'_title'] ) && empty( $option[$plugin_prefix.$i.'_content'] ) )
          continue;

        if( $option[$plugin_prefix.$i.'_view'] ) {
          wp_add_dashboard_widget(
            'jr_'.$plugin_prefix.$i,
            $option[$plugin_prefix.$i.'_title'],
            function() use( $plugin_prefix, $option, $i ) {
              echo nl2br( $option[$plugin_prefix.$i.'_content'] );
            }
          );
        }
      }
    }

//--------------------------------------------------------------------------------------------------
//  管理画面 : Dashboard : footer
//--------------------------------------------------------------------------------------------------

    function the_footer_text( $text ) {
      $plugin_prefix = self::PLUGIN_PREFIX;

      $option = $this->option;

      if( ! empty( $option[$plugin_prefix.'footer_text'] ) ) {
        $text = $option[$plugin_prefix.'footer_text'];
      }

      return $text;
    }

//--------------------------------------------------------------------------------------------------
//
//--------------------------------------------------------------------------------------------------



//====================================================================================================================================================
//  関数
//====================================================================================================================================================

//--------------------------------------------------------------------------------------------------
//  Form 作成
//--------------------------------------------------------------------------------------------------

    function _render_form_tabls( $form_tables = array(), $prefix = '' ) {

      $separator = ':';

      $option = $this->option;

      $html = array();

      foreach( $form_tables as $form_table ) {

        if( isset( $form_table['heading'] ) ) {
          $heading = $form_table['heading'];
          if( isset( $form_table['add_heading'] ) ) {
            $heading .= ' ' . $separator . ' ' . $form_table['add_heading'];
          }
          $html[] = '<h3>' . $heading . '</h3>';
        }

        $html[] = '<table class="form-table">';
        $html[] = '<tbody>';

        foreach( $form_table['row'] as $row ) {

          $row_id = implode( '_', $row['name'] );
          $row_name = '[' . implode( '][', $row['name'] ) . ']';

          $html[] = '<tr valign="top">';

          switch( $row['type'] ) {

            case 'checkbox' :
              $html[] = '<th scope="row"><label for="' . esc_attr( $row_id ) . '">' . $row['label'] . '</label></th>';
              $html[] = '<td>';
              $html[] = '<input type="hidden" name="setting' . $row_name . '" value="0">';
              $html_tmp = '';
              $html_tmp .= '<input type="checkbox" name="setting' . $row_name . '" id="' . esc_attr( $row_id ) . '" value="1"';
                if( $option[ $prefix . $row_id ] ) $html_tmp .= ' checked';
                $html_tmp .= '>' . PHP_EOL;
                $html[] = $html_tmp;
              $html[] = '</td>';
              break;

            case 'select' :
              if( empty( $row['option'] ) ) {
                break;
              }
              $html[] = '<th scope="row"><label for="' . esc_attr( $row_id ) . '">' . $row['label'] . '</label></th>';
              $html[] = '<td>';
              $html[] = '<select name="setting' . $row_name . '">';
              foreach( $row['option'] as $key => $value ) {
                $html_tmp = '';
                $html_tmp .= '<option value="' . esc_attr( $key ) . '"';
                if( $key == $option[ $prefix . $row_id ] ) $html_tmp .= ' selected';
                $html_tmp .= '>' . $value . '</option>';
                $html[] = $html_tmp;
              }
              $html[] = '</select>';
              $html[] = '</td>';
              break;

            case 'text' :
              $html[] = '<th scope="row"><label for="' . esc_attr( $row_id ) . '">' . $row['label'] . '</label></th>';
              $html[] = '<td>';
              $html[] = '<input type="text" name="setting' . $row_name . '" id="' . esc_attr( $row_id ) . '" value="' . esc_attr( $option[ $prefix . $row_id ] ) . '" size="80">';
              $html[] = '</td>';
              break;

            case 'textarea' :
              $html[] = '<th scope="row"><label for="' . esc_attr( $row_id ) . '">' . $row['label'] . '</label></th>';
              $html[] = '<td>';
              $html[] = '<textarea name="setting' . $row_name . '" id="' . esc_attr( $row_id ) . '" cols="80" rows="10">' . esc_textarea( $option[ $prefix . $row_id ] ) . '</textarea>';
              $html[] = '</td>';
              break;

          }
          $html[] = '</tr>';
        }

        $html[] = '</tbody>';
        $html[] = '</table>';

      }

      echo( implode( PHP_EOL, $html ) );

    }

//--------------------------------------------------------------------------------------------------
//
//--------------------------------------------------------------------------------------------------

  }

  if( is_admin() ) {
    new jr_dashboard_widget_editor;
  }

}

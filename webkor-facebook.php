<?php
/*
Plugin Name: Webkor Facebook Box
Description: Plugin adds fancy Facebook Page Plugin (formerly Like Box) slider
Author: Adrian Korzan
Version: 1.0.0
License: GPLv2 or later
Text Domain: webkorfacebook
*/

defined('ABSPATH') or exit();
include_once('WkfbBox.php');

// 'Plugin dodaje box ze stroną Facebookową (oś czasu, wydarzenia, wiadomości)';

if ( !class_exists( 'WebkorFbBox' ) ) {

  define('WKFBOX_VERSION', '1.0.0');

  $plugin_url = plugin_dir_url( __FILE__ );

  if( is_ssl() ) {
    $plugin_url = str_replace('http://', 'https://', $plugin_url);
  }

  define('WKFBOX_URL', $plugin_url );
  define('WKFBOX_ADMIN_URL', 'themes.php?page=' . basename( __FILE__ ) );

  /**
  * Class for add and configure Facebook Block on Wordpress
  * @author Adrian Korzan (Webkor) <adrian.korzan@gmail.com>
  */
  class WebkorFbBox {

    /**
    * @var Array Config Array
    */
    public $cf = [];

    /**
     * @var Array admin messages
     */
    private $messages = [];

    /**
     * @var Array admin errors
     */
    private $errors = [];

    /**
     * Constructor
     */
    public function __construct() {

      /* Configuration */
      $this->settings();

      add_action( 'admin_menu',               array( &$this, 'adminMenu'));
      add_action( 'wp_footer',                array( &$this, 'get_html' ), 21 );
      add_action( 'admin_enqueue_scripts',    array( &$this, 'admin_scripts') );
      add_action( 'wp_ajax_afbl_hide_notice', array( &$this, 'admin_notices_handle') );
      add_action( 'wp_enqueue_scripts',       array( &$this, 'init_scripts') );
      add_filter( 'plugin_action_links',      array( &$this, 'settings_link' ), 10, 2);

      register_uninstall_hook( __FILE__, array( 'WebkorFbBox', 'uninstall' ) );
    }

    public function settings() {

      /* Defaults */
      $cf_default = [
        'aspexifblikebox_version' => WKFBOX_VERSION,
        'url'         => '',
        'locale'      => 'pl_PL',
        'status'      => 'enabled',
        'page_plugin' => 'enabled',
        'hide_notice' => '0',
        'height'      => 234,
        'width'       => 245,
        'adaptative'  => false,
        'friendFaces' => true,
        'showPosts'   => true,
        'hideCta'     => false,
        'hideCover'   => false,
        'timeLine'    => 'timeline,',
        'messages'    => 'messages',
        'events'      => 'events',
        'fbIcon'      => 'fb2-top.png',
        'edgeSpace'   => 0,
        'iconVertical' => 'top',
        'iconVerticalConst' => 0
      ];

      if ( !get_option( 'aspexifblikebox_options' ) )
          add_option( 'aspexifblikebox_options', $cf_default, '', 'yes' );

        $this->cf = get_option( 'aspexifblikebox_options' );
    }

    
    public function settings_link( $action_links, $plugin_file ){
      if( $plugin_file == plugin_basename(__FILE__) ) {
        $settings_link = '<a href="themes.php?page=' . basename( __FILE__ )  .  '">'.__('Settings').'</a>';
        array_unshift( $action_links, $settings_link );
      }
      return $action_links;
    }

    /**
    * @param String $message 
    */
    private function addMessage( String $message = '' ): void 
    {
      $message = trim( $message );

      if( strlen( $message ) )
          $this->messages[] = $message;
    }

    /**
    * @param String $error 
    */
    private function addError( String $error = 'Błąd' ): void 
    {
        $error = trim( $error );

        if( strlen( $error ) )
            $this->errors[] = $error;
    }

    /**
     * @return String
     */
    public function hasErrors(): Int 
    {
      return count( $this->errors );
    }

    /**
    * Display admin notices
    * @param Boolean
    * @return String
    */
    public function displayAdminNotices( $echo = false )
    {
      $ret = '';

      foreach( (array)$this->errors as $error ) {
        $ret .= '<div class="error fade"><p><strong>'.$error.'</strong></p></div>';
      }

      foreach( (array)$this->messages as $message ) {
        $ret .= '<div class="updated fade"><p><strong>'.$message.'</strong></p></div>';
      }

      if( $echo )
        echo $ret;
      else
        return $ret;
    }

    /**
     * Add submenu
     */
    public function adminMenu() {
      add_submenu_page( 'themes.php', 'Facebook Like Box', 'Facebook Like Box', 'manage_options', basename(__FILE__), array( &$this, 'adminPage') );
    }

    public function adminPage() {

      !current_user_can('manage_options') ? wp_die( __('You do not have sufficient permissions to access this page')) : null;

      $preview = false;

      if( isset( $_REQUEST['pp'] ) && check_admin_referer( 'page_plugin_toggle' ) ) {
        if( 'enabled' == $this->is_pp() && 'disable' == $_REQUEST['pp']) {
          $this->cf['page_plugin'] = 'disabled';
          update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
        }
      }

    // request action
      if ( isset( $_REQUEST['wkfbSaveAction'] ) ) {

        if( !in_array( $_REQUEST['wkfb_status'], array('enabled','disabled') ) )
          $this->addError( 'Błędny status. Box powinien mieć status aktywny/nieaktywny' );

        if( !$this->hasErrors() ) {
          $wkfbRequestOptions = array();

          $wkfbRequestOptions['url']                = isset( $_REQUEST['wkfb_url'] ) ? trim( $_REQUEST['wkfb_url'] ) : '';
          $wkfbRequestOptions['locale']             = isset( $_REQUEST['wkfb_locale'] ) ? $_REQUEST['wkfb_locale'] : '';
          $wkfbRequestOptions['status']             = isset( $_REQUEST['wkfb_status'] ) ? $_REQUEST['wkfb_status'] : '';
          $wkfbRequestOptions['height']             = isset( $_REQUEST['wkfb_height'] ) ? $_REQUEST['wkfb_height'] : 234;
          $wkfbRequestOptions['width']              = isset( $_REQUEST['wkfb_width'] ) ? $_REQUEST['wkfb_width'] : 245;
          $wkfbRequestOptions['adaptative']         = isset( $_REQUEST['wkfb_adaptive_width'] ) ? 'true' : 'false';
          $wkfbRequestOptions['friendsFaces']       = isset( $_REQUEST['wkfb_faces'] ) ? 'true' : 'false';
          $wkfbRequestOptions['showPosts']          = isset( $_REQUEST['wkfb_stream'] ) ? 'true' : 'false';
          $wkfbRequestOptions['hideCta']            = isset( $_REQUEST['wkfb_cta'] ) ? 'true' : 'false';
          $wkfbRequestOptions['hideCover']          = isset( $_REQUEST['wkfb_header'] ) ? 'true' : 'false';
          $wkfbRequestOptions['smallHeader']        = isset( $_REQUEST['wkfb_small_header'] ) ? 'true' : 'false';
          $wkfbRequestOptions['timeLine']           = isset( $_REQUEST['wkfb_timeline'] ) ? 'timeline,' : '';
          $wkfbRequestOptions['messages']           = isset( $_REQUEST['wkfb_messages'] ) ? 'messages,' : '';
          $wkfbRequestOptions['events']             = isset( $_REQUEST['wkfb_events'] ) ? 'events' : '';
          $wkfbRequestOptions['fbIcon']             = isset( $_REQUEST['wkfb_btimage'] ) ? $_REQUEST['wkfb_btimage'] : 'fb2-top.png';
          $wkfbRequestOptions['edgeSpace']          = isset( $_REQUEST['wkfb_btspace'] ) ? $_REQUEST['wkfb_btspace'] : 0;
          $wkfbRequestOptions['iconVertical']       = isset( $_REQUEST['wkfb_btvertical'] ) ? $_REQUEST['wkfb_btvertical'] : 'top';
          $wkfbRequestOptions['iconVerticalConst']  = isset( $_REQUEST['wkfb_btvertical_val'] ) ? $_REQUEST['wkfb_btvertical_val'] : 0;
          
          $this->cf = array_merge( (array)$this->cf, $wkfbRequestOptions );
          
          update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
          $this->addMessage( 'Ustawienia zostały zapisane' );
        }

        if( @$_REQUEST['preview'] )
          $preview = true;
        else
          $preview = false;
      }

      $locales = ['Polish' => 'pl_PL'];

      $locales_input = '<select name="wkfb_locale">';

      foreach( $locales as $k => $v ) {
          $locales_input .= '<option value="'.$v.'"'.( ( $this->cf['locale'] == $v ) ? ' selected="selected"' : '' ).'>'.$k.'</option>';
      }

      $locales_input .= '</select>';

      include_once('adminFormTemplate.php');
    ?>

    <?php
      if( $preview ) {
        $this->init_scripts();
        $this->get_html($preview);
      }
    }

    public function get_html( $preview = false ) {
      $wkfbBox = new WkfbBox($this->cf);
      $wkfbBox->frontView();
    }
    
      public function init_scripts() {
          $width      = apply_filters( 'aspexifblikebox_width', $this->cf['width'] );
          $placement  = 'right';
          $slideon    = 'hover';
          $ismobile   = wp_is_mobile();

          wp_enqueue_script( 'aspexi-facebook-like-box', WKFBOX_URL . 'js/aflb.js', array( 'jquery' ), false, true );
          wp_localize_script( 'aspexi-facebook-like-box', 'aflb', array(
              'slideon'   => $slideon,
              'placement' => $placement,
              'width'     => (int)$width,
              'ismobile'  => $ismobile
          ) );
      }

      public static function uninstall() {

          delete_option( 'aspexifblikebox_options' );
      }

      public function admin_scripts() {
          
          wp_enqueue_script( 'aspexi-facebook-like-box-admin', WKFBOX_URL . 'js/aflb-admin.js', array( 'jquery' ), false, true );

          wp_localize_script( 'aspexi-facebook-like-box-admin', 'aflb_admin', array(
            'nonce'   => wp_create_nonce( "afblhidenotice-nonce" )
          ) );

          return;
      }

      public function extras_qtranslate_detect() {
          global $q_config;
          return (isset($q_config) && !empty($q_config));
      }

      // Check if Page Plugin is enabled
      public function is_pp() {

          return $this->cf['page_plugin'];
      }

      public function admin_notices_handle() {

          check_ajax_referer( 'afblhidenotice-nonce', 'nonce' );

          $this->cf['hide_notice'] = '1';

          update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );

          die();
      }
  }

  /* Let's start the show */
  global $aspexifblikebox;

  $aspexifblikebox = new WebkorFbBox();
}

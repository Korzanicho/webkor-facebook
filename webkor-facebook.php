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

__( 'Plugin adds fancy Facebook Page Plugin (formerly Like Box) slider', 'aspexifblikebox' );

if ( !class_exists( 'WebkorFbBox' ) ) {

    define('WKFBOX_VERSION', '1.0.0');

    $plugin_url = plugin_dir_url( __FILE__ );

    if( is_ssl() ) {
        $plugin_url = str_replace('http://', 'https://', $plugin_url);
    }

    define('WKFBOX_URL', $plugin_url );
    define('WKFBOX_ADMIN_URL', 'themes.php?page=' . basename( __FILE__ ) );

    class WebkorFbBox {

        public $cf          = array(); // config array
        private $messages   = array(); // admin messages
        private $errors     = array(); // admin errors

        public function __construct() {

            /* Configuration */
            $this->settings();

            add_action( 'admin_menu',           array( &$this, 'admin_menu')); //zrobione
            add_action( 'admin_notices',        array( &$this, 'admin_notices')); //zrobione
            add_action( 'init',                 array( &$this, 'init' ), 10 ); // NIE - tylko tłumaczenia
            add_action( 'wp_footer',            array( &$this, 'get_html' ), 21 );
            add_action( 'admin_enqueue_scripts',array( &$this, 'admin_scripts') );
            add_action( 'wp_ajax_afbl_hide_notice', array( &$this, 'admin_notices_handle') );
            add_action( 'wp_enqueue_scripts',   array( &$this, 'init_scripts') );
            add_filter( 'plugin_action_links',  array( &$this, 'settings_link' ), 10, 2);

            register_uninstall_hook( __FILE__, array( 'WebkorFbBox', 'uninstall' ) );
        }

        /* WP init action */
        public function init() {

            /* Internationalization */
            load_plugin_textdomain( 'aspexifblikebox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

            /* Exras */
            $this->extras_init();
        }

        public function settings() {

            /* Defaults */
            $cf_default = array(
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
                'events'      => 'events'
            );

            if ( !get_option( 'aspexifblikebox_options' ) )
                add_option( 'aspexifblikebox_options', $cf_default, '', 'yes' );

            $this->cf = get_option( 'aspexifblikebox_options' );

            /* Upgrade */
            if( $this->cf['aspexifblikebox_version'] != WKFBOX_VERSION ) {
                switch( $this->cf['aspexifblikebox_version'] ) {
                    case '1.0.0':
                        $this->cf['status'] = 'enabled';
                        $this->cf['aspexifblikebox_version'] = WKFBOX_VERSION;
                        update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                        break;
                    default:
                        $this->cf = array_merge( $cf_default, (array)$this->cf );
                        $this->cf['aspexifblikebox_version'] = WKFBOX_VERSION;
                        update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                }
            }
        }

        public function settings_link( $action_links, $plugin_file ){
            if( $plugin_file == plugin_basename(__FILE__) ) {

                $pro_link = $this->get_pro_link();
                array_unshift( $action_links, $pro_link );

                $settings_link = '<a href="themes.php?page=' . basename( __FILE__ )  .  '">' . __("Settings") . '</a>';
                array_unshift( $action_links, $settings_link );

            }
            return $action_links;
        }

        private function add_message( $message ) {
            $message = trim( $message );

            if( strlen( $message ) )
                $this->messages[] = $message;
        }

        private function add_error( $error ) {
            $error = trim( $error );

            if( strlen( $error ) )
                $this->errors[] = $error;
        }

        public function has_errors() {
            return count( $this->errors );
        }

        public function display_admin_notices( $echo = false ) {
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

        public function admin_menu() {
            add_submenu_page( 'themes.php', 'Facebook Like Box', 'Facebook Like Box', 'manage_options', basename(__FILE__), array( &$this, 'admin_page') );
        }

        public function admin_page() {

            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            $preview = false;

            if( isset( $_REQUEST['pp'] ) && check_admin_referer( 'page_plugin_toggle' ) ) {

                if( 'enabled' == $this->is_pp() && 'disable' == $_REQUEST['pp']) {
                    $this->cf['page_plugin'] = 'disabled';
                    update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                }
            }

            // request action
            if ( isset( $_REQUEST['wkfb_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'wkfb_nonce_name' ) ) {

                if( !in_array( $_REQUEST['wkfb_status'], array('enabled','disabled') ) )
                    $this->add_error( __( 'Wrong or missing status. Available statuses: enabled and disabled. Settings not saved.', 'aspexifblikebox' ) );

                if( !$this->has_errors() ) {
                    $aspexifblikebox_request_options = array();

                    $aspexifblikebox_request_options['url']           = isset( $_REQUEST['wkfb_url'] ) ? trim( $_REQUEST['wkfb_url'] ) : '';
                    $aspexifblikebox_request_options['locale']        = isset( $_REQUEST['wkfb_locale'] ) ? $_REQUEST['wkfb_locale'] : '';
                    $aspexifblikebox_request_options['status']        = isset( $_REQUEST['wkfb_status'] ) ? $_REQUEST['wkfb_status'] : '';
                    $aspexifblikebox_request_options['height']        = isset( $_REQUEST['wkfb_height'] ) ? $_REQUEST['wkfb_height'] : 234;
                    $aspexifblikebox_request_options['width']         = isset( $_REQUEST['wkfb_width'] ) ? $_REQUEST['wkfb_width'] : 245;
                    $aspexifblikebox_request_options['adaptative']    = isset( $_REQUEST['wkfb_adaptive_width'] ) ? 'true' : 'false';
                    $aspexifblikebox_request_options['friendsFaces']  = isset( $_REQUEST['wkfb_faces'] ) ? 'true' : 'false';
                    $aspexifblikebox_request_options['showPosts']     = isset( $_REQUEST['wkfb_stream'] ) ? 'true' : 'false';
                    $aspexifblikebox_request_options['hideCta']       = isset( $_REQUEST['wkfb_cta'] ) ? 'true' : 'false';
                    $aspexifblikebox_request_options['hideCover']     = isset( $_REQUEST['wkfb_header'] ) ? 'true' : 'false';
                    $aspexifblikebox_request_options['smallHeader']   = isset( $_REQUEST['wkfb_small_header'] ) ? 'true' : 'false';
                    $aspexifblikebox_request_options['timeLine']      = isset( $_REQUEST['wkfb_timeline'] ) ? 'timeline,' : '';
                    $aspexifblikebox_request_options['messages']     = isset( $_REQUEST['wkfb_messages'] ) ? 'messages,' : '';
                    $aspexifblikebox_request_options['events']        = isset( $_REQUEST['wkfb_events'] ) ? 'events' : '';
                    $this->cf = array_merge( (array)$this->cf, $aspexifblikebox_request_options );
                    
                    update_option( 'aspexifblikebox_options',  $this->cf, '', 'yes' );
                    $this->add_message( __( 'Settings saved.', 'aspexifblikebox' ) );
                }

                // Preview maybe
                if( @$_REQUEST['preview'] )
                    $preview = true;
                else
                    $preview = false;
            }

            // Locale
            $locales = array(
                'Polish' => 'pl_PL',
            );

            $locales_input = '<select name="wkfb_locale">';

            foreach( $locales as $k => $v ) {
                $locales_input .= '<option value="'.$v.'"'.( ( $this->cf['locale'] == $v ) ? ' selected="selected"' : '' ).'>'.$k.'</option>';
            }

            $locales_input .= '</select>';

            ?>
            <div class="wrap">
                <div id="icon-link" class="icon32"></div><h2><?php _e( 'Aspexi Like Box Slider Settings', 'aspexifblikebox' ); ?></h2>
                <?php $this->display_admin_notices( true ); ?>
                <div id="poststuff" class="metabox-holder">
                    <div id="post-body">
                        <div id="post-body-content">
                            <form method="post" action="<?php echo WKFBOX_ADMIN_URL; ?>">

                                <input type="hidden" name="wkfb_form_submit" value="submit" />

                                <div class="postbox">
                                    <h3><span><?="Ustawienia" ?></span></h3>
                                    <div class="inside">
                                        <table class="form-table">
                                            <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?="Aktywny?" ?></th>
                                                <td><select name="wkfb_status">
                                                        <option value="enabled"<?php if( 'enabled' == $this->cf['status'] ) echo ' selected="selected"'; ?>><?php _e('enabled', 'aspexifblikebox'); ?></option>
                                                        <option value="disabled"<?php if( 'disabled' == $this->cf['status'] ) echo ' selected="selected"'; ?>><?php _e('disabled', 'aspexifblikebox'); ?></option>
                                                    </select></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Facebook Page URL', 'aspexifblikebox'); ?></strong></th>
                                                <td>http://www.facebook.com/&nbsp;<input type="text" name="wkfb_url" value="<?php echo $this->cf['url']; ?>" />
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?= "Wysokość boksu" ?></th>
                                                <td><input type="text" name="wkfb_height" value="<?= $this->cf['height']; ?>" size="3"/>&nbsp;px</td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?="Szerokość boksu"; ?></th>
                                                <td><input type="text" name="wkfb_width" value="<?= $this->cf['width']; ?>" size="3"/>&nbsp;px</td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?="Automatyczne dostosowanie szerokości"; ?></th>
                                                <td>
                                                  <input 
                                                    value="on" 
                                                    type="checkbox" 
                                                    name="wkfb_adaptive_width"
                                                    <?php echo $this->cf['adaptative'] == "true" ? 'checked' : '';?>
                                                  />
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?= "Pokaż zdjęcia profilowe znajomych" ?></th>
                                                <td>
                                                  <input 
                                                    value="on" 
                                                    type="checkbox" 
                                                    name="wkfb_faces" 
                                                    <?php echo $this->cf['friendsFaces'] == "true" ? 'checked' : '';?>
                                                  />
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?= "Pokaż posty"; ?></th>
                                                <td>
                                                  <input 
                                                    type="checkbox" 
                                                    value="on" 
                                                    name="wkfb_stream" 
                                                    <?php echo $this->cf['showPosts'] == "true" ? 'checked' : '';?>
                                                  />
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row">
                                                    <?= "Ukryj CTA" ?><br>
                                                    <span style="font-size: 10px;"><?= "Ukryj przycisk CTA (jeżeli jest dostępny)"; ?></span>
                                                </th>
                                                <td>
                                                  <input 
                                                    value="on" 
                                                    type="checkbox" 
                                                    name="wkfb_cta"
                                                    <?php echo $this->cf['hideCta'] == "true" ? 'checked' : '';?>
                                                  >
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?="Ukryj zdjęcie w tle"; ?><br /><span style="font-size: 10px"><?="Ukryj zdjęcie w tyle w nagłówku"; ?></span></th>
                                                <td>
                                                  <input 
                                                    type="checkbox" 
                                                    value="on" 
                                                    name="wkfb_header"
                                                    <?php echo $this->cf['hideCover'] == "true" ? 'checked' : '';?>
                                                  />
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                              <th scope="row"><?="Mały nagłówek"; ?><br /><span style="font-size: 10px"><?="Użyj mniejszego nagłówka"; ?></span></th>
                                              <td>
                                                <input 
                                                  type="checkbox" 
                                                  value="on" 
                                                  name="wkfb_small_header"
                                                  <?php echo $this->cf['smallHeader'] == "true" ? 'checked' : '';?>
                                                />
                                              </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?= "Język" ?><br /><span style="font-size: 10px"><?= "Zmiana może nie być widoczna automatycznie" ?></span></th>
                                                <td><?php echo $locales_input; ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row">
                                                    <?= "Zakładki" ?>
                                                </th>
                                                <td>
                                                    <label>
                                                        <input type="checkbox" value="on" name="wkfb_timeline" <?php echo $this->cf['timeLine'] == "timeline," ? 'checked' : '';?>/> Oś czasu <br>
                                                    </label>
                                                    <label>
                                                        <input type="checkbox" value="on" name="wkfb_events" <?php echo $this->cf['events'] == "events" ? 'checked' : '';?>/> Wydarzenia <br>
                                                    </label>
                                                    <label>
                                                        <input type="checkbox" value="on" name="wkfb_messages" <?php echo $this->cf['messages'] == "messages," ? 'checked' : '';?>/> Wiadomości
                                                    </label>
                                                </td>
                                            </tr>
                                            <?php
                                            echo apply_filters('aspexifblikebox_admin_settings', '');
                                            ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save all settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                    <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>
                                <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wkfb_nonce_name' ); ?>

                                <div class="postbox">
                                    <h3><span><?php _e('Button Settings', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                        <table class="form-table">
                                            <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Button Space', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Space between button and page edge', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="text" name="wkfb_btspace" value="0" size="3" disabled readonly />&nbsp;px<?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Button Placement', 'aspexifblikebox'); ?></th>
                                                <td><input type="radio" name="wkfb_btvertical" value="top" disabled readonly />&nbsp;<?php _e('top of like box','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="wkfb_btvertical" value="middle" checked disabled readonly />&nbsp;<?php _e('middle of like box','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="wkfb_btvertical" value="bottom" disabled readonly />&nbsp;<?php _e('bottom of like box','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="wkfb_btvertical" value="fixed" disabled readonly />&nbsp;<?php _e('fixed','aspexifblikebox'); ?>
                                                    <input type="text" name="wkfb_btvertical_val" value="" size="3" disabled readonly />&nbsp;px <?php _e('from slider top','aspexifblikebox'); ?><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Button Image', 'aspexifblikebox'); ?></th>
                                                <td><span><input type="radio" name="wkfb_btimage" value="fb1-right" checked disabled readonly />&nbsp;<img src="<?php echo WKFBOX_URL.'images/fb1-right.png'; ?>" alt="" style="cursor:pointer;" /></span>
                                                    <span><input type="radio" name="wkfb_btimage" value="" disabled readonly />&nbsp;<img src="<?php echo WKFBOX_URL.'images/preview-buttons.jpg'; ?>" alt="" style="cursor:pointer;" /></span><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('High Resolution', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Use SVG high quality images instead of PNG if possible. Recommended for Retina displays (iPhone, iPad, MacBook Pro).', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="checkbox" value="on" name="wkfb_bthq" disabled readonly />&nbsp;<img src="<?php echo WKFBOX_URL.'images/svgonoff.png'; ?>" alt="" style="cursor:pointer;" /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save all settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                    <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>

                                <div class="postbox">
                                    <h3><span><?php _e('Advanced Look and Feel', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                        <table class="form-table">
                                            <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Animate on page load', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_animate_on_page_load" disabled readonly />&nbsp
	                                                <?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Placement', 'aspexifblikebox'); ?></th>
                                                <td><select name="wkfb_placement" disabled readonly>
                                                        <option value="left"><?php _e('left', 'aspexifblikebox'); ?></option>
                                                        <option value="right" selected="selected"><?php _e('right', 'aspexifblikebox'); ?></option>
                                                    </select><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Vertical placement', 'aspexifblikebox'); ?></th>
                                                <td><input type="radio" name="wkfb_vertical" value="middle" checked disabled readonly />&nbsp;<?php _e('middle','aspexifblikebox'); ?><br />
                                                    <input type="radio" name="wkfb_vertical" value="fixed" disabled readonly />&nbsp;<?php _e('fixed','aspexifblikebox'); ?>
                                                    <input type="text" name="wkfb_vertical_val" value="" size="3" disabled readonly />&nbsp;px <?php _e('from page top','aspexifblikebox'); ?><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Border Color', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="wkfb_bordercolor" class="bordercolor-field" value="#3B5998" size="6" disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Border Width', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="wkfb_borderwidth" value="2" size="3" disabled readonly />&nbsp;px<?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Slide on Mouse', 'aspexifblikebox'); ?></th>
                                                <td><select name="wkfb_slideon" disabled readonly>
                                                        <option value="hover" selected="selected"><?php _e('hover', 'aspexifblikebox'); ?></option>
                                                        <option value="click"><?php _e('click', 'aspexifblikebox'); ?></option>
                                                    </select><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Slide Time', 'aspexifblikebox'); ?></th>
                                                <td><input type="text" name="wkfb_slidetime" value="400" size="3" disabled readonly />&nbsp;<?php _e('milliseconds', 'aspexifblikebox'); ?><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Auto open', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_autoopen" disabled readonly /><?php echo $this->get_pro_link(); ?><br>
                                                    <?php _e('Auto open after', 'aspexifblikebox'); ?>&nbsp;<input type="text" name="wkfb_autoopentime" value="400" size="3" disabled readonly />&nbsp;<?php _e('milliseconds', 'aspexifblikebox'); ?> (1000 milliseconds = 1 second)
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Auto close', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_autoopen" disabled readonly /><?php echo $this->get_pro_link(); ?><br>
                                                    <?php _e('Auto close after', 'aspexifblikebox'); ?>&nbsp;<input type="text" name="wkfb_autoopentime" value="400" size="3" disabled readonly />&nbsp;<?php _e('milliseconds', 'aspexifblikebox'); ?> (1000 milliseconds = 1 second)
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Auto open when user reaches bottom of the page', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="wkfb_autoopenonbottom" disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Auto open when user reaches position', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_autoopenonposition" disabled readonly /><br>
                                                    <?php echo __( 'Auto open when user is', 'aspexifblikebox' ); ?>:&nbsp;<input type="text" disabled readonly name="wkfb_autoopenonposition_px" size="5">px&nbsp;from:
                                                    <select name="wkfb_autoopenonposition_name" disabled readonly>
                                                        <option value="top"><?php echo __( 'Top', 'aspexifblikebox' ); ?></option>
                                                        <option value="bottom"><?php echo __( 'Bottom', 'aspexifblikebox' ); ?></option>
                                                    </select><br>
                                                    <?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Auto open when user reaches element', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_autoopenonelement" disabled readonly /><br>
                                                    <?php echo __( 'Auto open when user reaches', 'aspexifblikebox' ); ?>:&nbsp;<input type="text" disabled readonly name="wkfb_autoopenonelement_name" size="10" value=""><small><?php echo __( '(jQuery selector for example #element_id, .some_class)', 'aspexifblikebox' ); ?></small><br>
                                                    <?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Delay FB content load', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Checking this box will prevent from loading the facebook content while loading the whole page. With this box checked the page will load faster, but facebook content may appear a bit later while opening the box for the first time.', 'aspexifbsidebox'); ?></span></th>
                                                <td><input type="checkbox" value="on" name="afbsb_async" disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on GET', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Example: set Parameter=iframe and Value=true. Like Box will be disabled on all URLs like yourwebsite.com/?iframe=true.', 'aspexifblikebox'); ?></span></th>
                                                <td><?php _e('Parameter', 'aspexifblikebox'); ?>:&nbsp;<input type="text" name="wkfb_disableparam" value="" size="6" disabled readonly /><br />
                                                    <?php _e('Value', 'aspexifblikebox'); ?>:&nbsp;<input type="text" name="wkfb_disableval" value="" size="6" disabled readonly /><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on Posts / Pages (comma separated):', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="text" name="wkfb_disabled_on_ids" value="" disabled readonly /><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on Posts:', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_disabled_on_posts" disabled readonly /><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on all Pages:', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_disabled_on_pages" disabled readonly /><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <?php
                                            $types = get_post_types();
                                            unset($types['post']);
                                            unset($types['page']);
                                            unset($types['attachment']);
                                            unset($types['revision']);
                                            unset($types['nav_menu_item']);
                                            unset($types['custom_css']);
                                            unset($types['customize_changeset']);
                                            unset($types['oembed_cache']);
                                            if( count( $types ) > 0 ) :
                                            ?>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on post types:', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <?php
                                                    foreach ($types as $post_type) {
                                                        echo '<input type="checkbox" value="' . $post_type . '" name="wkfb_disabled_on_posttypes[]" disabled readonly /> ' . $post_type . '<br>';
                                                    }
                                                    ?>
                                                    <?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on Archives (listings):', 'aspexifblikebox'); ?></th>
                                                <td>
                                                    <input type="checkbox" value="on" name="wkfb_disabled_on_archives" disabled readonly /><?php echo $this->get_pro_link(); ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Disable on Small Screens', 'aspexifblikebox'); ?><br /><span style="font-size: 10px"><?php _e('Dynamically hide the plugin if screen size is smaller than like box size (CSS media query)', 'aspexifblikebox'); ?></span></th>
                                                <td><input type="checkbox" value="on" name="wkfb_smallscreens" checked disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save all settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                    <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>

                                <div class="postbox">
                                    <h3><span><?php _e('Enable on Mobile', 'aspexifblikebox'); ?></span></h3>
                                    <div class="inside">
                                        <table class="form-table">
                                            <tbody>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('iPad & iPod', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="wkfb_edipad" checked disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('iPhone', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="wkfb_ediphone" checked disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Android', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="wkfb_edandroid" checked disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e('Other Mobile Devices', 'aspexifblikebox'); ?></th>
                                                <td><input type="checkbox" value="on" name="wkfb_edothers" checked disabled readonly /><?php echo $this->get_pro_link(); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <p><input class="button-primary" type="submit" name="send" value="<?php _e('Save all settings', 'aspexifblikebox'); ?>" id="submitbutton" />
                                    <input class="button-secondary" type="submit" name="preview" value="<?php _e('Save and preview', 'aspexifblikebox'); ?>" id="previewbutton" /></p>
                            </form>
                            <div id="aspexifblikebox-footer" style="text-align:left;text-shadow:0 1px 0 #fff;margin:0 0 10px;color:#888;"><?php echo sprintf(__('If you like %s please leave us a %s rating. A huge thank you in advance!'), '<strong>Aspexi Like Box Slider HD</strong>', '<a href="https://wordpress.org/support/plugin/aspexi-facebook-like-box/reviews/#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733</a>') ?></div>
                            <script type="text/javascript">
                                jQuery(document).ready(function(){
                                    jQuery('#wpfooter').prepend( jQuery('#aspexifblikebox-footer') );
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <?php

            // Preview
            if( $preview ) {
                $this->init_scripts();
                $this->get_html($preview);
            }
        }

        public function get_pro_url() {
            return 'http://aspexi.com/downloads/aspexi-facebook-like-box-slider-hd/?src=free_plugin';
        }

        public function get_pro_link() {
            $ret = '';

            $ret .= '&nbsp;&nbsp;&nbsp;<a href="'.$this->get_pro_url().'" target="_blank">'.__( 'Get PRO version', 'aspexifblikebox' ).'</a>';

            return $ret;
        }

        public function get_html( $preview = false ) {

            $url            = apply_filters( 'aspexifblikebox_url', $this->cf['url'] );
            $status         = apply_filters( 'aspexifblikebox_status', $this->cf['status'] );

            // Disable maybe
            if( ( !strlen( $url ) || 'enabled' != $status ) && !$preview )
                return;

            // Options
            $locale         = apply_filters( 'aspexifblikebox_locale', $this->cf['locale'] );
            $height         = apply_filters( 'aspexifblikebox_height', $this->cf['height'] );
            $width          = apply_filters( 'aspexifblikebox_width', $this->cf['width'] );
            $adaptative     = apply_filters( 'aspexifblikebox_adaptative', $this->cf['adaptative'] );
            $friendsFaces   = apply_filters( 'aspexifblikebox_friendsFaces', $this->cf['friendsFaces'] );
            $showPosts      = apply_filters( 'aspexifblikebox_showPosts', $this->cf['showPosts'] );
            $hideCta        = apply_filters( 'aspexifblikebox_hideCta', $this->cf['hideCta'] );
            $hideCover      = apply_filters( 'aspexifblikebox_hideCover', $this->cf['hideCover'] );
            $smallHeader    = apply_filters( 'aspexifblikebox_smallHeader', $this->cf['smallHeader'] );
            $timeLine       = apply_filters( 'aspexifblikebox_timeLine', $this->cf['timeLine'] );
            $messages       = apply_filters( 'aspexifblikebox_messages', $this->cf['messages'] );
            $events         = apply_filters( 'aspexifblikebox_smallHeader', $this->cf['events'] );
            $placement      = 'right';
            $btspace        = 0;
            $btimage        = 'fb2-top.png';
            $bordercolor    = '#3B5998';
            $borderwidth    = 2;
            $bgcolor        = '#ffffff';

            $css_placement = array();
            if( 'left' == $placement ) {
                $css_placement[0] = 'right';
                $css_placement[1] = '0 '.(48+$btspace).'px 0 5px';
            } else {
                $css_placement[0] = 'left';
                $css_placement[1] = '0 0 0 '.(48+$btspace).'px';
            }

            $css_placement[2] = '50%;margin-top:-'.floor($height/2).'px';

            $smallscreenscss = '';
            if( $width > 0 ) {
                $widthmax = (int)($width + 48 + $borderwidth + 10);
                $smallscreenscss = '@media (max-width: '.$widthmax.'px) { .aspexifblikebox { display: none; } }';
            }

            $stream     = 'false';
            $header     = 'false';

            // Facebook button image (check in THEME CHILD -> THEME PARENT -> PLUGIN DIR)
            // TODO: move this to admin page
            $users_button_custom    = '/plugins/'.basename( dirname( __FiLE__ ) ).'/images/aspexi-fb-custom.png';
            $users_button_template  = get_template_directory() . $users_button_custom;
            $users_button_child     = get_stylesheet_directory() . $users_button_custom;
            $button_uri             = '';

            if( file_exists( $users_button_child ) )
                $button_uri = get_stylesheet_directory_uri() . $users_button_custom;
            elseif( file_exists( $users_button_template ) )
                $button_uri = get_template_directory_uri() . $users_button_custom;
            elseif( file_exists( plugin_dir_path( __FILE__ ).'images/'.$btimage ) )
                $button_uri = WKFBOX_URL.'images/'.$btimage;

            if( '' == $button_uri ) {
                $button_uri = WKFBOX_URL.'images/fb1-right.png';
            }

            $button_uri  = apply_filters( 'aspexifblikebox_button_uri', $button_uri );

            $output = '';

            $page_url = 'https://www.facebook.com/'.$url;

            $output .= '<div class="fb-root"></div>
            <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/'.$locale.'/sdk.js#xfbml=1&version=v3.3&appId=339779149790099";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, \'script\', \'facebook-jssdk\'));</script>
            <style type="text/css">' . $smallscreenscss.' .fb-xfbml-parse-ignore {
                    display: none;
                }
                
                .aspexifblikebox {
                    overflow: hidden;
                    z-index: 99999999;
                    position: fixed;
                    padding: '.$css_placement[1].';
                    top: ' . $css_placement[2] . ';
                    right: -' . ($width) . 'px;
                }
                
                .aspexifblikebox .aspexi_facebook_iframe {
                    padding: 0;
                    border: ' . $borderwidth . 'px solid ' . $bordercolor . ';
                    background: #fff;
                    width: ' . $width . 'px;
                    height: ' . $height . 'px;
                    box-sizing: border-box;
                }
                
                .aspexifblikebox .fb-page {
                    background: url("' . WKFBOX_URL . 'images/load.gif") no-repeat center center;
                    width: ' . ($width - ($borderwidth * 2)). 'px;
                    height: ' . ($height - ($borderwidth * 2)). 'px;
                    margin: 0;
                }
                
                .aspexifblikebox .fb-page span {
                    background: #fff;
                    height: 100% !important;
                }
                
                .aspexifblikebox .aspexi_facebook_button {
                    background: url("' . $button_uri . '") no-repeat scroll transparent;
                    height: 155px;
                    width: 48px;
                    position: absolute;
                    top: 0;
                    left: 0;
                    cursor: pointer;
                }
            </style>
            <div class="aspexifblikebox">
                <div class="aspexi_facebook_button"></div>
                <div class="aspexi_facebook_iframe">
                    <div 
                      class="fb-page" 
                      data-href="'.$page_url.'" 
                      data-hide-cta="'.$hideCta.'"
                      data-width="'.($width - 4).'" 
                      data-height="'.($height - 4).'" 
                      data-hide-cover="'.$hideCover.'"
                      data-show-posts="'.$showPosts.'" 
                      data-show-facepile="'.$friendsFaces.'" 
                      data-adapt-container-width="'.$adaptative.'"
                      data-small-header="'.$smallHeader.'"
                      data-tabs="'.$timeLine.$messages.$events.'"
                    >
                    <div class="fb-xfbml-parse-ignore"><blockquote cite="'.$page_url.'"><a href="'.$page_url.'">Facebook</a></blockquote></div></div>
                </div>
            </div>';

            $output = apply_filters( 'aspexifblikebox_output', $output );

            echo $output;
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

        public function extras_init() {
            /* qTranslate */
            add_filter( 'aspexifblikebox_admin_settings', array( &$this, 'extras_qtranslate_admin' ) );
            add_filter( 'aspexifblikebox_admin_settings', array( &$this, 'extras_polylang_admin' ) );
        }

        public function extras_qtranslate_detect() {
            global $q_config;
            return (isset($q_config) && !empty($q_config));
        }

        public function extras_qtranslate_admin( $extra_admin_content ) {
            $qtranslate_locale = $this->extras_qtranslate_detect();

            if( $qtranslate_locale ) {
                $extra_admin_content .= '<tr valign="top">
    <th scope="row">'.__('qTranslate/mqTranslate', 'aspexifblikebox').'<br /><span style="font-size: 10px">'.__('Try to detect qTranslate/mqTranslate language and force it instead of language set in Localization.', 'aspexifblikebox').'</span></th>
    <td><input type="checkbox" value="on" name="wkfb_qtranslate" disabled readonly />'.$this->get_pro_link().'</td>
</tr>';
            }

            return $extra_admin_content;
        }

        public function extras_polylang_admin( $extra_admin_content ) {

            if(function_exists('pll_current_language')) {
                $extra_admin_content .= '<tr valign="top">
    <th scope="row">'.__('Polylang', 'aspexifblikebox').'<br /><span style="font-size: 10px">'.__('Try to detect Polylang language and force it instead of language set in Localization.', 'aspexifblikebox').'</span></th>
    <td><input type="checkbox" value="on" name="wkfb_polylang" disabled readonly />'.$this->get_pro_link().'</td>
</tr>';
            }

            return $extra_admin_content;
        }

        // Check if Page Plugin is enabled
        public function is_pp() {

            return $this->cf['page_plugin'];
        }

        public function admin_notices() {

            if( !isset($this->cf['hide_notice']) || @$this->cf['hide_notice'] != '1' ) {
        ?>
            <div class="notice notice-success" id="afblnotice" style="display: flex;flex-wrap: wrap;">
                <p><?php echo 'To jest plugin tworzony na podstawie innego pluginu'; ?>
                    <div style="flex: 1 300px;margin: .5em 0;text-align: right;">
                        <input type="button" id="afblhidenotice" value="<?="Zamknij" ?>" class="button" />
                    </div>
                </p>
            </div>
        <?php
            }
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

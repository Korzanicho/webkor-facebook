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

'Plugin dodaje box ze stroną Facebookową (oś czasu, wydarzenia, wiadomości)';

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

      add_action( 'admin_menu',               array( &$this, 'adminMenu')); //zrobione
      add_action( 'init',                     array( &$this, 'init' ), 10 ); // NIE - tylko tłumaczenia
      add_action( 'wp_footer',                array( &$this, 'get_html' ), 21 );
      add_action( 'admin_enqueue_scripts',    array( &$this, 'admin_scripts') );
      add_action( 'wp_ajax_afbl_hide_notice', array( &$this, 'admin_notices_handle') );
      add_action( 'wp_enqueue_scripts',       array( &$this, 'init_scripts') );
      add_filter( 'plugin_action_links',      array( &$this, 'settings_link' ), 10, 2);

      register_uninstall_hook( __FILE__, array( 'WebkorFbBox', 'uninstall' ) );
    }

    /* WP init action */
    public function init() {

        /* Internationalization */
        load_plugin_textdomain( 'aspexifblikebox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
        $settings_link = '<a href="themes.php?page=' . basename( __FILE__ )  .  '">' . __("Settings") . '</a>';
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
      add_submenu_page( 'themes.php', 'Facebook Like Box', 'Facebook Like Box', 'manage_options', basename(__FILE__), array( &$this, 'admin_page') );
    }

    public function admin_page() {

    !current_user_can('manage_options') ? wp_die('Nie masz uprawnień do tej strony') : null;

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
        $this->addError( 'Błędny status. Box powinien mieć status aktywny/nieaktywny' );

      if( !$this->hasErrors() ) {
        $wkfbRequestOptions = array();

        $wkfbRequestOptions['url']           = isset( $_REQUEST['wkfb_url'] ) ? trim( $_REQUEST['wkfb_url'] ) : '';
        $wkfbRequestOptions['locale']        = isset( $_REQUEST['wkfb_locale'] ) ? $_REQUEST['wkfb_locale'] : '';
        $wkfbRequestOptions['status']        = isset( $_REQUEST['wkfb_status'] ) ? $_REQUEST['wkfb_status'] : '';
        $wkfbRequestOptions['height']        = isset( $_REQUEST['wkfb_height'] ) ? $_REQUEST['wkfb_height'] : 234;
        $wkfbRequestOptions['width']         = isset( $_REQUEST['wkfb_width'] ) ? $_REQUEST['wkfb_width'] : 245;
        $wkfbRequestOptions['adaptative']    = isset( $_REQUEST['wkfb_adaptive_width'] ) ? 'true' : 'false';
        $wkfbRequestOptions['friendsFaces']  = isset( $_REQUEST['wkfb_faces'] ) ? 'true' : 'false';
        $wkfbRequestOptions['showPosts']     = isset( $_REQUEST['wkfb_stream'] ) ? 'true' : 'false';
        $wkfbRequestOptions['hideCta']       = isset( $_REQUEST['wkfb_cta'] ) ? 'true' : 'false';
        $wkfbRequestOptions['hideCover']     = isset( $_REQUEST['wkfb_header'] ) ? 'true' : 'false';
        $wkfbRequestOptions['smallHeader']   = isset( $_REQUEST['wkfb_small_header'] ) ? 'true' : 'false';
        $wkfbRequestOptions['timeLine']      = isset( $_REQUEST['wkfb_timeline'] ) ? 'timeline,' : '';
        $wkfbRequestOptions['messages']      = isset( $_REQUEST['wkfb_messages'] ) ? 'messages,' : '';
        $wkfbRequestOptions['events']        = isset( $_REQUEST['wkfb_events'] ) ? 'events' : '';
        $wkfbRequestOptions['fbIcon']        = isset( $_REQUEST['wkfb_btimage'] ) ? $_REQUEST['wkfb_btimage'] : 'fb2-top.png';
        $wkfbRequestOptions['edgeSpace']     = isset( $_REQUEST['wkfb_btspace'] ) ? $_REQUEST['wkfb_btspace'] : 0;
        $wkfbRequestOptions['iconVertical']  = isset( $_REQUEST['wkfb_btvertical'] ) ? $_REQUEST['wkfb_btvertical'] : 'top';
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

    ?>
      <div class="wrap">
        <div id="icon-link" class="icon32"></div><h2><?="Ustawienia Webkor Facebook Box'a" ?></h2>
        <?php $this->displayAdminNotices( true ); ?>
        <div id="poststuff" class="metabox-holder">
          <div id="post-body">
            <div id="post-body-content">
              <form method="post" action="<?= WKFBOX_ADMIN_URL; ?>">
                <input type="hidden" name="wkfb_form_submit" value="submit" />
                <div class="postbox">
                  <h3><span><?="Ustawienia" ?></span></h3>
                  <div class="inside">
                    <table class="form-table">
                      <tbody>
                        <tr valign="top">
                          <th scope="row"><?="Aktywny?" ?></th>
                          <td>
                            <select name="wkfb_status">
                              <option value="enabled"<?= 'enabled' == $this->cf['status'] ? ' selected="selected"' : null; ?>>Aktywny</option>
                              <option value="disabled"<?= 'disabled' == $this->cf['status'] ? ' selected="selected"' : null; ?>>Niekatywny</option>
                            </select>
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row"><?= "Adres strony na Facebooku" ?></strong></th>
                          <td>http://www.facebook.com/ <input type="text" name="wkfb_url" value="<?php echo $this->cf['url']; ?>" />
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row"><?= "Wysokość boksu" ?></th>
                          <td><input type="text" name="wkfb_height" value="<?= $this->cf['height']; ?>" size="3"/>&nbsp;px</td>
                        </tr>
                        <tr valign="top">
                          <th scope="row"><?="Szerokość boksu"; ?></th>
                          <td><input type="text" name="wkfb_width" value="<?= $this->cf['width']; ?>" size="3"/> px</td>
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
                              <input type="checkbox" value="on" name="wkfb_messages" <?php echo $this->cf['messages'] == "messages," ? 'checked' : '';?>/> Wiadomości
                            </label>
                            <label>
                              <input type="checkbox" value="on" name="wkfb_events" <?php echo $this->cf['events'] == "events" ? 'checked' : '';?>/> Wydarzenia <br>
                            </label>
                          </td>
                        </tr>
                        <?= apply_filters('aspexifblikebox_admin_settings', ''); ?>
                      </tbody>
                    </table>
                  </div>
                </div>

                <p>
                  <input class="button-primary" type="submit" name="send" value="<?= "Zapisz wszystkie ustawienia" ?>" id="submitbutton" />
                  <input class="button-secondary" type="submit" name="preview" value="<?= "Zapisz i zobacz podgląd"; ?>" id="previewbutton" />
                </p>
                <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wkfb_nonce_name' ); ?>

                <div class="postbox">
                  <h3><span><?= "Ustawienia ikony"; ?></span></h3>
                  <div class="inside">
                    <table class="form-table">
                      <tbody>
                      <tr valign="top">
                        <th scope="row">
                          Odległość od krawędzi<br />
                          <span style="font-size: 10px">Określ pustą przestrzeń między ikoną, a krawędzią strony</span>
                        </th>
                        <td>
                          <input type="text" name="wkfb_btspace" value="<?= $this->cf['edgeSpace'] ?>" size="3"/> px</td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Położenie ikony</th>
                        <td><input type="radio" name="wkfb_btvertical" value="top" <?= $this->cf['iconVertical'] == "top" ? 'checked' : '';?>/>Na górze box'a<br />
                          <input type="radio" name="wkfb_btvertical" value="middle" <?= $this->cf['iconVertical'] == "middle" ? 'checked' : '';?>/>Na środku box'a<br />
                          <input type="radio" name="wkfb_btvertical" value="bottom" <?= $this->cf['iconVertical'] == "bottom" ? 'checked' : '';?>/>Na dole box'a<br />
                          <input type="radio" name="wkfb_btvertical" value="fixed" <?= $this->cf['iconVertical'] == "fixed" ? 'checked' : '';?>/>Stała wartość
                          <input type="text" name="wkfb_btvertical_val" value="<?= $this->cf['iconVerticalConst'];?>" size="3" /> px (od góry box'a)
                        </td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Obrazek ikony</th>
                        <td>
                          <span>
                            <input type="radio" name="wkfb_btimage" value="fb1-right.png" <?php echo $this->cf['fbIcon'] == "fb1-right.png" ? 'checked' : '';?>/>
                            <img src="<?php echo WKFBOX_URL.'images/fb1-right.png'; ?>" alt="Facebook" style="cursor:pointer;" />
                          </span>
                          <span>
                            <input type="radio" name="wkfb_btimage" value="fb2-top.png" <?php echo $this->cf['fbIcon'] == "fb2-top.png" ? 'checked' : '';?> />
                            <img src="<?php echo WKFBOX_URL.'images/fb2-top.png'; ?>" alt="Facebook" style="cursor:pointer;" />
                          </span>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <p>
                  <input class="button-primary" type="submit" name="send" value="Zapisz wszystkie zmiany" id="submitbutton" />
                  <input class="button-secondary" type="submit" name="preview" value="Zapisz i zobacz podgląd" id="previewbutton" />
                </p>

                <div class="postbox">
                  <h3><span>Zaawansowane ustawienia wyglądu</span></h3>
                  <div class="inside">
                    <table class="form-table">
                      <tbody>
                        <tr valign="top">
                          <th scope="row">Animuj przy załadowaniu strony</th>
                          <td>
                            <input type="checkbox" value="on" name="wkfb_animate_on_page_load" disabled readonly />        
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Położenie</th>
                          <td>
                            <select name="wkfb_placement" disabled readonly>
                              <option value="left">Po lewej</option>
                              <option value="right" selected="selected">Po prawej</option>
                            </select></td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Położenie w pionie</th>
                          <td>
                            <input type="radio" name="wkfb_vertical" value="middle" checked disabled readonly />Na środku<br />
                            <input type="radio" name="wkfb_vertical" value="fixed" disabled readonly /> Stała wartość
                            <input type="text" name="wkfb_vertical_val" value="" size="3" disabled readonly />px Od góry strony
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Kolor Obramowania</th>
                          <td><input type="text" name="wkfb_bordercolor" class="bordercolor-field" value="#3B5998" size="6" disabled readonly /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Szerokość obramowania</th>
                            <td><input type="text" name="wkfb_borderwidth" value="2" size="3" disabled readonly />px</td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Wysuń</th>
                          <td>
                            <select name="wkfb_slideon" disabled readonly>
                              <option value="hover" selected="selected">Po najechaniu</option>
                              <option value="click">Po kliknięciu</option>
                            </select>
                          </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Prędkość wysuwania</th>
                            <td><input type="text" name="wkfb_slidetime" value="400" size="3" disabled readonly />milisekund</td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Autootwieranie</th>
                          <td>
                            <input type="checkbox" value="on" name="wkfb_autoopen" disabled readonly /><br>
                            Automatycznie otwórz po <input type="text" name="wkfb_autoopentime" value="400" size="3" disabled readonly /> milisekundach (1000 millisekund = 1 sekunda)
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Auto zamykanie</th>
                          <td>
                            <input type="checkbox" value="on" name="wkfb_autoopen" disabled readonly /><br>
                            Automatycznie zamknij po <input type="text" name="wkfb_autoopentime" value="400" size="3" disabled readonly />milisekunach (1000 millisekund = 1 sekunda)
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Automatyczne otwieranie po zjechaniu na dół strony</th>
                          <td><input type="checkbox" value="on" name="wkfb_autoopenonbottom" disabled readonly /></td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Otwórz automatycznie gdy użytkownik przejdzie do określonej pozycji</th>
                          <td>
                            <input type="checkbox" value="on" name="wkfb_autoopenonposition" disabled readonly /><br>
                            Otwórz automatycznie gdy użytkownik jest: <input type="text" disabled readonly name="wkfb_autoopenonposition_px" size="5">px od:
                            <select name="wkfb_autoopenonposition_name" disabled readonly>
                              <option value="top">Góry</option>
                              <option value="bottom">Dołu</option>
                            </select><br>
                          </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Otwórz automatycznie gdy użytkownik zobaczy element</th>
                            <td>
                              <input type="checkbox" value="on" name="wkfb_autoopenonelement" disabled readonly /><br>
                              Otwórz gdy użytkownik zobaczy: <input type="text" disabled readonly name="wkfb_autoopenonelement_name" size="10" value=""><small> selektor jQuery na przykład #element_id, .some_class)</small><br>
                            </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Opóźnij ładowanie boxu<br /><span style="font-size: 10px">Zaznaczenie tego pola uniemożliwi ładowanie treści na Facebooku podczas ładowania całej strony. Gdy to pole jest zaznaczone, strona ładuje się szybciej, ale treść na Facebooku może pojawić się nieco później podczas otwierania okna po raz pierwszy.</span></th>
                          <td><input type="checkbox" value="on" name="afbsb_async" disabled readonly /></td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Wyłącz dla zmiennej GET<br /><span style="font-size: 10px">Przykładowo, ustawiając Parametr=iframe i Wartosc=true, Like Box nie wyświetli się na stronach zawierających te zmienne w adresie, np. yourwebsite.com/?iframe=true.</span></th>
                          <td>
                            Parametr: <input type="text" name="wkfb_disableparam" value="" size="6" disabled readonly /><br />
                            Wartość: <input type="text" name="wkfb_disableval" value="" size="6" disabled readonly />
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Wyłącz dla postów/stron (oddzielone przecinkami)</th>
                          <td>
                            <input type="text" name="wkfb_disabled_on_ids" value="" disabled readonly />
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Wyłącz dla postów</th>
                          <td>
                            <input type="checkbox" value="on" name="wkfb_disabled_on_posts" disabled readonly />
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row">Wyłącz na stronach</th>
                          <td>
                            <input type="checkbox" value="on" name="wkfb_disabled_on_pages" disabled readonly />
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
                          if( count( $types ) > 0 ) {
                        ?>
                          <tr valign="top">
                            <th scope="row"><?php _e('Disable on post types:', 'aspexifblikebox'); ?></th>
                            <td>
                              <?php
                              foreach ($types as $post_type) {
                                echo '<input type="checkbox" value="' . $post_type . '" name="wkfb_disabled_on_posttypes[]" disabled readonly /> ' . $post_type . '<br>';
                              }
                              ?>
                            </td>
                          </tr>
                        <?php } ?>
                        <tr valign="top">
                          <th scope="row">Wyłącz na małych ekranach<br /><span style="font-size: 10px">Automatycznie ukryj baner jeśli jego szerokość jest większa niż szerokość urządzenia</span></th>
                          <td><input type="checkbox" value="on" name="wkfb_smallscreens" checked disabled readonly /></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <p>
                  <input class="button-primary" type="submit" name="send" value="Zapisz wszystkie zmiany" id="submitbutton" />
                  <input class="button-secondary" type="submit" name="preview" value="Zapisz i zobacz podgląd" id="previewbutton" />
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php
      if( $preview ) {
        $this->init_scripts();
        $this->get_html($preview);
      }
    }

    public function get_html( $preview = false ) {
      include_once('facebookBox.php');
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

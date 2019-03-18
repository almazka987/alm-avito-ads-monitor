<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Alm_Avito_Ads_Monitor {

	/**
	 * The single instance of Alm_Avito_Ads_Monitor.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * The plugin necessary Avito options
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $avito_enable_option;
    public $avito_city_option;
    public $avito_email_option;
    public $avito_keys_option;
    public $avito_keywords_array;
    public $avito_db_data;
    public $avito_monitor_data;

    /**
     * Block our IP on Avito ru
     * @var     bool
     * @access  public
     * @since   1.0.0
     */
    public $blocked;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @param string $file
     * @param string $version
     */
	public function __construct ( $file = '', $version = '1.0.0' ) {
        date_default_timezone_set('Europe/Moscow');
        $this->_version = $version;
        $this->_token = 'alm_avito_ads_monitor';
        $this->blocked = false;

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = plugin_dir_path( $file ); //$this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

        // Handle localisation
        $this->load_localisation();

        $this->upd_avito_options();
        $this->avito_monitor_data = array();

        // Create DB table
        $this->db_install();
        $this->upd_avito_db_data();

        register_activation_hook( $this->file, array( $this, 'install' ) );
        register_activation_hook( $this->file, array( $this, 'alio_cron_activation' ) );
        register_deactivation_hook( $this->file, array( $this, 'alio_cron_deactivation' ) );

        // Load admin JS & CSS
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

        add_action('updated_option', array( $this, 'new_start_after_save_options' ), 10, 2);

        // Load API for generic admin functions
        if ( is_admin() ) {
            $this->admin = new Alm_Avito_Ads_Monitor_Admin_API();
        }

        // Start searching
        $this->load_searching();

	} // End __construct()

    /**
     * Update all avito options
     * @return void
     */
    public function upd_avito_options() {
        $this->avito_enable_option = get_option('aam_avito_enable');
        $this->avito_city_option = $this->transliterate( $this->clear( get_option('aam_avito_city') ) );
        $this->avito_keys_option = get_option('aam_avito_keys');
        $this->avito_email_option = get_option('aam_avito_email');
        $this->avito_keywords_array = $this->get_keys_array($this->avito_keys_option);
    }

    public function new_start_after_save_options( $option_name ) {
    if ( $option_name == 'aam_avito_city' || $option_name == 'aam_avito_email' || $option_name == 'aam_avito_keys' )
        if ( $this->avito_enable_option ) {
            $this->upd_avito_options();
            $this->alio_parse_avito();
        }
    }

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @param string $hook
     * @return  void
     */
    public function admin_enqueue_styles ( $hook = '' ) {
        wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
        wp_enqueue_style( $this->_token . '-admin' );
    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @param string $hook
     * @return  void
     */
    public function admin_enqueue_scripts ( $hook = '' ) {
        wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
        wp_enqueue_script( $this->_token . '-admin' );
        $this->load_ajax_scripts();
    } // End admin_enqueue_scripts ()

    /**
     * Load AJAX scripts
     */
    public function load_ajax_scripts() {
        wp_localize_script( 'jquery', 'ajaxUrl', array(
            'url' => admin_url( 'admin-ajax.php' )
        ) );
    }

    /*
    *  Wrapper for the get_locale() function
    *  @type	function
    *  @param	n/a
    *  @return	(string)
    */
    public function aam_get_locale() {
        return is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
    }

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
        // vars
        $domain = 'alm-avito-ads-monitor';
        $locale = apply_filters( 'plugin_locale', $this->aam_get_locale(), $domain );
        $mofile = $domain . '-' . $locale . '.mo';

        // load from the languages directory first
        load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );

        // load from plugin lang folder
        load_textdomain( $domain, $this->dir . '/lang/' . $mofile );

		//$res = load_plugin_textdomain( 'alm-avito-ads-monitor', false, dirname( plugin_basename( $this->file ) ) . '/lang/' . $mofile );
	} // End load_localisation ()

    /**
     * Create plugin DB table
     * @return void
     */
    private function db_install() {
        global $wpdb;
        $wpdb->show_errors( true );
        $table_name = $wpdb->prefix . 'alm_ads_monitor';
        if( !$wpdb->get_var("SHOW TABLES LIKE '$table_name'") ) {
            $charset_collate = $wpdb->get_charset_collate();
    
            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                search_date datetime NOT NULL,
                site tinytext NOT NULL,
                data longtext NOT NULL,
                new_data longtext NOT NULL,
                exclude_items text NOT NULL,
                PRIMARY KEY  (id)
                ) $charset_collate;";
    
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }

    public function upd_avito_db_data() {
        global $wpdb;
        $wpdb->show_errors( true );
        $this->avito_db_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'alm_ads_monitor WHERE site="%s"', 'avito' ) );
    }

    /**
     * Clear spaces, lowercase helper
     * @param $str
     * @return string
     */
    public function clear( $str ) {
        return trim( str_replace( ', ', ',', mb_strtolower( $str, 'UTF-8' ) ) );
    }

    /**
     * Transliterate cyrillic
     * @param $string
     * @return string
     */
    public function transliterate( $string ) {
        $roman = array("Sch","sch",'Yo','Zh','Kh','Ts','Ch','Sh','Yu','ya','yo','zh','kh','ts','ch','sh','yu','ya','A','B','V','G','D','E','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','','Y','','E','a','b','v','g','d','e','z','i','y','k','l','m','n','o','p','r','s','t','u','f','','y','','e');
        $cyrillic = array("Щ","щ",'Ё','Ж','Х','Ц','Ч','Ш','Ю','я','ё','ж','х','ц','ч','ш','ю','я','А','Б','В','Г','Д','Е','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Ь','Ы','Ъ','Э','а','б','в','г','д','е','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','ь','ы','ъ','э');
        return str_replace( $cyrillic, $roman, $string );
    }

    /**
     * Get keys array from the string option
     * @param $str
     * @return array
     */
    public function get_keys_array( $str ) {
        return ( !empty( $str ) ) ? explode( ',', $this->clear( $str ) ) : array();
    }

    /**
     * Get count of pages Avito searching
     * @param $key
     * @param int $page
     * @return int
     */
    public function lets_parse_avito_page( $key, $page ) {
        $cnt = 0;
        $city = $this->avito_city_option ?: '';
        $p_request = ( $page == 0 ) ? '' : 'p=' . $page . '&';
        $url = 'https://www.avito.ru/' . $city . '?' . $p_request . 'q=' . $key;

        $key_id = $this->transliterate( $this->clear( $key ) );
        $file = file_get_contents( $url );

        if ( $doc = phpQuery::newDocumentHTML( $file, 'utf-8' ) ) {

            if ( $page == 0 ) {
                $cnt = $doc->find('div.pagination-pages > a.pagination-page' )->count();
            }

            if ( $doc->find('div.item.item_table') ) {
                foreach ($doc->find('div.item.item_table') as $item_table) {
                    $item_table = pq($item_table);
                    $avito_item_id = $key_id . $item_table->attr('id');
                    $exclude_arr = !empty($this->avito_db_data[0]->exclude_items) ? json_decode($this->avito_db_data[0]->exclude_items, true) : array();

                    if (!empty($exclude_arr) && in_array($avito_item_id, $exclude_arr)) continue;

                    $bad_symbols = array('background-image: url(', ')', ';');

                    $img_from_ul = $item_table->find('.item-slider-list .item-slider-item:eq(0) div.item-slider-image img.large-picture-img')->attr('src');

                    // maybe they will return it - where src is find in 'style' and 'srcpath' attrs
                    $img_from_ul_style = $item_table->find('.item-slider-list .item-slider-item:eq(0) div.item-slider-image')->attr('style');
                    $img_from_ul_srcpath = $item_table->find('.item-slider-list .item-slider-item:eq(0) div.item-slider-image')->attr('data-srcpath');
                    $img_from_ul_or_a = (empty($img_from_ul)) ? $item_table->find('a.large-picture-img')->attr('src') : $img_from_ul;

                    $item_title = $item_table->find('div.item_table-header > h3 > a span')->text();
                    $item_link = $item_table->find('div.item_table-header > h3 > a')->attr('href');
                    if ($item_link) {
                        $item_link = 'https://www.avito.ru/' . $item_link;
                    }
                    $item_price = $item_table->find('div.item_table-header span.price')->html();

                    if ($img_from_ul_or_a) {
                        $img_from_ul_or_a = str_replace(array('//', 'https://', 'http://'), 'http://', $img_from_ul_or_a);
                        $this->avito_monitor_data[$avito_item_id]['image'] = '<img alt="" src="' . $img_from_ul_or_a . '">';
                    } elseif ($img_from_ul_style && !$img_from_ul_srcpath) {
                        $img_from_ul_style = 'http:' . str_replace($bad_symbols, '', $img_from_ul_style);
                        $this->avito_monitor_data[$avito_item_id]['image'] = '<img alt="" src="' . $img_from_ul_style . '">';
                    } elseif ($img_from_ul_srcpath) {
                        $this->avito_monitor_data[$avito_item_id]['image'] = '<img alt="" src="http:' . $img_from_ul_srcpath . '">';
                    }

                    $this->avito_monitor_data[$avito_item_id]['keyword'] = $key;
                    $this->avito_monitor_data[$avito_item_id]['description'] = '<div><h3><a href="' . $item_link . '" target="blank">' . $item_title . '</a></h3>' . $item_price . '</div>';
                }
            } else {
                $this->blocked = true;
            }
        }

        return $cnt;
    }

    /**
     * Get Array from new Avito items
     * @return array
     */
    public function get_new_keys_arr() {
        $new_keys = array();
        $new_data = json_decode( $this->avito_db_data[0]->new_data, true );
        if ( is_array( $new_data ) ) {
            foreach ( $new_data as $new_data_arr ) {
                $new_keys[] = $new_data_arr['keyword'];
            }
            $new_keys = array_unique( $new_keys );
        }
        return $new_keys;
    }

    /**
     * Parse and save Avito data in DB
     * @return void
     */
    public function alio_parse_avito() {
        $site = 'avito';
        $search_date = time();
        foreach ( $this->avito_keywords_array as $key ) {
            $pages_count = $this->lets_parse_avito_page( $key, 0 );

            if ( $pages_count > 0 ) {
                for ( $i = 2; $i <= $pages_count; $i++ ) {
                    $this->lets_parse_avito_page( $key, $i );
                }
            }
        }

        global $wpdb;
        $wpdb->show_errors( true );
        
        $new_data = $this->avito_search_new();

        if ( empty( $this->avito_db_data ) ) {
            $wpdb->insert( $wpdb->prefix . 'alm_ads_monitor', array(
                'ID' => '',
                'search_date' => date('Y/m/d H:i:s', $search_date ),
                'site' => $site,
                'data' => json_encode( $this->avito_monitor_data, JSON_UNESCAPED_UNICODE ),
                'new_data' => json_encode( $new_data, JSON_UNESCAPED_UNICODE ),
            ) );
        } else {
            $wpdb->update( $wpdb->prefix . 'alm_ads_monitor', array(
                'search_date' => date('Y/m/d H:i:s', $search_date ),
                'site' => $site,
                'data' => json_encode( $this->avito_monitor_data, JSON_UNESCAPED_UNICODE ),
                'new_data' => json_encode( $new_data, JSON_UNESCAPED_UNICODE ),
            ), array('id' => $this->avito_db_data[0]->id) );
        }
        $this->upd_avito_db_data();

        if ( $this->avito_email_option && !empty( $new_data ) ) {
            $this->avito_send_mail(); //for testing use add_action( 'plugins_loaded', array( $this, 'avito_send_mail' ) );
        }

    }

    /**
     * Send email with Avito monitor results
     * @return void
     */
    public function avito_send_mail() {
        $new_data = json_decode( $this->avito_db_data[0]->new_data, true );
        $all_data = json_decode( $this->avito_db_data[0]->data, true );
        $new_keywords = $this->get_new_keys_arr();
        $other_data = array_diff_key( $all_data, $new_data );

        $msg  = '';
        $descr_text = ( $this->avito_city_option && $this->avito_keys_option ) ? __( 'Founded updates by keywords: ', 'alm-avito-ads-monitor' ) . implode(', ', $this->get_new_keys_arr() ) : '';
        $descr_text = ( $this->avito_city_option && $this->avito_keys_option ) ? __( 'Founded updates by keywords: ', 'alm-avito-ads-monitor' ) . implode(', ', $this->get_new_keys_arr() ) : '';
        $msg .= '<div style="text-align: right;"><a href="' . get_site_url() . '/wp-admin/options-general.php?page=alm_avito_ads_monitor_settings" target="blank">' . __( 'Go to Settings Page to customize settings or exclude items', 'alm-avito-ads-monitor' ) . '</a></div>';
        $msg .= '<div class="last-monitor-holder" style="min-height: 100%;margin: 0;padding: 0;background-color: #c4d3f6;border-radius: 25px;max-width: 900px;"><div class="title-block"><h2 style="padding: 20px;border-bottom: 1px dashed;text-align: center;">' . __( 'Avito Monitoring', 'alm-avito-ads-monitor' ) . '</h2>
        <p style="font-size: 14px;text-align: center;">' . $descr_text . '</p>';
        if ( !empty( $this->avito_db_data[0]->search_date ) ) {
            $msg .= '<p style="font-size: 14px;text-align: center;">' . __( 'Last Parsing ', 'alm-avito-ads-monitor' ) . $this->avito_db_data[0]->search_date . ' MSK</p>';
        }
        $msg .= '</div><div class="last-monitor-wrapper">';

        if ( !empty( $new_keywords ) ) {
            foreach( $new_keywords as $k_word ) {
                $msg .= '<div class="table-header" style="overflow: hidden;padding: 20px;font-weight: bold;background: #6c7ae0;font-size: 14px;color: #000;text-align: center;"><h4 style="margin: 0;">' . __( 'Keyword: ', 'alm-avito-ads-monitor' ) . $k_word . '</h4></div>';

                $msg .= '<div class="table-scrolling-container" style="max-height: 500px;overflow-y: scroll;"><div class="last-monitor-table" style="display: table;width: 100%;">';

                foreach ($new_data as $item) {
                    if ( $item['keyword'] == $k_word ) {
                        $img = isset( $item['image'] ) ? $item['image'] : '';
                        $msg .= '<div class="row" style="display: table-row;background: #fbc3d5;"><div class="cell image" style="text-align: center;display: table-cell;vertical-align: middle;width: 200px;padding: 20px;border-bottom: 1px solid #fff;">' . $img . '</div><div class="cell" style="display: table-cell;vertical-align: middle;width: 200px;padding: 20px;border-bottom: 1px solid #fff;">' . $item['description'] . '</div></div>';
                    }
                }
                foreach ($other_data as $oth_item) {
                    if ( $oth_item['keyword'] == $k_word ) {
                        $img = isset( $oth_item['image'] ) ? $oth_item['image'] : '';
                        $msg .= '<div class="row" style="display: table-row;"><div class="cell image" style="text-align: center;display: table-cell;vertical-align: middle;width: 200px;padding: 20px;border-bottom: 1px solid #fff;">' . $img . '</div><div class="cell" style="display: table-cell;vertical-align: middle;width: 200px;padding: 20px;border-bottom: 1px solid #fff;">' . $oth_item['description'] . '</div></div>';
                    }
                }

                $msg .= '</div><!-- End Table--></div>';

            }
        }
        $msg .= '<div class="last-monitor-option-table" style="border-top: 1px dashed #fff;display: table;width: 100%;"><div style="display: table-row;"><div style="display: table-cell;vertical-align: middle;width: 200px;padding: 20px;"></div><div style="display: table-cell;vertical-align: middle;width: 200px;padding: 20px;"></div><div style="display: table-cell;vertical-align: middle;width: 200px;padding: 20px;"></div></div></div>';
        $msg .= '</div><!-- End last-monitor-wrapper --></div>';

        $subject = __( 'Avito Parsing Info', 'alm-avito-ads-monitor' );
        $headers = "Content-Type: text/html \r\n From: Alm Avito Ads Monitor <noanswer@". $_SERVER['HTTP_HOST'] .">" . "\r\n";
        wp_mail( $this->avito_email_option, $subject, $msg, $headers );
    }

    /**
     * Check Avito saved data and enable to replace or not
     * @return array
     */
    public function avito_search_new() {
        $res_arr = array();
        if ( !empty( $this->avito_db_data ) ) {
            $data_from_db = json_decode( $this->avito_db_data[0]->data, true );
            $diff_arr = array_diff_key( $this->avito_monitor_data, $data_from_db );
            if ( !empty( $diff_arr ) ) {
                $res_arr = $diff_arr;
            }
        } else {
            $res_arr = $this->avito_monitor_data;
        }
        return $res_arr;
    }

    /**
     * Start searching with cron for every 2 hours in day
     * @return void
     */
    public function load_searching () {
        add_filter( 'cron_schedules', array( $this, 'alio_interval' ) );
        add_action( 'cron_daily', array( $this, 'alio_cron_daily' ) );
    }

    public function alio_cron_activation () {
        wp_schedule_event( time(), 'every_2', 'cron_daily' );
    }

    public function alio_cron_deactivation () {
        wp_clear_scheduled_hook( 'cron_daily' );
    }

    public function alio_interval ( $schedule ) {
        $schedule['every_2'] = array(
            'interval' => 7200,
            'display' => 'Every 2 hours'
        );
        return $schedule;
    }

    /**
     * Main parsing searching actions
     * @return void
     */
    public function alio_cron_daily() {
        if ( $this->avito_enable_option ) {
            $this->alio_parse_avito();
        }
    }

    /**
     * Main Alm_Avito_Ads_Monitor Instance
     *
     * Ensures only one instance of Alm_Avito_Ads_Monitor is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see Alm_Avito_Ads_Monitor()
     * @param string $file
     * @param string $version
     * @return Alm_Avito_Ads_Monitor instance
     */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if (self::$_instance === null) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
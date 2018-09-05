<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Alio_Ads_Monitor {

	/**
	 * The single instance of Alio_Ads_Monitor.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

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

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
        date_default_timezone_set('Asia/Omsk');
        $this->_version = $version;
        $this->_token = 'alio_ads_monitor';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
        $this->avito_enable_option = get_option('aam_avito_enable');
        $this->avito_city_option = $this->transliterate( $this->clear( get_option('aam_avito_city') ) );
        $this->avito_keys_option = get_option('aam_avito_keys');
        $this->avito_email_option = get_option('aam_avito_email');
        $this->avito_keywords_array = $this->get_keys_array($this->avito_keys_option);
        $this->upd_avito_db_data();

		register_activation_hook( $this->file, array( $this, 'install' ) );
        register_activation_hook( $this->file, array( $this, 'alio_cron_activation' ) );
        register_deactivation_hook( $this->file, array( $this, 'alio_cron_deactivation' ) );

        // Load admin JS & CSS
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Alio_Ads_Monitor_Admin_API();
		}

		// Handle localisation
		$this->load_localisation();

		// Create DB table
		$this->db_install();

		// Start searching
        $this->load_searching();
	} // End __construct ()

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
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
     * @return  void
     */
    public function admin_enqueue_scripts ( $hook = '' ) {
        wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
        wp_enqueue_script( $this->_token . '-admin' );
        $this->load_ajax_scripts();
    } // End admin_enqueue_scripts ()

    public function load_ajax_scripts() {
        wp_localize_script( 'jquery', 'ajaxUrl', array(
            'url' => admin_url( 'admin-ajax.php' )
        ) );
    }

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'alio-ads-monitor', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

    /**
     * Create plugin DB table
     * @return void
     */
    private function db_install() {
        global $wpdb;
        $wpdb->show_errors( true );
        $table_name = $wpdb->prefix . "alio_ads_monitor";
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

    public function upd_avito_db_data() {
        global $wpdb;
        $wpdb->show_errors( true );
        $this->avito_db_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'alio_ads_monitor WHERE site="%s"', 'avito' ) );
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
     * Get keys aray from the string option
     * @param $str
     * @return array
     */
    function get_keys_array( $str = '' ) {
        $k_array = explode( ',', $this->clear( $str ), -1 );
        return $k_array;
    }

    /**
     * Parse and save Avito data in DB
     * @return void
     */
    public function alio_parse_avito() {
        $site = 'avito';
        $city = ( $this->avito_city_option ) ? $this->avito_city_option : 'omsk';
        $avito_monitor_data = array();
        $search_date = time();

        foreach ( $this->avito_keywords_array as $key ) {
            $url = 'https://www.avito.ru/' . $city . '?s_trg=3&q=' . $key;
            $file = file_get_contents( $url );
            if ( $doc = phpQuery::newDocumentHTML( $file, 'utf-8' ) ) {
                foreach ($doc->find('div.item.item_table') as $item_table) {
                    $item_table = pq($item_table);
                    $avito_item_id = $item_table->attr('id');

                    $excludes_from_db = $this->avito_db_data[0]->exclude_items;
                    $exclude_arr = ( !empty( $excludes_from_db ) ) ? json_decode( $excludes_from_db, true ) : array();
                    if ( !empty( $exclude_arr ) && in_array( $avito_item_id, $exclude_arr ) ) continue;
                    $bad_symbols = array('background-image: url(', ')', ';');
                    $img_from_ul_style = $item_table->find('a.large-picture .item-slider-list .item-slider-item:eq(0) div.item-slider-image')->attr('style');
                    $img_from_ul_srcpath = $item_table->find('a.large-picture .item-slider-list .item-slider-item:eq(0) div.item-slider-image')->attr('data-srcpath');
                    $img_from_a = $item_table->find('a.large-picture > img.large-picture')->attr('data-srcpath');

                    $item_title = $item_table->find('div.item_table-header > h3 > a span')->text();
                    $item_link = $item_table->find('div.item_table-header > h3 > a')->attr('href');
                    if ( $item_link ) {
                        $item_link = 'https://www.avito.ru/' . $item_link;
                    }
                    $item_price = $item_table->find('div.item_table-header span.price')->html();

                    if ( $img_from_ul_style && !$img_from_ul_srcpath ) {
                        $img_from_ul_style = 'http:' . str_replace( $bad_symbols, '', $img_from_ul_style );
                        $avito_monitor_data[$avito_item_id]['image'] = '<img alt="" src="' . $img_from_ul_style . '">';
                    } elseif ( $img_from_ul_srcpath ) {
                            $avito_monitor_data[$avito_item_id]['image'] = '<img alt="" src="http:' . $img_from_ul_srcpath . '">';
                    } elseif ( $img_from_a ) {
                            $avito_monitor_data[$avito_item_id]['image'] = '<img alt="" src="http:' . $img_from_a . '">';
                    }
                    $avito_monitor_data[$avito_item_id]['keyword'] = $key;
                    $avito_monitor_data[$avito_item_id]['description'] = '<div><h3><a href="' . $item_link . '" target="blank">' . $item_title. '</a></h3>' . $item_price . '</div>';
                }
            }
        }
//$avito_monitor_data['lol696894409'] = array('keyword' => 'опал', 'image' => '', 'description' => '');
        global $wpdb;
        $wpdb->show_errors( true );
        
        $new_data = $this->avito_search_new( $avito_monitor_data );

        if ( empty( $this->avito_db_data ) ) {
            $wpdb->insert( $wpdb->prefix . 'alio_ads_monitor', array(
                'ID' => '',
                'search_date' => date('Y/m/d H:i:s', $search_date ),
                'site' => $site,
                'data' => json_encode( $avito_monitor_data, JSON_UNESCAPED_UNICODE ),
                'new_data' => json_encode( $new_data, JSON_UNESCAPED_UNICODE ),
            ) );
        } else {
            $wpdb->update( $wpdb->prefix . 'alio_ads_monitor', array(
                'search_date' => date('Y/m/d H:i:s', $search_date ),
                'site' => $site,
                'data' => json_encode( $avito_monitor_data, JSON_UNESCAPED_UNICODE ),
                'new_data' => json_encode( $new_data, JSON_UNESCAPED_UNICODE ),
            ), array('id' => $this->avito_db_data[0]->id) );
        }
        $this->upd_avito_db_data();

        if ( $this->avito_email_option && !empty( $new_data ) ) {
error_log(print_R('not empty new data:', true));
error_log(print_R($new_data, true));
            add_action( 'plugins_loaded', array( $this, 'avito_send_mail' ) );
        }

    }

    /**
     * Send email with Avito monitor results
     * @return void
     */
    public function avito_send_mail() {
        $new_data = json_decode( $this->avito_db_data[0]->new_data, true );
        $all_data = json_decode( $this->avito_db_data[0]->data, true );
        $other_data = array_diff_key( $all_data, $new_data );
        $msg  = '';
        $descr_text = ( $this->avito_city_option && $this->avito_keys_option ) ? __( 'Search Ads in ', 'alio-ads-monitor' ) . $this->avito_city_option . __( ' city using keywords: ', 'alio-ads-monitor' ) . $this->avito_keys_option : __( 'City and search keyword was not specified!', 'alio-ads-monitor' );
        $msg .= '<div style="text-align: right;"><a href="' . get_site_url() . '/wp-admin/options-general.php?page=alio_ads_monitor_settings" target="blank">' . __( 'Go to Settings Page to customize settings or exclude items', 'alio-ads-monitor' ) . '</a></div>
        <div style="width:100%;min-height:100%;margin:0;padding:0;background-color:#eeeeee;border-radius: 25px;">
        <h2 style="padding: 20px;border-bottom: 1px dashed;text-align: center;">' . __( 'Avito Monitoring', 'alio-ads-monitor' ) . '</h2>
        <p style="text-align: center;">' . $descr_text . '</p>
        <table border="0" cellpadding="0" cellspacing="0" valign="top" style="margin-left:auto;margin-right:auto;width: 100%;"><tbody>';
        foreach( $this->avito_keywords_array as $k_word ) {
            $msg .= '<tr><td colspan="2" bgcolor="#6c7ae0" width="100" height="59" style="text-align: center;font-weight: bold;">' . __( 'Keyword: ', 'alio-ads-monitor' ) . $k_word . '</td></tr>';
            if ( !empty( $new_data ) ) {
                foreach ($new_data as $new_item_arr) {
                    if ( $new_item_arr['keyword'] == $k_word ) {
                        $msg .= '<tr style="background: #c4d3f6;"><td style="padding: 20px;border-bottom: 1px solid #fff;text-align: center;">' . $new_item_arr['image'] . '</td><td style="padding: 20px;border-bottom: 1px solid #fff;">' . $new_item_arr['description'] . '</td></tr>';
                    }
                }
            }
            foreach ($other_data as $item) {
                if ( $item['keyword'] == $k_word ) {
                    $msg .= '<tr><td style="padding: 20px;border-bottom: 1px solid #fff;text-align: center;">' . $item['image'] . '</td><td style="padding: 20px;border-bottom: 1px solid #fff;">' . $item['description'] . '</td></tr>';
                }
            }
        }
        $msg .= '</tbody></table></div>';
        $subject = "Avito Parsing Info";
        $headers = "Content-Type: text/html \r\n From: Alio Ads Monitor <noanswer@". $_SERVER['HTTP_HOST'] .">" . "\r\n";
        //$result = wp_mail( $this->avito_email_option, $subject, $msg, $headers );
//error_log(print_R('email send?', true));
//error_log(print_R($result, true));
    }

    /**
     * Check Avito saved data and enable to replace or not
     * @param $query
     * @param $data_from_monitor
     * @return array
     */
    public function avito_search_new( $data_from_monitor ) {
        $res_arr = array();
        if ( !empty( $this->avito_db_data ) ) {
            $data_from_db = json_decode( $this->avito_db_data[0]->data, true );
            $diff_arr = array_diff_key( $data_from_monitor, $data_from_db );
            if ( !empty( $diff_arr ) ) {
                $res_arr = $diff_arr;
            }
        } else {
            $res_arr = $data_from_monitor;
        }
        return $res_arr;
    }

    /**
     * Start searching with cron for every 2 hours in day
     * @return void
     */
    public function load_searching () {
error_log(print_R('from load searching foo', true));
// код который потом будет в кроновской функции - тут пишу чтоб сразу запускался - удали комм когда все перенесешь
        if ( $this->avito_enable_option ) {
            $this->alio_parse_avito();
        }



        // кроновский код конец

        add_filter( 'cron_schedules', array( $this, 'alio_interval' ) );
        add_action( 'cron_daily', array( $this, 'alio_cron_daily' ) );
    }

    public function alio_cron_activation () {
error_log(print_R('in cron activation ' . time(), true));
        wp_schedule_event( time(), 'every_2', 'cron_daily' );
    }

    public function alio_cron_deactivation () {
error_log(print_R('from cron deactivation ' . time(), true));
        wp_clear_scheduled_hook( 'cron_daily' );
    }

    public function alio_interval ( $schedule ) {
error_log(print_R('from alio interval ' . time(), true));
        $schedule['every_2'] = array(
            'interval' => 7200,
            'display' => 'Every 2 hours'
        );
        $schedule['every_05'] = array(
            'interval' => 300,
            'display' => 'Every 05 minutes'
        );
        return $schedule;
    }

    /**
     * Main parsing searching actions
     * @return void
     */
    public function alio_cron_daily() {
error_log(print_R('from alio_cron_daily', true));
        // сюда все переносим
        $this->cron_testing();
    }

    public function cron_testing() {
error_log(print_R('from testing cron', true));
        $msg  = 'cron test';
        $my_post = array(
            'post_title' => '>>>> Report on the work of Cron ' . date('Y-m-d H:i:s'),
            'post_content' => $msg,
            'post_status' => 'publish',
            'post_author' => 1,
            'post_category' => array(0)
        );
        wp_insert_post( $my_post );
    }

	/**
	 * Main Alio_Ads_Monitor Instance
	 *
	 * Ensures only one instance of Alio_Ads_Monitor is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Alio_Ads_Monitor()
	 * @return Main Alio_Ads_Monitor instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
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
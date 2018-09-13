<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Alm_Avito_Ads_Monitor_Settings {

	/**
	 * The single instance of Alm_Avito_Ads_Monitor_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'aam_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

        add_action( 'wp_ajax_nopriv_exclude_avito_item', array( $this, 'exclude_avito_item' ) );
        add_action( 'wp_ajax_exclude_avito_item', array( $this, 'exclude_avito_item' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		add_options_page( __( 'Ads Monitor Settings', 'alm-avito-ads-monitor' ) , __( 'Avito Ads Monitor', 'alm-avito-ads-monitor' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'alm-avito-ads-monitor' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

        $settings['avito'] = array(
            'title'                 => '',
            'description'           => '',
            'fields'                => array(
                array(
                    'id' 			=> 'avito_enable',
                    'label'			=> __( 'Enable Avito Ads Monitoring', 'alm-avito-ads-monitor' ),
                    'description'	=> __( '' ),
                    'type'			=> 'checkbox',
                    'default'		=> ''
                ),
                array(
                    'id'            => 'avito_city',
                    'label'         => __( 'Search in City' , 'alm-avito-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => '',
                ),
                array(
                    'id'            => 'avito_keys',
                    'label'         => __( 'Keywords comma separated' , 'alm-avito-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => __( 'Example: "Dog, cat, bird"', 'alm-avito-ads-monitor' )
                ),
                array(
                    'id'            => 'avito_email',
                    'label'         => __( 'Notify to e-mail' , 'alm-avito-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => __( 'Please enter your email address to receive notification in case of success finding new ads according to your request', 'alm-avito-ads-monitor' ),
                ),
            )
        );

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

            foreach ( $this->settings as $section => $data ) {

                // Add section to page
                add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );


                foreach ( $data['fields'] as $field ) {

                    // Validation callback for field
                    $validation = '';
                    if ( isset( $field['callback'] ) ) {
                        $validation = $field['callback'];
                    }

                    // Register field
                    $option_name = $this->base . $field['id'];
                    register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}
			}
		}
	}

    public function settings_section ( $section ) {
        $html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>';
        echo $html;
    }

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2 class="dashicons-before dashicons-admin-generic options-icon">' . __( 'Avito Ads Monitor Settings' , 'alm-avito-ads-monitor' ) . '</h2>' . "\n";

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'alm-avito-ads-monitor' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";

		$html .= $this->avito_last_monitor_results();

		$html .= '</div>' . "\n";

		echo $html;
	}

    /**
     * Ajax exclude avito item from monitor
     */
    public function exclude_avito_item() {

        if ( !empty( $this->parent->avito_db_data ) ) {
            $excludes_from_db = $this->parent->avito_db_data[0]->exclude_items;
            $exclude_arr = !empty( $excludes_from_db ) ? json_decode( $excludes_from_db, true ) : array();
            $exclude_id = !empty( $_POST['itemID'] ) ? $_POST['itemID'] : false;
            $exclude_ids = ( !empty( $_POST['excludeIDs'] ) && is_array( $_POST['excludeIDs'] ) ) ? $_POST['excludeIDs'] : false ;
                $res = '';

            if ( $exclude_id ) {
                $exclude_arr[] = $exclude_id;
                $res = 1;
            }
            if ( $exclude_ids ) {
                foreach ( $exclude_ids as $item ) {
                    $exclude_arr[] = $item;
                }
                $res = json_encode( $exclude_ids, JSON_UNESCAPED_UNICODE );
            }
            global $wpdb;
                $wpdb->show_errors( true );
                $wpdb->update( $wpdb->prefix . 'alm_ads_monitor', array(
                    'exclude_items' => json_encode( $exclude_arr, JSON_UNESCAPED_UNICODE )
                ), array('id' => $this->parent->avito_db_data[0]->id) );
                $this->parent->upd_avito_db_data();
                $this->parent->alio_parse_avito();
                echo $res;
        }
        wp_die();
    }

    /**
     * Get last Avito parsing data saved in DB
     * @return string
     */
    public function avito_last_monitor_results() {
        $out = '';

        if ( !empty( $this->parent->avito_db_data ) && !empty( $this->parent->avito_keys_option ) && $this->parent->avito_enable_option && !$this->parent->blocked ) {
            $new_data = json_decode($this->parent->avito_db_data[0]->new_data, true);
            $all_data = json_decode($this->parent->avito_db_data[0]->data, true);

            $city_from_opts = get_option('aam_avito_city');

            $keywords = $this->parent->avito_keywords_array;
            $other_data = array_diff_key($all_data, $new_data);

            $descr_text = ($this->parent->avito_city_option && $this->parent->avito_keys_option) ? __('Search Ads in ', 'alm-avito-ads-monitor') . $city_from_opts . __(' city using keywords: ', 'alm-avito-ads-monitor') . $this->parent->avito_keys_option : __('City and/or search keyword is not specified!', 'alm-avito-ads-monitor');

            $out .= '<div class="last-monitor-holder"><div class="title-block"><h2>' . __('Last Avito Monitor Results', 'alm-avito-ads-monitor') . '</h2>
            <p class="last-monitor-descr">' . $descr_text . '</p>';
            if (!empty($this->parent->avito_db_data[0]->search_date)) {
                $out .= '<p class="last-monitor-descr">' . __( 'Last Parsing ', 'alm-avito-ads-monitor' ) . $this->parent->avito_db_data[0]->search_date . ' MSK</p>';
            }
            $out .= '</div><div class="last-monitor-wrapper"><div class="last-monitor-loader"></div>';

            if (!empty($keywords)) {
                foreach ($keywords as $k_word) {
                    $out .= '<div class="table-header"><h4 class="keyword-heading">' . __('Keyword: ', 'alm-avito-ads-monitor') . $k_word . '</h4></div>';
                    $out .= '<div class="table-scrolling-container"><div class="last-monitor-table" data-keyword="' . $k_word . '">';
                    if (!empty($new_data)) {
                        foreach ($new_data as $item_id => $item) {
                            if ($item['keyword'] == $k_word) {
                                $img = isset( $item['image'] ) ? $item['image'] : '';
                                $out .= '<div class="row new item-block item' . $item_id . '"><div class="cell monitor-item image">' . $img . '</div><div class="cell monitor-item">' . $item['description'] . '</div><div class="cell monitor-item"><div class="bulk-checkbox-holder"><label class="checkcontainer"><strong class="bulk-checkbox-title">' . __( 'Exclude from monitoring', 'alm-avito-ads-monitor') . '</strong><input type="checkbox" name="bulk-exclude-checks" data-exclude-id="' . $item_id . '"></label></div></div></div>';
                            }
                        }
                    }
                    if (!empty($other_data)) {
                        foreach ($other_data as $item_id => $item) {
                            $img = isset( $item['image'] ) ? $item['image'] : '';
                            $dscr = isset( $item['description'] ) ? $item['description'] : '';
                            if ($item['keyword'] == $k_word) {
                                $out .= '<div class="row item-block item' . $item_id . '"><div class="cell monitor-item image">' . $img . '</div><div class="cell monitor-item">' . $dscr . '</div><div class="cell monitor-item"><div class="bulk-checkbox-holder"><label class="checkcontainer"><strong class="bulk-checkbox-title">' . __( 'Exclude from monitoring', 'alm-avito-ads-monitor') . '</strong><input type="checkbox" name="bulk-exclude-checks" data-exclude-id="' . $item_id . '"></label></div></div></div>';
                            }
                        }
                    }
                    $out .= '</div><!-- End Last monitor table --></div>';
                }
            }
            $out .= '<div class="footer-block last-monitor-option-table"><div class="row"><div class="cell"></div><div class="cell"></div><div class="cell"><button class="button-primary js-bulk-avito-exclude">' . __( 'Bulk Exclude Items', 'alm-avito-ads-monitor') . '</button></div></div></div>';
            $out .= '</div></div>';
        }
        if ( $this->parent->blocked ) {
            $out .= __( 'It seems that you are too frequently changed settings and the site Avito this activity seemed suspicious. There is nothing to worry, just go to the site avito and confirm that you are not a robot - ', 'alm-avito-ads-monitor' ) . '<a href="https://www.avito.ru">' . __( 'go to avito website' ) . '</a>';
        }
        return $out;
	}

    /**
     * Main Alm_Avito_Ads_Monitor_Settings Instance
     *
     * Ensures only one instance of Alm_Avito_Ads_Monitor_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see Alm_Avito_Ads_Monitor()
     * @param $parent
     * @return Alm_Avito_Ads_Monitor_Settings instance
     */
	public static function instance ( $parent ) {
		if (self::$_instance === null) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
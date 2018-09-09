<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Alio_Avito_Ads_Monitor_Settings {

	/**
	 * The single instance of Alio_Avito_Ads_Monitor_Settings.
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
		add_options_page( __( 'Ads Monitor Settings', 'alio-avito-ads-monitor' ) , __( 'Avito Ads Monitor', 'alio-avito-ads-monitor' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'alio-avito-ads-monitor' ) . '</a>';
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
                    'label'			=> __( 'Enable Avito Ads Monitoring', 'alio-avito-ads-monitor' ),
                    'description'	=> __( '' ),
                    'type'			=> 'checkbox',
                    'default'		=> ''
                ),
                array(
                    'id'            => 'avito_city',
                    'label'         => __( 'Search in City' , 'alio-avito-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => '',
                ),
                array(
                    'id'            => 'avito_keys',
                    'label'         => __( 'Keywords comma separated' , 'alio-avito-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => 'Example: "Dog, cat, bird"',
                ),
                array(
                    'id'            => 'avito_email',
                    'label'         => __( 'Notify to e-mail:' , 'alio-avito-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => 'Please enter your email address to receive notification in case of success finding new ads according to your request',
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
			$html .= '<h2 class="dashicons-before dashicons-admin-generic options-icon">' . __( 'Avito Ads Monitor Settings' , 'alio-avito-ads-monitor' ) . '</h2>' . "\n";

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'alio-avito-ads-monitor' ) ) . '" />' . "\n";
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
                $wpdb->update( $wpdb->prefix . 'alio_ads_monitor', array(
                    'exclude_items' => json_encode( $exclude_arr, JSON_UNESCAPED_UNICODE )
                ), array('id' => $this->parent->avito_db_data[0]->id) );
                $this->parent->upd_avito_db_data();
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
        if ( !empty( $this->parent->avito_db_data ) && !empty( $this->parent->avito_keys_option ) ) {
            $new_data = json_decode($this->parent->avito_db_data[0]->new_data, true);
            $all_data = json_decode($this->parent->avito_db_data[0]->data, true);

            $keywords = $this->parent->avito_keywords_array;
            $other_data = array_diff_key($all_data, $new_data);

            $descr_text = ($this->parent->avito_city_option && $this->parent->avito_keys_option) ? __('Search Ads in ', 'alio-avito-ads-monitor') . $this->parent->avito_city_option . __(' city using keywords: ', 'alio-avito-ads-monitor') . $this->parent->avito_keys_option : __('City and search keyword was not specified!', 'alio-avito-ads-monitor');

            $out .= '<div class="last-monitor-holder"><div class="title-block"><h2>' . __('Last Avito Monitor Results', 'alio-avito-ads-monitor') . '</h2>
            <p class="last-monitor-descr">' . $descr_text . '</p>';
            if (!empty($this->parent->avito_db_data[0]->search_date)) {
                $out .= '<p class="last-monitor-descr">Last Parsing ' . $this->parent->avito_db_data[0]->search_date . '</p>';
            }
            $out .= '</div><div class="last-monitor-wrapper"><div class="last-monitor-loader"></div>';

            if (!empty($keywords)) {
                foreach ($keywords as $k_word) {
                    $out .= '<div class="table-header"><h4 class="keyword-heading">' . __('Keyword: ', 'alio-avito-ads-monitor') . $k_word . '</h4></div>';
                    $out .= '<div class="table-scrolling-container"><div class="last-monitor-table" data-keyword="' . $k_word . '">';
                    if (!empty($new_data)) {
                        foreach ($new_data as $item_id => $item) {
                            if ($item['keyword'] == $k_word) {
                                $out .= '<div class="row new item-block item' . $item_id . '"><div class="cell monitor-item image">' . $item['image'] . '</div><div class="cell monitor-item">' . $item['description'] . '</div><div class="cell monitor-item"><a href="" class="exclude-item js-avito-exclude" data-exclude-id="' . $item_id . '">Exclude item from monitoring</a><div class="bulk-checkbox-holder"><h3>or</h3><label class="checkcontainer"><span class="bulk-checkbox-title">bulk exclude</span><input type="checkbox" name="bulk-exclude-checks" data-exclude-id="' . $item_id . '"></label></div></div></div>';
                            }
                        }
                    }
                    if (!empty($other_data)) {
                        foreach ($other_data as $item_id => $item) {
                            $img = !empty( $item['image'] ) ? $item['image'] : '';
                            $dscr = !empty( $item['description'] ) ? $item['description'] : '';
                            if ($item['keyword'] == $k_word) {
                                $out .= '<div class="row item-block item' . $item_id . '"><div class="cell monitor-item image">' . $img . '</div><div class="cell monitor-item">' . $dscr . '</div><div class="cell monitor-item"><a href="" class="exclude-item js-avito-exclude" data-exclude-id="' . $item_id . '">Exclude item from monitoring</a><div class="bulk-checkbox-holder"><h3>or</h3><label class="checkcontainer"><span class="bulk-checkbox-title">bulk exclude</span><input type="checkbox" name="bulk-exclude-checks" data-exclude-id="' . $item_id . '"></label></div></div></div>';
                            }
                        }
                    }
                    $out .= '</div><!-- End Last monitor table --></div>';
                }
            }
            $out .= '<div class="footer-block last-monitor-option-table"><div class="row"><div class="cell"></div><div class="cell"></div><div class="cell"><button class="button-primary js-bulk-avito-exclude">Bulk Exclude Items</button></div></div></div>';
            $out .= '</div></div>';
        }
        return $out;
	}

    /**
     * Main Alio_Avito_Ads_Monitor_Settings Instance
     *
     * Ensures only one instance of Alio_Avito_Ads_Monitor_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see Alio_Avito_Ads_Monitor()
     * @param $parent
     * @return Alio_Avito_Ads_Monitor_Settings instance
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
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Alio_Ads_Monitor_Settings {

	/**
	 * The single instance of Alio_Ads_Monitor_Settings.
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
		$page = add_options_page( __( 'Ads Monitor Settings', 'alio-ads-monitor' ) , __( 'Ads Monitor', 'alio-ads-monitor' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'alio-ads-monitor' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

        $settings['avito'] = array(
            'title'                 => __( 'Avito Website Monitoring', 'alio-ads-monitor' ),
            'description'           => __( 'Parsing Settings', 'alio-ads-monitor' ),
            'fields'                => array(
                array(
                    'id' 			=> 'avito_enable',
                    'label'			=> __( 'Enable Avito Ads Monitoring', 'alio-ads-monitor' ),
                    'description'	=> __( '' ),
                    'type'			=> 'checkbox',
                    'default'		=> ''
                ),
                array(
                    'id'            => 'avito_city',
                    'label'         => __( 'Search in City' , 'alio-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => '',
                ),
                array(
                    'id'            => 'avito_keys',
                    'label'         => __( 'Keywords comma separated' , 'alio-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => 'Example: "Dog, cat, bird"',
                ),
                array(
                    'id'            => 'avito_email',
                    'label'         => __( 'Notify to e-mail:' , 'alio-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => 'Please enter your email address to receive notification in case of success finding new ads according to your request',
                ),
            )
        );
        $settings['darudar'] = array(
            'title'                 => __( 'DaruDar Website Monitoring', 'alio-ads-monitor' ),
            'description'           => __( 'Parsing Settings', 'alio-ads-monitor' ),
            'fields'                => array(
                array(
                    'id' 			=> 'darudar_enable',
                    'label'			=> __( 'Enable DaruDar Ads Monitoring', 'alio-ads-monitor' ),
                    'description'	=> __( '' ),
                    'type'			=> 'checkbox',
                    'default'		=> ''
                ),
                array(
                    'id'            => 'darudar_city',
                    'label'         => __( 'Search in City' , 'alio-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => '',
                ),
                array(
                    'id'            => 'darudar_keys',
                    'label'         => __( 'Keywords comma separated' , 'alio-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => 'Example: "Dog, cat, bird"',
                ),
                array(
                    'id'            => 'darudar_email',
                    'label'         => __( 'Notify to e-mail:' , 'alio-ads-monitor' ),
                    'type'          => 'text',
                    'default'       => '',
                    'placeholder'   => '',
                    'description'   => 'Please enter your email address to receive notification in case of success finding new ads according to your request',
                ),
            )
        );
		$settings['omskmama'] = array(
			'title'					=> __( 'Omskmama Website Monitoring', 'alio-ads-monitor' ),
			'description'			=> __( 'Parsing Settings', 'alio-ads-monitor' ),
			'fields'				=> array(
                array(
                    'id' 			=> 'omskmama_enable',
                    'label'			=> __( 'Enable Omskmama Ads Monitoring', 'alio-ads-monitor' ),
                    'description'	=> '',
                    'type'			=> 'checkbox',
                    'default'		=> ''
                ),
                array(
                    'id'            => 'omskmama_categories',
                    'label'         => __( 'Select search categories', 'alio-ads-monitor' ),
                    'type'          => 'checkbox_multi',
                    'options'       => array(
                                        '143' => 'Предметы интерьера, ухода, гигиены для детей',
                                        '205' => 'Одежда и обувь для малышей до трех лет',
                                        '206' => 'Одежда и обувь для девочек от 3 дет',
                                        '207' => 'Одежда и обувь для мальчиков от 3 лет',
                                        '214' => 'Одежда и обувь для школьников и подростков',
                                        '219' => 'Верхняя одежда взрослая',
                                        '220' => 'Легкая одежда взрослая',
                                        '221' => 'Обувь взрослая',
                                        '144' => 'Все для домаобустройства и уюта',
                                        '161' => 'Флора и фауна',
                                        '208' => 'Бытовая техника, компьютеры, телефоны',
                                        '209' => 'Косметика, парфюмерия, аксессуары',
                                        '210' => 'Продукты питания',
                                        '215' => 'Товары для поддержания здоровья',
                                        '113' => 'Услуги',
                                        '74' => 'Hand-Made',
                                        '192' => 'Работа',
                                        '83' => 'Недвижимость',
                                        '211' => 'Отдам безвозмездно(или символическую плату)',
                                        '212' => 'Приму в дар',
                                        '213' => 'Обменяю',
                                        '218' => 'Возьму/сдам напрокат',
                                        '85' => 'Куплю',
                                        '132' => 'Объявления для районов области',
                                    ),
                    'description'   => '',
                ),
                array(
                    'id'            => 'omskmama_email',
                    'label'         => __( 'Notify to e-mail:' , 'alio-ads-monitor' ),
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

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}


            foreach ( $this->settings as $section => $data ) {

                if ( $current_section && $current_section != $section ) continue;

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

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Ads Monitor Settings' , 'alio-ads-monitor' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'alio-ads-monitor' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main Alio_Ads_Monitor_Settings Instance
	 *
	 * Ensures only one instance of Alio_Ads_Monitor_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Alio_Ads_Monitor()
	 * @return Main Alio_Ads_Monitor_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
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
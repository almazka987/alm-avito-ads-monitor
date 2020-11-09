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
                    'id'            => 'avito_enable',
                    'label'         => __( 'Enable Avito Ads Monitoring', 'alm-avito-ads-monitor' ),
                    'description'   => __( '' ),
                    'type'          => 'checkbox',
                    'default'       => ''
                ),
                array(
                    'id' 			=> 'avito_test_mode',
                    'label'			=> __( 'Enable Test Mode (Force parsing every page update)', 'alm-avito-ads-monitor' ),
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
                    'id'            => 'avito_category',
                    'label'         => __( 'Category' , 'alm-avito-ads-monitor' ),
                    'type'        => 'select',
					'options'     => array(
						'any' => 'Любая',
						'dlya_doma_i_dachi?cd=1' => 'Для дома и дачи',

						'remont_i_stroitelstvo?cd=1' => 'Ремонт и строительство',
						'remont_i_stroitelstvo/santehnika_i_sauna-ASgBAgICAURYngI?cd=1' => 'Сантехника и сауна',
						'remont_i_stroitelstvo/stroymaterialy-ASgBAgICAURYoAI?cd=1' => 'Стройматериалы',
						'remont_i_stroitelstvo/stroymaterialy/drugoe-ASgBAgICAkRYoAKmvg2mxDU?cd=1' => 'Другое',
						'remont_i_stroitelstvo/stroymaterialy/obshestroitelnye_materialy-ASgBAgICAkRYoAKmvg2Kgzc?cd=1' => 'Общестроительные материалы',
						'remont_i_stroitelstvo/stroymaterialy/laki_i_kraski-ASgBAgICAkRYoAKmvg2cxDU?cd=1' => 'Лаки и краски',
						'remont_i_stroitelstvo/stroymaterialy/otdelka-ASgBAgICAkRYoAKmvg2ixDU?cd=1' => 'Отделка',
						'remont_i_stroitelstvo/stroymaterialy/krovlya_i_vodostok-ASgBAgICAkRYoAKmvg2axDU?cd=1' => 'Кровля и водосток',
						'remont_i_stroitelstvo/stroymaterialy/pilomaterialy-ASgBAgICAkRYoAKmvg2QxDU?cd=1' => 'Пиломатериалы',
						'remont_i_stroitelstvo/okna_i_balkony-ASgBAgICAURYoEs?cd=1' => 'Окна и балконы',
						'remont_i_stroitelstvo/instrumenty-ASgBAgICAURYoks?cd=1' => 'Инструменты',
						'remont_i_stroitelstvo/dveri-ASgBAgICAURYnEs?cd=1' => 'Двери',

						'mebel_i_interer?cd=1' => 'Мебель и интерьер',
						'mebel_i_interer/predmety_interera_iskusstvo-ASgBAgICAURapAI?cd=1' => 'Предметы интерьера, искусство',
						'mebel_i_interer/kuhonnye_garnitury-ASgBAgICAURazk8?cd=1' => 'Кухонные гарнитуры',
						'mebel_i_interer/drugoe-ASgBAgICAURatgI?cd=1' => 'Другое',
						'mebel_i_interer/podstavki_i_tumby-ASgBAgICAURargI?cd=1' => 'Подставки и тумбы',
						'mebel_i_interer/shkafy_i_komody-ASgBAgICAURaqAI?cd=1' => 'Шкафы и комоды',
						'mebel_i_interer/osveschenie-ASgBAgICAURarAI?cd=1' => 'Освещение',
						'mebel_i_interer/stoly_i_stulya-ASgBAgICAURasAI?cd=1' => 'Столы и стулья',
						'mebel_i_interer/myagkaya_mebel-ASgBAgICAURaqgI?cd=1' => 'Кровати, диваны и кресла',
						'mebel_i_interer/myagkaya-mebel/krovati-ASgBAgICAkRaqgKMvg3~rTU?cd=1' => 'Кровати',
						'mebel_i_interer/myagkaya-mebel/divany-ASgBAgICAkRaqgKMvg2ArjU?cd=1' => 'Диваны',
						'mebel_i_interer/myagkaya-mebel/kresla-ASgBAgICAkRaqgKMvg2MrjU?cd=1' => 'Кресла',
						'mebel_i_interer/myagkaya-mebel/matrasy-ASgBAgICAkRaqgKMvg2OrjU?cd=1' => 'Матрасы',
						'mebel_i_interer/myagkaya-mebel/prochee-ASgBAgICAkRaqgKMvg2QrjU?cd=1' => 'Прочее',

						'posuda_i_tovary_dlya_kuhni?cd=1' => 'Посуда и товары для кухни',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola-ASgBAgICAUSiww3i3Tk?cd=1' => 'Сервировка стола',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/nabory_posudy_i_servizy-ASgBAgICAkSiww3i3Tmkww3w3Tk?cd=1' => 'Наборы посуды и сервизы',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/kruzhki_blyudca_pary-ASgBAgICAkSiww3i3Tmkww3y3Tk?cd=1' => 'Кружки, блюдца, пары',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/bokaly_i_stakany-ASgBAgICAkSiww3i3Tmkww303Tk?cd=1' => 'Бокалы и стаканы',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/predmety_servirovki-ASgBAgICAkSiww3i3Tmkww323Tk?cd=1' => 'Предметы сервировки',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/tarelki-ASgBAgICAkSiww3i3Tmkww343Tk?cd=1' => 'Тарелки',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/vazy-ASgBAgICAkSiww3i3Tmkww363Tk?cd=1' => 'Вазы',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/stolovye_pribory-ASgBAgICAkSiww3i3Tmkww383Tk?cd=1' => 'Столовые приборы',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/blyuda_i_salatniki-ASgBAgICAkSiww3i3Tmkww3~3Tk?cd=1' => 'Блюда и салатники',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/kuvshiny_i_grafiny-ASgBAgICAkSiww3i3Tmkww2A3jk?cd=1' => 'Кувшины и графины',
						'posuda_i_tovary_dlya_kuhni/servirovka_stola/ryumki_i_stopki-ASgBAgICAkSiww3i3Tmkww2C3jk?cd=1' => 'Рюмки и стопки',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi-ASgBAgICAUSiww3k3Tk?cd=1' => 'Приготовление пищи',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi/kazany_skovorody_soteiniki-ASgBAgICAkSiww3k3Tmmww2E3jk?cd=1' => 'Казаны, сковороды, сотейники',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi/kastryuli_i_kovshi-ASgBAgICAkSiww3k3Tmmww2G3jk?cd=1' => 'Кастрюли и ковши',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi/posuda_i_formy_dlya_vypechki_i_zapekaniya-ASgBAgICAkSiww3k3Tmmww2I3jk?cd=1' => 'Посуда и формы для выпечки и запекания',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi/prinadlezhnosti_dlya_gotovki-ASgBAgICAkSiww3k3Tmmww2K3jk?cd=1' => 'Принадлежности для готовки',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi/grili_barbekyu_tovary_dlya_piknika-ASgBAgICAkSiww3k3Tmmww2M3jk?cd=1' => 'Грили, барбекю, товары для пикника',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_pishi/kukhonnye_pribory-ASgBAgICAkSiww3k3Tmmww2O3jk?cd=1' => 'Кухонные приборы',
						'posuda_i_tovary_dlya_kuhni/khranenie_produktov-ASgBAgICAUSiww3m3Tk?cd=1' => 'Хранение продуктов',
						'posuda_i_tovary_dlya_kuhni/khranenie_produktov/banki_bidony_butylki-ASgBAgICAkSiww3m3Tmoww2Q3jk?cd=1' => 'Банки, бидоны, бутылки',
						'posuda_i_tovary_dlya_kuhni/khranenie_produktov/konteinery_i_lanch_boksy-ASgBAgICAkSiww3m3Tmoww2S3jk?cd=1' => 'Контейнеры и ланч-боксы',
						'posuda_i_tovary_dlya_kuhni/khranenie_produktov/drugoe-ASgBAgICAkSiww3m3Tmoww2U3jk?cd=1' => 'Другое',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_napitkov-ASgBAgICAUSiww3o3Tk?cd=1' => 'Приготовление напитков',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_napitkov/posuda_dlya_prigotovleniya_napitkov-ASgBAgICAkSiww3o3Tmqww2W3jk?cd=1' => 'Посуда для приготовления напитков',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_napitkov/samogonnye_apparaty-ASgBAgICAkSiww3o3Tmqww2Y3jk?cd=1' => 'Самогонные аппараты',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_napitkov/termokruzhki_termosy_flyagi-ASgBAgICAkSiww3o3Tmqww2a3jk?cd=1' => 'Термокружки, термосы, фляги',
						'posuda_i_tovary_dlya_kuhni/prigotovlenie_napitkov/drugoe-ASgBAgICAkSiww3o3Tmqww2c3jk?cd=1' => 'Другое',
						'posuda_i_tovary_dlya_kuhni/khozyaistvennye_tovary-ASgBAgICAUSiww3q3Tk?cd=1' => 'Хозяйственные товары',
						'posuda_i_tovary_dlya_kuhni/khozyaistvennye_tovary/bytovaya_khimiya-ASgBAgICAkSiww3q3Tmsww2e3jk?cd=1' => 'Бытовая химия',
						'posuda_i_tovary_dlya_kuhni/khozyaistvennye_tovary/inventar_dlya_uborki_i_khraneniya-ASgBAgICAkSiww3q3Tmsww2g3jk?cd=1' => 'Инвентарь для уборки и хранения',
						'posuda_i_tovary_dlya_kuhni/kukhonnye_aksessuary-ASgBAgICAUSiww3s3Tk?cd=1' => 'Кухонные аксессуары',
						'posuda_i_tovary_dlya_kuhni/kukhonnye_aksessuary/tekstil_dlya_kukhni-ASgBAgICAkSiww3s3Tmuww2i3jk?cd=1' => 'Текстиль для кухни',
						'posuda_i_tovary_dlya_kuhni/kukhonnye_aksessuary/podstavki_i_derzhateli-ASgBAgICAkSiww3s3Tmuww2k3jk?cd=1' => 'Подставки и держатели',
						'posuda_i_tovary_dlya_kuhni/kukhonnye_aksessuary/drugoe-ASgBAgICAkSiww3s3Tmuww2m3jk?cd=1' => 'Другое - кухонные аксессуары',
						'posuda_i_tovary_dlya_kuhni/drugoe-ASgBAgICAUSiww3u3Tk?cd=1' => 'Другое - товары для кухни',

						'bytovaya_tehnika?cd=1' => 'Бытовая техника',
                        'bytovaya_tehnika/dlya_doma-ASgBAgICAURgpE8?cd=1' => 'Для дома',
                        'bytovaya_tehnika/dlya_doma/stiralnye_mashiny-ASgBAgICAkRgpE_OB6ZP?cd=1' => 'Стиральные машины',
                        'bytovaya_tehnika/klimaticheskoe_oborudovanie-ASgBAgICAURguE8?cd=1' => 'Климатическое оборудование',
                        'bytovaya_tehnika/klimaticheskoe_oborudovanie/obogrevateli-ASgBAgICAkRguE_SB75P?cd=1' => 'Обогреватели',
                        'bytovaya_tehnika/klimaticheskoe_oborudovanie/termometry_i_meteostantsii-ASgBAgICAkRguE_SB8JP?cd=1' => 'Термометры и метеостанции',
                        'bytovaya_tehnika/dlya_kuhni-ASgBAgICAURglk8?cd=1' => 'Для кухни',
                        'bytovaya_tehnika/dlya_kuhni/melkaya_kuhonnaya_tehnika-ASgBAgICAkRglk_MB6JP?cd=1' => 'Мелкая кухонная техника',
                        'bytovaya_tehnika/dlya_kuhni/plity-ASgBAgICAkRglk_MB5xP?cd=1' => 'Плиты',
                        'bytovaya_tehnika/dlya_individualnogo_uhoda-ASgBAgICAURgrk8?cd=1' => 'Для индивидуального ухода',
                        'bytovaya_tehnika/dlya_individualnogo_uhoda/britvy_i_trimmery-ASgBAgICAkRgrk_QB7BP?cd=1' => 'Бритвы и триммеры',
                        'bytovaya_tehnika/drugoe-ASgBAgICAURgvgs?cd=1' => 'Другое',
                        'produkty_pitaniya?cd=1' => 'Продукты питания',
                        'rasteniya?cd=1' => 'Растения',
                        'nedvizhimost?cd=1' => 'Недвижимость',
                        'komnaty?cd=1' => 'Комнаты',
                        'doma_dachi_kottedzhi?cd=1' => 'Дачи',
                        'kvartiry/prodam-ASgBAgICAUSSA8YQ?cd=1' => 'Все квартиры',
                        'kvartiry/prodam/novostroyka-ASgBAQICAUSSA8YQAUDmBxSOUg?cd=1' => 'Квартиры в новостройках',
                        'kvartiry/sdam/na_dlitelnyy_srok-ASgBAgICAkSSA8gQ8AeQUg?cd=1' => 'Квартиры в аренду',
                        'kvartiry/sdam/posutochno/-ASgBAgICAkSSA8gQ8AeSUg?cd=1' => 'Квартиры посуточно',
                        'kvartiry/kuplyu-ASgBAgICAUSSA8QQ?cd=1' => 'Покупатели квартир',
                        'kvartiry/snimu-ASgBAgICAUSSA8oQ?cd=1' => 'Арендаторы квартир',
                        'kommercheskaya_nedvizhimost?cd=1' => 'Коммерческая недвижимость',
                        'garazhi_i_mashinomesta?cd=1' => 'Гаражи и машиноместа',
                        'zemelnye_uchastki?cd=1' => 'Земельные участки',
                        'predlozheniya_uslug?cd=1' => 'Услуги',
                        'predlozheniya_uslug/remont_stroitelstvo-ASgBAgICAUSYC8CfAQ?cd=1' => 'Ремонт, строительство',
                        'predlozheniya_uslug/remont_stroitelstvo/santekhnika-ASgBAgICAkSYC8CfAcQVsvUB?cd=1' => 'Сантехника',
                        'predlozheniya_uslug/remont_stroitelstvo/remont_kvartiri-ASgBAgICAkSYC8CfAcQVwPUB?cd=1' => 'Ремонт квартиры',
                        'predlozheniya_uslug/remont_stroitelstvo/remont_vannoy-ASgBAgICAkSYC8CfAcQVuPUB?cd=1' => 'Ремонт ванной',
                        'predlozheniya_uslug/remont_stroitelstvo/melkiy_bytovoy_remont-ASgBAgICAkSYC8CfAcQVrvUB?cd=1' => 'Отделочные работы',
                        'predlozheniya_uslug/remont_stroitelstvo/stroitelstvo_domov_kottedzhey-ASgBAgICAkSYC8CfAcQVvvUB?cd=1' => 'Строительство домов, коттеджей',
                        'predlozheniya_uslug/remont_stroitelstvo/sborka_i_remont_mebeli-ASgBAgICAkSYC8CfAcQVrPUB?cd=1' => 'Сборка и ремонт мебели',
                        'predlozheniya_uslug/remont_stroitelstvo/elektrika-ASgBAgICAkSYC8CfAcQVsPUB?cd=1' => 'Электрика',
                        'predlozheniya_uslug/remont_stroitelstvo/ostekleniye_balkonov-ASgBAgICAkSYC8CfAcQVtvUB?cd=1' => 'Остекление балконов',
                        'predlozheniya_uslug/master_na_chas-ASgBAgICAUSYC7zvAQ?cd=1' => 'Мастер на час',
                        'predlozheniya_uslug/krasota_zdorove-ASgBAgICAUSYC6qfAQ?cd=1' => 'Красота, здоровье',
                        'predlozheniya_uslug/krasota_zdorove/manikyur_pedikyur-ASgBAgICAkSYC6qfAaIrgLgC?cd=1' => 'Маникюр, педикюр',
                        'predlozheniya_uslug/krasota_zdorove/drugoe-ASgBAgICAkSYC6qfAaIriLgC?cd=1' => 'Другое - красота и здоровье',
                        'predlozheniya_uslug/krasota_zdorove/raru_pirsing-ASgBAgICAkSYC6qfAaIrhLgC?cd=1' => 'Тату, пирсинг',
                        'predlozheniya_uslug/uborka_klining-ASgBAgICAUSYC7L3AQ?cd=1' => 'Уборка',
                        'predlozheniya_uslug/uborka_klining/vyvoz_musora-ASgBAgICAkSYC7L3AdoVuvcB?cd=1' => 'Вывоз мусора',
                        'predlozheniya_uslug/uborka_klining/generalnaya_uborka-ASgBAgICAkSYC7L3AdoVvPcB?cd=1' => 'Генеральная уборка',
                        'predlozheniya_uslug/uborka_klining/prostaya_uborka-ASgBAgICAkSYC7L3AdoVwvcB?cd=1' => 'Простая уборка',
                        'predlozheniya_uslug/sad_blagoustroystvo-ASgBAgICAUSYC8KfAQ?cd=1' => 'Сад, благоустройство',
                        'predlozheniya_uslug/transport_perevozki-ASgBAgICAUSYC8SfAQ?cd=1' => 'Транспорт, перевозки',
                        'predlozheniya_uslug/transport_perevozki/spetstekhnika-ASgBAgICAkSYC8SfAZoL3J8B?cd=1' => 'Спецтехника',
                        'predlozheniya_uslug/transport_perevozki/avtoservis-ASgBAgICAkSYC8SfAZoL2p8B?cd=1' => 'Автосервис',
                        'predlozheniya_uslug/prazdniki_meropriyatiya-ASgBAgICAUSYC7yfAQ?cd=1' => 'Праздники, мероприятия',
                        'predlozheniya_uslug/uhod_za_zhivotnymi-ASgBAgICAUSYC8afAQ?cd=1' => 'Уход за животными',
                        'predlozheniya_uslug/oborudovanie_proizvodstvo-ASgBAgICAUSYC7SfAQ?cd=1' => 'Оборудование, производство',
                        'predlozheniya_uslug/oborudovanie_proizvodstvo/proizvodstvo_obrabotka-ASgBAgICAkSYC7SfAaALiKAB?cd=1' => 'Производство, обработка',
                        'predlozheniya_uslug/ustanovka_tehniki-ASgBAgICAUSYC9r1AQ?cd=1' => 'Установка техники',
                        'predlozheniya_uslug/remont_i_obsluzhivanie_tehniki-ASgBAgICAUSYC7T3AQ?cd=1' => 'Ремонт и обслуживание техники',
                        'predlozheniya_uslug/remont_i_obsluzhivanie_tehniki/melkaya_bytovaya_tekhnika-ASgBAgICAkSYC7T3Ad4V4PcB?cd=1' => 'Мелкая бытовая техника',
                        'hobbi_i_otdyh?cd=1' => 'Хобби и отдых',
                        'sport_i_otdyh?cd=1' => 'Спорт и отдых',
                        'sport_i_otdyh/zimnie_vidy_sporta-ASgBAgICAUTKAtoK?cd=1' => 'Зимние виды спорта',
                        'sport_i_otdyh/edinoborstva-ASgBAgICAUTKAoRP?cd=1' => 'Единоборства',
                        'sport_i_otdyh/drugoe-ASgBAgICAUTKAuIK?cd=1' => 'Другое спорт и отдых',
                        'sport_i_otdyh/igry_s_myachom-ASgBAgICAUTKAtwK?cd=1' => 'Игры с мячом',
                        'sport_i_otdyh/nastolnye_igry-ASgBAgICAUTKAoZP?cd=1' => 'Настольные игры',
                        'sport_i_otdyh/roliki_i_skeytbording-ASgBAgICAUTKAopP?cd=1' => 'Ролики и скейтбординг',
                        'kollektsionirovanie?cd=1' => 'Коллекционирование',
                        'kollektsionirovanie/drugoe-ASgBAgICAUQckEw?cd=1' => 'Другое',
                        'kollektsionirovanie/kartiny-ASgBAgICAUQcoBc?cd=1' => 'Картины',
                        'kollektsionirovanie/etiketki_butylki_probki-ASgBAgICAUQcpgE?cd=1' => 'Этикетки, бутылки, пробки',
                        'kollektsionirovanie/konverty_i_pochtovye_kartochki-ASgBAgICAUQclAE?cd=1' => 'Конверты и почтовые карточки',
                        'kollektsionirovanie/modeli-ASgBAgICAUQcmAE?cd=1' => 'Модели',
                        'kollektsionirovanie/otkrytki-ASgBAgICAUQcnAE?cd=1' => 'Открытки',
                        'knigi_i_zhurnaly?cd=1' => 'Книги и журналы',
                        'knigi_i_zhurnaly/uchebnaya_literatura-ASgBAgICAUTOApRM?cd=1' => 'Учебная литература',
                        'lichnye_veschi?cd=1' => 'Личные вещи',
                        'tovary_dlya_detey_i_igrushki?cd=1' => 'Товары для детей и игрушки',
                        'tovary_dlya_detey_i_igrushki/kupit-igrushki-ASgBAgICAUT~AZYJ?cd=1' => 'Игрушки',
                        'tovary_dlya_detey_i_igrushki/kupit-detskaya_mebel-ASgBAgICAUT~AZQJ?cd=1' => 'Детская мебель',
                        'tovary_dlya_detey_i_igrushki/kupit-tovary_dlya_kupaniya-ASgBAgICAUT~AcZP?cd=1' => 'Товары для купания',
                        'tovary_dlya_detey_i_igrushki/kupit-tovary_dlya_shkoly-ASgBAgICAUT~AchP?cd=1' => 'Товары для школы',
                        'tovary_dlya_detey_i_igrushki/kupit-detskie_kolyaski-ASgBAgICAUT~AZgJ?cd=1' => 'Детские коляски',
                        'chasy_i_ukrasheniya?cd=1' => 'Часы и украшения',
                        'chasy_i_ukrasheniya/kupit-bizhuteriya-ASgBAgICAUTQAYoG?cd=1' => 'Бижутерия',
                        'chasy_i_ukrasheniya/kupit-yuvelirnye_izdeliya-ASgBAgICAUTQAYgG?cd=1' => 'Ювелирные изделия',
                        'chasy_i_ukrasheniya/kupit-chasy-ASgBAgICAUTQAYYG?cd=1' => 'Часы',
                        'odezhda_obuv_aksessuary?cd=1' => 'Одежда, обувь, аксессуары',
                        'odezhda_obuv_aksessuary/kupit-aksessuary-ASgBAgICAUTeAtoL?cd=1' => 'Аксессуары',
                        'odezhda_obuv_aksessuary/kupit-zhenskaya_odezhda-ASgBAgICAUTeAtYL?cd=1' => 'Женская одежда',
                        'odezhda_obuv_aksessuary/kupit-obuv-zhenskaya-ASgBAgICAkSmAeYD3gLWCw?cd=1' => 'Обувь',
                        'odezhda_obuv_aksessuary/kupit-verhnyaya_odezhda-zhenskaya_odezhda-ASgBAgICAkSmAeID3gLWCw?cd=1' => 'Верхняя одежда',
                        'odezhda_obuv_aksessuary/kupit-kupalniki-zhenskaya_odezhda-ASgBAgICAkSmAbpL3gLWCw?cd=1' => 'Купальники',
                        'odezhda_obuv_aksessuary/kupit-platya_i_yubki-zhenskaya_odezhda-ASgBAgICAkSmAeoD3gLWCw?cd=1' => 'Платья и юбки',
                        'odezhda_obuv_aksessuary/kupit-svadebnye_platya-zhenskaya_odezhda-ASgBAgICAkSmAbhL3gLWCw?cd=1' => 'Свадебные платья',
                        'odezhda_obuv_aksessuary/kupit-muzhskaya_odezhda-ASgBAgICAUTeAtgL?cd=1' => 'Мужская одежда',
                        'odezhda_obuv_aksessuary/kupit-trikotazh_i_futbolki-muzhskaya_odezhda-ASgBAgICAkTeAtgL4ALoCw?cd=1' => 'Трикотаж и футболки',
                        'odezhda_obuv_aksessuary/kupit-bryuki-muzhskaya_odezhda-ASgBAgICAkTeAtgL4ALcCw?cd=1' => 'Брюки',
                        'odezhda_obuv_aksessuary/kupit-drugoe-muzhskaya_odezhda-ASgBAgICAkTeAtgL4ALqCw?cd=1' => 'Другое',
                        'krasota_i_zdorove?cd=1' => 'Красота и здоровье',
                        'krasota_i_zdorove/kupit-pribory_i_aksessuary-ASgBAgICAUSEAqAJ?cd=1' => 'Приборы и аксессуары',
                        'krasota_i_zdorove/kupit-meditsinskie_izdeliya-ASgBAgICAUSEAqgJ?cd=1' => 'Медицинские изделия',
                        'krasota_i_zdorove/kupit-sredstva_gigieny-ASgBAgICAUSEAqoJ?cd=1' => 'Средства гигиены',
                        'krasota_i_zdorove/kupit-kosmetika-ASgBAgICAUSEAqIJ?cd=1' => 'Косметика',
                        'krasota_i_zdorove/kupit-bad-ASgBAgICAUSEAri~Ag?cd=1' => 'Биологически активные добавки',
                        'detskaya_odezhda_i_obuv?cd=1' => 'Детская одежда и обувь',
                        'detskaya_odezhda_i_obuv/kupit-odezhdu-dlya_devochek-ASgBAgICAUTkAuwL?cd=1' => 'Для девочек',
                        'detskaya_odezhda_i_obuv/kupit-platya_i_yubki-dlya_devochek-ASgBAgICAkTkAuwL5gL6Cw?cd=1' => 'Платья и юбки',
                        'detskaya_odezhda_i_obuv/kupit-trikotazh-dlya_devochek-ASgBAgICAkTkAuwL5gKgTA?cd=1' => 'Трикотаж',
                        'detskaya_odezhda_i_obuv/kupit-drugoe-dlya_devochek-ASgBAgICAkTkAuwL5gL~Cw?cd=1' => 'Другое',
                        'zhivotnye?cd=1' => 'Животные',
                        'akvarium?cd=1' => 'Аквариум',
                        'tovary_dlya_zhivotnyh?cd=1' => 'Товары для животных',
                        'drugie_zhivotnye?cd=1' => 'Другие животные',
                        'drugie_zhivotnye/drugoe-ASgBAgICAUSyA9IV?cd=1' => 'Другое',
                        'dlya_biznesa?cd=1' => 'Готовый бизнес и оборудование',
                        'oborudovanie_dlya_biznesa?cd=1' => 'Оборудование для бизнеса',
                        'oborudovanie_dlya_biznesa/dlya_salona_krasoty-ASgBAgICAUTqAvxP?cd=1' => 'Для салона красоты',
                        'oborudovanie_dlya_biznesa/promyshlennoe-ASgBAgICAUTqAv5P?cd=1' => 'Промышленное',
                        'oborudovanie_dlya_biznesa/drugoe-ASgBAgICAUTqArYM?cd=1' => 'Другое',
                        'oborudovanie_dlya_biznesa/dlya_magazina-ASgBAgICAUTqAvZP?cd=1' => 'Для магазина',
                        'oborudovanie_dlya_biznesa/dlya_restorana-ASgBAgICAUTqAvpP?cd=1' => 'Для ресторана',
                        'gotoviy_biznes?cd=1' => 'Готовый бизнес',
                        'gotoviy_biznes/sfera_uslug-ASgBAgICAUToDKq2AQ?cd=1' => 'Сфера услуг',
                        'gotoviy_biznes/torgovlya-ASgBAgICAUToDKy2AQ?cd=1' => 'Торговля',
                        'transport?cd=1' => 'Транспорт',
                        'zapchasti_i_aksessuary?cd=1' => 'Запчасти и аксессуары',
                        'zapchasti_i_aksessuary/zapchasti-ASgBAgICAUQKJA?cd=1' => 'Запчасти',
                        'zapchasti_i_aksessuary/zapchasti/dlya_avtomobiley-ASgBAgICAkQKJKwJ~GM?cd=1' => 'Для автомобилей',
                        'zapchasti_i_aksessuary/aksessuary-ASgBAgICAUQKnk0?cd=1' => 'Аксессуары',
                        'gruzoviki_i_spetstehnika?cd=1' => 'Грузовики и спецтехника',
                        'gruzoviki_i_spetstehnika/avtodoma-ASgBAgICAURUkk8?cd=1' => 'Автодома',
                        'gruzoviki_i_spetstehnika/pritsepy-ASgBAgICAURU4k0?cd=1' => 'Прицепы',
                        'vodnyy_transport?cd=1' => 'Водный транспорт',
                        'vodnyy_transport/katera_i_yahty-ASgBAgICAUQOPg?cd=1' => 'Катера и яхты',
                        'vodnyy_transport/motornye_lodki-ASgBAgICAUQOOA?cd=1' => 'Моторные лодки',
                        'avtomobili?cd=1' => 'Автомобили',
                        'bytovaya_elektronika?cd=1' => 'Бытовая электроника',
                        'audio_i_video?cd=1' => 'Аудио и видео',
                        'audio_i_video/naushniki-ASgBAgICAUSIAtRO?cd=1' => 'Наушники',
                        'planshety_i_elektronnye_knigi?cd=1' => 'Планшеты и электронные книги',
                        'planshety_i_elektronnye_knigi/aksessuary-ASgBAgICAUSYAopO?cd=1' => 'Аксессуары',
                        'planshety_i_elektronnye_knigi/aksessuary/garnitury_i_naushniki-ASgBAgICAkSYAopOwgeOTg?cd=1' => 'Гарнитуры и наушники',
                        'telefony?cd=1' => 'Телефоны',
                        'telefony/aksessuary-ASgBAgICAUSeAvZN?cd=1' => 'Аксессуары',
                        'telefony/aksessuary/garnitury_i_naushniki-ASgBAgICAkSeAvZNwAf6TQ?cd=1' => 'Гарнитуры и наушники',
                        'rabota?cd=1' => 'Работа',
                        'rezume?cd=1' => 'Резюме',
                        'rezume/zhkh_ekspluatatsiya-ASgBAgICAUSUC5CfAQ?cd=1' => 'ЖКХ, эксплуатация',
                        'rezume/marketing_reklama_pr-ASgBAgICAUSUC5afAQ?cd=1' => 'Маркетинг, реклама, PR',
                        'rezume/stroitelstvo-ASgBAgICAUSUC_ieAQ?cd=1' => 'Строительство',
                        'vakansii?cd=1' => 'Вакансии',
                        'vakansii/fitnes_salony_krasoty-ASgBAgICAUSOC4SeAQ?cd=1' => 'Фитнес, салоны красоты',
					),
					'default'     => 'any',
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

        if ( !empty( $this->parent->avito_db_data ) && !empty( $this->parent->avito_keys_option ) && $this->parent->avito_enable_option && !$this->parent->avito_block_status ) {
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
        if ( $this->parent->avito_block_status ) {
            $out .= __( 'It seems that you are too frequently changed settings and the site Avito this activity seemed suspicious. There is nothing to worry, just go to the site avito and confirm that you are not a robot - ', 'alm-avito-ads-monitor' ) . '<a href="https://www.avito.ru" target="blank">' . __( 'go to avito website' ) . '</a>';
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

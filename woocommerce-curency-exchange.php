<?php
/**
 * Plugin Name: Currency Exchange for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/currency-exchange-for-woocommerce/
 * Description: WooCommerce currency exchange for your shop. You can easily setup exchange to any currencies in WooCommerce.
 * Version: 1.0.5
 * Author: BeRocket
 * Requires at least: 4.0
 * Author URI: http://berocket.com
 * Text Domain: BeRocket_CE_domain
 * Domain Path: /languages/
 */
define( "BeRocket_Curency_Exchange_version", '1.0.5' );
define( "BeRocket_CE_domain", 'BeRocket_CE_domain'); 
define( "CE_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
load_plugin_textdomain('BeRocket_CE_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'includes/admin_notices.php');
require_once(plugin_dir_path( __FILE__ ).'includes/widget.php');
require_once(plugin_dir_path( __FILE__ ).'includes/functions.php');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Class BeRocket_CE
 */
class BeRocket_CE {
    public static $currency;
    public static $currency_symbol;
    public static $currency_modifier;

    public static $info = array( 
        'id'        => 8,
        'version'   => BeRocket_Curency_Exchange_version,
        'plugin'    => '',
        'slug'      => '',
        'key'       => '',
        'name'      => ''
    );

    /**
     * Defaults values
     */
    public static $defaults = array(
        'visual_only'       => '1',
        'use_open_exchange' => '0',
        'use_currency'      => array(),
        'currency'          => array(),
        'currency_site'     => 'oer',
        'open_exchange_api' => '',
        'currencylayer_api' => '',
        'custom_css'        => '',
        'last_oer_data'     => array('base' => 'USD'),
        'script'            => array(
            'js_page_load'      => '',
            'js_before_set'     => '',
            'js_after_set'      => 'location.reload();',
        ),
    );
    public static $values = array(
        'settings_name' => '',
        'option_page'   => 'br-curency-exchange',
        'premium_slug'  => 'woocommerce-currency-exchange',
    );
    
    function __construct () {
        register_uninstall_hook(__FILE__, array( __CLASS__, 'deactivation' ) );

        if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && 
            br_get_woocommerce_version() >= 2.1 ) {
            $options = BeRocket_CE::get_ce_option();
            add_action ( 'init', array( __CLASS__, 'init' ), 1 );
            add_action ( 'wp_head', array( __CLASS__, 'set_styles' ) );
            add_action ( 'admin_init', array( __CLASS__, 'register_ce_options' ) );
            add_action ( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
            add_action ( "widgets_init", array( __CLASS__, 'widgets_init' ) );
            add_action ( 'admin_menu', array( __CLASS__, 'ce_options' ) );
            add_action( "wp_ajax_br_ce_settings_save", array ( __CLASS__, 'save_settings' ) );
            add_action( "wp_ajax_open_exchange_load", array ( __CLASS__, 'open_exchange_load' ) );
            add_shortcode( 'br_currency_exchange', array( __CLASS__, 'shortcode' ) );
            
            if ( ! $options['visual_only'] ) {
                //add_action( 'raw_woocommerce_price', array( __CLASS__, 'return_custom_price_one' ) );

                if ( br_woocommerce_version_check() ) {
                    add_filter('woocommerce_product_get_price', array( __CLASS__, 'return_custom_price' ), 10, 2);
                    add_filter('woocommerce_product_get_regular_price', array( __CLASS__, 'return_custom_price' ), 10, 2); 
                    add_filter('woocommerce_product_get_sale_price', array( __CLASS__, 'return_custom_price' ), 10, 2); 
                    add_filter('woocommerce_variation_prices', array( __CLASS__, 'return_custom_price_variable_array' ), 10);
                    add_filter('woocommerce_product_variation_get_price', array( __CLASS__, 'return_custom_price_variable' ), 10, 2); 
                    add_filter('woocommerce_product_variation_get_regular_price', array( __CLASS__, 'return_custom_price_variable' ), 10, 2); 
                    add_filter('woocommerce_product_variation_get_sale_price', array( __CLASS__, 'return_custom_price_variable' ), 10, 2); 
                } else {
                    add_filter('woocommerce_get_price', array( __CLASS__, 'return_custom_price' ), 10, 2);
                    add_filter('woocommerce_get_regular_price', array( __CLASS__, 'return_custom_price' ), 10, 2); 
                    add_filter('woocommerce_get_sale_price', array( __CLASS__, 'return_custom_price' ), 10, 2); 
                    add_filter('woocommerce_get_variation_price', array( __CLASS__, 'return_custom_price_variable' ), 10, 2); 
                    add_filter('woocommerce_get_variation_regular_price', array( __CLASS__, 'return_custom_price_variable' ), 10, 2); 
                    add_filter('woocommerce_get_variation_sale_price', array( __CLASS__, 'return_custom_price_variable' ), 10, 2); 
                }

                add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'update_meta_data_with_new_currency' ) );
                add_action( 'woocommerce_currency', array( __CLASS__, 'woocommerce_currency' ) );
            } else {
                add_filter('berocket_lmp_user_func', array( __CLASS__, 'filters_load_more_fix' ), 10); 
                add_filter('berocket_aapf_user_func', array( __CLASS__, 'filters_load_more_fix' ), 10);
                add_filter('berocket_pp_user_func', array( __CLASS__, 'product_preview_fix' ), 10);
            }
            add_action( 'woocommerce_price_filter_widget_min_amount', array( __CLASS__, 'return_custom_price_one' ) );
            add_action( 'woocommerce_price_filter_widget_max_amount', array( __CLASS__, 'return_custom_price_one' ) );
            add_filter('berocket_min_max_filter', array( __CLASS__, 'invert_custom_price_one' ) );
            add_action( 'current_screen', array( __CLASS__, 'current_screen' ) );
            add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
            $plugin_base_slug = plugin_basename( __FILE__ );
            add_filter( 'plugin_action_links_' . $plugin_base_slug, array( __CLASS__, 'plugin_action_links' ) );
            add_filter( 'is_berocket_settings_page', array( __CLASS__, 'is_settings_page' ) );
        }
    }
    public static function is_settings_page($settings_page) {
        if( ! empty($_GET['page']) && $_GET['page'] == self::$values[ 'option_page' ] ) {
            $settings_page = true;
        }
        return $settings_page;
    }
    public static function plugin_action_links($links) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page='.self::$values['option_page'] ) . '" title="' . __( 'View Plugin Settings', 'BeRocket_products_label_domain' ) . '">' . __( 'Settings', 'BeRocket_products_label_domain' ) . '</a>',
		);
		return array_merge( $action_links, $links );
    }
    public static function plugin_row_meta($links, $file) {
        $plugin_base_slug = plugin_basename( __FILE__ );
        if ( $file == $plugin_base_slug ) {
			$row_meta = array(
				'docs'    => '<a href="http://berocket.com/docs/plugin/'.self::$values['premium_slug'].'" title="' . __( 'View Plugin Documentation', 'BeRocket_products_label_domain' ) . '" target="_blank">' . __( 'Docs', 'BeRocket_products_label_domain' ) . '</a>',
				'premium'    => '<a href="http://berocket.com/product/'.self::$values['premium_slug'].'" title="' . __( 'View Premium Version Page', 'BeRocket_products_label_domain' ) . '" target="_blank">' . __( 'Premium Version', 'BeRocket_products_label_domain' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
    }
    public static function widgets_init() {
        register_widget("berocket_ce_widget");
    }
    public static function filters_load_more_fix($script) {
        $script['after_update'] = 'ce_money_replace();'.$script['after_update'];
        return $script;
    }
    public static function product_preview_fix($script) {
        $script['on_open'] = 'ce_money_replace();'.$script['on_open'];
        return $script;
    }
    /**
     * Function that use for WordPress init action
     *
     * @return void
     */
    public static function init () {
        $options = BeRocket_CE::get_ce_option();
            
            global $pagenow;
            if( ! is_admin () || ! in_array($pagenow, array('post.php', 'post-new.php')) ) {
                $currency_cur = false;
                if( isset($_COOKIE['br_ce_language']) ) {
                    $currency_cur = $_COOKIE['br_ce_language'];
                }

                if( $currency_cur && in_array($currency_cur, $options['use_currency']) ) {
                    self::$currency = $currency_cur;
                    if( isset($options['currency'][$currency_cur]) ) {
                        self::$currency_modifier = $options['currency'][$currency_cur];
                    }
                }
            }
        $current_currency = BeRocket_CE::get_woocommerce_currency();
        if( isset(self::$currency) ) {
            self::$currency_symbol = get_woocommerce_currency_symbol(self::$currency);
        }
        wp_enqueue_script("jquery");
        wp_enqueue_script( 'berocket_jquery_cookie', plugins_url( 'js/jquery.cookie.js', __FILE__ ), array( 'jquery' ), BeRocket_Curency_Exchange_version );
        wp_enqueue_script( 'berocket_ce_currency_exchange', plugins_url( 'js/curency_exchange.js', __FILE__ ), array( 'jquery' ), BeRocket_Curency_Exchange_version );
        wp_register_style( 'berocket_ce_style', plugins_url( 'css/shop_ce.css', __FILE__ ), "", BeRocket_Curency_Exchange_version );
        wp_enqueue_style( 'berocket_ce_style' );
        wp_register_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
        wp_enqueue_style( 'font-awesome' );
        if ( $options['visual_only'] ) {
            wp_enqueue_script( 'open_money', plugins_url( 'js/money.min.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'open_accounting', plugins_url( 'js/accounting.min.js', __FILE__ ), array( 'jquery' ) );
        }
        
        $currency_pos         = get_option( 'woocommerce_currency_pos' );
        $currency_thousand    = get_option( 'woocommerce_price_thousand_sep' );
        $currency_decimal     = get_option( 'woocommerce_price_decimal_sep' );
        $currency_decimal_num = get_option( 'woocommerce_price_num_decimals' );
        switch ( $currency_pos ) {
            case 'left' :
                $currency_pos = '%s%v';
                break;
            case 'right' :
                $currency_pos = '%v%s';
                break;
            case 'left_space' :
                $currency_pos = '%s %v';
                break;
            case 'right_space' :
                $currency_pos = '%v %s';
                break;
        }
        
        wp_localize_script(
            'berocket_ce_currency_exchange',
            'the_ce_js_data',
            array(
                'script'      => apply_filters( 'berocket_ce_user_func', $options['script'] ),
                'rates'       => $options['currency'],
                'base'        => $current_currency,
                'visual_only' => $options['visual_only'],
                'current'     => (isset(self::$currency) ? self::$currency : 'none'),
                'symbol'      => (isset(self::$currency_symbol) ? self::$currency_symbol : 'none'),
                'accounting'  => array(
                    'symbol'      => (isset(self::$currency_symbol) ? self::$currency_symbol : 'none'),
                    'decimal'     => $currency_decimal,
                    'thousand'    => $currency_thousand,
                    'precision'   => $currency_decimal_num,
                    'format'      => $currency_pos
                ),
            )
        );
    }
    /**
     * Function set styles in wp_head WordPress action
     *
     * @return void
     */
    public static function set_styles () {
        $options = BeRocket_CE::get_ce_option();
        echo '<style>'.$options['custom_css'].'</style>';
    }
    /**
     * Load template
     *
     * @access public
     *
     * @param string $name template name
     *
     * @return void
     */
    public static function br_get_template_part( $name = '' ) {
        $template = '';

        // Look in your_child_theme/woocommerce-list-grid/name.php
        if ( $name ) {
            $template = locate_template( "woocommerce-curency-exchange/{$name}.php" );
        }

        // Get default slug-name.php
        if ( ! $template && $name && file_exists( CE_TEMPLATE_PATH . "{$name}.php" ) ) {
            $template = CE_TEMPLATE_PATH . "{$name}.php";
        }

        // Allow 3rd party plugin filter template file from their plugin
        $template = apply_filters( 'ce_get_template_part', $template, $name );

        if ( $template ) {
            load_template( $template, false );
        }
    }

    public static function admin_enqueue_scripts() {
        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        } else {
            wp_enqueue_style( 'thickbox' );
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'thickbox' );
        }
    }

    /**
     * Function adding styles/scripts and settings to admin_init WordPress action
     *
     * @access public
     *
     * @return void
     */
    public static function register_ce_options () {
        wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( 'css/colpick.css', __FILE__ ) );
        wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );
        wp_register_style( 'berocket_ce_admin_style', plugins_url( 'css/admin_ce.css', __FILE__ ), "", BeRocket_Curency_Exchange_version );
        wp_enqueue_style( 'berocket_ce_admin_style' );
        register_setting('br_ce_general_option', 'br_ce_general_option', array( __CLASS__, 'sanitize_ce_option' ));
        add_settings_section( 
            'br_ce_general_page',
            'Currency Exchange Settings',
            'br_ce_general_display_callback',
            'br_ce_general_option'
        );
    }
    /**
     * Function add options button to admin panel
     *
     * @access public
     *
     * @return void
     */
    public static function ce_options() {
        add_submenu_page( 'woocommerce', __('Currency Exchange settings', 'BeRocket_CE_domain'), __('Currency Exchange', 'BeRocket_CE_domain'), 'manage_options', 'br-curency-exchange', array(
            __CLASS__,
            'ce_option_form'
        ) );
    }
    /**
     * Function add options form to settings page
     *
     * @access public
     *
     * @return void
     */
    public static function ce_option_form() {
        $plugin_info = get_plugin_data(__FILE__, false, true);
        include CE_TEMPLATE_PATH . "settings.php";
    }
    /**
     * Function remove settings from database
     *
     * @return void
     */
    public static function deactivation () {
        delete_option( 'br_ce_options' );
    }

    public static function save_settings () {
        if( current_user_can( 'manage_options' ) ) {
            if( isset($_POST['br_ce_options']) ) {
                update_option( 'br_ce_options', self::sanitize_ce_option($_POST['br_ce_options']) );
                echo json_encode($_POST['br_ce_options']);
            }
        }
        wp_die();
    }

    public static function shortcode( $atts = array() ) {
        $atts = apply_filters( 'berocket_ce_shortcode_options', $atts );
        the_widget( 'berocket_ce_widget', $atts);
    }

    public static function woocommerce_currency($currency) {
        if( isset(self::$currency) ) {
            $currency = self::$currency;
        }
        return $currency;
    }

    public static function return_custom_price($price, $product) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
            $price = $price * self::$currency_modifier;
        }
        return $price;
    }

    public static function return_custom_price_one($price) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
            $price = $price * self::$currency_modifier;
        }
        return $price;
    }

    public static function return_custom_price_variable($price, $product) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
            $price = $price * self::$currency_modifier;
        }
        return $price;
    }

    public static function return_custom_price_variable_array($prices) {
        if( is_array($prices) ) {
            foreach($prices as &$price_type) {
                if( is_array($prices) ) {
                    foreach($price_type as &$price) {
                        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 && $price ) {
                            $price = $price * self::$currency_modifier;
                        }
                    }
                }
            }
        }
        return $prices;
    }

    public static function invert_custom_price_one($price) {
        if( isset( self::$currency_modifier ) && self::$currency_modifier > 0 ) {
            if( is_array($price) ) {
                foreach($price as &$prices) {
                    $prices = $prices / self::$currency_modifier;
                }
            } else {
                $price = $price / self::$currency_modifier;
            }
        }
        return $price;
    }

    public static function update_meta_data_with_new_currency( $order_id ) {
        if( isset(self::$currency) ) {
            update_post_meta( $order_id, 'currency_used', self::$currency );
        }
    }

    public static function get_woocommerce_currency() {
        $options = BeRocket_CE::get_ce_option();
        if( !$options['visual_only'] ) {
            remove_action( 'woocommerce_currency', array( __CLASS__, 'woocommerce_currency' ) );
            $currency = get_woocommerce_currency();
            add_action( 'woocommerce_currency', array( __CLASS__, 'woocommerce_currency' ) );
        } else {
            $currency = get_woocommerce_currency();
        }
        return $currency;
    }
    public static function get_woocommerce_currencies() {
        return array( 'AED', 'ARS', 'AUD', 'BDT', 'BGN', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'DOP', 'EGP',
            'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JPY', 'KES', 'LAK', 'KRW', 'MXN', 'MYR', 'NGN', 'NOK',
            'NPR', 'NZD', 'PHP', 'PKR', 'PLN', 'PYG', 'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'UAH', 'USD', 'VND', 'ZAR' );
    }

    public static function current_screen() {
        $screen = get_current_screen();
        if(strpos($screen->id, 'br-curency-exchange') !== FALSE) {
            wp_enqueue_script( 'berocket_aapf_widget-colorpicker', plugins_url( 'js/colpick.js', __FILE__ ), array( 'jquery' ) );
        }
        wp_enqueue_script( 'berocket_ce_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_Curency_Exchange_version );
    }

    public static function open_exchange_load () {
        self::get_open_exchange(true);
        wp_die();
    }

    public static function get_open_exchange () {
        $options = BeRocket_CE::get_ce_option();
        $current_currency = BeRocket_CE::get_woocommerce_currency();
        $rates = self::get_currencies_from_site($options);
        if( $current_currency != 'USD' ) {
            $current_rate = $rates[$current_currency];
            
            foreach( $rates as $rate_name => $rate ) {
                $rates[$rate_name] = $rate / $current_rate;
            }
        }
        $options['currency'] = array_merge($options['currency'], $rates);
        $options['last_oer_data']['base'] = $current_currency;
        update_option( 'br_ce_options', $options );
    }

    public static function get_currencies_from_site($options) {
        $rates = array();
        if( $curl = curl_init() ) {
            $not_exist_day = true;
            $day = 1;
            while($not_exist_day && $day < 30) {
                curl_setopt($curl, CURLOPT_URL, 'https://www.mastercard.com/psder/eu/callPsder.do');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, "baseCurrency=USD&service=getExchngRateDetails&settlementDate=".date('m/d/Y', strtotime("-".$day." day")));
                $out = curl_exec($curl);
                echo '<pre>', $out, '</pre>';
                preg_match_all("|<transaction_currency_dtl>(.+?)<\/transaction_currency_dtl>|i", $out, $matches);
                if(count($matches[1]) > 0) {
                    $not_exist_day = false;
                    foreach( $matches[1] as $match ) {
                        preg_match("|<alpha_curency_code>(.+?)<\/alpha_curency_code>.*?<conversion_rate>(.+?)<\/conversion_rate>|i", $match, $match_rates);
                        $rates[$match_rates[1]] = $match_rates[2];
                    }
                }
                $day++;
            }
            $rates['USD'] = '1.0';
        }
        return $rates;
    }

    public static function sanitize_ce_option( $input ) {
        $default = BeRocket_CE::$defaults;
        $result = self::recursive_array_set( $default, $input );
        return $result;
    }
    public static function recursive_array_set( $default, $options ) {
        $result = array();
        foreach( $default as $key => $value ) {
            if( array_key_exists( $key, $options ) ) {
                if( is_array( $value ) ) {
                    if( is_array( $options[$key] ) ) {
                        $result[$key] = self::recursive_array_set( $value, $options[$key] );
                    } else {
                        $result[$key] = self::recursive_array_set( $value, array() );
                    }
                } else {
                    $result[$key] = $options[$key];
                }
            } else {
                if( is_array( $value ) ) {
                    $result[$key] = self::recursive_array_set( $value, array() );
                } else {
                    $result[$key] = '';
                }
            }
        }
        foreach( $options as $key => $value ) {
            if( ! array_key_exists( $key, $result ) ) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    public static function get_ce_option() {
        $options = get_option( 'br_ce_options' );
        if ( @ $options && is_array ( $options ) ) {
            $options = array_merge( BeRocket_CE::$defaults, $options );
        } else {
            $options = BeRocket_CE::$defaults;
        }
        return $options;
    }
}

new BeRocket_CE;

berocket_admin_notices::generate_subscribe_notice();
new berocket_admin_notices(array(
    'start' => 1498413376, // timestamp when notice start
    'end'   => 1504223940, // timestamp when notice end
    'name'  => 'name', //notice name must be unique for this time period
    'html'  => 'Only <strong>$10</strong> for <strong>Premium</strong> WooCommerce Load More Products plugin!
        <a class="berocket_button" href="http://berocket.com/product/woocommerce-load-more-products" target="_blank">Buy Now</a>
         &nbsp; <span>Get your <strong class="red">50% discount</strong> and save <strong>$10</strong> today</span>
        ', //text or html code as content of notice
    'righthtml'  => '<a class="berocket_no_thanks">No thanks</a>', //content in the right block, this is default value. This html code must be added to all notices
    'rightwidth'  => 80, //width of right content is static and will be as this value. berocket_no_thanks block is 60px and 20px is additional
    'nothankswidth'  => 60, //berocket_no_thanks width. set to 0 if block doesn't uses. Or set to any other value if uses other text inside berocket_no_thanks
    'contentwidth'  => 400, //width that uses for mediaquery is image_width + contentwidth + rightwidth
    'subscribe'  => false, //add subscribe form to the righthtml
    'priority'  => 10, //priority of notice. 1-5 is main priority and displays on settings page always
    'height'  => 50, //height of notice. image will be scaled
    'repeat'  => false, //repeat notice after some time. time can use any values that accept function strtotime
    'repeatcount'  => 1, //repeat count. how many times notice will be displayed after close
    'image'  => array(
        'local' => plugin_dir_url( __FILE__ ) . 'images/ad_white_on_orange.png', //notice will be used this image directly
    ),
));

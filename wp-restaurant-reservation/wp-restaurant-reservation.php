<?php
/**
 * Plugin Name: Yenolx Restaurant Reservation
 * Description: Advanced restaurant reservation management with table booking, dynamic pricing, and operating hours
 * Version: 1.5.0
 * Author: Yenolx
 * Text Domain: yenolx-restaurant
 */

if (defined('YRR_PLUGIN_LOADED')) return;
define('YRR_PLUGIN_LOADED', true);

if (!defined('ABSPATH')) exit;

define('YRR_VERSION', '1.5.0');
define('YRR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('YRR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YRR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Enhanced database structure for v1.5
function yrr_ensure_database_structure() {
    if (!is_admin() || wp_doing_ajax()) return;
    
    if (get_transient('yrr_db_check_done')) return;
    
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Settings table
    $settings_table = $wpdb->prefix . 'yrr_settings';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $settings_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        setting_name varchar(100) NOT NULL,
        setting_value longtext DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY setting_name (setting_name)
    ) $charset_collate");
    
    // Reservations table
    $reservations_table = $wpdb->prefix . 'yrr_reservations';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $reservations_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        reservation_code varchar(20) NOT NULL DEFAULT '',
        customer_name varchar(100) NOT NULL DEFAULT '',
        customer_email varchar(100) NOT NULL DEFAULT '',
        customer_phone varchar(20) NOT NULL DEFAULT '',
        party_size int(11) NOT NULL DEFAULT 1,
        reservation_date date NOT NULL,
        reservation_time time NOT NULL,
        special_requests text DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        table_id int(11) DEFAULT NULL,
        total_price decimal(10,2) DEFAULT 0.00,
        price_breakdown text DEFAULT NULL,
        notes text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY reservation_code (reservation_code),
        INDEX idx_date (reservation_date),
        INDEX idx_status (status),
        INDEX idx_table (table_id)
    ) $charset_collate");
    
    // Tables management
    $tables_table = $wpdb->prefix . 'yrr_tables';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $tables_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        table_number varchar(20) NOT NULL,
        capacity int(11) NOT NULL,
        status varchar(20) DEFAULT 'available',
        location varchar(100) DEFAULT '',
        table_type varchar(50) DEFAULT 'standard',
        position_x int(11) DEFAULT 0,
        position_y int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY table_number (table_number)
    ) $charset_collate");
    
    // Operating hours
    $hours_table = $wpdb->prefix . 'yrr_operating_hours';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $hours_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        day_of_week varchar(10) NOT NULL,
        shift_name varchar(50) DEFAULT 'all_day',
        open_time time DEFAULT NULL,
        close_time time DEFAULT NULL,
        is_closed boolean DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY day_shift (day_of_week, shift_name)
    ) $charset_collate");
    
    // Pricing rules
    $pricing_table = $wpdb->prefix . 'yrr_pricing_rules';
    $wpdb->query("CREATE TABLE IF NOT EXISTS $pricing_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        rule_name varchar(100) NOT NULL,
        start_time time DEFAULT NULL,
        end_time time DEFAULT NULL,
        days_applicable varchar(20) DEFAULT 'all',
        price_modifier decimal(10,2) DEFAULT 0.00,
        modifier_type varchar(10) DEFAULT 'add',
        is_active boolean DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate");
    
    // Insert default data
    yrr_insert_default_data();
    
    set_transient('yrr_db_check_done', true, DAY_IN_SECONDS);
}

function yrr_insert_default_data() {
    global $wpdb;
    
    // Default settings
    $settings = array(
        'restaurant_open' => '1',
        'restaurant_name' => get_bloginfo('name'),
        'restaurant_email' => get_option('admin_email'),
        'restaurant_phone' => '',
        'restaurant_address' => '',
        'max_party_size' => '12',
        'base_price_per_person' => '0.00',
        'booking_time_slots' => '30',
        'max_booking_advance_days' => '60'
    );
    
    foreach ($settings as $name => $value) {
        $wpdb->replace($wpdb->prefix . 'yrr_settings', array(
            'setting_name' => $name,
            'setting_value' => $value
        ));
    }
    
    // Default operating hours
    $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    foreach ($days as $day) {
        $wpdb->replace($wpdb->prefix . 'yrr_operating_hours', array(
            'day_of_week' => $day,
            'shift_name' => 'all_day',
            'open_time' => '10:00:00',
            'close_time' => '22:00:00',
            'is_closed' => 0
        ));
    }
    
    // Default tables
    $tables = array(
        array('table_number' => 'T1', 'capacity' => 2, 'location' => 'Window'),
        array('table_number' => 'T2', 'capacity' => 4, 'location' => 'Center'),
        array('table_number' => 'T3', 'capacity' => 6, 'location' => 'Private'),
        array('table_number' => 'T4', 'capacity' => 8, 'location' => 'VIP')
    );
    
    foreach ($tables as $table) {
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}yrr_tables WHERE table_number = %s",
            $table['table_number']
        ));
        
        if (!$existing) {
            $wpdb->insert($wpdb->prefix . 'yrr_tables', $table);
        }
    }
    
    // Default pricing rules
    $pricing_rules = array(
        array(
            'rule_name' => 'Lunch Discount',
            'start_time' => '11:00:00',
            'end_time' => '15:00:00',
            'days_applicable' => 'weekdays',
            'price_modifier' => -1.00,
            'modifier_type' => 'add'
        ),
        array(
            'rule_name' => 'Dinner Premium',
            'start_time' => '18:00:00',
            'end_time' => '21:00:00',
            'days_applicable' => 'all',
            'price_modifier' => 2.00,
            'modifier_type' => 'add'
        ),
        array(
            'rule_name' => 'Weekend Surcharge',
            'start_time' => '00:00:00',
            'end_time' => '23:59:59',
            'days_applicable' => 'weekends',
            'price_modifier' => 15.00,
            'modifier_type' => 'percent'
        )
    );
    
    foreach ($pricing_rules as $rule) {
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}yrr_pricing_rules WHERE rule_name = %s",
            $rule['rule_name']
        ));
        
        if (!$existing) {
            $wpdb->insert($wpdb->prefix . 'yrr_pricing_rules', $rule);
        }
    }
}

add_action('admin_init', 'yrr_ensure_database_structure', 1);

// Autoloader
spl_autoload_register('yrr_autoloader');

function yrr_autoloader($class_name) {
    if (strpos($class_name, 'YRR_') !== 0) return;
    
    $class_file = str_replace('_', '-', strtolower(substr($class_name, 4)));
    $directories = array('models/', 'controllers/', 'includes/');
    
    foreach ($directories as $directory) {
        $file = YRR_PLUGIN_PATH . $directory . 'class-' . $class_file . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}

// Plugin initialization
class YRR_Plugin {
    private $loader;
    private $controllers;
    
    public function __construct() {
        $this->load_dependencies();
        $this->init_controllers();
        $this->define_hooks();
    }
    
    private function load_dependencies() {
        require_once YRR_PLUGIN_PATH . 'includes/class-database.php';
        require_once YRR_PLUGIN_PATH . 'includes/class-plugin-loader.php';
        $this->loader = new YRR_Plugin_Loader();
    }
    
    private function init_controllers() {
        $this->controllers = array(
            'admin' => new YRR_Admin_Controller(),
            'reservation' => new YRR_Reservation_Controller(),
            'settings' => new YRR_Settings_Controller(),
            'tables' => new YRR_Tables_Controller(),
            'hours' => new YRR_Hours_Controller(),
            'pricing' => new YRR_Pricing_Controller()
        );
    }
    
    private function define_hooks() {
        register_activation_hook(__FILE__, array('YRR_Database', 'create_tables'));
        
        $this->loader->add_action('admin_menu', $this->controllers['admin'], 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $this->controllers['admin'], 'enqueue_admin_assets');
        
        $this->loader->add_shortcode('yenolx_booking_form', $this->controllers['reservation'], 'display_booking_form');
        
        // AJAX hooks
        $this->loader->add_action('wp_ajax_yrr_get_available_tables', $this->controllers['tables'], 'ajax_get_available_tables');
        $this->loader->add_action('wp_ajax_nopriv_yrr_get_available_tables', $this->controllers['tables'], 'ajax_get_available_tables');
        $this->loader->add_action('wp_ajax_yrr_calculate_price', $this->controllers['pricing'], 'ajax_calculate_price');
        $this->loader->add_action('wp_ajax_nopriv_yrr_calculate_price', $this->controllers['pricing'], 'ajax_calculate_price');
    }
    
    public function run() {
        $this->loader->run();
    }
}

function yrr_init_plugin() {
    $yrr_plugin = new YRR_Plugin();
    $yrr_plugin->run();
}

add_action('plugins_loaded', 'yrr_init_plugin');
?>

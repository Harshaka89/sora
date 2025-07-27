<?php
/**
 * Plugin Name: Restaurant Reservation System MVC v1.4
 * Description: Complete restaurant reservation management with proper MVC architecture
 * Version: 1.4.0
 * Author: Your Name
 * Text Domain: restaurant-reservations
 */

if (!defined('ABSPATH')) exit;

define('RRS_VERSION', '1.4.0');
define('RRS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RRS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Database fix function
function rrs_ensure_database_structure() {
    if (!is_admin() || wp_doing_ajax()) return;
    
    if (get_transient('rrs_db_check_done')) return;
    
    global $wpdb;
    
    // Check and create settings table
    $settings_table = $wpdb->prefix . 'rrs_settings';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$settings_table'") == $settings_table;
    
    if (!$table_exists) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $settings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_name varchar(100) NOT NULL,
            setting_value longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_name (setting_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Ensure reservations table has all columns
    $reservations_table = $wpdb->prefix . 'rrs_reservations';
    $res_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$reservations_table'") == $reservations_table;
    
    if ($res_table_exists) {
        $columns_to_add = array(
            'table_number' => "ALTER TABLE $reservations_table ADD COLUMN table_number VARCHAR(20) DEFAULT ''",
            'notes' => "ALTER TABLE $reservations_table ADD COLUMN notes TEXT DEFAULT NULL",
            'updated_at' => "ALTER TABLE $reservations_table ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        );
        
        foreach ($columns_to_add as $column => $sql) {
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $reservations_table LIKE '$column'");
            if (empty($column_exists)) {
                $wpdb->query($sql);
            }
        }
    }
    
    set_transient('rrs_db_check_done', true, DAY_IN_SECONDS);
}

add_action('admin_init', 'rrs_ensure_database_structure', 1);

// Autoloader for MVC classes
spl_autoload_register('rrs_autoloader');

function rrs_autoloader($class_name) {
    if (strpos($class_name, 'RRS_') !== 0) return;
    
    $class_file = str_replace('_', '-', strtolower(substr($class_name, 4)));
    $directories = array('models/', 'controllers/', 'includes/');
    
    foreach ($directories as $directory) {
        $file = RRS_PLUGIN_PATH . $directory . 'class-' . $class_file . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}

// Initialize MVC Plugin System
class RRS_Plugin {
    private $loader;
    private $controllers;
    
    public function __construct() {
        $this->load_dependencies();
        $this->init_controllers();
        $this->define_hooks();
    }
    
    private function load_dependencies() {
        require_once RRS_PLUGIN_PATH . 'includes/class-database.php';
        require_once RRS_PLUGIN_PATH . 'includes/class-plugin-loader.php';
        $this->loader = new RRS_Plugin_Loader();
    }
    
    private function init_controllers() {
        $this->controllers = array(
            'admin' => new RRS_Admin_Controller(),
            'reservation' => new RRS_Reservation_Controller(),
            'settings' => new RRS_Settings_Controller()
        );
    }
    
    private function define_hooks() {
        register_activation_hook(__FILE__, array('RRS_Database', 'create_tables'));
        
        $this->loader->add_action('admin_menu', $this->controllers['admin'], 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $this->controllers['admin'], 'enqueue_admin_assets');
        
        $this->loader->add_shortcode('restaurant_booking_form', $this->controllers['reservation'], 'display_booking_form');
        
        $this->loader->add_action('wp_ajax_rrs_update_reservation', $this->controllers['reservation'], 'ajax_update_reservation');
        $this->loader->add_action('wp_ajax_rrs_delete_reservation', $this->controllers['reservation'], 'ajax_delete_reservation');
    }
    
    public function run() {
        $this->loader->run();
    }
}

function rrs_init_mvc_system() {
    $rrs_plugin = new RRS_Plugin();
    $rrs_plugin->run();
}

add_action('plugins_loaded', 'rrs_init_mvc_system');
?>

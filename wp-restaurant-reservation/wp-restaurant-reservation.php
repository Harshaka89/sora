<?php
/**
 * Plugin Name: Restaurant Reservation System MVC v1.4
 * Description: Complete restaurant reservation management with MVC architecture
 * Version: 1.4.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

define('RRS_VERSION', '1.4.0');
define('RRS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RRS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Simple check - if MVC files don't exist, use basic functionality
if (!file_exists(RRS_PLUGIN_PATH . 'includes/class-database.php')) {
    // Fallback to basic functionality if MVC files are missing
    add_action('admin_menu', 'rrs_basic_menu');
    
    function rrs_basic_menu() {
        add_menu_page('Reservations', 'Reservations', 'manage_options', 'reservations', 'rrs_basic_dashboard', 'dashicons-calendar-alt', 26);
    }
    
    function rrs_basic_dashboard() {
        echo '<div class="wrap"><h1>🔧 MVC Files Missing</h1><p>Please add the MVC component files to activate full functionality.</p></div>';
    }
    return;
}

// Autoloader for MVC classes
spl_autoload_register('rrs_autoloader');

function rrs_autoloader($class_name) {
    if (strpos($class_name, 'RRS_') !== 0) {
        return;
    }
    
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
        // Activation hook
        register_activation_hook(__FILE__, array('RRS_Database', 'create_tables'));
        
        // Admin hooks
        $this->loader->add_action('admin_menu', $this->controllers['admin'], 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $this->controllers['admin'], 'enqueue_admin_assets');
        
        // Public hooks
        $this->loader->add_shortcode('restaurant_booking_form', $this->controllers['reservation'], 'display_booking_form');
        
        // AJAX hooks
        $this->loader->add_action('wp_ajax_rrs_update_reservation', $this->controllers['reservation'], 'ajax_update_reservation');
        $this->loader->add_action('wp_ajax_rrs_delete_reservation', $this->controllers['reservation'], 'ajax_delete_reservation');
    }
    
    public function run() {
        $this->loader->run();
    }
}

// Initialize MVC system
function rrs_init_mvc_system() {
    $rrs_plugin = new RRS_Plugin();
    $rrs_plugin->run();
}

add_action('plugins_loaded', 'rrs_init_mvc_system');




function rrs_check_database_structure() {
    if (!current_user_can('manage_options') || !isset($_GET['rrs_debug'])) {
        return;
    }
    
    global $wpdb;
    
    echo '<div style="background: white; padding: 20px; margin: 20px; border: 2px solid #007cba; border-radius: 10px;">';
    echo '<h3>🔍 Database Structure Check</h3>';
    
    // Check if settings table exists
    $table_name = $wpdb->prefix . 'rrs_settings';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    echo '<p><strong>Settings Table Exists:</strong> ' . ($table_exists ? '✅ YES' : '❌ NO') . '</p>';
    
    if ($table_exists) {
        // Show table structure
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        echo '<p><strong>Table Columns:</strong></p><ul>';
        foreach ($columns as $column) {
            echo '<li>' . $column->Field . ' (' . $column->Type . ')</li>';
        }
        echo '</ul>';
        
        // Show current data
        $settings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY setting_name");
        echo '<p><strong>Current Settings:</strong></p>';
        if ($settings) {
            echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>Setting Name</th><th>Setting Value</th><th>Updated At</th></tr>';
            foreach ($settings as $setting) {
                echo '<tr>';
                echo '<td>' . esc_html($setting->setting_name) . '</td>';
                echo '<td>' . esc_html($setting->setting_value) . '</td>';
                echo '<td>' . esc_html($setting->updated_at ?? 'N/A') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No settings found in database.</p>';
        }
    } else {
        echo '<p style="color: red;">❌ Settings table does not exist! This is the problem.</p>';
    }
    
    echo '</div>';
}

add_action('admin_notices', 'rrs_check_database_structure');




?>

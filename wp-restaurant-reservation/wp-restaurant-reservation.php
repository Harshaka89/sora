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
?>

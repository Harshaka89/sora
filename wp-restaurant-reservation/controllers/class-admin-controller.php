<?php
/**
 * Admin Controller Class
 * 
 * Handles admin interface logic and dashboard functionality
 * 
 * @package RestaurantReservations
 * @subpackage Controllers
 * @version 1.4.0
 * @since 1.0.0
 * @author Your Name
 * 
 * @class RRS_Admin_Controller
 * @description Main controller for admin interface management
 * 
 * Responsibilities:
 * - Admin menu creation and management
 * - Dashboard page rendering
 * - Weekly view functionality
 * - Asset enqueueing (CSS/JS)
 * - Admin notice handling
 * - User permission checking
 * 
 * Methods:
 * - add_admin_menu() - Register admin menu items
 * - dashboard_page() - Render dashboard
 * - weekly_view_page() - Render weekly view
 * - enqueue_admin_assets() - Load admin styles/scripts
 * - handle_dashboard_actions() - Process admin actions
 */

if (!defined('ABSPATH')) exit;


class RRS_Admin_Controller {
    private $reservation_model;
    private $settings_model;
    
    public function __construct() {
        $this->reservation_model = new RRS_Reservation_Model();
        $this->settings_model = new RRS_Settings_Model();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Restaurant Reservations',
            'Reservations',
            'manage_options',
            'reservations',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            26
        );
        
        add_submenu_page('reservations', 'Dashboard', 'Dashboard', 'manage_options', 'reservations', array($this, 'dashboard_page'));
        add_submenu_page('reservations', 'Weekly View', 'Weekly View', 'manage_options', 'weekly-view', array($this, 'weekly_view_page'));
        add_submenu_page('reservations', 'All Reservations', 'All Reservations', 'manage_options', 'all-reservations', array($this, 'all_reservations_page'));
        add_submenu_page('reservations', 'Settings', 'Settings', 'manage_options', 'res-settings', array($this, 'settings_page'));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'reservations') !== false || 
            strpos($hook, 'weekly-view') !== false || 
            strpos($hook, 'res-settings') !== false) {
            
            wp_enqueue_style(
                'rrs-admin-styles',
                RRS_PLUGIN_URL . 'restaurant-reservations.css',
                array(),
                RRS_VERSION
            );
            
            wp_localize_script('jquery', 'rrs_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rrs_admin_nonce'),
                'plugin_url' => RRS_PLUGIN_URL
            ));
        }
    }
    
    public function dashboard_page() {
        // Handle form submissions
        $this->handle_dashboard_actions();
        
        // Get data for the view
        $data = array(
            'statistics' => $this->reservation_model->get_statistics(),
            'today_reservations' => $this->reservation_model->get_by_date(date('Y-m-d')),
            'restaurant_status' => $this->settings_model->get('restaurant_open', '1'),
            'restaurant_name' => $this->settings_model->get('restaurant_name', get_bloginfo('name'))
        );
        
        // Load the view
        $this->load_view('admin/dashboard', $data);
    }
    
  public function weekly_view_page() {
    $week_start = isset($_GET['week']) ? sanitize_text_field($_GET['week']) : date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
    
    $data = array(
        'week_start' => $week_start,
        'week_end' => $week_end,
        'reservations' => $this->reservation_model->get_by_date_range($week_start, $week_end)
    );
    
    $this->load_view('admin/weekly-view', $data);
}

    
    public function all_reservations_page() {
        // Handle search and filters
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        
        $data = array(
            'reservations' => $this->reservation_model->get_filtered_reservations($search, $status_filter, $date_from, $date_to),
            'search' => $search,
            'status_filter' => $status_filter,
            'date_from' => $date_from,
            'date_to' => $date_to
        );
        
        $this->load_view('admin/all-reservations', $data);
    }
    
    public function settings_page() {
        // Handle settings form submission
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'rrs_settings_save')) {
            $this->handle_settings_save();
            wp_redirect(add_query_arg('message', 'settings_saved', admin_url('admin.php?page=res-settings')));
            exit;
        }
        
        $data = array(
            'settings' => $this->settings_model->get_all()
        );
        
        $this->load_view('admin/settings', $data);
    }
    
    private function handle_dashboard_actions() {
        if (isset($_GET['action']) && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'reservation_action')) {
            $id = intval($_GET['id']);
            $action = sanitize_text_field($_GET['action']);
            
            switch ($action) {
                case 'confirm':
                    $this->reservation_model->update($id, array('status' => 'confirmed'));
                    $this->add_admin_notice('Reservation confirmed successfully!', 'success');
                    break;
                    
                case 'cancel':
                    $this->reservation_model->update($id, array('status' => 'cancelled'));
                    $this->add_admin_notice('Reservation cancelled successfully!', 'success');
                    break;
                    
                case 'delete':
                    $this->reservation_model->delete($id);
                    $this->add_admin_notice('Reservation deleted successfully!', 'success');
                    break;
            }
            
            wp_redirect(admin_url('admin.php?page=reservations'));
            exit;
        }
        
        // Handle edit form submission
        if (isset($_POST['edit_reservation']) && wp_verify_nonce($_POST['edit_nonce'], 'edit_reservation')) {
            $this->handle_edit_reservation();
        }
    }
    
    private function handle_edit_reservation() {
        $id = intval($_POST['reservation_id']);
        $data = array(
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests']),
            'table_number' => sanitize_text_field($_POST['table_number']),
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        $validation = $this->reservation_model->validate($data);
        if ($validation === true) {
            $this->reservation_model->update($id, $data);
            $this->add_admin_notice('Reservation updated successfully!', 'success');
        } else {
            $this->add_admin_notice('Validation errors: ' . implode(', ', $validation), 'error');
        }
    }
    
    private function handle_settings_save() {
        $settings_to_save = array(
            'restaurant_open',
            'max_party_size',
            'restaurant_name',
            'restaurant_phone',
            'restaurant_email',
            'advance_booking_hours',
            'max_advance_days',
            'time_format',
            'currency_symbol'
        );
        
        foreach ($settings_to_save as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                $this->settings_model->set($setting, $value);
            }
        }
        
        // Handle opening hours
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        foreach ($days as $day) {
            if (isset($_POST[$day . '_open']) && isset($_POST[$day . '_close'])) {
                $hours = sanitize_text_field($_POST[$day . '_open']) . '-' . sanitize_text_field($_POST[$day . '_close']);
                $this->settings_model->set($day . '_hours', $hours);
            }
        }
        
        $this->add_admin_notice('Settings saved successfully!', 'success');
    }
    
    private function load_view($view, $data = array()) {
        $view_file = RRS_PLUGIN_PATH . 'views/' . $view . '.php';
        
        if (file_exists($view_file)) {
            extract($data);
            include $view_file;
        } else {
            echo '<div class="wrap"><h1>Restaurant Reservations</h1><p>View file not found: ' . $view . '</p></div>';
        }
    }
    
    private function add_admin_notice($message, $type = 'info') {
        add_action('admin_notices', function() use ($message, $type) {
            echo '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $message . '</p></div>';
        });
    }
}
?>

<?php
/**
 * Admin Controller Class - MVC Pattern
 * Complete version with phone number support
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
    
    public function dashboard_page() {
        // Handle actions with proper nonce verification
        if (isset($_GET['action']) && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'reservation_action')) {
            $id = intval($_GET['id']);
            $redirect_url = admin_url('admin.php?page=reservations');
            
            switch ($_GET['action']) {
                case 'confirm':
                    $result = $this->reservation_model->update($id, array('status' => 'confirmed'));
                    $redirect_url = add_query_arg('message', $result ? 'confirmed' : 'error', $redirect_url);
                    break;
                    
                case 'cancel':
                    $result = $this->reservation_model->update($id, array('status' => 'cancelled'));
                    $redirect_url = add_query_arg('message', $result ? 'cancelled' : 'error', $redirect_url);
                    break;
                    
                case 'delete':
                    $result = $this->reservation_model->delete($id);
                    $redirect_url = add_query_arg('message', $result ? 'deleted' : 'error', $redirect_url);
                    break;
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Handle edit form submission  
        if (isset($_POST['edit_reservation']) && wp_verify_nonce($_POST['edit_nonce'], 'edit_reservation')) {
            $id = intval($_POST['reservation_id']);
            $update_data = array(
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
            
            $result = $this->reservation_model->update($id, $update_data);
            wp_redirect(add_query_arg('message', $result ? 'updated' : 'error', admin_url('admin.php?page=reservations')));
            exit;
        }
        
        // Get data for dashboard
        $statistics = $this->reservation_model->get_statistics();
        $today_reservations = $this->reservation_model->get_by_date(date('Y-m-d'));
        $restaurant_status = $this->settings_model->get('restaurant_open', '1');
        $restaurant_name = $this->settings_model->get('restaurant_name', get_bloginfo('name'));
        
        // Load dashboard view
        $this->load_view('admin/dashboard', array(
            'statistics' => $statistics,
            'today_reservations' => $today_reservations,
            'restaurant_status' => $restaurant_status,
            'restaurant_name' => $restaurant_name
        ));
    }
    
    public function weekly_view_page() {
        $week_start = isset($_GET['week']) ? sanitize_text_field($_GET['week']) : date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
        
        $reservations = $this->reservation_model->get_by_date_range($week_start, $week_end);
        
        $this->load_view('admin/weekly-view', array(
            'week_start' => $week_start,
            'week_end' => $week_end,
            'reservations' => $reservations
        ));
    }
    
    public function all_reservations_page() {
        // Get filter parameters
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        
        // Get filtered reservations
        $reservations = $this->reservation_model->get_filtered_reservations($search, $status_filter, $date_from, $date_to);
        
        $this->load_view('admin/all-reservations', array(
            'reservations' => $reservations,
            'search' => $search,
            'status_filter' => $status_filter,
            'date_from' => $date_from,
            'date_to' => $date_to
        ));
    }
    
    public function settings_page() {
        // Handle form submission with the fixed method
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'rrs_settings_save')) {
            $this->save_settings_fixed();
        }
        
        // Get current settings
        $settings = $this->settings_model->get_all();
        
        // Load settings view
        $this->load_view('admin/settings', array('settings' => $settings));
    }
    
    /**
     * FIXED SETTINGS SAVE METHOD - With Phone Number Support
     */
    private function save_settings_fixed() {
        global $wpdb;
        
        // Include phone number and address in settings to save
        $settings_to_save = array(
            'restaurant_open' => 'restaurant_open',
            'restaurant_name' => 'restaurant_name',
            'restaurant_email' => 'restaurant_email',
            'restaurant_phone' => 'restaurant_phone', // Phone number support
            'restaurant_address' => 'restaurant_address', // Address support
            'max_party_size' => 'max_party_size'
        );
        
        $saved_count = 0;
        $table_name = $wpdb->prefix . 'rrs_settings';
        
        foreach ($settings_to_save as $post_key => $setting_name) {
            if (isset($_POST[$post_key])) {
                $value = sanitize_text_field($_POST[$post_key]);
                
                // Special validation for phone numbers
                if ($post_key === 'restaurant_phone') {
                    // Clean phone number - remove invalid characters but keep valid ones
                    $value = preg_replace('/[^0-9\+\-\(\)\s\.]/', '', $value);
                }
                
                // Special validation for email
                if ($post_key === 'restaurant_email' && !is_email($value)) {
                    $value = get_option('admin_email'); // Fallback to admin email
                }
                
                // Delete existing setting first, then insert new one
                $wpdb->delete($table_name, array('setting_name' => $setting_name));
                
                $result = $wpdb->insert($table_name, array(
                    'setting_name' => $setting_name,
                    'setting_value' => $value,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ));
                
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        // Clear all caches to ensure fresh data
        wp_cache_flush();
        delete_transient('rrs_settings_cache');
        wp_cache_delete('rrs_all_settings', 'rrs');
        
        // Redirect with success message
        $redirect_url = add_query_arg(array(
            'message' => 'saved',
            'count' => $saved_count
        ), admin_url('admin.php?page=res-settings'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'reservations') !== false) {
            wp_enqueue_style('rrs-admin-styles', RRS_PLUGIN_URL . 'restaurant-reservations.css', array(), RRS_VERSION);
            wp_enqueue_script('jquery');
        }
    }
    
    private function load_view($view, $data = array()) {
        extract($data);
        include RRS_PLUGIN_PATH . 'views/' . $view . '.php';
    }
}
?>

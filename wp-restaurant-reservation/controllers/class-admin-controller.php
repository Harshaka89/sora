<?php
/**
 * Admin Controller - Yenolx Restaurant Reservation v1.5.1
 * WORKING VERSION - Fixed all duplicate methods and enhanced functionality
 */

if (!defined('ABSPATH')) exit;

class YRR_Admin_Controller {
    private $reservation_model;
    private $settings_model;
    private $tables_model;
    private $hours_model;
    private $pricing_model;
    private $coupons_model;
    
    public function __construct() {
        $this->reservation_model = new YRR_Reservation_Model();
        $this->settings_model = new YRR_Settings_Model();
        $this->tables_model = new YRR_Tables_Model();
        $this->hours_model = new YRR_Hours_Model();
        $this->pricing_model = new YRR_Pricing_Model();
        $this->coupons_model = new YRR_Coupons_Model();
        
        add_action('init', array($this, 'add_custom_roles'));
    }
    
    public function add_custom_roles() {
        if (!get_role('yrr_admin')) {
            add_role('yrr_admin', 'Restaurant Admin', array(
                'read' => true,
                'yrr_manage_reservations' => true,
                'yrr_view_dashboard' => true
            ));
        }
        
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('yrr_manage_reservations');
            $admin_role->add_cap('yrr_view_dashboard');
            $admin_role->add_cap('yrr_manage_tables');
            $admin_role->add_cap('yrr_manage_hours');
            $admin_role->add_cap('yrr_manage_settings');
        }
    }
    
    private function check_permissions($capability = 'yrr_view_dashboard') {
        if (!current_user_can($capability)) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Yenolx Reservations',
            'Reservations',
            'yrr_view_dashboard',
            'yenolx-reservations',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            26
        );
        
        add_submenu_page('yenolx-reservations', 'Dashboard', 'Dashboard', 'yrr_view_dashboard', 'yenolx-reservations', array($this, 'dashboard_page'));
        add_submenu_page('yenolx-reservations', 'All Reservations', 'All Reservations', 'yrr_manage_reservations', 'yrr-all-reservations', array($this, 'all_reservations_page'));
        add_submenu_page('yenolx-reservations', 'Weekly View', 'Weekly View', 'yrr_manage_reservations', 'yrr-weekly-reservations', array($this, 'weekly_reservations_page'));
        add_submenu_page('yenolx-reservations', 'Table Schedule', 'Table Schedule', 'yrr_manage_reservations', 'yrr-table-schedule', array($this, 'table_schedule_page'));
        add_submenu_page('yenolx-reservations', 'Tables Management', 'Tables', 'yrr_manage_tables', 'yrr-tables', array($this, 'tables_page'));
        add_submenu_page('yenolx-reservations', 'Operating Hours', 'Hours', 'yrr_manage_hours', 'yrr-hours', array($this, 'hours_page'));
        add_submenu_page('yenolx-reservations', 'Settings', 'Settings', 'yrr_manage_settings', 'yrr-settings', array($this, 'settings_page'));
    }
    
    // ✅ SINGLE dashboard_page method - NO DUPLICATES
    public function dashboard_page() {
        $this->check_permissions('yrr_view_dashboard');
        
        // Handle manual reservation creation
        if (isset($_POST['create_manual_reservation']) && wp_verify_nonce($_POST['manual_reservation_nonce'], 'create_manual_reservation')) {
            $this->create_manual_reservation();
        }
        
        // Handle edit form submission
        if (isset($_POST['edit_reservation']) && wp_verify_nonce($_POST['edit_nonce'], 'edit_reservation')) {
            $this->handle_edit_reservation();
        }
        
        // Handle confirm with table assignment
        if (isset($_POST['confirm_with_table_action']) && wp_verify_nonce($_POST['confirm_table_nonce'], 'confirm_with_table')) {
            $this->handle_confirm_with_table();
        }
        
        // Handle reservation actions
        if (isset($_GET['action']) && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'reservation_action')) {
            $this->handle_reservation_actions();
        }
        
        // Load dashboard data
        $statistics = $this->reservation_model->get_statistics();
        $today_reservations = $this->reservation_model->get_by_date(date('Y-m-d'));
        $restaurant_status = $this->settings_model->get('restaurant_open', '1');
        $restaurant_name = $this->settings_model->get('restaurant_name', get_bloginfo('name'));
        
        // Pass user permission variables to prevent undefined errors
        $current_user = wp_get_current_user();
        $is_super_admin = in_array('administrator', $current_user->roles);
        $is_admin = $is_super_admin || in_array('yrr_admin', $current_user->roles);
        
        $this->load_view('admin/dashboard', array(
            'statistics' => $statistics,
            'today_reservations' => $today_reservations,
            'restaurant_status' => $restaurant_status,
            'restaurant_name' => $restaurant_name,
            'is_super_admin' => $is_super_admin,
            'is_admin' => $is_admin
        ));
    }
    
    public function weekly_reservations_page() {
        $this->check_permissions('yrr_manage_reservations');
        
        $current_week = isset($_GET['week']) ? sanitize_text_field($_GET['week']) : date('Y-m-d', strtotime('monday this week'));
        $weekly_reservations = $this->reservation_model->get_weekly_reservations($current_week);
        
        $this->load_view('admin/weekly-view', array(
            'weekly_reservations' => $weekly_reservations,
            'current_week' => $current_week
        ));
    }
    
    public function hours_page() {
        $this->check_permissions('yrr_manage_hours');
        
        // Handle form submission for saving hours
        if (isset($_POST['save_hours']) && wp_verify_nonce($_POST['hours_nonce'], 'yrr_hours_save')) {
            $this->save_operating_hours_complete();
        }
        
        $hours = $this->hours_model->get_all_hours();
        
        $this->load_view('admin/hours', array(
            'hours' => $hours
        ));
    }
    
    private function save_operating_hours_complete() {
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $saved_count = 0;
        
        foreach ($days as $day) {
            $is_closed = isset($_POST[$day . '_closed']) ? 1 : 0;
            $open_time = sanitize_text_field($_POST[$day . '_open'] ?? '10:00');
            $close_time = sanitize_text_field($_POST[$day . '_close'] ?? '22:00');
            
            $result = $this->hours_model->set_hours(
                $day, 
                $open_time . ':00', 
                $close_time . ':00', 
                $is_closed
            );
            
            if ($result) {
                $saved_count++;
            }
        }
        
        wp_redirect(add_query_arg(array(
            'message' => 'hours_saved', 
            'count' => $saved_count
        ), admin_url('admin.php?page=yrr-hours')));
        exit;
    }
    
    public function settings_page() {
        $this->check_permissions('yrr_manage_settings');
        
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'yrr_settings_save')) {
            $this->save_settings_enhanced();
        }
        
        $settings = $this->settings_model->get_all();
        $this->load_view('admin/settings', array('settings' => $settings));
    }
    
    private function save_settings_enhanced() {
        $settings_to_save = array(
            'restaurant_open', 'restaurant_name', 'restaurant_email', 'restaurant_phone',
            'max_party_size', 'time_slot_duration', 'booking_buffer_minutes',
            'max_advance_booking', 'auto_confirm_reservations'
        );
        
        $saved_count = 0;
        
        foreach ($settings_to_save as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                $result = $this->settings_model->set($setting, $value);
                if ($result !== false) $saved_count++;
            }
        }
        
        wp_redirect(add_query_arg(array('message' => 'saved', 'count' => $saved_count), admin_url('admin.php?page=yrr-settings')));
        exit;
    }
    
    // Additional helper methods...
    public function all_reservations_page() {
        $this->check_permissions('yrr_manage_reservations');
        $reservations = $this->reservation_model->get_all();
        $this->load_view('admin/all-reservations', array('reservations' => $reservations));
    }
    
    public function table_schedule_page() {
        $this->check_permissions('yrr_manage_reservations');
        $this->load_view('admin/table-schedule');
    }
    
    public function tables_page() {
        $this->check_permissions('yrr_manage_tables');
        $tables = $this->tables_model->get_all_tables();
        $this->load_view('admin/tables', array('tables' => $tables));
    }
    
    private function create_manual_reservation() {
        $reservation_code = 'MAN-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $reservation_data = array(
            'reservation_code' => $reservation_code,
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? ''),
            'status' => 'confirmed',
            'table_id' => !empty($_POST['table_id']) ? intval($_POST['table_id']) : null,
            'original_price' => 0.00,
            'final_price' => 0.00
        );
        
        $result = $this->reservation_model->create($reservation_data);
        
        if ($result) {
            wp_redirect(add_query_arg('message', 'reservation_created', admin_url('admin.php?page=yenolx-reservations')));
        } else {
            wp_redirect(add_query_arg('message', 'error', admin_url('admin.php?page=yenolx-reservations')));
        }
        exit;
    }
    
    private function handle_edit_reservation() {
        $id = intval($_POST['reservation_id']);
        
        $update_data = array(
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? '')
        );
        
        $result = $this->reservation_model->update($id, $update_data);
        
        if ($result !== false) {
            wp_redirect(add_query_arg('message', 'updated', admin_url('admin.php?page=yenolx-reservations')));
        } else {
            wp_redirect(add_query_arg('message', 'update_failed', admin_url('admin.php?page=yenolx-reservations')));
        }
        exit;
    }
    
    private function handle_confirm_with_table() {
        $reservation_id = intval($_POST['reservation_id']);
        $table_id = intval($_POST['table_id']);
        
        $update_data = array(
            'status' => 'confirmed',
            'table_id' => $table_id
        );
        
        $result = $this->reservation_model->update($reservation_id, $update_data);
        
        if ($result !== false) {
            wp_redirect(add_query_arg('message', 'confirmed_with_table', admin_url('admin.php?page=yenolx-reservations')));
        } else {
            wp_redirect(add_query_arg('message', 'confirm_failed', admin_url('admin.php?page=yenolx-reservations')));
        }
        exit;
    }
    
    private function handle_reservation_actions() {
        $id = intval($_GET['id']);
        $redirect_url = admin_url('admin.php?page=yenolx-reservations');
        
        switch ($_GET['action']) {
            case 'confirm':
                $result = $this->reservation_model->update($id, array('status' => 'confirmed'));
                $redirect_url = add_query_arg('message', $result ? 'confirmed' : 'error', $redirect_url);
                break;
            case 'cancel':
                $result = $this->reservation_model->update($id, array('status' => 'cancelled'));
                $redirect_url = add_query_arg('message', $result ? 'cancelled' : 'error', $redirect_url);
                break;
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    private function load_view($view, $data = array()) {
        extract($data);
        $view_file = YRR_PLUGIN_PATH . 'views/' . $view . '.php';
        
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>View file not found: ' . esc_html($view) . '.php</p></div>';
        }
    }
}
?>

<?php
/**
 * Admin Controller - Yenolx Restaurant Reservation v1.5
 */

if (!defined('ABSPATH')) exit;

class YRR_Admin_Controller {
    private $reservation_model;
    private $settings_model;
    private $tables_model;
    private $hours_model;
    private $pricing_model;
    
    public function __construct() {
        $this->reservation_model = new YRR_Reservation_Model();
        $this->settings_model = new YRR_Settings_Model();
        $this->tables_model = new YRR_Tables_Model();
        $this->hours_model = new YRR_Hours_Model();
        $this->pricing_model = new YRR_Pricing_Model();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Yenolx Reservations',
            'Reservations',
            'manage_options',
            'yenolx-reservations',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            26
        );
        
        add_submenu_page('yenolx-reservations', 'Dashboard', 'Dashboard', 'manage_options', 'yenolx-reservations', array($this, 'dashboard_page'));
        add_submenu_page('yenolx-reservations', 'All Reservations', 'All Reservations', 'manage_options', 'yrr-all-reservations', array($this, 'all_reservations_page'));
        add_submenu_page('yenolx-reservations', 'Tables Management', 'Tables', 'manage_options', 'yrr-tables', array($this, 'tables_page'));
        add_submenu_page('yenolx-reservations', 'Operating Hours', 'Hours', 'manage_options', 'yrr-hours', array($this, 'hours_page'));
        add_submenu_page('yenolx-reservations', 'Pricing Rules', 'Pricing', 'manage_options', 'yrr-pricing', array($this, 'pricing_page'));
        add_submenu_page('yenolx-reservations', 'Settings', 'Settings', 'manage_options', 'yrr-settings', array($this, 'settings_page'));
    }
    
    public function dashboard_page() {
        $statistics = $this->reservation_model->get_statistics();
        $today_reservations = $this->reservation_model->get_by_date(date('Y-m-d'));
        $restaurant_status = $this->settings_model->get('restaurant_open', '1');
        $restaurant_name = $this->settings_model->get('restaurant_name', get_bloginfo('name'));
        
        $this->load_view('admin/dashboard', array(
            'statistics' => $statistics,
            'today_reservations' => $today_reservations,
            'restaurant_status' => $restaurant_status,
            'restaurant_name' => $restaurant_name
        ));
    }
    
    public function settings_page() {
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'yrr_settings_save')) {
            $this->save_settings_enhanced();
        }
        
        $settings = $this->settings_model->get_all();
        
        $this->load_view('admin/settings', array('settings' => $settings));
    }
    
    private function save_settings_enhanced() {
        $settings_to_save = array(
            'restaurant_open',
            'restaurant_name',
            'restaurant_email',
            'restaurant_phone',
            'restaurant_address',
            'max_party_size',
            'base_price_per_person',
            'booking_time_slots',
            'max_booking_advance_days',
            'currency_symbol',
            'booking_buffer_minutes',
            'max_dining_duration'
        );
        
        $saved_count = 0;
        $errors = array();
        
        foreach ($settings_to_save as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                
                // Enhanced validation
                if ($setting === 'restaurant_phone') {
                    $validated_phone = $this->settings_model->validate_phone($value);
                    if ($validated_phone === false && !empty($value)) {
                        $errors[] = 'Invalid phone number format';
                        continue;
                    }
                    $value = $validated_phone;
                }
                
                if ($setting === 'restaurant_address') {
                    $value = $this->settings_model->validate_address($value);
                }
                
                if ($setting === 'restaurant_email' && !empty($value) && !is_email($value)) {
                    $errors[] = 'Invalid email format';
                    continue;
                }
                
                $result = $this->settings_model->set($setting, $value);
                if ($result !== false) {
                    $saved_count++;
                } else {
                    $errors[] = "Failed to save $setting";
                }
            }
        }
        
        wp_cache_flush();
        
        update_option('yrr_save_result', array(
            'saved_count' => $saved_count,
            'errors' => $errors,
            'timestamp' => current_time('mysql')
        ));
        
        $redirect_url = add_query_arg(array(
            'message' => 'saved',
            'count' => $saved_count,
            'error_count' => count($errors)
        ), admin_url('admin.php?page=yrr-settings'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    public function tables_page() {
        // Handle table management
        if (isset($_POST['add_table']) && wp_verify_nonce($_POST['table_nonce'], 'yrr_table_action')) {
            $this->add_table();
        }
        
        if (isset($_POST['update_table']) && wp_verify_nonce($_POST['table_nonce'], 'yrr_table_action')) {
            $this->update_table();
        }
        
        if (isset($_GET['delete_table']) && wp_verify_nonce($_GET['_wpnonce'], 'yrr_table_action')) {
            $this->delete_table(intval($_GET['delete_table']));
        }
        
        $tables = $this->tables_model->get_all_tables();
        
        $this->load_view('admin/tables', array('tables' => $tables));
    }
    
    public function hours_page() {
        if (isset($_POST['save_hours']) && wp_verify_nonce($_POST['hours_nonce'], 'yrr_hours_save')) {
            $this->save_operating_hours();
        }
        
        $hours = $this->hours_model->get_all_hours();
        
        $this->load_view('admin/hours', array('hours' => $hours));
    }
    
    public function pricing_page() {
        if (isset($_POST['add_rule']) && wp_verify_nonce($_POST['pricing_nonce'], 'yrr_pricing_action')) {
            $this->add_pricing_rule();
        }
        
        $rules = $this->pricing_model->get_all_rules();
        
        $this->load_view('admin/pricing', array('rules' => $rules));
    }
    
    private function add_table() {
        $data = array(
            'table_number' => sanitize_text_field($_POST['table_number']),
            'capacity' => intval($_POST['capacity']),
            'location' => sanitize_text_field($_POST['location']),
            'table_type' => sanitize_text_field($_POST['table_type'] ?? 'standard'),
            'status' => 'available'
        );
        
        $result = $this->tables_model->create_table($data);
        
        $redirect_url = add_query_arg('message', $result ? 'table_added' : 'error', admin_url('admin.php?page=yrr-tables'));
        wp_redirect($redirect_url);
        exit;
    }
    
    private function save_operating_hours() {
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $saved_count = 0;
        
        foreach ($days as $day) {
            $is_closed = isset($_POST[$day . '_closed']) ? 1 : 0;
            $open_time = sanitize_text_field($_POST[$day . '_open'] ?? '10:00');
            $close_time = sanitize_text_field($_POST[$day . '_close'] ?? '22:00');
            
            $result = $this->hours_model->set_hours($day, 'all_day', $open_time . ':00', $close_time . ':00', $is_closed);
            if ($result !== false) $saved_count++;
        }
        
        $redirect_url = add_query_arg(array(
            'message' => 'hours_saved',
            'count' => $saved_count
        ), admin_url('admin.php?page=yrr-hours'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    private function add_pricing_rule() {
        $data = array(
            'rule_name' => sanitize_text_field($_POST['rule_name']),
            'start_time' => sanitize_text_field($_POST['start_time']) . ':00',
            'end_time' => sanitize_text_field($_POST['end_time']) . ':00',
            'days_applicable' => sanitize_text_field($_POST['days_applicable']),
            'price_modifier' => floatval($_POST['price_modifier']),
            'modifier_type' => sanitize_text_field($_POST['modifier_type']),
            'is_active' => 1
        );
        
        $result = $this->pricing_model->create_rule($data);
        
        $redirect_url = add_query_arg('message', $result ? 'rule_added' : 'error', admin_url('admin.php?page=yrr-pricing'));
        wp_redirect($redirect_url);
        exit;
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'yenolx') !== false || strpos($hook, 'yrr') !== false) {
            wp_enqueue_style('yrr-admin-styles', YRR_PLUGIN_URL . 'assets/admin.css', array(), YRR_VERSION);
            wp_enqueue_script('yrr-admin-js', YRR_PLUGIN_URL . 'assets/admin.js', array('jquery'), YRR_VERSION, true);
            
            wp_localize_script('yrr-admin-js', 'yrr_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('yrr_ajax_nonce')
            ));
        }
    }
    
    private function load_view($view, $data = array()) {
        extract($data);
        include YRR_PLUGIN_PATH . 'views/' . $view . '.php';
    }
}
?>

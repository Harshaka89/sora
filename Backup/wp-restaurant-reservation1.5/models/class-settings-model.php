<?php
/**
 * Settings Model - Yenolx Restaurant Reservation v1.5
 */

if (!defined('ABSPATH')) exit;

class YRR_Settings_Model {
    private $table_name;
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'yrr_settings';
    }
    
    public function get($setting_name, $default = '') {
        $value = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT setting_value FROM {$this->table_name} WHERE setting_name = %s",
            $setting_name
        ));
        
        return $value !== null ? $value : $default;
    }
    
    public function set($setting_name, $setting_value) {
        return $this->wpdb->query($this->wpdb->prepare("
            REPLACE INTO {$this->table_name} (setting_name, setting_value, created_at, updated_at) 
            VALUES (%s, %s, %s, %s)
        ", $setting_name, $setting_value, current_time('mysql'), current_time('mysql')));
    }
    
    public function get_all() {
        $results = $this->wpdb->get_results("SELECT setting_name, setting_value FROM {$this->table_name}");
        
        $settings = array();
        if ($results) {
            foreach ($results as $row) {
                $settings[$row->setting_name] = $row->setting_value;
            }
        }
        
        $defaults = array(
            'restaurant_open' => '1',
            'restaurant_name' => get_bloginfo('name'),
            'restaurant_email' => get_option('admin_email'),
            'restaurant_phone' => '',
            'restaurant_address' => '',
            'max_party_size' => '12',
            'base_price_per_person' => '0.00',
            'booking_time_slots' => '30',
            'max_booking_advance_days' => '60',
            'currency_symbol' => '$',
            'time_format' => '12hour',
            'booking_buffer_minutes' => '15',
            'max_dining_duration' => '120'
        );
        
        return array_merge($defaults, $settings);
    }
    
    public function validate_phone($phone) {
        $cleaned = preg_replace('/[^0-9\+\-\(\)\s\.]/', '', trim($phone));
        
        if (empty($cleaned)) return '';
        
        $digits_only = preg_replace('/[^0-9]/', '', $cleaned);
        if (strlen($digits_only) < 7 || strlen($digits_only) > 15) {
            return false;
        }
        
        return $cleaned;
    }
    
    public function validate_address($address) {
        $cleaned = trim($address);
        return !empty($cleaned) ? sanitize_text_field($cleaned) : '';
    }
    
    public function is_restaurant_open() {
        return $this->get('restaurant_open', '1') === '1';
    }
    
    public function get_base_price() {
        return floatval($this->get('base_price_per_person', '0.00'));
    }
    
    public function get_booking_slots() {
        return intval($this->get('booking_time_slots', '30'));
    }
}
?>

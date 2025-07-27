<?php
/**
 * Settings Model Class
 */

if (!defined('ABSPATH')) exit;

class RRS_Settings_Model {
    private $table_name;
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'rrs_settings';
    }
    
    public function get($setting_name, $default = '') {
        $value = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT setting_value FROM {$this->table_name} WHERE setting_name = %s",
            $setting_name
        ));
        
        return $value !== null ? $value : $default;
    }
    
    public function set($setting_name, $setting_value) {
        return $this->wpdb->replace($this->table_name, array(
            'setting_name' => $setting_name,
            'setting_value' => $setting_value,
            'updated_at' => current_time('mysql')
        ));
    }
    
    /**
     * ENHANCED get_all() method with comprehensive defaults
     */
    public function get_all() {
        // Force fresh data from database
        $results = $this->wpdb->get_results("SELECT setting_name, setting_value FROM {$this->table_name}");
        
        $settings = array();
        if ($results) {
            foreach ($results as $row) {
                $settings[$row->setting_name] = $row->setting_value;
            }
        }
        
        // Comprehensive defaults to ensure all settings exist
        $defaults = array(
            'restaurant_open' => '1',
            'max_party_size' => '12',
            'restaurant_name' => get_bloginfo('name'),
            'restaurant_email' => get_option('admin_email'),
            'restaurant_phone' => '',
            'advance_booking_hours' => '2',
            'max_advance_days' => '60',
            'currency_symbol' => '$',
            'time_format' => '12hour',
            'monday_hours' => '10:00-22:00',
            'tuesday_hours' => '10:00-22:00',
            'wednesday_hours' => '10:00-22:00',
            'thursday_hours' => '10:00-22:00',
            'friday_hours' => '10:00-23:00',
            'saturday_hours' => '09:00-23:00',
            'sunday_hours' => '09:00-21:00'
        );
        
        return array_merge($defaults, $settings);
    }
    
    public function delete($setting_name) {
        return $this->wpdb->delete($this->table_name, array(
            'setting_name' => $setting_name
        ));
    }
    
    public function is_restaurant_open() {
        return $this->get('restaurant_open', '1') === '1';
    }
    
    public function get_hours($day) {
        $hours = $this->get($day . '_hours', '10:00-22:00');
        $parts = explode('-', $hours);
        
        return array(
            'open' => isset($parts[0]) ? trim($parts[0]) : '10:00',
            'close' => isset($parts[1]) ? trim($parts[1]) : '22:00',
            'full' => $hours
        );
    }
}
?>

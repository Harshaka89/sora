<?php
/**
 * Settings Model Class
 * 
 * Handles all database operations for restaurant settings
 * 
 * @package RestaurantReservations
 * @subpackage Models
 * @version 1.4.0
 * @since 1.0.0
 * @author Your Name
 * 
 * @class RRS_Settings_Model
 * @description Configuration and settings data layer
 * 
 * Methods:
 * - get($setting_name, $default) - Get setting value
 * - set($setting_name, $value) - Set setting value
 * - get_all() - Get all settings
 * - delete($setting_name) - Delete setting
 * - is_restaurant_open() - Check if open
 * - get_hours($day) - Get opening hours
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
            'setting_value' => $setting_value
        ));
    }
    
    public function get_all() {
        $results = $this->wpdb->get_results("SELECT setting_name, setting_value FROM {$this->table_name}");
        
        $settings = array();
        foreach ($results as $row) {
            $settings[$row->setting_name] = $row->setting_value;
        }
        
        $defaults = array(
            'restaurant_open' => '1',
            'max_party_size' => '12',
            'restaurant_name' => get_bloginfo('name'),
            'restaurant_email' => get_option('admin_email')
        );
        
        return array_merge($defaults, $settings);
    }
    
    public function is_restaurant_open() {
        return $this->get('restaurant_open', '1') === '1';
    }
}
?>
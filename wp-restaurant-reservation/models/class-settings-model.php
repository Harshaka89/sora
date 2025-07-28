<?php
/**
 * Settings Model - Yenolx Restaurant Reservation v1.5.1
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
        $result = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT setting_value FROM {$this->table_name} WHERE setting_name = %s",
            $setting_name
        ));
        
        return $result !== null ? $result : $default;
    }
    
    public function set($setting_name, $setting_value) {
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE setting_name = %s",
            $setting_name
        ));
        
        if ($existing) {
            return $this->wpdb->update(
                $this->table_name,
                array(
                    'setting_value' => $setting_value,
                    'updated_at' => current_time('mysql')
                ),
                array('setting_name' => $setting_name)
            );
        } else {
            return $this->wpdb->insert($this->table_name, array(
                'setting_name' => $setting_name,
                'setting_value' => $setting_value,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ));
        }
    }
    
    public function get_all() {
        $results = $this->wpdb->get_results("SELECT setting_name, setting_value FROM {$this->table_name}");
        $settings = array();
        
        foreach ($results as $result) {
            $settings[$result->setting_name] = $result->setting_value;
        }
        
        return $settings;
    }
    
    public function validate_phone($phone) {
        if (empty($phone)) return '';
        
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^\d\+]/', '', $phone);
        
        return $phone;
    }
    
/**
 * Generate time slots based on current settings
 */
public function get_available_time_slots($date = null) {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    // Get current settings
    $duration = intval($this->get('time_slot_duration', 60)); // minutes
    $hours_model = new YRR_Hours_Model();
    $today_hours = $hours_model->get_today_hours();
    
    if (!$today_hours || $today_hours->is_closed) {
        return array(); // Restaurant closed today
    }
    
    $open_time = date('H:i', strtotime($today_hours->open_time));
    $close_time = date('H:i', strtotime($today_hours->close_time));
    
    // Generate time slots
    $slots = array();
    $open_minutes = $this->time_to_minutes($open_time);
    $close_minutes = $this->time_to_minutes($close_time);
    
    // Handle overnight service
    if ($close_minutes <= $open_minutes) {
        $close_minutes += 24 * 60;
    }
    
    for ($current = $open_minutes; $current < $close_minutes; $current += $duration) {
        $hour = floor($current / 60) % 24;
        $minute = $current % 60;
        $time_string = sprintf('%02d:%02d', $hour, $minute);
        
        $slots[] = array(
            'value' => $time_string,
            'display' => $this->format_time_12hour($time_string),
            'available' => $this->is_slot_available($date, $time_string)
        );
    }
    
    return $slots;
}

private function time_to_minutes($time) {
    list($hours, $minutes) = explode(':', $time);
    return intval($hours) * 60 + intval($minutes);
}

private function format_time_12hour($time) {
    return date('g:i A', strtotime($time));
}

private function is_slot_available($date, $time) {
    global $wpdb;
    
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}yrr_reservations 
         WHERE reservation_date = %s AND reservation_time = %s AND status IN ('confirmed', 'pending')",
        $date, $time . ':00'
    ));
    
    $total_tables = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}yrr_tables WHERE status = 'available'");
    
    return $existing < $total_tables;
}



    public function validate_address($address) {
        return sanitize_text_field($address);
    }
}
?>

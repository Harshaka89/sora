<?php
/**
 * Hours Model - WORKING VERSION - No duplicate methods
 */

if (!defined('ABSPATH')) exit;

class YRR_Hours_Model {
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'yrr_operating_hours';
    }
    
    public function get_all_hours() {
        $results = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY 
             FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')"
        );
        
        $hours = array();
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        
        foreach ($days as $day) {
            $found = false;
            foreach ($results as $result) {
                if ($result->day_of_week === $day) {
                    $hours[$day] = $result;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $hours[$day] = (object) array(
                    'day_of_week' => $day,
                    'open_time' => '10:00:00',
                    'close_time' => '22:00:00',
                    'is_closed' => 0,
                    'break_start' => null,
                    'break_end' => null
                );
            }
        }
        
        return $hours;
    }
    
    public function set_hours($day, $open_time, $close_time, $is_closed = 0) {
        $data = array(
            'day_of_week' => $day,
            'open_time' => $open_time,
            'close_time' => $close_time,
            'is_closed' => intval($is_closed),
            'updated_at' => current_time('mysql')
        );
        
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE day_of_week = %s",
            $day
        ));
        
        if ($existing) {
            $result = $this->wpdb->update(
                $this->table_name,
                $data,
                array('day_of_week' => $day)
            );
            return $result !== false;
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $this->wpdb->insert($this->table_name, $data);
            return $result !== false;
        }
    }
    
    // ✅ SINGLE get_today_hours method - NO DUPLICATES
    public function get_today_hours() {
        $today = strtolower(date('l')); // monday, tuesday, etc.
        return $this->get_day_hours($today);
    }
    
    // ✅ SINGLE get_day_hours method - NO DUPLICATES
    public function get_day_hours($day) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE day_of_week = %s",
            $day
        ));
    }
}
?>

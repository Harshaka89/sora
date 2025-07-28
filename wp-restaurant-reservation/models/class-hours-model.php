<?php
/**
 * Hours Model - Clean version without duplicates
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
    
    /**
     * Get all operating hours
     */
public function get_all_hours() {
    $results = $this->wpdb->get_results(
        "SELECT * FROM {$this->table_name} ORDER BY 
         FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')"
    );
    
    $hours = array();
    $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    
    // Return objects, not arrays
    foreach ($days as $day) {
        $found = false;
        foreach ($results as $result) {
            if ($result->day_of_week === $day) {
                $hours[$day] = $result; // This is an object
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Create default object
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

    
    /**
     * Set hours for a specific day
     */
    public function set_hours($day, $open_time, $close_time, $is_closed = 0, $break_start = null, $break_end = null) {
        $data = array(
            'day_of_week' => $day,
            'open_time' => $open_time,
            'close_time' => $close_time,
            'is_closed' => intval($is_closed),
            'break_start' => $break_start,
            'break_end' => $break_end,
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
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $this->wpdb->insert($this->table_name, $data);
        }
        
        return $result !== false;
    }
    
    /**
     * Get hours for a specific day - ONLY ONE VERSION
     */
    public function get_day_hours($day) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE day_of_week = %s",
            $day
        ));
    }
    
    /**
     * Get today's hours - ONLY ONE VERSION
     */
    public function get_today_hours() {
        $today = strtolower(date('l')); // monday, tuesday, etc.
        return $this->get_day_hours($today);
    }
    
    /**
     * Check if restaurant is open at specific day/time
     */
    public function is_open($day, $time) {
        $hours = $this->get_day_hours($day);
        
        if (!$hours || $hours->is_closed) {
            return false;
        }
        
        $current_time = strtotime($time);
        $open_time = strtotime($hours->open_time);
        $close_time = strtotime($hours->close_time);
        
        // Handle overnight hours
        if ($close_time <= $open_time) {
            $close_time += 24 * 3600;
            if ($current_time < $open_time) {
                $current_time += 24 * 3600;
            }
        }
        
        return $current_time >= $open_time && $current_time <= $close_time;
    }
}
?>

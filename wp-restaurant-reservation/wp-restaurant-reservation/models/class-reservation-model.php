<?php
/**
 * Reservation Model - Yenolx Restaurant Reservation v1.5.1
 * FIXED: Removed all duplicate methods
 */

if (!defined('ABSPATH')) exit;

class YRR_Reservation_Model {
    private $wpdb;
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'yrr_reservations';
    }
    
    public function create($data) {
        $default_data = array(
            'reservation_code' => '',
            'customer_name' => '',
            'customer_email' => '',
            'customer_phone' => '',
            'party_size' => 1,
            'reservation_date' => date('Y-m-d'),
            'reservation_time' => date('H:i:s'),
            'special_requests' => '',
            'status' => 'pending',
            'table_id' => null,
            'coupon_code' => null,
            'original_price' => 0.00,
            'discount_amount' => 0.00,
            'final_price' => 0.00,
            'price_breakdown' => null,
            'notes' => '',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $default_data);
        
        $result = $this->wpdb->insert($this->table_name, $data);
        
        if ($result === false) {
            error_log('YRR: Failed to create reservation: ' . $this->wpdb->last_error);
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    public function update($id, $data) {
        $data['updated_at'] = current_time('mysql');
        
        $result = $this->wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        if ($result === false) {
            error_log('YRR: Failed to update reservation: ' . $this->wpdb->last_error);
        }
        
        return $result;
    }
    
    public function delete($id) {
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }
    
    public function get_by_id($id) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }
    
    public function get_by_date($date) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE reservation_date = %s 
             ORDER BY reservation_time ASC",
            $date
        ));
    }
    
    public function get_by_status($status) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE status = %s 
             ORDER BY reservation_date ASC, reservation_time ASC",
            $status
        ));
    }
    
    // ✅ ONLY ONE get_weekly_reservations method (no duplicates)
    public function get_weekly_reservations($start_date = null) {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('monday this week'));
        }
        
        $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));
        
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE reservation_date BETWEEN %s AND %s 
             AND status IN ('confirmed', 'pending', 'cancelled')
             ORDER BY reservation_date ASC, reservation_time ASC",
            $start_date, $end_date
        ));
    }
    
    public function get_filtered_reservations($search = '', $status_filter = '', $date_from = '', $date_to = '') {
        $where_clauses = array();
        $params = array();
        
        if (!empty($search)) {
            $where_clauses[] = "(customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s OR reservation_code LIKE %s)";
            $search_term = '%' . $this->wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($status_filter)) {
            $where_clauses[] = "status = %s";
            $params[] = $status_filter;
        }
        
        if (!empty($date_from)) {
            $where_clauses[] = "reservation_date >= %s";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where_clauses[] = "reservation_date <= %s";
            $params[] = $date_to;
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        $sql = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY reservation_date DESC, reservation_time DESC";
        
        if (!empty($params)) {
            return $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
        } else {
            return $this->wpdb->get_results($sql);
        }
    }
    
    public function get_statistics() {
        $stats = array(
            'total' => 0,
            'today' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'cancelled' => 0,
            'this_week' => 0,
            'this_month' => 0
        );
        
        // Total reservations
        $stats['total'] = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Today's reservations
        $stats['today'] = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date = %s",
            date('Y-m-d')
        ));
        
        // By status
        $stats['pending'] = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending'");
        $stats['confirmed'] = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'confirmed'");
        $stats['cancelled'] = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'cancelled'");
        
        // This week
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        $stats['this_week'] = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date BETWEEN %s AND %s",
            $week_start, $week_end
        ));
        
        // This month
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');
        $stats['this_month'] = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date BETWEEN %s AND %s",
            $month_start, $month_end
        ));
        
        return $stats;
    }
    
    public function get_upcoming_reservations($limit = 10) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE reservation_date >= %s 
             AND status IN ('confirmed', 'pending')
             ORDER BY reservation_date ASC, reservation_time ASC 
             LIMIT %d",
            date('Y-m-d'), $limit
        ));
    }
    
    public function get_reservations_by_table($table_id, $date = null) {
        if ($date) {
            return $this->wpdb->get_results($this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                 WHERE table_id = %d AND reservation_date = %s 
                 ORDER BY reservation_time ASC",
                $table_id, $date
            ));
        } else {
            return $this->wpdb->get_results($this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                 WHERE table_id = %d AND reservation_date >= %s 
                 ORDER BY reservation_date ASC, reservation_time ASC",
                $table_id, date('Y-m-d')
            ));
        }
    }
    
    public function check_availability($date, $time, $party_size, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE reservation_date = %s 
                AND reservation_time = %s 
                AND status IN ('confirmed', 'pending')";
        
        $params = array($date, $time);
        
        if ($exclude_id) {
            $sql .= " AND id != %d";
            $params[] = $exclude_id;
        }
        
        $existing_reservations = $this->wpdb->get_var($this->wpdb->prepare($sql, $params));
        
        // Simple availability check - can be enhanced with table capacity logic
        return $existing_reservations < 10; // Assuming max 10 concurrent reservations
    }
    
    public function get_revenue_by_period($start_date, $end_date) {
        return $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT SUM(final_price) FROM {$this->table_name} 
             WHERE reservation_date BETWEEN %s AND %s 
             AND status = 'confirmed'",
            $start_date, $end_date
        ));
    }
}
?>

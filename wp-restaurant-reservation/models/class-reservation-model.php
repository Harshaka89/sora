<?php
/**
 * Reservation Model - Yenolx Restaurant Reservation v1.5
 */

if (!defined('ABSPATH')) exit;

class YRR_Reservation_Model {
    private $table_name;
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'yrr_reservations';
    }
    
    public function get_all() {
        return $this->wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY reservation_date DESC, reservation_time DESC");
    }
    
    public function get_by_id($id) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }
    
    public function get_by_date($date) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE reservation_date = %s ORDER BY reservation_time",
            $date
        ));
    }
    
    public function get_by_date_range($start_date, $end_date) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE reservation_date BETWEEN %s AND %s 
             ORDER BY reservation_date, reservation_time",
            $start_date, $end_date
        ));
    }
    
    public function create($data) {
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->insert($this->table_name, $data);
    }
    
    public function update($id, $data) {
        $data['updated_at'] = current_time('mysql');
        return $this->wpdb->update($this->table_name, $data, array('id' => $id));
    }
    
    public function delete($id) {
        return $this->wpdb->delete($this->table_name, array('id' => $id));
    }
    
    public function get_statistics() {
        $total = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $confirmed = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'confirmed'");
        $pending = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending'");
        $today = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date = %s",
            date('Y-m-d')
        ));
        
        return array(
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'today' => $today
        );
    }
    
    public function get_filtered_reservations($search = '', $status = '', $date_from = '', $date_to = '') {
        $where_conditions = array();
        $params = array();
        
        if (!empty($search)) {
            $where_conditions[] = "(customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s)";
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($status)) {
            $where_conditions[] = "status = %s";
            $params[] = $status;
        }
        
        if (!empty($date_from)) {
            $where_conditions[] = "reservation_date >= %s";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where_conditions[] = "reservation_date <= %s";
            $params[] = $date_to;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT * FROM {$this->table_name} $where_clause ORDER BY reservation_date DESC, reservation_time DESC";
        
        if (!empty($params)) {
            return $this->wpdb->get_results($this->wpdb->prepare($sql, ...$params));
        } else {
            return $this->wpdb->get_results($sql);
        }
    }
}
?>

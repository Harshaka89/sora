<?php
/**
 * Reservation Model - MVC Data Layer
 */

if (!defined('ABSPATH')) exit;

class RRS_Reservation_Model {
    private $table_name;
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'rrs_reservations';
    }
    
    public function create($data) {
        $defaults = array(
            'reservation_code' => $this->generate_reservation_code(),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        $result = $this->wpdb->insert($this->table_name, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create reservation');
        }
        
        return $this->wpdb->insert_id;
    }
    
    public function get_by_id($id) {
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id);
        return $this->wpdb->get_row($sql);
    }
    
    public function get_by_date($date) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE reservation_date = %s ORDER BY reservation_time ASC",
            $date
        );
        return $this->wpdb->get_results($sql);
    }
    
    public function get_by_date_range($start_date, $end_date) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE reservation_date BETWEEN %s AND %s ORDER BY reservation_date, reservation_time",
            $start_date, $end_date
        );
        return $this->wpdb->get_results($sql);
    }
    
    /**
     * Get filtered reservations - FIXED METHOD
     */
    public function get_filtered_reservations($search = '', $status_filter = '', $date_from = '', $date_to = '') {
        $sql = "SELECT * FROM {$this->table_name} WHERE 1=1";
        $params = array();
        
        // Search filter
        if (!empty($search)) {
            $search_term = '%' . $search . '%';
            $sql .= " AND (customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s OR reservation_code LIKE %s)";
            $params = array_merge($params, array($search_term, $search_term, $search_term, $search_term));
        }
        
        // Status filter
        if (!empty($status_filter)) {
            $sql .= " AND status = %s";
            $params[] = $status_filter;
        }
        
        // Date from filter
        if (!empty($date_from)) {
            $sql .= " AND reservation_date >= %s";
            $params[] = $date_from;
        }
        
        // Date to filter
        if (!empty($date_to)) {
            $sql .= " AND reservation_date <= %s";
            $params[] = $date_to;
        }
        
        $sql .= " ORDER BY reservation_date DESC, reservation_time DESC LIMIT 100";
        
        if (empty($params)) {
            return $this->wpdb->get_results($sql);
        } else {
            return $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
        }
    }
    
    public function update($id, $data) {
        $data['updated_at'] = current_time('mysql');
        $result = $this->wpdb->update($this->table_name, $data, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update reservation');
        }
        
        return $result;
    }
    
    public function delete($id) {
        $result = $this->wpdb->delete($this->table_name, array('id' => $id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete reservation');
        }
        
        return $result;
    }
    
    public function get_statistics() {
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        
        return array(
            'today' => $this->count_by_date($today),
            'pending' => $this->count_by_status('pending'),
            'confirmed_today' => $this->count_by_date_and_status($today, 'confirmed'),
            'week' => $this->count_by_date_range($week_start, $week_end),
            'total' => $this->count_all()
        );
    }
    
    private function count_by_date($date) {
        $sql = $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date = %s", $date);
        return $this->wpdb->get_var($sql);
    }
    
    private function count_by_status($status) {
        $sql = $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s", $status);
        return $this->wpdb->get_var($sql);
    }
    
    private function count_by_date_and_status($date, $status) {
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date = %s AND status = %s",
            $date, $status
        );
        return $this->wpdb->get_var($sql);
    }
    
    private function count_by_date_range($start_date, $end_date) {
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_date BETWEEN %s AND %s",
            $start_date, $end_date
        );
        return $this->wpdb->get_var($sql);
    }
    
    private function count_all() {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    private function generate_reservation_code() {
        do {
            $code = 'RES-' . date('Ymd') . '-' . strtoupper(wp_generate_password(6, false));
            $exists = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE reservation_code = %s",
                $code
            ));
        } while ($exists > 0);
        
        return $code;
    }
    
    public function validate($data) {
        $errors = array();
        
        if (empty($data['customer_name'])) {
            $errors[] = 'Customer name is required';
        }
        
        if (empty($data['customer_email']) || !is_email($data['customer_email'])) {
            $errors[] = 'Valid email address is required';
        }
        
        if (empty($data['customer_phone'])) {
            $errors[] = 'Phone number is required';
        }
        
        if (empty($data['party_size']) || $data['party_size'] < 1) {
            $errors[] = 'Valid party size is required';
        }
        
        if (empty($data['reservation_date'])) {
            $errors[] = 'Reservation date is required';
        }
        
        if (empty($data['reservation_time'])) {
            $errors[] = 'Reservation time is required';
        }
        
        return empty($errors) ? true : $errors;
    }
}
?>

<?php
class RRS_Reservation {
    
    public function create_reservation($data) {
        global $wpdb;
        
        $result = $wpdb->insert($wpdb->prefix . 'rrs_reservations', $data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    public function get_reservation($id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rrs_reservations WHERE id = %d",
            $id
        ), ARRAY_A);
    }
    
    public function update_reservation($id, $data) {
        global $wpdb;
        
        return $wpdb->update(
            $wpdb->prefix . 'rrs_reservations',
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
    }
}

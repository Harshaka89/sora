<?php
class RRS_Database_Manager {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Enhanced reservations table
        $reservations_sql = "CREATE TABLE {$wpdb->prefix}rrs_reservations (
            id int(11) NOT NULL AUTO_INCREMENT,
            reservation_code varchar(20) UNIQUE NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(20) NOT NULL,
            party_size int(11) NOT NULL,
            reservation_date date NOT NULL,
            reservation_time time NOT NULL,
            special_requests text,
            status varchar(20) DEFAULT 'pending',
            gdpr_consent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_date_time (reservation_date, reservation_time),
            INDEX idx_status (status),
            INDEX idx_customer_email (customer_email)
        ) $charset_collate;";
        
        // Tables management
        $tables_sql = "CREATE TABLE {$wpdb->prefix}rrs_tables (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            capacity_min int(11) NOT NULL DEFAULT 1,
            capacity_max int(11) NOT NULL DEFAULT 8,
            x_position int(11) DEFAULT 0,
            y_position int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Settings table
        $settings_sql = "CREATE TABLE {$wpdb->prefix}rrs_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_name varchar(100) NOT NULL,
            setting_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_name (setting_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($reservations_sql);
        dbDelta($tables_sql);
        dbDelta($settings_sql);
        
        // Insert sample data
        self::insert_sample_data();
    }
    
    private static function insert_sample_data() {
        global $wpdb;
        
        // Check if sample data already exists
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rrs_reservations");
        
        if ($existing == 0) {
            // Sample reservations
            $sample_reservations = array(
                array(
                    'reservation_code' => 'RES-001',
                    'customer_name' => 'John Smith',
                    'customer_email' => 'john@example.com',
                    'customer_phone' => '123-456-7890',
                    'party_size' => 4,
                    'reservation_date' => date('Y-m-d'),
                    'reservation_time' => '19:00:00',
                    'special_requests' => 'Window table please',
                    'status' => 'confirmed',
                    'gdpr_consent' => 1
                ),
                array(
                    'reservation_code' => 'RES-002',
                    'customer_name' => 'Sarah Johnson',
                    'customer_email' => 'sarah@example.com',
                    'customer_phone' => '987-654-3210',
                    'party_size' => 2,
                    'reservation_date' => date('Y-m-d'),
                    'reservation_time' => '20:30:00',
                    'special_requests' => 'Anniversary dinner',
                    'status' => 'pending',
                    'gdpr_consent' => 1
                ),
                array(
                    'reservation_code' => 'RES-003',
                    'customer_name' => 'Mike Wilson',
                    'customer_email' => 'mike@example.com',
                    'customer_phone' => '555-123-4567',
                    'party_size' => 6,
                    'reservation_date' => date('Y-m-d', strtotime('+1 day')),
                    'reservation_time' => '18:30:00',
                    'special_requests' => 'Birthday celebration',
                    'status' => 'confirmed',
                    'gdpr_consent' => 1
                )
            );
            
            foreach ($sample_reservations as $reservation) {
                $wpdb->insert($wpdb->prefix . 'rrs_reservations', $reservation);
            }
            
            // Sample tables
            $sample_tables = array(
                array('name' => 'Table 1', 'capacity_min' => 2, 'capacity_max' => 4),
                array('name' => 'Table 2', 'capacity_min' => 2, 'capacity_max' => 4),
                array('name' => 'Table 3', 'capacity_min' => 4, 'capacity_max' => 6),
                array('name' => 'Table 4', 'capacity_min' => 6, 'capacity_max' => 8),
                array('name' => 'Table 5', 'capacity_min' => 2, 'capacity_max' => 4),
                array('name' => 'Table 6', 'capacity_min' => 8, 'capacity_max' => 12)
            );
            
            foreach ($sample_tables as $table) {
                $wpdb->insert($wpdb->prefix . 'rrs_tables', $table);
            }
            
            // Default settings
            $default_settings = array(
                'opening_hours' => json_encode(array(
                    'monday' => array('open' => '10:00', 'close' => '22:00'),
                    'tuesday' => array('open' => '10:00', 'close' => '22:00'),
                    'wednesday' => array('open' => '10:00', 'close' => '22:00'),
                    'thursday' => array('open' => '10:00', 'close' => '22:00'),
                    'friday' => array('open' => '10:00', 'close' => '23:00'),
                    'saturday' => array('open' => '09:00', 'close' => '23:00'),
                    'sunday' => array('open' => '09:00', 'close' => '21:00')
                )),
                'booking_rules' => json_encode(array(
                    'max_party_size' => 12,
                    'min_advance_booking' => 2,
                    'max_advance_booking' => 60,
                    'slot_interval' => 30
                )),
                'email_settings' => json_encode(array(
                    'admin_email' => get_option('admin_email'),
                    'sender_name' => get_bloginfo('name'),
                    'send_confirmations' => true,
                    'send_reminders' => true
                ))
            );
            
            foreach ($default_settings as $name => $value) {
                $wpdb->insert($wpdb->prefix . 'rrs_settings', array(
                    'setting_name' => $name,
                    'setting_value' => $value
                ));
            }
        }
    }
}

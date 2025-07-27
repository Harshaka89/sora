<?php
class RRS_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_post_create_reservation', array($this, 'handle_create_reservation'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    public function handle_admin_actions() {
        // Handle confirmation
        if (isset($_GET['action']) && $_GET['action'] === 'confirm' && isset($_GET['reservation_id'])) {
            $this->confirm_reservation(intval($_GET['reservation_id']));
        }
    }
    
    private function confirm_reservation($reservation_id) {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'rrs_reservations',
            array('status' => 'confirmed'),
            array('id' => $reservation_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=restaurant-reservations&message=confirmed'));
        } else {
            wp_redirect(admin_url('admin.php?page=restaurant-reservations&message=error'));
        }
        exit;
    }
    
    public function add_admin_menus() {
        add_menu_page(
            'Restaurant Reservations',
            'Reservations',
            'manage_options',
            'restaurant-reservations',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            26
        );
        
        add_submenu_page(
            'restaurant-reservations',
            'All Reservations',
            'All Reservations',
            'manage_options',
            'rrs-all-reservations',
            array($this, 'all_reservations_page')
        );
    }
    
    public function dashboard_page() {
        include RRS_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }
    
    public function all_reservations_page() {
        global $wpdb;
        
        $reservations = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rrs_reservations ORDER BY reservation_date DESC, reservation_time DESC LIMIT 100"
        );
        
        ?>
        <div class="wrap">
            <h1>All Reservations</h1>
            
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <?php if (!empty($reservations)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Contact Info</th>
                                <th>Party Size</th>
                                <th>Status</th>
                                <th>Special Requests</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><strong><?php echo esc_html($reservation->reservation_code); ?></strong></td>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($reservation->reservation_date)); ?></strong><br>
                                    <?php echo date('g:i A', strtotime($reservation->reservation_time)); ?>
                                </td>
                                <td><strong><?php echo esc_html($reservation->customer_name); ?></strong></td>
                                <td>
                                    <?php echo esc_html($reservation->customer_email); ?><br>
                                    <small><?php echo esc_html($reservation->customer_phone); ?></small>
                                </td>
                                <td style="text-align: center;"><?php echo $reservation->party_size; ?></td>
                                <td>
                                    <span style="padding: 4px 12px; background: <?php echo $reservation->status === 'confirmed' ? '#00a32a' : '#d63638'; ?>; color: white; border-radius: 12px; font-size: 12px;">
                                        <?php echo ucfirst($reservation->status); ?>
                                    </span>
                                </td>
                                <td><small><?php echo esc_html($reservation->special_requests ?: 'None'); ?></small></td>
                                <td><small><?php echo date('M j, g:i A', strtotime($reservation->created_at)); ?></small></td>
                                <td>
                                    <?php if ($reservation->status === 'pending'): ?>
                                        <a href="<?php echo admin_url('admin.php?page=restaurant-reservations&action=confirm&reservation_id=' . $reservation->id); ?>" class="button button-primary button-small">Confirm</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No reservations found.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    public function handle_create_reservation() {
        if (!wp_verify_nonce($_POST['reservation_nonce'], 'create_reservation')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        
        $reservation_data = array(
            'reservation_code' => 'ADM-' . time() . '-' . rand(100, 999),
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'party_size' => intval($_POST['party_size']),
            'reservation_date' => sanitize_text_field($_POST['reservation_date']),
            'reservation_time' => sanitize_text_field($_POST['reservation_time']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        $result = $wpdb->insert($wpdb->prefix . 'rrs_reservations', $reservation_data);
        
        if ($result) {
            wp_redirect(admin_url('admin.php?page=restaurant-reservations&message=created'));
        } else {
            wp_redirect(admin_url('admin.php?page=restaurant-reservations&message=error'));
        }
        exit;
    }
}

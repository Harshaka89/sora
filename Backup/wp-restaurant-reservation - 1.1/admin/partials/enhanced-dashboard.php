<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap rrs-admin-wrap">
    <div class="rrs-header">
        <h1 class="wp-heading-inline">Restaurant Reservations Dashboard</h1>
        <span class="rrs-version">Version 1.1</span>
        <a href="#" class="page-title-action rrs-create-reservation-btn">
            <span class="dashicons dashicons-plus-alt"></span>
            Add New Reservation
        </a>
    </div>
    
    <div class="rrs-dashboard-nav">
        <a href="<?php echo admin_url('admin.php?page=restaurant-reservations'); ?>" class="nav-tab nav-tab-active">Dashboard</a>
        <a href="<?php echo admin_url('admin.php?page=rrs-todays-schedule'); ?>" class="nav-tab">Today's Schedule</a>
        <a href="<?php echo admin_url('admin.php?page=rrs-all-reservations'); ?>" class="nav-tab">All Reservations</a>
    </div>
    
    <!-- Enhanced Statistics Grid -->
    <div class="rrs-stats-grid">
        <div class="rrs-stat-card primary">
            <div class="rrs-stat-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="rrs-stat-content">
                <h3><?php echo $stats['today_reservations']; ?></h3>
                <p>Today's Reservations</p>
                <div class="rrs-stat-change">
                    <?php 
                    $change = $stats['today_reservations'] - $stats['yesterday_reservations'];
                    $change_class = $change >= 0 ? 'positive' : 'negative';
                    $change_icon = $change >= 0 ? 'arrow-up-alt' : 'arrow-down-alt';
                    ?>
                    <span class="change <?php echo $change_class; ?>">
                        <span class="dashicons dashicons-<?php echo $change_icon; ?>"></span>
                        <?php echo abs($change); ?> from yesterday
                    </span>
                </div>
            </div>
        </div>
        
        <div class="rrs-stat-card warning">
            <div class="rrs-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="rrs-stat-content">
                <h3><?php echo $stats['pending_reservations']; ?></h3>
                <p>Pending Approval</p>
                <?php if ($stats['pending_reservations'] > 0): ?>
                    <div class="rrs-stat-action">
                        <a href="<?php echo admin_url('admin.php?page=rrs-all-reservations&status=pending'); ?>" class="button button-small">Review Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="rrs-stat-card success">
            <div class="rrs-stat-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="rrs-stat-content">
                <h3><?php echo $stats['confirmed_reservations']; ?></h3>
                <p>Confirmed Reservations</p>
            </div>
        </div>
        
        <div class="rrs-stat-card info">
            <div class="rrs-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="rrs-stat-content">
                <h3><?php echo $stats['total_guests_today']; ?></h3>
                <p>Total Guests Today</p>
                <div class="rrs-stat-meta">
                    Avg party size: <?php echo number_format($stats['avg_party_size'], 1); ?>
                </div>
            </div>
        </div>
        
        <div class="rrs-stat-card seated">
            <div class="rrs-stat-icon">
                <span class="dashicons dashicons-businessman"></span>
            </div>
            <div class="rrs-stat-content">
                <h3><?php echo $stats['seated_reservations']; ?></h3>
                <p>Currently Seated</p>
            </div>
        </div>
        
        <div class="rrs-stat-card completed">
            <div class="rrs-stat-icon">
                <span class="dashicons dashicons-saved"></span>
            </div>
            <div class="rrs-stat-content">
                <h3><?php echo $stats['completed_today']; ?></h3>
                <p>Completed Today</p>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Reservations Alert -->
    <?php if (!empty($upcoming_reservations)): ?>
    <div class="rrs-urgent-section">
        <h3>
            <span class="dashicons dashicons-bell"></span>
            Arriving Soon (Next 2 Hours)
        </h3>
        <div class="rrs-upcoming-grid">
            <?php foreach ($upcoming_reservations as $reservation): ?>
                <div class="rrs-upcoming-card status-<?php echo $reservation->status; ?>">
                    <div class="rrs-upcoming-time">
                        <?php 
                        $time_diff = strtotime($reservation->reservation_date . ' ' . $reservation->reservation_time) - time();
                        $minutes = round($time_diff / 60);
                        ?>
                        <strong><?php echo date('g:i A', strtotime($reservation->reservation_time)); ?></strong>
                        <span class="time-remaining">
                            <?php if ($minutes <= 0): ?>
                                <span class="overdue">OVERDUE</span>
                            <?php elseif ($minutes <= 30): ?>
                                <span class="urgent">in <?php echo $minutes; ?> min</span>
                            <?php else: ?>
                                <span>in <?php echo $minutes; ?> min</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="rrs-upcoming-details">
                        <h4><?php echo esc_html($reservation->customer_name); ?></h4>
                        <p>Party of <?php echo $reservation->party_size; ?> • <?php echo esc_html($reservation->reservation_code); ?></p>
                        <p class="contact-info">
                            <?php echo esc_html($reservation->customer_phone); ?>
                        </p>
                    </div>
                    <div class="rrs-upcoming-actions">
                        <?php if ($reservation->status === 'pending'): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=confirm&reservation_id=' . $reservation->id), 'confirm_reservation'); ?>" 
                               class="button button-primary button-small">Confirm</a>
                        <?php elseif ($reservation->status === 'confirmed'): ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=seat&reservation_id=' . $reservation->id), 'seat_reservation'); ?>" 
                               class="button button-primary button-small">Seat Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Today's Full Schedule -->
    <div class="rrs-dashboard-section">
        <div class="rrs-section-header">
            <h2>Today's Complete Schedule - <?php echo date('F j, Y'); ?></h2>
            <div class="rrs-section-actions">
                <a href="<?php echo admin_url('admin.php?page=rrs-todays-schedule'); ?>" class="button">View Full Schedule</a>
                <button class="button rrs-refresh-schedule">
                    <span class="dashicons dashicons-update"></span>
                    Refresh
                </button>
            </div>
        </div>
        
        <div class="rrs-schedule-container">
            <?php if (!empty($todays_reservations)): ?>
                <div class="rrs-reservations-timeline">
                    <?php foreach ($todays_reservations as $reservation): ?>
                        <div class="rrs-timeline-item status-<?php echo $reservation->status; ?>">
                            <div class="rrs-timeline-time">
                                <strong><?php echo date('g:i A', strtotime($reservation->reservation_time)); ?></strong>
                            </div>
                            <div class="rrs-timeline-content">
                                <div class="rrs-reservation-header">
                                    <h4><?php echo esc_html($reservation->customer_name); ?></h4>
                                    <span class="rrs-status-badge status-<?php echo $reservation->status; ?>">
                                        <?php echo ucfirst($reservation->status); ?>
                                    </span>
                                </div>
                                <div class="rrs-reservation-details">
                                    <span class="party-size">
                                        <span class="dashicons dashicons-groups"></span>
                                        <?php echo $reservation->party_size; ?> guests
                                    </span>
                                    <span class="contact">
                                        <span class="dashicons dashicons-phone"></span>
                                        <?php echo esc_html($reservation->customer_phone); ?>
                                    </span>
                                    <span class="code">
                                        <span class="dashicons dashicons-tag"></span>
                                        <?php echo esc_html($reservation->reservation_code); ?>
                                    </span>
                                </div>
                                <?php if ($reservation->special_requests): ?>
                                    <div class="rrs-special-requests">
                                        <span class="dashicons dashicons-info"></span>
                                        <?php echo esc_html($reservation->special_requests); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="rrs-timeline-actions">
                                <div class="rrs-action-buttons">
                                    <?php if ($reservation->status === 'pending'): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=confirm&reservation_id=' . $reservation->id), 'confirm_reservation'); ?>" 
                                           class="button button-primary button-small">
                                            <span class="dashicons dashicons-yes"></span> Confirm
                                        </a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=cancel&reservation_id=' . $reservation->id), 'cancel_reservation'); ?>" 
                                           class="button button-secondary button-small">
                                            <span class="dashicons dashicons-no"></span> Cancel
                                        </a>
                                    <?php elseif ($reservation->status === 'confirmed'): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=seat&reservation_id=' . $reservation->id), 'seat_reservation'); ?>" 
                                           class="button button-primary button-small">
                                            <span class="dashicons dashicons-businessman"></span> Seat
                                        </a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=cancel&reservation_id=' . $reservation->id), 'cancel_reservation'); ?>" 
                                           class="button button-secondary button-small">
                                            <span class="dashicons dashicons-no"></span> Cancel
                                        </a>
                                    <?php elseif ($reservation->status === 'seated'): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=restaurant-reservations&rrs_action=complete&reservation_id=' . $reservation->id), 'complete_reservation'); ?>" 
                                           class="button button-primary button-small">
                                            <span class="dashicons dashicons-saved"></span> Complete
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button class="button button-small rrs-view-details" data-reservation-id="<?php echo $reservation->id; ?>">
                                        <span class="dashicons dashicons-visibility"></span> View
                                    </button>
                                    <button class="button button-small rrs-edit-reservation" data-reservation-id="<?php echo $reservation->id; ?>">
                                        <span class="dashicons dashicons-edit"></span> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="rrs-empty-schedule">
                    <div class="rrs-empty-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <h3>No Reservations Today</h3>
                    <p>New reservations will appear here automatically</p>
                    <button class="button button-primary rrs-create-reservation-btn">
                        <span class="dashicons dashicons-plus-alt"></span>
                        Create First Reservation
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Stats and Actions Panel -->
    <div class="rrs-quick-panel">
        <div class="rrs-quick-stats">
            <h3>Quick Actions</h3>
            <div class="rrs-action-grid">
                <a href="#" class="rrs-quick-action rrs-create-reservation-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span>New Reservation</span>
                </a>
                <a href="<?php echo admin_url('admin.php?page=rrs-all-reservations&status=pending'); ?>" class="rrs-quick-action">
                    <span class="dashicons dashicons-clock"></span>
                    <span>Review Pending</span>
                    <?php if ($stats['pending_reservations'] > 0): ?>
                        <span class="rrs-badge"><?php echo $stats['pending_reservations']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=rrs-todays-schedule'); ?>" class="rrs-quick-action">
                    <span class="dashicons dashicons-calendar"></span>
                    <span>Today's Schedule</span>
                </a>
                <a href="<?php echo admin_url('admin.php?page=rrs-all-reservations'); ?>" class="rrs-quick-action">
                    <span class="dashicons dashicons-list-view"></span>
                    <span>All Reservations</span>
                </a>
            </div>
        </div>
        
        <div class="rrs-recent-activity">
            <h3>Recent Activity</h3>
            <?php if (!empty($recent_reservations)): ?>
                <div class="rrs-activity-list">
                    <?php foreach (array_slice($recent_reservations, 0, 5) as $reservation): ?>
                        <div class="rrs-activity-item">
                            <div class="rrs-activity-icon status-<?php echo $reservation->status; ?>">
                                <span class="dashicons dashicons-calendar-alt"></span>
                            </div>
                            <div class="rrs-activity-content">
                                <p><strong><?php echo esc_html($reservation->customer_name); ?></strong> 
                                   made a reservation for <?php echo date('M j', strtotime($reservation->reservation_date)); ?></p>
                                <small><?php echo human_time_diff(strtotime($reservation->created_at)); ?> ago</small>
                            </div>
                            <div class="rrs-activity-status">
                                <span class="rrs-status-badge status-<?php echo $reservation->status; ?>">
                                    <?php echo ucfirst($reservation->status); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No recent activity</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reservation Management Modal -->
<div id="rrs-reservation-modal" style="display: none;">
    <div class="rrs-modal-content">
        <div class="rrs-modal-header">
            <h3 id="rrs-modal-title">Reservation Details</h3>
            <button class="rrs-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="rrs-modal-body">
            <!-- Modal content will be loaded here -->
        </div>
    </div>
</div>

<!-- New Reservation Modal -->
<div id="rrs-new-reservation-modal" style="display: none;">
    <div class="rrs-modal-content">
        <div class="rrs-modal-header">
            <h3>Create New Reservation</h3>
            <button class="rrs-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="rrs-modal-body">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="rrs-reservation-form">
                <input type="hidden" name="action" value="create_reservation">
                <?php wp_nonce_field('create_reservation', 'reservation_nonce'); ?>
                
                <div class="rrs-form-row">
                    <div class="rrs-form-group">
                        <label for="customer_name">Customer Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="rrs-form-group">
                        <label for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                </div>
                
                <div class="rrs-form-row">
                    <div class="rrs-form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required>
                    </div>
                    <div class="rrs-form-group">
                        <label for="party_size">Party Size *</label>
                        <select id="party_size" name="party_size" required>
                            <?php for($i = 1; $i <= 15; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="rrs-form-row">
                    <div class="rrs-form-group">
                        <label for="reservation_date">Date *</label>
                        <input type="date" id="reservation_date" name="reservation_date" required 
                               min="<?php echo date('Y-m-d'); ?>" 
                               max="<?php echo date('Y-m-d', strtotime('+90 days')); ?>"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="rrs-form-group">
                        <label for="reservation_time">Time *</label>
                        <input type="time" id="reservation_time" name="reservation_time" required value="19:00">
                    </div>
                </div>
                
                <div class="rrs-form-group">
                    <label for="special_requests">Special Requests</label>
                    <textarea id="special_requests" name="special_requests" rows="3" 
                              placeholder="Any special requirements, dietary restrictions, or notes..."></textarea>
                </div>
                
                <div class="rrs-form-row">
                    <div class="rrs-form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="seated">Seated</option>
                        </select>
                    </div>
                </div>
                
                <div class="rrs-form-actions">
                    <button type="button" class="button rrs-modal-close">Cancel</button>
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Enhanced Admin Styles for Version 1.1 */
.rrs-admin-wrap {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.rrs-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.rrs-version {
    background: #0073aa;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.rrs-dashboard-nav {
    margin-bottom: 20px;
}

.rrs-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.rrs-stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #0073aa;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.rrs-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.rrs-stat-card.primary { border-left-color: #0073aa; }
.rrs-stat-card.warning { border-left-color: #f57c00; }
.rrs-stat-card.success { border-left-color: #2e7d32; }
.rrs-stat-card.info { border-left-color: #1976d2; }
.rrs-stat-card.seated { border-left-color: #7b1fa2; }
.rrs-stat-card.completed { border-left-color: #388e3c; }

.rrs-stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(0,115,170,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.rrs-stat-icon .dashicons {
    font-size: 28px;
    color: #0073aa;
}

.rrs-stat-content h3 {
    font-size: 36px;
    font-weight: bold;
    margin: 0 0 8px 0;
    color: #1d2327;
}

.rrs-stat-content p {
    margin: 0;
    color: #646970;
    font-size: 14px;
    font-weight: 500;
}

.rrs-stat-change {
    margin-top: 8px;
}

.rrs-stat-change .change {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 500;
}

.rrs-stat-change .positive { color: #2e7d32; }
.rrs-stat-change .negative { color: #d32f2f; }

.rrs-urgent-section {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border: 2px solid #f57c00;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.rrs-urgent-section h3 {
    color: #e65100;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 20px 0;
    font-size: 18px;
}

.rrs-upcoming-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 16px;
}

.rrs-upcoming-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #2196f3;
}

.rrs-upcoming-card.status-pending { border-left-color: #ff9800; }
.rrs-upcoming-card.status-confirmed { border-left-color: #4caf50; }

.rrs-upcoming-time {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.time-remaining .urgent { color: #f44336; font-weight: bold; }
.time-remaining .overdue { color: #d32f2f; font-weight: bold; background: #ffebee; padding: 2px 6px; border-radius: 4px; }

.rrs-reservations-timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.rrs-timeline-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 4px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.2s;
}

.rrs-timeline-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.rrs-timeline-item.status-pending { border-left-color: #ff9800; }
.rrs-timeline-item.status-confirmed { border-left-color: #4caf50; }
.rrs-timeline-item.status-seated { border-left-color: #9c27b0; }
.rrs-timeline-item.status-completed { border-left-color: #2e7d32; }
.rrs-timeline-item.status-cancelled { border-left-color: #f44336; }

.rrs-timeline-time {
    flex-shrink: 0;
    width: 80px;
    text-align: center;
}

.rrs-timeline-content {
    flex: 1;
}

.rrs-reservation-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.rrs-reservation-header h4 {
    margin: 0;
    font-size: 16px;
    color: #1d2327;
}

.rrs-status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    color: white;
}

.rrs-status-badge.status-pending { background: #ff9800; }
.rrs-status-badge.status-confirmed { background: #4caf50; }
.rrs-status-badge.status-seated { background: #9c27b0; }
.rrs-status-badge.status-completed { background: #2e7d32; }
.rrs-status-badge.status-cancelled { background: #f44336; }

.rrs-reservation-details {
    display: flex;
    gap: 20px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.rrs-reservation-details span {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #646970;
}

.rrs-special-requests {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    color: #495057;
    margin-top: 8px;
}

.rrs-timeline-actions {
    flex-shrink: 0;
}

.rrs-action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.rrs-action-buttons .button {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    padding: 6px 12px;
    height: auto;
    line-height: 1.4;
}

.rrs-empty-schedule {
    text-align: center;
    padding: 60px 20px;
    color: #646970;
}

.rrs-empty-icon .dashicons {
    font-size: 64px;
    color: #c3c4c7;
    margin-bottom: 16px;
}

.rrs-quick-panel {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.rrs-quick-stats,
.rrs-recent-activity {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.rrs-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
}

.rrs-quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-decoration: none;
    color: #1d2327;
    transition: all 0.2s;
    position: relative;
}

.rrs-quick-action:hover {
    border-color: #0073aa;
    color: #0073aa;
    text-decoration: none;
}

.rrs-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #d63638;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.rrs-activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rrs-activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.rrs-activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.rrs-activity-icon.status-pending { background: #fff3e0; color: #f57c00; }
.rrs-activity-icon.status-confirmed { background: #e8f5e8; color: #2e7d32; }
.rrs-activity-icon.status-seated { background: #f3e5f5; color: #7b1fa2; }

.rrs-activity-content {
    flex: 1;
}

.rrs-activity-content p {
    margin: 0 0 4px 0;
    font-size: 14px;
}

.rrs-activity-content small {
    color: #646970;
    font-size: 12px;
}

/* Modal Styles */
#rrs-reservation-modal,
#rrs-new-reservation-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rrs-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.rrs-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0 24px;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 24px;
}

.rrs-modal-header h3 {
    margin: 0;
    font-size: 20px;
    color: #1d2327;
}

.rrs-modal-close {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.2s;
}

.rrs-modal-close:hover {
    background: #f0f0f0;
}

.rrs-modal-body {
    padding: 0 24px 24px 24px;
}

.rrs-reservation-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.rrs-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.rrs-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.rrs-form-group label {
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.rrs-form-group input,
.rrs-form-group select,
.rrs-form-group textarea {
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.rrs-form-group input:focus,
.rrs-form-group select:focus,
.rrs-form-group textarea:focus {
    outline: none;
    border-color: #0073aa;
}

.rrs-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .rrs-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .rrs-form-row {
        grid-template-columns: 1fr;
    }
    
    .rrs-quick-panel {
        grid-template-columns: 1fr;
    }
    
    .rrs-upcoming-grid {
        grid-template-columns: 1fr;
    }
    
    .rrs-timeline-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .rrs-reservation-details {
        flex-direction: column;
        gap: 8px;
    }
    
    .rrs-action-buttons {
        justify-content: flex-start;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Modal functionality
    $('.rrs-create-reservation-btn').on('click', function(e) {
        e.preventDefault();
        $('#rrs-new-reservation-modal').fadeIn(300);
    });
    
    $('.rrs-modal-close').on('click', function() {
        $('#rrs-reservation-modal, #rrs-new-reservation-modal').fadeOut(300);
    });
    
    // Close modal when clicking outside
    $('#rrs-reservation-modal, #rrs-new-reservation-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).fadeOut(300);
        }
    });
    
    // Refresh schedule
    $('.rrs-refresh-schedule').on('click', function() {
        location.reload();
    });
    
    // Status update confirmation
    $('a[href*="rrs_action="]').on('click', function(e) {
        var action = $(this).attr('href').match(/rrs_action=(\w+)/);
        if (action && ['cancel', 'delete'].includes(action[1])) {
            if (!confirm('Are you sure you want to ' + action[1] + ' this reservation?')) {
                e.preventDefault();
            }
        }
    });
    
    // Auto-refresh dashboard every 2 minutes
    if (window.location.search.indexOf('page=restaurant-reservations') > -1) {
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                $('.rrs-refresh-schedule').trigger('click');
            }
        }, 120000); // 2 minutes
    }
});
</script>

<?php
/**
 * Admin Dashboard View
 * 
 * Main reservation management interface for administrators
 * 
 * @package RestaurantReservations
 * @subpackage Views/Admin
 * @version 1.4.0
 * @since 1.0.0
 * @author Your Name
 * 
 * @var array $statistics Dashboard statistics data
 * @var array $today_reservations Today's reservation list
 * @var string $restaurant_status Open/closed status
 * @var string $restaurant_name Restaurant name from settings
 * 
 * Features:
 * - Real-time statistics cards
 * - Today's reservation management
 * - Quick action buttons (confirm, cancel, delete)
 * - Edit reservation modal
 * - Auto-refresh functionality
 */

if (!defined('ABSPATH')) exit;
?>

<div class="rrs-fullscreen">
    <div class="rrs-container rrs-fade-in">
        <div class="rrs-header">
            <h1>🍽️ Restaurant Reservations v<?php echo RRS_VERSION; ?></h1>
            <p>Professional Restaurant Management System - MVC Architecture</p>
        </div>
        
        <div class="rrs-announcement <?php echo $restaurant_status ? 'rrs-open' : 'rrs-closed'; ?>">
            <?php if ($restaurant_status): ?>
                🟢 Restaurant is OPEN for reservations
            <?php else: ?>
                🔴 Restaurant is CLOSED for reservations
            <?php endif; ?>
        </div>
        
        <div class="rrs-stats">
            <div class="rrs-card rrs-slide-up" style="animation-delay: 0.1s;">
                <h2><?php echo $statistics['today']; ?></h2>
                <p>Today's Reservations</p>
            </div>
            <div class="rrs-card rrs-slide-up" style="animation-delay: 0.2s;">
                <h2><?php echo $statistics['pending']; ?></h2>
                <p>Pending Approval</p>
            </div>
            <div class="rrs-card rrs-slide-up" style="animation-delay: 0.3s;">
                <h2><?php echo $statistics['confirmed_today']; ?></h2>
                <p>Confirmed Today</p>
            </div>
            <div class="rrs-card rrs-slide-up" style="animation-delay: 0.4s;">
                <h2><?php echo $statistics['week']; ?></h2>
                <p>This Week</p>
            </div>
            <div class="rrs-card rrs-slide-up" style="animation-delay: 0.5s;">
                <h2><?php echo $statistics['total']; ?></h2>
                <p>Total Reservations</p>
            </div>
        </div>
        
        <div class="rrs-nav-buttons">
            <a href="<?php echo admin_url('admin.php?page=weekly-view'); ?>" class="rrs-nav-btn">📅 Weekly View</a>
            <a href="<?php echo admin_url('admin.php?page=res-settings'); ?>" class="rrs-nav-btn">⚙️ Settings</a>
            <button onclick="location.reload()" class="rrs-nav-btn">🔄 Refresh</button>
        </div>
        
        <div class="rrs-reservations-table">
            <div class="rrs-table-header">
                <h2>📅 Today's Schedule - <?php echo date('F j, Y'); ?></h2>
            </div>
            
            <div style="padding: 25px;">
                <?php if (!empty($today_reservations)): ?>
                    <?php foreach ($today_reservations as $index => $reservation): ?>
                        <div class="rrs-reservation-item <?php echo $reservation->status; ?> rrs-fade-in" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                            <div class="rrs-reservation-content">
                                <div class="rrs-reservation-info">
                                    <h3>
                                        🕐 <?php echo date('g:i A', strtotime($reservation->reservation_time)); ?> - 
                                        👤 <?php echo esc_html($reservation->customer_name); ?>
                                    </h3>
                                    <p>
                                        📧 <?php echo esc_html($reservation->customer_email); ?> • 
                                        📞 <?php echo esc_html($reservation->customer_phone); ?> • 
                                        👥 <?php echo $reservation->party_size; ?> guests
                                        <?php if ($reservation->table_number): ?>
                                            • 🪑 Table <?php echo esc_html($reservation->table_number); ?>
                                        <?php endif; ?>
                                    </p>
                                    <p style="font-size: 0.9rem; color: #6c757d;">
                                        🏷️ <?php echo esc_html($reservation->reservation_code); ?>
                                    </p>
                                    <?php if ($reservation->special_requests): ?>
                                        <div class="special-request">
                                            💬 <?php echo esc_html($reservation->special_requests); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="rrs-actions">
                                    <span class="rrs-status-badge <?php echo $reservation->status; ?>">
                                        <?php echo strtoupper($reservation->status); ?>
                                    </span>
                                    
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px;">
                                        <?php if ($reservation->status === 'pending'): ?>
                                            <a href="?page=reservations&action=confirm&id=<?php echo $reservation->id; ?>" class="rrs-action-btn confirm">✅ Confirm</a>
                                        <?php endif; ?>
                                        
                                        <button onclick="editReservation(<?php echo htmlspecialchars(json_encode($reservation)); ?>)" class="rrs-action-btn edit">✏️ Edit</button>
                                        
                                        <a href="?page=reservations&action=cancel&id=<?php echo $reservation->id; ?>" onclick="return confirm('Cancel this reservation?')" class="rrs-action-btn cancel">❌ Cancel</a>
                                        
                                        <a href="?page=reservations&action=delete&id=<?php echo $reservation->id; ?>" onclick="return confirm('Delete this reservation?')" class="rrs-action-btn delete">🗑️ Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rrs-empty-state">
                        <div class="rrs-empty-icon">📅</div>
                        <h3>No Reservations Today</h3>
                        <p>New reservations will appear here automatically</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="rrs-modal" style="display: none;">
    <div class="rrs-modal-content">
        <div class="rrs-modal-header">
            <h3>✏️ Edit Reservation</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d; float: right;">×</button>
        </div>
        
        <form method="post">
            <?php wp_nonce_field('edit_reservation', 'edit_nonce'); ?>
            <input type="hidden" id="edit_id" name="reservation_id">
            <input type="hidden" name="edit_reservation" value="1">
            
            <div class="rrs-form-row">
                <div class="rrs-form-group">
                    <label for="edit_name">Customer Name *</label>
                    <input type="text" id="edit_name" name="customer_name" required>
                </div>
                <div class="rrs-form-group">
                    <label for="edit_email">Email Address *</label>
                    <input type="email" id="edit_email" name="customer_email" required>
                </div>
            </div>
            
            <div class="rrs-form-row">
                <div class="rrs-form-group">
                    <label for="edit_phone">Phone Number *</label>
                    <input type="tel" id="edit_phone" name="customer_phone" required>
                </div>
                <div class="rrs-form-group">
                    <label for="edit_party">Party Size *</label>
                    <select id="edit_party" name="party_size" required>
                        <?php for($i = 1; $i <= 15; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="rrs-form-row">
                <div class="rrs-form-group">
                    <label for="edit_date">Reservation Date *</label>
                    <input type="date" id="edit_date" name="reservation_date" required>
                </div>
                <div class="rrs-form-group">
                    <label for="edit_time">Reservation Time *</label>
                    <input type="time" id="edit_time" name="reservation_time" required>
                </div>
            </div>
            
            <div class="rrs-form-row">
                <div class="rrs-form-group">
                    <label for="edit_table">Table Number</label>
                    <input type="text" id="edit_table" name="table_number" placeholder="e.g., T1, Table 5">
                </div>
            </div>
            
            <div class="rrs-form-group">
                <label for="edit_requests">Special Requests</label>
                <textarea id="edit_requests" name="special_requests" rows="3" placeholder="Any special requirements..."></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 25px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">💾 Update Reservation</button>
            </div>
        </form>
    </div>
</div>

<script>
function editReservation(res) {
    document.getElementById('edit_id').value = res.id;
    document.getElementById('edit_name').value = res.customer_name;
    document.getElementById('edit_email').value = res.customer_email;
    document.getElementById('edit_phone').value = res.customer_phone;
    document.getElementById('edit_party').value = res.party_size;
    document.getElementById('edit_date').value = res.reservation_date;
    document.getElementById('edit_time').value = res.reservation_time;
    document.getElementById('edit_table').value = res.table_number || '';
    document.getElementById('edit_requests').value = res.special_requests || '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

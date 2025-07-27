<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        🍽️ Restaurant Reservations Dashboard 
        <span style="background: #0073aa; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">v1.1</span>
    </h1>
    <a href="#" class="page-title-action" onclick="openReservationModal()">
        ➕ Add New Reservation
    </a>
    
    <!-- Enhanced Statistics - Simple Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
        <!-- Today's Reservations -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px;">📅</div>
                <div>
                    <h2 style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo $stats['today_reservations']; ?></h2>
                    <p style="margin: 0; opacity: 0.9;">Today's Reservations</p>
                </div>
            </div>
        </div>
        
        <!-- Pending Approval -->
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px;">⏳</div>
                <div>
                    <h2 style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo $stats['pending_reservations']; ?></h2>
                    <p style="margin: 0; opacity: 0.9;">Pending Approval</p>
                    <?php if ($stats['pending_reservations'] > 0): ?>
                        <a href="<?php echo admin_url('admin.php?page=rrs-all-reservations'); ?>" style="color: white; text-decoration: underline; font-size: 12px;">Review Now →</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Total Reservations -->
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 48px;">📊</div>
                <div>
                    <h2 style="margin: 0; font-size: 32px; font-weight: bold;"><?php echo $stats['total_reservations']; ?></h2>
                    <p style="margin: 0; opacity: 0.9;">Total Reservations</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Action Buttons -->
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h2 style="margin: 0 0 20px 0;">🚀 Quick Actions</h2>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <button onclick="openReservationModal()" style="background: #00a32a; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" onmouseover="this.style.background='#008a2e'" onmouseout="this.style.background='#00a32a'">
                ➕ Create Reservation
            </button>
            <a href="<?php echo admin_url('admin.php?page=rrs-all-reservations'); ?>" style="background: #0073aa; color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" onmouseover="this.style.background='#005a87'" onmouseout="this.style.background='#0073aa'">
                📋 View All Reservations
            </a>
            <button onclick="location.reload()" style="background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                🔄 Refresh Dashboard
            </button>
        </div>
    </div>
    
    <!-- Today's Schedule with Enhanced Actions -->
    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                📅 Today's Schedule - <?php echo date('F j, Y'); ?>
            </h2>
            <div style="display: flex; gap: 10px;">
                <span style="background: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                    <?php echo count($todays_reservations); ?> reservations today
                </span>
            </div>
        </div>
        
        <?php if (!empty($todays_reservations)): ?>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($todays_reservations as $reservation): ?>
                    <div style="background: <?php echo $reservation->status === 'confirmed' ? '#e8f5e8' : ($reservation->status === 'pending' ? '#fff3cd' : '#f8f9fa'); ?>; border: 2px solid <?php echo $reservation->status === 'confirmed' ? '#4caf50' : ($reservation->status === 'pending' ? '#ffc107' : '#dee2e6'); ?>; border-radius: 12px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                            <!-- Time and Status -->
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="background: <?php echo $reservation->status === 'confirmed' ? '#4caf50' : ($reservation->status === 'pending' ? '#ffc107' : '#6c757d'); ?>; color: white; padding: 12px; border-radius: 50%; font-weight: bold; min-width: 60px; text-align: center;">
                                    <?php echo date('g:i', strtotime($reservation->reservation_time)); ?>
                                </div>
                                <div>
                                    <h3 style="margin: 0; font-size: 18px; color: #1d2327;">
                                        👤 <?php echo esc_html($reservation->customer_name); ?>
                                    </h3>
                                    <div style="display: flex; gap: 20px; margin-top: 8px; color: #666;">
                                        <span>👥 <?php echo $reservation->party_size; ?> guests</span>
                                        <span>📞 <?php echo esc_html($reservation->customer_phone); ?></span>
                                        <span>🏷️ <?php echo esc_html($reservation->reservation_code); ?></span>
                                    </div>
                                    <?php if ($reservation->special_requests): ?>
                                        <div style="background: rgba(0,0,0,0.05); padding: 8px 12px; border-radius: 6px; margin-top: 8px; font-size: 14px; color: #495057;">
                                            💬 <?php echo esc_html($reservation->special_requests); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Enhanced Action Buttons -->
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <?php if ($reservation->status === 'pending'): ?>
                                    <a href="<?php echo admin_url('admin.php?page=restaurant-reservations&rrs_action=confirm&reservation_id=' . $reservation->id); ?>" 
                                       style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: bold; display: flex; align-items: center; gap: 4px; transition: all 0.3s;" 
                                       onmouseover="this.style.background='#218838'" 
                                       onmouseout="this.style.background='#28a745'">
                                        ✅ Confirm
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=restaurant-reservations&rrs_action=cancel&reservation_id=' . $reservation->id); ?>" 
                                       style="background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: bold; display: flex; align-items: center; gap: 4px; transition: all 0.3s;" 
                                       onmouseover="this.style.background='#c82333'" 
                                       onmouseout="this.style.background='#dc3545'"
                                       onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                        ❌ Cancel
                                    </a>
                                <?php elseif ($reservation->status === 'confirmed'): ?>
                                    <button style="background: #17a2b8; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.3s;" 
                                            onmouseover="this.style.background='#138496'" 
                                            onmouseout="this.style.background='#17a2b8'">
                                        🪑 Seat Customer
                                    </button>
                                    <a href="<?php echo admin_url('admin.php?page=restaurant-reservations&rrs_action=cancel&reservation_id=' . $reservation->id); ?>" 
                                       style="background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: bold; display: flex; align-items: center; gap: 4px; transition: all 0.3s;" 
                                       onmouseover="this.style.background='#5a6268'" 
                                       onmouseout="this.style.background='#6c757d'"
                                       onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                        ❌ Cancel
                                    </a>
                                <?php endif; ?>
                                
                                <button onclick="viewReservation(<?php echo $reservation->id; ?>)" 
                                        style="background: #6f42c1; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.3s;" 
                                        onmouseover="this.style.background='#5a32a3'" 
                                        onmouseout="this.style.background='#6f42c1'">
                                    👁️ View
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; border: 2px dashed #dee2e6;">
                <div style="font-size: 64px; margin-bottom: 20px;">📅</div>
                <h3 style="color: #6c757d; margin: 0 0 10px 0;">No Reservations Today</h3>
                <p style="color: #6c757d; margin: 0 0 20px 0; font-size: 16px;">New reservations will appear here automatically</p>
                <button onclick="openReservationModal()" style="background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#0056b3'" onmouseout="this.style.background='#007bff'">
                    ➕ Create First Reservation
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced Modal -->
<div id="reservation-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e9ecef;">
            <h2 style="margin: 0; color: #1d2327; display: flex; align-items: center; gap: 10px;">
                ➕ Create New Reservation
            </h2>
            <button onclick="closeReservationModal()" style="background: #f8f9fa; border: none; padding: 8px; border-radius: 50%; cursor: pointer; font-size: 16px; color: #6c757d; transition: all 0.3s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                ✕
            </button>
        </div>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="create_reservation">
            <?php wp_nonce_field('create_reservation', 'reservation_nonce'); ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Customer Name *</label>
                    <input type="text" name="customer_name" required style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Email *</label>
                    <input type="email" name="customer_email" required style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Phone *</label>
                    <input type="tel" name="customer_phone" required style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Party Size *</label>
                    <select name="party_size" required style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                        <?php for($i = 1; $i <= 15; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Date *</label>
                    <input type="date" name="reservation_date" required value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+90 days')); ?>" style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Time *</label>
                    <input type="time" name="reservation_time" required value="19:00" style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Special Requests</label>
                <textarea name="special_requests" rows="3" style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px; resize: vertical;" placeholder="Any special requirements, dietary restrictions, celebrations..."></textarea>
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold; color: #495057;">Status</label>
                <select name="status" style="width: 100%; padding: 12px; border: 2px solid #ced4da; border-radius: 8px; font-size: 14px;">
                    <option value="confirmed">✅ Confirmed</option>
                    <option value="pending">⏳ Pending</option>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeReservationModal()" style="background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                    Cancel
                </button>
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">
                    ✅ Create Reservation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReservationModal() {
    document.getElementById('reservation-modal').style.display = 'flex';
}

function closeReservationModal() {
    document.getElementById('reservation-modal').style.display = 'none';
}

function viewReservation(id) {
    alert('Viewing reservation #' + id + '\n\nDetailed view will be available in the next update!');
}

// Close modal when clicking outside
document.getElementById('reservation-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReservationModal();
    }
});

// Auto-refresh every 5 minutes
setInterval(function() {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000);
</script>

<?php
if (!defined('ABSPATH')) exit;

$current_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');

// Get tables and bookings
global $wpdb;
$tables = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}yrr_tables ORDER BY table_number");
$bookings = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}yrr_reservations WHERE reservation_date = %s AND status IN ('confirmed', 'pending') ORDER BY reservation_time",
    $current_date
));

// Organize bookings by table
$table_bookings = array();
foreach ($bookings as $booking) {
    if (!isset($table_bookings[$booking->table_id])) {
        $table_bookings[$booking->table_id] = array();
    }
    $table_bookings[$booking->table_id][] = $booking;
}

// Generate time slots (10 AM to 10 PM, 30-minute intervals)
$time_slots = array();
for ($hour = 10; $hour <= 22; $hour++) {
    for ($minute = 0; $minute < 60; $minute += 30) {
        if ($hour == 22 && $minute > 0) break;
        $time_slots[] = sprintf('%02d:%02d', $hour, $minute);
    }
}
?>

<div class="wrap">
    <div style="max-width: 1800px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #007cba;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">📅 Table Schedule & Time Slots</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Visual table booking schedule for <?php echo date('F j, Y', strtotime($current_date)); ?></p>
        </div>
        
        <!-- Date Navigation -->
        <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                <?php 
                $prev_date = date('Y-m-d', strtotime($current_date . ' -1 day'));
                $next_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                ?>
                
                <a href="?page=yrr-table-schedule&date=<?php echo $prev_date; ?>" 
                   style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    ← Previous Day
                </a>
                
                <input type="date" value="<?php echo $current_date; ?>" 
                       onchange="window.location.href='?page=yrr-table-schedule&date='+this.value"
                       style="padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem;">
                
                <a href="?page=yrr-table-schedule&date=<?php echo $next_date; ?>" 
                   style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    Next Day →
                </a>
                
                <a href="?page=yrr-table-schedule" 
                   style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    Today
                </a>
            </div>
        </div>
        
        <!-- Legend -->
        <div style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 10px; text-align: center;">
            <h4 style="margin: 0 0 10px 0;">📋 Booking Status Legend</h4>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <span style="background: #28a745; color: white; padding: 5px 15px; border-radius: 15px; font-weight: bold;">✅ Confirmed</span>
                <span style="background: #ffc107; color: black; padding: 5px 15px; border-radius: 15px; font-weight: bold;">⏳ Pending</span>
                <span style="background: #dc3545; color: white; padding: 5px 15px; border-radius: 15px; font-weight: bold;">❌ Cancelled</span>
                <span style="background: #f8f9fa; color: #333; padding: 5px 15px; border-radius: 15px; font-weight: bold; border: 2px solid #dee2e6;">🆓 Available</span>
            </div>
        </div>
        
        <!-- Time Slots Grid -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 1200px;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th style="padding: 15px; text-align: left; font-weight: bold; min-width: 120px;">Table</th>
                        <?php foreach ($time_slots as $slot): ?>
                            <th style="padding: 10px 5px; text-align: center; font-weight: bold; min-width: 80px; font-size: 0.9rem;">
                                <?php echo date('g:i A', strtotime($slot)); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $table): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <!-- Table Info -->
                            <td style="padding: 15px; background: #f8f9fa; font-weight: bold; border-right: 2px solid #dee2e6;">
                                <div style="font-size: 1.2rem; color: #2c3e50; margin-bottom: 5px;">
                                    🍽️ <?php echo esc_html($table->table_number); ?>
                                </div>
                                <div style="font-size: 0.9rem; color: #6c757d;">
                                    👥 <?php echo intval($table->capacity); ?> seats
                                </div>
                                <div style="font-size: 0.8rem; color: #6c757d; margin-top: 3px;">
                                    📍 <?php echo esc_html($table->location); ?>
                                </div>
                            </td>
                            
                            <!-- Time Slot Cells -->
                            <?php foreach ($time_slots as $slot): ?>
                                <?php 
                                $has_booking = false;
                                $booking = null;
                                
                                if (isset($table_bookings[$table->id])) {
                                    foreach ($table_bookings[$table->id] as $b) {
                                        $booking_time = date('H:i', strtotime($b->reservation_time));
                                        if ($booking_time === $slot) {
                                            $has_booking = true;
                                            $booking = $b;
                                            break;
                                        }
                                    }
                                }
                                
                                $color = $has_booking ? 
                                    ($booking->status === 'confirmed' ? '#28a745' : '#ffc107') : 
                                    '#f8f9fa';
                                ?>
                                
                                <td style="padding: 5px; text-align: center; height: 60px; position: relative;">
                                    <?php if ($has_booking): ?>
                                        <!-- Booked Slot -->
                                        <div onclick="showBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)"
                                             style="background: <?php echo $color; ?>; color: white; padding: 8px 4px; border-radius: 8px; cursor: pointer; font-size: 0.75rem; font-weight: bold; height: 100%; display: flex; flex-direction: column; justify-content: center;">
                                            <div style="margin-bottom: 2px;"><?php echo esc_html(substr($booking->customer_name, 0, 10)); ?></div>
                                            <div style="font-size: 0.7rem; opacity: 0.9;">👥 <?php echo intval($booking->party_size); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Available Slot -->
                                        <div onclick="quickBook('<?php echo $table->id; ?>', '<?php echo $current_date; ?>', '<?php echo $slot; ?>')"
                                             style="background: #f8f9fa; border: 2px dashed #dee2e6; padding: 8px 4px; border-radius: 8px; cursor: pointer; font-size: 0.7rem; color: #6c757d; height: 100%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <span>🆓<br>Available</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showBookingDetails(booking) {
    alert(`Customer: ${booking.customer_name}\nParty Size: ${booking.party_size} guests\nTime: ${booking.reservation_time}\nStatus: ${booking.status}\nPhone: ${booking.customer_phone}\nEmail: ${booking.customer_email}${booking.special_requests ? '\nSpecial Requests: ' + booking.special_requests : ''}`);
}

function quickBook(tableId, date, time) {
    if (confirm(`Create a new booking for Table ${tableId} at ${time} on ${date}?`)) {
        const formattedTime = time + ':00';
        window.location.href = `admin.php?page=yenolx-reservations&quick_book=1&table_id=${tableId}&date=${date}&time=${formattedTime}`;
    }
}

// Hover effects for available slots
document.addEventListener('DOMContentLoaded', function() {
    const availableSlots = document.querySelectorAll('div[onclick^="quickBook"]');
    availableSlots.forEach(slot => {
        slot.addEventListener('mouseenter', function() {
            this.style.background = '#e3f2fd';
            this.style.borderColor = '#007cba';
        });
        slot.addEventListener('mouseleave', function() {
            this.style.background = '#f8f9fa';
            this.style.borderColor = '#dee2e6';
        });
    });
});
</script>

<style>
@media (max-width: 1200px) {
    table {
        font-size: 0.8rem;
    }
    
    th, td {
        padding: 5px !important;
        min-width: 60px !important;
    }
}

@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

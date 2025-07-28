<?php
if (!defined('ABSPATH')) exit;

// Get current settings
$settings = isset($settings) ? $settings : array();
$time_slot_duration = isset($settings['time_slot_duration']) ? $settings['time_slot_duration'] : 60; // Default 1 hour
$restaurant_open_time = isset($settings['restaurant_open_time']) ? $settings['restaurant_open_time'] : '10:00';
$restaurant_close_time = isset($settings['restaurant_close_time']) ? $settings['restaurant_close_time'] : '22:00';
$max_advance_booking = isset($settings['max_advance_booking']) ? $settings['max_advance_booking'] : 30;
?>

<div class="wrap">
    <div style="max-width: 1200px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #007cba;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">⚙️ Restaurant Settings</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Configure time slots, operating hours, and reservation rules</p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('yrr_settings_save', 'settings_nonce'); ?>
            <input type="hidden" name="save_settings" value="1">
            
            <!-- Time Slot Management Section -->
            <div style="background: #e3f2fd; padding: 25px; border-radius: 15px; margin-bottom: 30px;">
                <h3 style="margin: 0 0 20px 0; color: #1976d2; font-size: 1.4rem;">🕐 Time Slot Configuration</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Time Slot Duration</label>
                        <select name="time_slot_duration" id="time_slot_duration" onchange="updateTimeSlotPreview()" 
                                style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <option value="15" <?php selected($time_slot_duration, 15); ?>>15 minutes</option>
                            <option value="30" <?php selected($time_slot_duration, 30); ?>>30 minutes</option>
                            <option value="45" <?php selected($time_slot_duration, 45); ?>>45 minutes</option>
                            <option value="60" <?php selected($time_slot_duration, 60); ?>>1 hour</option>
                            <option value="90" <?php selected($time_slot_duration, 90); ?>>1.5 hours</option>
                            <option value="120" <?php selected($time_slot_duration, 120); ?>>2 hours</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Restaurant Opens</label>
                        <input type="time" name="restaurant_open_time" id="restaurant_open_time" 
                               value="<?php echo esc_attr($restaurant_open_time); ?>" 
                               onchange="updateTimeSlotPreview()"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Restaurant Closes</label>
                        <input type="time" name="restaurant_close_time" id="restaurant_close_time" 
                               value="<?php echo esc_attr($restaurant_close_time); ?>" 
                               onchange="updateTimeSlotPreview()"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                </div>
                
                <!-- Live Time Slot Preview -->
                <div style="background: white; padding: 20px; border-radius: 10px; border: 2px solid #1976d2;">
                    <h4 style="margin: 0 0 15px 0; color: #1976d2;">📋 Live Preview - Available Time Slots</h4>
                    <div id="time_slots_preview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    <div id="slots_count" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: center; font-weight: bold;">
                        <!-- Will show total slots count -->
                    </div>
                </div>
            </div>
            
            <!-- Restaurant Information -->
            <div style="background: #e8f5e8; padding: 25px; border-radius: 15px; margin-bottom: 30px;">
                <h3 style="margin: 0 0 20px 0; color: #155724; font-size: 1.4rem;">🍽️ Restaurant Information</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Restaurant Name</label>
                        <input type="text" name="restaurant_name" 
                               value="<?php echo esc_attr($settings['restaurant_name'] ?? get_bloginfo('name')); ?>" 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Contact Email</label>
                        <input type="email" name="restaurant_email" 
                               value="<?php echo esc_attr($settings['restaurant_email'] ?? get_option('admin_email')); ?>" 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone Number</label>
                        <input type="tel" name="restaurant_phone" 
                               value="<?php echo esc_attr($settings['restaurant_phone'] ?? ''); ?>" 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Maximum Party Size</label>
                        <select name="max_party_size" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected($settings['max_party_size'] ?? 12, $i); ?>><?php echo $i; ?> people</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Booking Rules -->
            <div style="background: #fff3cd; padding: 25px; border-radius: 15px; margin-bottom: 30px;">
                <h3 style="margin: 0 0 20px 0; color: #856404; font-size: 1.4rem;">📋 Booking Rules</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Advance Booking Days</label>
                        <input type="number" name="max_advance_booking" min="1" max="365" 
                               value="<?php echo esc_attr($max_advance_booking); ?>" 
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        <small style="color: #6c757d;">How many days ahead customers can book</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Booking Buffer (minutes)</label>
                        <select name="booking_buffer_minutes" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <option value="0" <?php selected($settings['booking_buffer_minutes'] ?? 60, 0); ?>>No buffer</option>
                            <option value="30" <?php selected($settings['booking_buffer_minutes'] ?? 60, 30); ?>>30 minutes</option>
                            <option value="60" <?php selected($settings['booking_buffer_minutes'] ?? 60, 60); ?>>1 hour</option>
                            <option value="120" <?php selected($settings['booking_buffer_minutes'] ?? 60, 120); ?>>2 hours</option>
                        </select>
                        <small style="color: #6c757d;">Minimum time before reservation time</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Auto-Confirm Reservations</label>
                        <select name="auto_confirm_reservations" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                            <option value="0" <?php selected($settings['auto_confirm_reservations'] ?? 0, 0); ?>>Manual confirmation required</option>
                            <option value="1" <?php selected($settings['auto_confirm_reservations'] ?? 0, 1); ?>>Auto-confirm all reservations</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div style="text-align: center; padding: 20px;">
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 20px 40px; border-radius: 15px; font-size: 1.2rem; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                    💾 Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateTimeSlotPreview() {
    const duration = parseInt(document.getElementById('time_slot_duration').value);
    const openTime = document.getElementById('restaurant_open_time').value;
    const closeTime = document.getElementById('restaurant_close_time').value;
    
    if (!openTime || !closeTime) return;
    
    const slots = generateTimeSlots(openTime, closeTime, duration);
    displayTimeSlots(slots);
}

function generateTimeSlots(openTime, closeTime, durationMinutes) {
    const slots = [];
    const [openHour, openMin] = openTime.split(':').map(Number);
    const [closeHour, closeMin] = closeTime.split(':').map(Number);
    
    const openTotalMinutes = openHour * 60 + openMin;
    const closeTotalMinutes = closeHour * 60 + closeMin;
    
    for (let currentMinutes = openTotalMinutes; currentMinutes < closeTotalMinutes; currentMinutes += durationMinutes) {
        const hour = Math.floor(currentMinutes / 60);
        const minute = currentMinutes % 60;
        
        if (hour < 24) { // Don't go past midnight
            const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            const displayTime = formatTime12Hour(hour, minute);
            slots.push({
                value: timeString,
                display: displayTime
            });
        }
    }
    
    return slots;
}

function formatTime12Hour(hour, minute) {
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${ampm}`;
}

function displayTimeSlots(slots) {
    const container = document.getElementById('time_slots_preview');
    const countDiv = document.getElementById('slots_count');
    
    if (slots.length === 0) {
        container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #dc3545; padding: 20px;">❌ Invalid time range or duration</div>';
        countDiv.innerHTML = '<span style="color: #dc3545;">No valid time slots</span>';
        return;
    }
    
    let slotsHTML = '';
    slots.forEach((slot, index) => {
        const bgColor = index % 2 === 0 ? '#e3f2fd' : '#f3e5f5';
        slotsHTML += `
            <div style="background: ${bgColor}; padding: 8px; border-radius: 5px; text-align: center; font-size: 0.9rem; font-weight: bold; border: 1px solid #1976d2;">
                ${slot.display}
            </div>
        `;
    });
    
    container.innerHTML = slotsHTML;
    countDiv.innerHTML = `<span style="color: #28a745;">✅ ${slots.length} time slots available per day</span>`;
}

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTimeSlotPreview();
});
</script>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

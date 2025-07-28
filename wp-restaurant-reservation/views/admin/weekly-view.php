<?php
if (!defined('ABSPATH')) exit;

// ✅ FIX: Define missing week variables
$current_week = isset($current_week) ? $current_week : date('Y-m-d', strtotime('monday this week'));
$weekly_reservations = isset($weekly_reservations) ? $weekly_reservations : array();

// Calculate week dates properly
$week_start = date('Y-m-d', strtotime($current_week));
$week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));

// Helper function for safe property access
function yrr_get_weekly_property($object, $property, $default = '') {
    return (is_object($object) && property_exists($object, $property) && !empty($object->$property)) ? $object->$property : $default;
}

// Generate days of the week
$days_of_week = array();
for ($i = 0; $i < 7; $i++) {
    $day_date = date('Y-m-d', strtotime($week_start . ' +' . $i . ' days'));
    $days_of_week[] = array(
        'date' => $day_date,
        'day_name' => date('l', strtotime($day_date)),
        'day_short' => date('D', strtotime($day_date)),
        'day_number' => date('j', strtotime($day_date))
    );
}

// Organize reservations by date
$reservations_by_date = array();
foreach ($weekly_reservations as $reservation) {
    $date = $reservation->reservation_date;
    if (!isset($reservations_by_date[$date])) {
        $reservations_by_date[$date] = array();
    }
    $reservations_by_date[$date][] = $reservation;
}
?>

<div class="wrap">
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #007cba;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">📅 Weekly Reservations Overview</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">
                Complete weekly view with detailed management
            </p>
            <p style="color: #6c757d; font-size: 1.1rem; margin: 5px 0 0 0;">
                <?php echo date('F j', strtotime($week_start)); ?> - <?php echo date('F j, Y', strtotime($week_end)); ?>
            </p>
        </div>
        
        <!-- Week Navigation -->
        <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
                <?php 
                $prev_week = date('Y-m-d', strtotime($current_week . ' -7 days'));
                $next_week = date('Y-m-d', strtotime($current_week . ' +7 days'));
                $this_week = date('Y-m-d', strtotime('monday this week'));
                ?>
                
                <a href="?page=yrr-weekly-reservations&week=<?php echo $prev_week; ?>" 
                   style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    ← Previous Week
                </a>
                
                <a href="?page=yrr-weekly-reservations&week=<?php echo $this_week; ?>" 
                   style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    This Week
                </a>
                
                <a href="?page=yrr-weekly-reservations&week=<?php echo $next_week; ?>" 
                   style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    Next Week →
                </a>
            </div>
        </div>
        
        <!-- Weekly Calendar Grid -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 15px; margin-bottom: 30px;">
            <?php foreach ($days_of_week as $day): ?>
                <?php 
                $day_reservations = isset($reservations_by_date[$day['date']]) ? $reservations_by_date[$day['date']] : array();
                $is_today = $day['date'] === date('Y-m-d');
                $day_bg = $is_today ? '#e3f2fd' : '#f8f9fa';
                $day_border = $is_today ? '#007cba' : '#dee2e6';
                ?>
                
                <div style="background: <?php echo $day_bg; ?>; border: 2px solid <?php echo $day_border; ?>; border-radius: 15px; padding: 20px; min-height: 300px;">
                    <!-- Day Header -->
                    <div style="text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid <?php echo $day_border; ?>;">
                        <div style="font-weight: bold; font-size: 1.2rem; color: #2c3e50;">
                            <?php echo $day['day_name']; ?>
                        </div>
                        <div style="font-size: 1.1rem; color: #6c757d;">
                            <?php echo $day['day_number']; ?>
                        </div>
                        <?php if ($is_today): ?>
                            <div style="background: #007cba; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; font-weight: bold; margin-top: 5px;">
                                TODAY
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reservations for the Day -->
                    <div>
                        <?php if (empty($day_reservations)): ?>
                            <div style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">
                                No reservations
                            </div>
                        <?php else: ?>
                            <?php 
                            // Sort reservations by time
                            usort($day_reservations, function($a, $b) {
                                return strcmp($a->reservation_time, $b->reservation_time);
                            });
                            ?>
                            
                            <?php foreach ($day_reservations as $reservation): ?>
                                <?php 
                                $status_colors = array(
                                    'confirmed' => '#28a745',
                                    'pending' => '#ffc107',
                                    'cancelled' => '#dc3545'
                                );
                                $status_color = $status_colors[$reservation->status] ?? '#6c757d';
                                $text_color = $reservation->status === 'pending' ? '#000' : '#fff';
                                ?>
                                
                                <div style="background: <?php echo $status_color; ?>; color: <?php echo $text_color; ?>; padding: 10px; border-radius: 8px; margin-bottom: 8px; cursor: pointer;"
                                     onclick="showReservationDetails(<?php echo htmlspecialchars(json_encode($reservation)); ?>)">
                                    <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 3px;">
                                        🕐 <?php echo date('g:i A', strtotime($reservation->reservation_time)); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; margin-bottom: 2px;">
                                        👤 <?php echo esc_html(substr($reservation->customer_name, 0, 15)); ?>
                                    </div>
                                    <div style="font-size: 0.8rem;">
                                        👥 <?php echo $reservation->party_size; ?> guests
                                    </div>
                                    <?php if ($reservation->table_id): ?>
                                        <?php
                                        global $wpdb;
                                        $table = $wpdb->get_row($wpdb->prepare("SELECT table_number FROM {$wpdb->prefix}yrr_tables WHERE id = %d", $reservation->table_id));
                                        ?>
                                        <div style="font-size: 0.8rem; margin-top: 2px;">
                                            🍽️ <?php echo $table ? $table->table_number : 'Table ' . $reservation->table_id; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Day Summary -->
                    <div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid <?php echo $day_border; ?>; text-align: center; font-size: 0.9rem; color: #6c757d;">
                        <strong><?php echo count($day_reservations); ?></strong> reservation<?php echo count($day_reservations) === 1 ? '' : 's'; ?>
                        <?php if (!empty($day_reservations)): ?>
                            <br>
                            <small>
                                <?php echo array_sum(array_column($day_reservations, 'party_size')); ?> total guests
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Weekly Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
            <?php
            $total_reservations = count($weekly_reservations);
            $total_guests = array_sum(array_column($weekly_reservations, 'party_size'));
            $confirmed_reservations = count(array_filter($weekly_reservations, function($r) { return $r->status === 'confirmed'; }));
            $pending_reservations = count(array_filter($weekly_reservations, function($r) { return $r->status === 'pending'; }));
            ?>
            
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_reservations; ?></div>
                <div>Total Reservations</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $confirmed_reservations; ?></div>
                <div>Confirmed</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $pending_reservations; ?></div>
                <div>Pending</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_guests; ?></div>
                <div>Total Guests</div>
            </div>
        </div>
    </div>
</div>

<script>
function showReservationDetails(reservation) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center;';
    
    const statusColors = {
        'confirmed': '#28a745',
        'pending': '#ffc107',
        'cancelled': '#dc3545'
    };
    
    const statusColor = statusColors[reservation.status] || '#6c757d';
    
    modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">📋 Reservation Details</h3>
                <div style="background: ${statusColor}; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-weight: bold; text-transform: uppercase; margin-bottom: 15px;">
                    ${reservation.status}
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div><strong>Customer:</strong><br>${reservation.customer_name}</div>
                <div><strong>Party Size:</strong><br>👥 ${reservation.party_size} guests</div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div><strong>Date:</strong><br>${reservation.reservation_date}</div>
                <div><strong>Time:</strong><br>${reservation.reservation_time}</div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div><strong>Email:</strong><br>${reservation.customer_email}</div>
                <div><strong>Phone:</strong><br>${reservation.customer_phone}</div>
            </div>
            
            ${reservation.special_requests ? `<div style="margin-bottom: 15px;"><strong>Special Requests:</strong><br>${reservation.special_requests}</div>` : ''}
            
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="this.closest('div').parentElement.remove()" style="background: #007cba; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-right: 10px;">Close</button>
                <a href="admin.php?page=yenolx-reservations" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;">Manage</a>
            </div>
        </div>
    `;
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.remove();
    });
    
    document.body.appendChild(modal);
}
</script>

<style>
@media (max-width: 1200px) {
    div[style*="grid-template-columns: repeat(7, 1fr)"] {
        grid-template-columns: repeat(4, 1fr) !important;
    }
}

@media (max-width: 768px) {
    div[style*="grid-template-columns: repeat(7, 1fr)"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

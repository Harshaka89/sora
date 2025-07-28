<?php
if (!defined('ABSPATH')) exit;

// Helper function for safe property access
function yrr_get_property_table($object, $property, $default = '') {
    return (property_exists($object, $property) && !empty($object->$property)) ? $object->$property : $default;
}
?>

<div class="wrap">
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #28a745;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">🍽️ Tables Management</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Manage restaurant tables, capacity, and seating arrangements</p>
        </div>
        
        <!-- Success Messages -->
        <?php if (isset($_GET['message'])): ?>
            <div style="padding: 15px; margin: 20px 0; border-radius: 8px; border: 2px solid; <?php
                switch($_GET['message']) {
                    case 'table_added':
                        echo 'background: #d4edda; color: #155724; border-color: #28a745;';
                        $msg = '✅ Table added successfully!';
                        break;
                    case 'table_updated':
                        echo 'background: #cce7ff; color: #004085; border-color: #007cba;';
                        $msg = '✅ Table updated successfully!';
                        break;
                    case 'table_deleted':
                        echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                        $msg = '🗑️ Table deleted successfully!';
                        break;
                    default:
                        echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                        $msg = '❌ An error occurred.';
                }
            ?>">
                <h4 style="margin: 0;"><?php echo $msg; ?></h4>
            </div>
        <?php endif; ?>
        
        <!-- Add New Table Form -->
        <div style="background: #e8f5e8; padding: 30px; border-radius: 15px; margin-bottom: 30px; border: 3px solid #28a745;">
            <h3 style="margin: 0 0 25px 0; color: #28a745;">➕ Add New Table</h3>
            
            <form method="post" action="">
                <?php wp_nonce_field('yrr_table_action', 'table_nonce'); ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">🏷️ Table Number *</label>
                        <input type="text" name="table_number" required maxlength="20" placeholder="e.g., T1, Table-5"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                        <small style="color: #6c757d; display: block; margin-top: 5px;">Unique identifier for the table</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">👥 Capacity *</label>
                        <input type="number" name="capacity" required min="1" max="20" value="4"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; text-align: center; font-size: 1.3rem; font-weight: bold; box-sizing: border-box;">
                        <small style="color: #6c757d; display: block; margin-top: 5px;">Maximum number of guests</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">📍 Location</label>
                        <select name="location" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                            <option value="Center">Center Area</option>
                            <option value="Window">Window Side</option>
                            <option value="Private">Private Section</option>
                            <option value="VIP">VIP Area</option>
                            <option value="Outdoor">Outdoor Seating</option>
                            <option value="Bar">Bar Area</option>
                        </select>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">Table location in restaurant</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">🎨 Table Type</label>
                        <select name="table_type" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                            <option value="standard">Standard Table</option>
                            <option value="booth">Booth Seating</option>
                            <option value="high_top">High Top Table</option>
                            <option value="round">Round Table</option>
                            <option value="square">Square Table</option>
                            <option value="rectangular">Rectangular Table</option>
                        </select>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">Type of table/seating</small>
                    </div>
                </div>
                
                <div style="text-align: center; padding-top: 20px; border-top: 2px solid #28a745;">
                    <button type="submit" name="add_table" value="1"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 15px 40px; border-radius: 10px; font-size: 1.2rem; font-weight: bold; cursor: pointer;">
                        🍽️ Add Table
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Current Tables List -->
        <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px;">
                <h3 style="margin: 0; font-size: 1.8rem;">📋 Current Tables (<?php echo is_array($tables) ? count($tables) : 0; ?> total)</h3>
            </div>
            
            <div style="padding: 20px;">
                <?php if (!empty($tables) && is_array($tables)): ?>
                    
                    <!-- Tables Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($tables as $table): ?>
                            <?php if (!is_object($table)) continue; ?>
                            
                            <div style="background: white; border: 2px solid #e9ecef; border-radius: 12px; padding: 20px; position: relative; <?php echo yrr_get_property_table($table, 'status') === 'available' ? 'border-color: #28a745;' : 'border-color: #dc3545;'; ?>">
                                
                                <!-- Status Badge -->
                                <div style="position: absolute; top: -10px; right: 15px; background: <?php echo yrr_get_property_table($table, 'status') === 'available' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 5px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                    <?php echo esc_html(yrr_get_property_table($table, 'status', 'available')); ?>
                                </div>
                                
                                <!-- Table Info -->
                                <div style="text-align: center; margin-bottom: 15px;">
                                    <h4 style="margin: 0 0 10px 0; font-size: 1.5rem; color: #2c3e50;">
                                        🍽️ <?php echo esc_html(yrr_get_property_table($table, 'table_number', 'Unknown')); ?>
                                    </h4>
                                    <div style="background: #007cba; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; font-weight: bold; margin-bottom: 10px;">
                                        👥 <?php echo intval(yrr_get_property_table($table, 'capacity', 1)); ?> guests
                                    </div>
                                </div>
                                
                                <!-- Table Details -->
                                <div style="margin-bottom: 15px;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
                                        <div>
                                            <strong>📍 Location:</strong><br>
                                            <?php echo esc_html(yrr_get_property_table($table, 'location', 'Not set')); ?>
                                        </div>
                                        <div>
                                            <strong>🎨 Type:</strong><br>
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', yrr_get_property_table($table, 'table_type', 'standard')))); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button onclick="editTable(<?php echo htmlspecialchars(json_encode($table)); ?>)" 
                                            style="background: #17a2b8; color: white; border: none; padding: 8px 12px; border-radius: 5px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                        ✏️ Edit
                                    </button>
                                    
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yrr-tables&delete_table=' . yrr_get_property_table($table, 'id')), 'yrr_table_action'); ?>" 
                                       onclick="return confirm('Delete this table permanently? This cannot be undone.')" 
                                       style="background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 0.8rem; font-weight: bold;">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </div>
                            
                        <?php endforeach; ?>
                    </div>
                    
                <?php else: ?>
                    
                    <!-- No Tables State -->
                    <div style="text-align: center; padding: 60px 20px; color: #6c757d;">
                        <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">🍽️</div>
                        <h3 style="margin: 0 0 15px 0;">No Tables Created</h3>
                        <p>Add your first table to start managing seating capacity and reservations.</p>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Navigation -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo admin_url('admin.php?page=yenolx-reservations'); ?>" 
               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: bold; margin-right: 15px;">
                📊 Dashboard
            </a>
            <a href="<?php echo admin_url('admin.php?page=yrr-hours'); ?>" 
               style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: bold;">
                ⏰ Operating Hours
            </a>
        </div>
    </div>
</div>

<!-- Edit Table Modal -->
<div id="editTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 600px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">✏️ Edit Table</h3>
            <button onclick="closeTableModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('yrr_table_action', 'table_nonce'); ?>
            <input type="hidden" id="edit_table_id" name="table_id">
            <input type="hidden" name="update_table" value="1">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">🏷️ Table Number *</label>
                    <input type="text" id="edit_table_number" name="table_number" required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">👥 Capacity *</label>
                    <input type="number" id="edit_capacity" name="capacity" min="1" max="20" required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; text-align: center; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">📍 Location</label>
                    <select id="edit_location" name="location" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        <option value="Center">Center Area</option>
                        <option value="Window">Window Side</option>
                        <option value="Private">Private Section</option>
                        <option value="VIP">VIP Area</option>
                        <option value="Outdoor">Outdoor Seating</option>
                        <option value="Bar">Bar Area</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">🎨 Table Type</label>
                    <select id="edit_table_type" name="table_type" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        <option value="standard">Standard Table</option>
                        <option value="booth">Booth Seating</option>
                        <option value="high_top">High Top Table</option>
                        <option value="round">Round Table</option>
                        <option value="square">Square Table</option>
                        <option value="rectangular">Rectangular Table</option>
                    </select>
                </div>
            </div>
            
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closeTableModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">💾 Update Table</button>
            </div>
        </form>
    </div>
</div>

<!-- Enhanced Table Card with Reservation Assignment -->
<div style="background: white; border: 2px solid #e9ecef; border-radius: 15px; padding: 20px; margin-bottom: 20px;">
    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 20px; align-items: center;">
        
        <!-- Table Icon and Basic Info -->
        <div style="text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 10px;">🍽️</div>
            <div style="background: <?php echo $table->status === 'available' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold;">
                <?php echo ucfirst($table->status); ?>
            </div>
        </div>
        
        <!-- Table Details -->
        <div>
            <h3 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 1.5rem;">
                <?php echo esc_html($table->table_number); ?>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 15px;">
                <div>
                    <strong>👥 Capacity:</strong> <?php echo intval($table->capacity); ?> seats
                </div>
                <div>
                    <strong>📍 Location:</strong> <?php echo esc_html($table->location); ?>
                </div>
                <div>
                    <strong>🏷️ Type:</strong> <?php echo esc_html($table->table_type); ?>
                </div>
            </div>
            
            <!-- Current Reservations for Today -->
            <?php
            global $wpdb;
            $today_bookings = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}yrr_reservations 
                 WHERE table_id = %d AND reservation_date = %s AND status IN ('confirmed', 'pending') 
                 ORDER BY reservation_time",
                $table->id, date('Y-m-d')
            ));
            ?>
            
            <?php if (!empty($today_bookings)): ?>
                <div style="background: #fff3cd; padding: 10px; border-radius: 8px; margin-top: 10px;">
                    <strong style="color: #856404;">📅 Today's Bookings:</strong>
                    <div style="margin-top: 5px;">
                        <?php foreach ($today_bookings as $booking): ?>
                            <span style="background: <?php echo $booking->status === 'confirmed' ? '#28a745' : '#ffc107'; ?>; color: <?php echo $booking->status === 'confirmed' ? 'white' : 'black'; ?>; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; margin-right: 5px; margin-bottom: 5px; display: inline-block;">
                                <?php echo date('g:i A', strtotime($booking->reservation_time)); ?> - <?php echo esc_html($booking->customer_name); ?> (<?php echo $booking->party_size; ?>)
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: #e8f5e8; padding: 10px; border-radius: 8px; margin-top: 10px; color: #155724;">
                    <strong>🆓 Available all day today</strong>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; flex-direction: column; gap: 10px;">
            
            <!-- Assign Reservation Button -->
            <button onclick="showReservationAssignmentModal(<?php echo $table->id; ?>, '<?php echo esc_js($table->table_number); ?>', <?php echo $table->capacity; ?>)" 
                    style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; border: none; padding: 10px 15px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.9rem;">
                📋 Assign Reservation
            </button>
            
            <!-- View Schedule Button -->
            <a href="<?php echo admin_url('admin.php?page=yrr-table-schedule&table_focus=' . $table->id); ?>" 
               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 15px; text-decoration: none; border-radius: 8px; font-weight: bold; text-align: center; font-size: 0.9rem;">
                📅 View Schedule
            </a>
            
            <!-- Edit Table Button -->
            <button onclick="editTable(<?php echo htmlspecialchars(json_encode($table)); ?>)" 
                    style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; border: none; padding: 10px 15px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.9rem;">
                ✏️ Edit Table
            </button>
            
            <!-- Delete Table Button -->
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yrr-tables&delete_table=' . $table->id), 'yrr_table_action'); ?>" 
               onclick="return confirm('Delete this table permanently? This will unassign all future reservations.')" 
               style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 10px 15px; text-decoration: none; border-radius: 8px; font-weight: bold; text-align: center; font-size: 0.9rem;">
                🗑️ Delete
            </a>
        </div>
    </div>
</div>
<!-- Reservation Assignment Modal -->
<div id="reservationAssignmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">📋 Assign Reservation to Table</h3>
            <button onclick="closeReservationAssignmentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <!-- Table Info Display -->
        <div style="background: #e8f5e8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; color: #155724;">🍽️ Table Details</h4>
            <div id="assignment_table_info">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        
        <!-- Available Reservations -->
        <div>
            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">📅 Available Reservations (No Table Assigned)</h4>
            <div id="unassigned_reservations" style="max-height: 300px; overflow-y: auto;">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        
        <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
            <button type="button" onclick="closeReservationAssignmentModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">Close</button>
        </div>
    </div>
</div>


<script>
// Table Assignment Modal Functions
function showTableAssignmentModal(reservationId, customerName, partySize) {
    document.getElementById('assign_reservation_id').value = reservationId;
    document.getElementById('assignment_customer_info').innerHTML = `
        <div style="font-weight: bold; margin-bottom: 5px;">Customer: ${customerName}</div>
        <div>Party Size: ${partySize} guests</div>
    `;
    
    // Load available tables
    loadAvailableTables(partySize);
    
    document.getElementById('tableAssignmentModal').style.display = 'flex';
}

function closeTableAssignmentModal() {
    document.getElementById('tableAssignmentModal').style.display = 'none';
    document.getElementById('selected_table_info').style.display = 'none';
}

function loadAvailableTables(partySize) {
    // In a real implementation, this would be an AJAX call
    // For now, we'll populate with PHP data
    const tablesGrid = document.getElementById('available_tables_grid');
    
    <?php
    global $wpdb;
    $all_tables = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}yrr_tables ORDER BY table_number");
    if ($all_tables):
    ?>
        let tablesHTML = '';
        const tables = <?php echo json_encode($all_tables); ?>;
        
        tables.forEach(table => {
            const issuitable = table.capacity >= partySize;
            const bgColor = issuitable ? '#e8f5e8' : '#f8d7da';
            const borderColor = issuitable ? '#28a745' : '#dc3545';
            const textColor = issuitable ? '#155724' : '#721c24';
            
            tablesHTML += `
                <div onclick="${issuitable ? `selectTable(${table.id}, '${table.table_number}', ${table.capacity}, '${table.location}')` : ''}" 
                     style="background: ${bgColor}; border: 2px solid ${borderColor}; padding: 15px; border-radius: 10px; text-align: center; cursor: ${isutable ? 'pointer' : 'not-allowed'}; color: ${textColor};">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🍽️</div>
                    <div style="font-weight: bold; margin-bottom: 5px;">${table.table_number}</div>
                    <div style="font-size: 0.9rem;">${table.capacity} seats</div>
                    <div style="font-size: 0.8rem; margin-top: 5px;">${table.location}</div>
                    ${!isutable ? '<div style="font-size: 0.8rem; margin-top: 5px; font-weight: bold;">Too Small</div>' : ''}
                </div>
            `;
        });
        
        tablesGrid.innerHTML = tablesHTML;
    <?php endif; ?>
}

function selectTable(tableId, tableName, capacity, location) {
    // Add hidden input for selected table
    const existingInput = document.getElementById('selected_table_id');
    if (existingInput) {
        existingInput.remove();
    }
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'selected_table_id';
    hiddenInput.name = 'table_id';
    hiddenInput.value = tableId;
    document.querySelector('#tableAssignmentModal form').appendChild(hiddenInput);
    
    // Show selected table info
    document.getElementById('selected_table_details').innerHTML = `
        <div style="font-weight: bold; margin-bottom: 5px;">🍽️ ${tableName}</div>
        <div>Capacity: ${capacity} seats</div>
        <div>Location: ${location}</div>
    `;
    document.getElementById('selected_table_info').style.display = 'block';
    
    // Highlight selected table
    document.querySelectorAll('#available_tables_grid > div').forEach(div => {
        div.style.boxShadow = 'none';
    });
    event.target.closest('div').style.boxShadow = '0 0 0 3px #007cba';
}

// Reservation Assignment Modal Functions
function showReservationAssignmentModal(tableId, tableName, capacity) {
    document.getElementById('assignment_table_info').innerHTML = `
        <div style="font-weight: bold; margin-bottom: 5px;">🍽️ ${tableName}</div>
        <div>Capacity: ${capacity} seats</div>
    `;
    
    // Load unassigned reservations
    loadUnassignedReservations(tableId, capacity);
    
    document.getElementById('reservationAssignmentModal').style.display = 'flex';
}

function closeReservationAssignmentModal() {
    document.getElementById('reservationAssignmentModal').style.display = 'none';
}

function loadUnassignedReservations(tableId, capacity) {
    // In a real implementation, this would be an AJAX call
    const container = document.getElementById('unassigned_reservations');
    
    <?php
    $unassigned = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}yrr_reservations 
        WHERE table_id IS NULL AND status IN ('confirmed', 'pending') 
        AND reservation_date >= '" . date('Y-m-d') . "' 
        ORDER BY reservation_date, reservation_time
    ");
    if ($unassigned):
    ?>
        const reservations = <?php echo json_encode($unassigned); ?>;
        let reservationsHTML = '';
        
        if (reservations.length === 0) {
            reservationsHTML = '<div style="text-align: center; padding: 20px; color: #6c757d;">No unassigned reservations found</div>';
        } else {
            reservations.forEach(res => {
                const isTableSuitable = res.party_size <= capacity;
                const bgColor = isTableSuitable ? '#e8f5e8' : '#f8d7da';
                const borderColor = isTableSuitable ? '#28a745' : '#dc3545';
                
                reservationsHTML += `
                    <div style="background: ${bgColor}; border: 1px solid ${borderColor}; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: center;">
                        <div>
                            <div style="font-weight: bold; margin-bottom: 5px;">${res.customer_name}</div>
                            <div style="font-size: 0.9rem; color: #6c757d; margin-bottom: 3px;">📅 ${res.reservation_date} at ${res.reservation_time.substring(0,5)}</div>
                            <div style="font-size: 0.9rem; color: #6c757d;">👥 ${res.party_size} guests</div>
                            ${!isTableSuitable ? '<div style="color: #dc3545; font-weight: bold; font-size: 0.8rem; margin-top: 5px;">⚠️ Party too large for this table</div>' : ''}
                        </div>
                        <div>
                            ${isTableSuitable ? 
                                `<button onclick="assignReservationToTable(${res.id}, ${tableId})" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">Assign</button>` : 
                                `<button disabled style="background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: not-allowed; font-weight: bold;">Too Large</button>`
                            }
                        </div>
                    </div>
                `;
            });
        }
        
        container.innerHTML = reservationsHTML;
    <?php endif; ?>
}

function assignReservationToTable(reservationId, tableId) {
    if (confirm('Assign this reservation to the selected table?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        // Add nonce
        const nonce = document.createElement('input');
        nonce.type = 'hidden';
        nonce.name = 'assign_table_nonce';
        nonce.value = '<?php echo wp_create_nonce('assign_table'); ?>';
        form.appendChild(nonce);
        
        // Add action
        const action = document.createElement('input');
        action.type = 'hidden';
        action.name = 'assign_table_action';
        action.value = '1';
        form.appendChild(action);
        
        // Add reservation ID
        const resId = document.createElement('input');
        resId.type = 'hidden';
        resId.name = 'reservation_id';
        resId.value = reservationId;
        form.appendChild(resId);
        
        // Add table ID
        const tblId = document.createElement('input');
        tblId.type = 'hidden';
        tblId.name = 'table_id';
        tblId.value = tableId;
        form.appendChild(tblId);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Enhanced edit function
function editReservation(res) {
    document.getElementById('edit_id').value = res.id || '';
    document.getElementById('edit_name').value = res.customer_name || '';
    document.getElementById('edit_email').value = res.customer_email || '';
    document.getElementById('edit_phone').value = res.customer_phone || '';
    document.getElementById('edit_party').value = res.party_size || '1';
    document.getElementById('edit_date').value = res.reservation_date || '';
    
    const time = res.reservation_time || '';
    document.getElementById('edit_time').value = time.length > 5 ? time.substring(0, 5) : time;
    
    document.getElementById('edit_requests').value = res.special_requests || '';
    document.getElementById('edit_notes').value = res.notes || '';
    
    // Set table selection
    const tableSelect = document.getElementById('edit_table');
    if (tableSelect && res.table_id) {
        tableSelect.value = res.table_id;
    }
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    ['editModal', 'tableAssignmentModal', 'reservationAssignmentModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if (modalId === 'editModal') closeModal();
                    else if (modalId === 'tableAssignmentModal') closeTableAssignmentModal();
                    else if (modalId === 'reservationAssignmentModal') closeReservationAssignmentModal();
                }
            });
        }
    });
});
</script>

<script>
function editTable(table) {
    document.getElementById('edit_table_id').value = table.id || '';
    document.getElementById('edit_table_number').value = table.table_number || '';
    document.getElementById('edit_capacity').value = table.capacity || '4';
    document.getElementById('edit_location').value = table.location || 'Center';
    document.getElementById('edit_table_type').value = table.table_type || 'standard';
    
    document.getElementById('editTableModal').style.display = 'flex';
}

function closeTableModal() {
    document.getElementById('editTableModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editTableModal').addEventListener('click', function(e) {
    if (e.target === this) closeTableModal();
});
</script>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr))"] {
        grid-template-columns: 1fr !important;
    }
    
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}

button:hover, a[style*="background:"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
</style>

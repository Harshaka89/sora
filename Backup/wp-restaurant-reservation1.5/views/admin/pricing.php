<?php
if (!defined('ABSPATH')) exit;

// Helper function for safe property access
function yrr_get_property_pricing($object, $property, $default = '') {
    return (property_exists($object, $property) && !empty($object->$property)) ? $object->$property : $default;
}
?>

<div class="wrap">
    <div style="max-width: 1400px; margin: 20px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #dc3545;">
            <h1 style="font-size: 2.5rem; color: #2c3e50; margin: 0;">💰 Dynamic Pricing Rules</h1>
            <p style="color: #6c757d; margin: 10px 0 0 0;">Configure time-based and day-based pricing modifiers</p>
        </div>
        
        <!-- Success Messages -->
        <?php if (isset($_GET['message'])): ?>
            <div style="padding: 15px; margin: 20px 0; border-radius: 8px; border: 2px solid; <?php
                switch($_GET['message']) {
                    case 'rule_added':
                        echo 'background: #d4edda; color: #155724; border-color: #28a745;';
                        $msg = '✅ Pricing rule added successfully!';
                        break;
                    case 'rule_updated':
                        echo 'background: #cce7ff; color: #004085; border-color: #007cba;';
                        $msg = '✅ Pricing rule updated successfully!';
                        break;
                    case 'rule_deleted':
                        echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                        $msg = '🗑️ Pricing rule deleted successfully!';
                        break;
                    default:
                        echo 'background: #f8d7da; color: #721c24; border-color: #dc3545;';
                        $msg = '❌ An error occurred.';
                }
            ?>">
                <h4 style="margin: 0;"><?php echo $msg; ?></h4>
            </div>
        <?php endif; ?>
        
        <!-- Add New Pricing Rule Form -->
        <div style="background: #f8d7da; padding: 30px; border-radius: 15px; margin-bottom: 30px; border: 3px solid #dc3545;">
            <h3 style="margin: 0 0 25px 0; color: #721c24;">➕ Create New Pricing Rule</h3>
            
            <form method="post" action="">
                <?php wp_nonce_field('yrr_pricing_action', 'pricing_nonce'); ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">🏷️ Rule Name *</label>
                        <input type="text" name="rule_name" required maxlength="100" placeholder="e.g., Happy Hour Discount"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                        <small style="color: #6c757d; display: block; margin-top: 5px;">Descriptive name for this pricing rule</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">🕐 Start Time *</label>
                        <input type="time" name="start_time" required value="18:00"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                        <small style="color: #6c757d; display: block; margin-top: 5px;">When this rule starts applying</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">🕕 End Time *</label>
                        <input type="time" name="end_time" required value="21:00"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                        <small style="color: #6c757d; display: block; margin-top: 5px;">When this rule stops applying</small>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">📅 Days Applicable *</label>
                        <select name="days_applicable" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                            <option value="all">All Days</option>
                            <option value="weekdays">Weekdays Only (Mon-Fri)</option>
                            <option value="weekends">Weekends Only (Sat-Sun)</option>
                            <option value="monday">Monday Only</option>
                            <option value="tuesday">Tuesday Only</option>
                            <option value="wednesday">Wednesday Only</option>
                            <option value="thursday">Thursday Only</option>
                            <option value="friday">Friday Only</option>
                            <option value="saturday">Saturday Only</option>
                            <option value="sunday">Sunday Only</option>
                        </select>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">Which days this rule applies</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">💰 Modifier Type *</label>
                        <select name="modifier_type" id="modifier_type" onchange="updateModifierLabel()" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1.1rem; box-sizing: border-box;">
                            <option value="add">Fixed Amount (+ or -)</option>
                            <option value="percent">Percentage (% change)</option>
                        </select>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">How to apply the price change</small>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50;">
                            <span id="modifier_label">💸 Price Modifier *</span>
                        </label>
                        <input type="number" name="price_modifier" required step="0.01" placeholder="2.00"
                               style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; text-align: center; font-size: 1.3rem; font-weight: bold; box-sizing: border-box;">
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            <span id="modifier_help">Enter amount (use negative for discount)</span>
                        </small>
                    </div>
                </div>
                
                <!-- Examples Section -->
                <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 5px solid #2196f3;">
                    <h4 style="margin: 0 0 15px 0; color: #1976d2;">💡 Pricing Examples</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; font-size: 0.9rem;">
                        <div>
                            <strong>🍽️ Dinner Premium:</strong><br>
                            6 PM - 9 PM, +$5.00 per person
                        </div>
                        <div>
                            <strong>🥗 Lunch Discount:</strong><br>
                            11 AM - 3 PM, -$2.00 per person
                        </div>
                        <div>
                            <strong>🎉 Weekend Surcharge:</strong><br>
                            All day, +15% percentage
                        </div>
                        <div>
                            <strong>😊 Happy Hour:</strong><br>
                            4 PM - 6 PM, -20% percentage
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; padding-top: 20px; border-top: 2px solid #dc3545;">
                    <button type="submit" name="add_rule" value="1"
                            style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; padding: 15px 40px; border-radius: 10px; font-size: 1.2rem; font-weight: bold; cursor: pointer;">
                        💰 Add Pricing Rule
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Current Pricing Rules -->
        <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px;">
                <h3 style="margin: 0; font-size: 1.8rem;">📋 Active Pricing Rules (<?php echo is_array($rules) ? count($rules) : 0; ?> total)</h3>
            </div>
            
            <div style="padding: 20px;">
                <?php if (!empty($rules) && is_array($rules)): ?>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 15px; text-align: left; font-weight: bold; color: #2c3e50;">Rule Name</th>
                                    <th style="padding: 15px; text-align: center; font-weight: bold; color: #2c3e50;">Time Period</th>
                                    <th style="padding: 15px; text-align: center; font-weight: bold; color: #2c3e50;">Days</th>
                                    <th style="padding: 15px; text-align: center; font-weight: bold; color: #2c3e50;">Price Modifier</th>
                                    <th style="padding: 15px; text-align: center; font-weight: bold; color: #2c3e50;">Status</th>
                                    <th style="padding: 15px; text-align: center; font-weight: bold; color: #2c3e50;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rules as $index => $rule): ?>
                                    <?php if (!is_object($rule)) continue; ?>
                                    
                                    <tr style="<?php echo $index % 2 == 0 ? 'background: #f8f9fa;' : 'background: white;'; ?> border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 15px;">
                                            <div style="font-weight: bold; font-size: 1.1rem; color: #2c3e50; margin-bottom: 5px;">
                                                <?php echo esc_html(yrr_get_property_pricing($rule, 'rule_name', 'Unnamed Rule')); ?>
                                            </div>
                                            <small style="color: #6c757d;">
                                                ID: <?php echo intval(yrr_get_property_pricing($rule, 'id', 0)); ?>
                                            </small>
                                        </td>
                                        
                                        <td style="padding: 15px; text-align: center;">
                                            <?php 
                                            $start_time = yrr_get_property_pricing($rule, 'start_time', '00:00:00');
                                            $end_time = yrr_get_property_pricing($rule, 'end_time', '23:59:59');
                                            ?>
                                            <div style="font-weight: bold; color: #007cba;">
                                                <?php echo date('g:i A', strtotime($start_time)); ?>
                                            </div>
                                            <div style="color: #6c757d; font-size: 0.9rem;">to</div>
                                            <div style="font-weight: bold; color: #007cba;">
                                                <?php echo date('g:i A', strtotime($end_time)); ?>
                                            </div>
                                        </td>
                                        
                                        <td style="padding: 15px; text-align: center;">
                                            <?php 
                                            $days = yrr_get_property_pricing($rule, 'days_applicable', 'all');
                                            $day_labels = array(
                                                'all' => '🌍 All Days',
                                                'weekdays' => '📅 Weekdays',
                                                'weekends' => '🎉 Weekends',
                                                'monday' => '📅 Monday',
                                                'tuesday' => '📅 Tuesday',
                                                'wednesday' => '📅 Wednesday',
                                                'thursday' => '📅 Thursday',
                                                'friday' => '📅 Friday',
                                                'saturday' => '🎉 Saturday',
                                                'sunday' => '🎉 Sunday'
                                            );
                                            ?>
                                            <span style="background: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold;">
                                                <?php echo $day_labels[$days] ?? ucfirst($days); ?>
                                            </span>
                                        </td>
                                        
                                        <td style="padding: 15px; text-align: center;">
                                            <?php 
                                            $modifier_type = yrr_get_property_pricing($rule, 'modifier_type', 'add');
                                            $modifier_value = floatval(yrr_get_property_pricing($rule, 'price_modifier', 0));
                                            
                                            if ($modifier_type === 'percent') {
                                                $color = $modifier_value >= 0 ? '#dc3545' : '#28a745';
                                                echo '<span style="background: ' . $color . '; color: white; padding: 8px 12px; border-radius: 15px; font-weight: bold;">';
                                                echo ($modifier_value >= 0 ? '+' : '') . $modifier_value . '%';
                                                echo '</span>';
                                            } else {
                                                $color = $modifier_value >= 0 ? '#dc3545' : '#28a745';
                                                echo '<span style="background: ' . $color . '; color: white; padding: 8px 12px; border-radius: 15px; font-weight: bold;">';
                                                echo ($modifier_value >= 0 ? '+' : '') . '$' . number_format(abs($modifier_value), 2);
                                                echo '</span>';
                                            }
                                            ?>
                                        </td>
                                        
                                        <td style="padding: 15px; text-align: center;">
                                            <?php 
                                            $is_active = intval(yrr_get_property_pricing($rule, 'is_active', 1));
                                            if ($is_active) {
                                                echo '<span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold;">✅ ACTIVE</span>';
                                            } else {
                                                echo '<span style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold;">❌ INACTIVE</span>';
                                            }
                                            ?>
                                        </td>
                                        
                                        <td style="padding: 15px; text-align: center;">
                                            <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                                                <button onclick="editPricingRule(<?php echo htmlspecialchars(json_encode($rule)); ?>)" 
                                                        style="background: #17a2b8; color: white; border: none; padding: 6px 10px; border-radius: 5px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                                    ✏️ Edit
                                                </button>
                                                
                                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yrr-pricing&delete_rule=' . yrr_get_property_pricing($rule, 'id')), 'yrr_pricing_action'); ?>" 
                                                   onclick="return confirm('Delete this pricing rule permanently?')" 
                                                   style="background: #dc3545; color: white; padding: 6px 10px; text-decoration: none; border-radius: 5px; font-size: 0.8rem; font-weight: bold;">
                                                    🗑️ Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php else: ?>
                    
                    <!-- No Rules State -->
                    <div style="text-align: center; padding: 60px 20px; color: #6c757d;">
                        <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">💰</div>
                        <h3 style="margin: 0 0 15px 0;">No Pricing Rules Created</h3>
                        <p>Create your first pricing rule to implement dynamic pricing based on time and day.</p>
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
            <a href="<?php echo admin_url('admin.php?page=yrr-coupons'); ?>" 
               style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: bold;">
                🎫 Discount Coupons
            </a>
        </div>
    </div>
</div>

<script>
function updateModifierLabel() {
    const modifierType = document.getElementById('modifier_type').value;
    const label = document.getElementById('modifier_label');
    const help = document.getElementById('modifier_help');
    
    if (modifierType === 'percent') {
        label.textContent = '📈 Percentage Change *';
        help.textContent = 'Enter percentage (e.g., 15 for 15% increase, -20 for 20% discount)';
    } else {
        label.textContent = '💸 Fixed Amount *';
        help.textContent = 'Enter amount (e.g., 5.00 for +$5, -3.00 for -$3 discount)';
    }
}

function editPricingRule(rule) {
    // This would open an edit modal - implement similar to table editing
    alert('Edit functionality would be implemented here for rule: ' + rule.rule_name);
}
</script>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))"] {
        grid-template-columns: 1fr !important;

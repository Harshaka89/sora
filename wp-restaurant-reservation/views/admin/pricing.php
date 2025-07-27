<!-- Edit Pricing Rule Modal -->
<div id="editPricingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 600px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
            <h3 style="margin: 0;">✏️ Edit Pricing Rule</h3>
            <button onclick="closePricingModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">×</button>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('yrr_pricing_action', 'pricing_nonce'); ?>
            <input type="hidden" id="edit_rule_id" name="rule_id">
            <input type="hidden" name="update_rule" value="1">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">🏷️ Rule Name *</label>
                    <input type="text" id="edit_rule_name" name="rule_name" required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">📅 Days Applicable</label>
                    <select id="edit_days_applicable" name="days_applicable" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        <option value="all">All Days</option>
                        <option value="weekdays">Weekdays Only</option>
                        <option value="weekends">Weekends Only</option>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">🕐 Start Time *</label>
                    <input type="time" id="edit_start_time" name="start_time" required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">🕕 End Time *</label>
                    <input type="time" id="edit_end_time" name="end_time" required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">💰 Modifier Type</label>
                    <select id="edit_modifier_type" name="modifier_type" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; box-sizing: border-box;">
                        <option value="add">Fixed Amount</option>
                        <option value="percent">Percentage</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">💸 Price Modifier *</label>
                    <input type="number" id="edit_price_modifier" name="price_modifier" step="0.01" required
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; text-align: center; box-sizing: border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; font-weight: bold; cursor: pointer;">
                    <input type="checkbox" id="edit_is_active" name="is_active" style="transform: scale(1.5);">
                    <span>✅ Rule is Active</span>
                </label>
            </div>
            
            <div style="text-align: right; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <button type="button" onclick="closePricingModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 8px; margin-right: 15px; cursor: pointer; font-weight: bold;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold;">💾 Update Rule</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPricingRule(rule) {
    document.getElementById('edit_rule_id').value = rule.id || '';
    document.getElementById('edit_rule_name').value = rule.rule_name || '';
    document.getElementById('edit_days_applicable').value = rule.days_applicable || 'all';
    
    // Remove seconds from time values
    const startTime = rule.start_time ? rule.start_time.substring(0, 5) : '18:00';
    const endTime = rule.end_time ? rule.end_time.substring(0, 5) : '21:00';
    
    document.getElementById('edit_start_time').value = startTime;
    document.getElementById('edit_end_time').value = endTime;
    document.getElementById('edit_modifier_type').value = rule.modifier_type || 'add';
    document.getElementById('edit_price_modifier').value = rule.price_modifier || '0';
    document.getElementById('edit_is_active').checked = rule.is_active == '1';
    
    document.getElementById('editPricingModal').style.display = 'flex';
}

function closePricingModal() {
    document.getElementById('editPricingModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editPricingModal').addEventListener('click', function(e) {
    if (e.target === this) closePricingModal();
});
</script>

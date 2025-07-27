<?php
/**
 * Weekly View Template - MVC Pattern
 * Complete error-safe version with property checks
 */

if (!defined('ABSPATH')) exit;
<div class="wrap">
    <div class="rrs-container rrs-fade-in" style="background: white; border-radius: 15px; padding: 30px; margin: 20px 0;">
        <div class="rrs-header" style="border-bottom: none; text-align: center;">
            <h1>📅 Weekly Reservations Overview</h1>
            <p>Complete weekly view with detailed management</p>
        </div>
        
        <div style="text-align: center; margin: 20px 0; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);">
            <a href="?page=weekly-view&week=<?php echo date('Y-m-d', strtotime($week_start . ' -7 days')); ?>" class="rrs-nav-btn">← Previous Week</a>
            
            <div style="display: inline-block; text-align: center; margin: 0 20px;">
                <strong style="font-size: 1.3rem; color: #2c3e50;">
                    <?php echo date('M j', strtotime($week_start)); ?> - <?php echo date('M j, Y', strtotime($week_end)); ?>
                </strong>
            </div>
            
            <a href="?page=weekly-view&week=<?php echo date('Y-m-d', strtotime($week_start . ' +7 days')); ?>" class="rrs-nav-btn">Next Week →</a>
            
            <a href="?page=weekly-view" style="background: #007cba; color: white; padding: 12px 20px; text-decoration: none; border-radius: 8px; margin-left: 20px; font-weight: bold;">Current Week</a>
        </div>
        
        <div class="rrs-weekly-grid">
            <?php 
            $weekly_data = array();
            foreach ($reservations as $res) {
                $weekly_data[$res->reservation_date][] = $res;
            }
            
            for ($i = 0; $i < 7; $i++):
                $current_date = date('Y-m-d', strtotime($week_start . " +{$i} days"));
                $day_reservations = isset($weekly_data[$current_date]) ? $weekly_data[$current_date] : array();
                $is_today = $current_date === date('Y-m-d');
                $day_name = date('l', strtotime($current_date));
            ?>
                <div class="rrs-day-card <?php echo $is_today ? 'today' : ''; ?>" style="animation-delay: <?php echo ($i * 0.1); ?>s;">
                    <div class="rrs-day-header <?php echo $is_today ? 'today' : ''; ?>">
                        <h3 style="margin: 0; font-size: 1.1rem;">
                            <?php echo $day_name; ?>
                            <br>
                            <span style="font-weight: normal; opacity: 0.8; font-size: 0.9rem;">
                                <?php echo date('M j', strtotime($current_date)); ?>
                            </span>
                            <?php if ($is_today): ?>
                                <br><small style="background: #007cba; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: bold;">TODAY</small>
                            <?php endif; ?>
                        </h3>
                    </div>
                    
                    <?php if (!empty($day_reservations)): ?>
                        <?php foreach ($day_reservations as $res): ?>
                            <div class="rrs-day-reservation <?php echo $res->status; ?>" title="<?php echo esc_attr($res->customer_name . ' - ' . $res->customer_email); ?>">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                                    <div style="font-weight: bold; font-size: 0.9rem; color: #2c3e50;">
                                        ⏰ <?php echo date('g:i A', strtotime($res->reservation_time)); ?>
                                    </div>
                                </div>
                                
                                <div style="font-size: 0.85rem; color: #495057; margin-bottom: 3px;">
                                    👤 <?php echo esc_html($res->customer_name); ?>
                                </div>
                                
                                <div style="font-size: 0.8rem; color: #6c757d; margin-bottom: 6px;">
                                    👥 <?php echo $res->party_size; ?> guests
                                    <?php if ($res->table_number): ?>
                                        • 🪑 <?php echo esc_html($res->table_number); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="background: <?php echo $res->status === 'confirmed' ? '#28a745' : ($res->status === 'pending' ? '#ffc107' : '#dc3545'); ?>; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; font-weight: bold; text-transform: uppercase;">
                                        <?php echo $res->status; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rrs-empty-state" style="padding: 20px 10px; text-align: center;">
                            <div style="font-size: 2rem; margin-bottom: 8px; opacity: 0.3;">📅</div>
                            <div style="font-size: 0.85rem; color: #6c757d;">No reservations</div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo admin_url('admin.php?page=reservations'); ?>" class="rrs-nav-btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">← Back to Dashboard</a>
        </div>
    </div>
</div>

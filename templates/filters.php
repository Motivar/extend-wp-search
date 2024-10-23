<?php
if (!defined('ABSPATH')) exit;
global $extend_wp_search_parameters;


?>
<div class="filters">
 <?php echo awm_show_content($extend_wp_search_parameters['filters']); ?>
</div>
<div class="filters-actions">
 <div class="undo">
  <div class="button" id="undo-checkboxes"><?php echo __('Remove filters', 'extend-wp-search'); ?></div>
 </div>
 <div class="apply">
  <div class="button" id="apply-checkboxes"><?php echo __('Apply filters', 'extend-wp-search'); ?></div>
 </div>
</div>
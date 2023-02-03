<?php
if (!defined('ABSPATH')) exit;
global $extend_wp_search_parameters;


?>
<div class="filters">
        <?php echo awm_show_content($extend_wp_search_parameters['filters']); ?>
</div>
<div class="filters-actions">
        <div class="undo">
                <div class="button" id="undo-checkboxes" onclick="disableCheckboxes();"><?php echo __('Remove filters', 'mtv-search'); ?></div>
        </div>
        <div class="apply">
                <div class="button" id="apply-checkboxes" onclick="newSearch();"><?php echo __('Apply filters', 'mtv-search'); ?></div>
        </div>
</div>
<?php
if (!defined('ABSPATH')) exit;
/**
 * this is the file for the full screen of the search area
 * @package motivar
 */
?>
<div id="search-full-screen" class="full-screen left">
 <div class="container">
  <?php echo do_shortcode('[extend_wp_search results="1" show_close="1" full_screen="1"]'); ?>
 </div>
</div>
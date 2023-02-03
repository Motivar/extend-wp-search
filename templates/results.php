<?php
if (!defined('ABSPATH')) exit;
global $extend_wp_search_parameters;
global $extend_wp_search_results;
global $search_title;
global $search_default_img;
global $extend_wp_search_action;

if (isset($_REQUEST['searchpage'])) {
  if (in_array($_REQUEST['searchpage'], extend_wp_search_pages())) {
    $extend_wp_search_action = false;
  }
}
?>
<div class="results-title"><?php echo $search_title; ?></div>
<?php
if (empty($extend_wp_search_results)) {
?>
  <div id="results-empty">
    <?php
    echo __('Unfortunately, there are no results for your search. Please try once more with different criteria.', 'mtv-search');
    ?>
  </div>
<?php
  return;
}
$search_default_img = get_option('extend_wp_search_img_id') ?: '';
?>
<div class="results-wrapper">
  <?php
  foreach ($extend_wp_search_results as $post) {
    global $result_post;
    $result_post = $post;
    echo extend_wp_search_template_part('result.php');
  }
  ?>
</div>
<?php

if ($extend_wp_search_action) {
  echo extend_wp_search_template_part('more_results.php');
}

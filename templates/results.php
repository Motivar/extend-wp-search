<?php
if (!defined('ABSPATH')) exit;
global $extend_wp_search_params;
global $extend_wp_search_results;
global $search_title;
global $search_default_img;
global $extend_wp_search_action;
if (isset($extend_wp_search_params['searchpage'])) {
  if (in_array($extend_wp_search_params['searchpage'], extend_wp_search_pages())) {
    $extend_wp_search_action = false;
  }

  if (isset($extend_wp_search_params['paged']) && $extend_wp_search_params['paged'] > 1 && $extend_wp_search_params['pagination'] == 'button') {
    foreach ($extend_wp_search_results->posts as $post) {
      global $result_post;
      $result_post = $post;
      echo extend_wp_search_template_part('result.php');
    }
    if (!$extend_wp_search_action && isset($extend_wp_search_params['pagination'])) {
      echo extend_wp_search_template_part('pagination.php');
    }
    return;
  }
}
?>
<div class="results-title"><?php echo $search_title; ?></div>
<?php
if (empty($extend_wp_search_results->posts)) {
?>
<div id="results-empty">
 <?php
    echo __('Unfortunately, there are no results for your search. Please try once more with different criteria.', 'extend-wp-search');
    ?>
</div>
<?php
  return;
}
$search_default_img = get_option('extend_wp_search_img_id') ?: '';
?>
<div class="results-wrapper">
 <?php
  foreach ($extend_wp_search_results->posts as $post) {
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

if (!$extend_wp_search_action && isset($extend_wp_search_params['pagination'])) {

  echo extend_wp_search_template_part('pagination.php');
}
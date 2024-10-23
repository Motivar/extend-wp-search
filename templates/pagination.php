<?php
if (!defined('ABSPATH')) exit;
global $extend_wp_search_params;
global $extend_wp_search_results;
global $ewp_config;
$current_page = max(1, isset($extend_wp_search_results->query_vars['paged']) ? $extend_wp_search_results->query_vars['paged'] : 1);
?>
<div class="ewps-pagination">
 <?php
  switch ($extend_wp_search_params['pagination']) {
    case 'button':
      if ($current_page < $extend_wp_search_results->max_num_pages) {
        echo '<button class="ewps-load-more" data-page="' . $current_page . '" data-max-pages="' . $extend_wp_search_results->max_num_pages . '">' . __('More', 'extend-wp-search') . '</button>';
      }
      break;
    default:
      echo paginate_links(array(
        'format' => 'page/%#%/',
        'current' => $current_page,
        'total' => $extend_wp_search_results->max_num_pages,
        'prev_text' => __('« prev', 'extend-wp-search'),
        'next_text' => __('next »', 'extend-wp-search'),
        'show_all' => true
      ));
      break;
  }
  ?></div>
<?php

namespace EWP_Search;

if (!defined('ABSPATH')) {
 exit;
}

class Setup
{
 public function __construct()
 {
  add_action('rest_api_init', array($this, 'extend_wp_search_rest_endpoints'));
  add_filter('awm_add_options_boxes_filter', array($this, 'extend_wp_search_settings'), 100);
  add_action('init', array($this, 'registerScripts'), 10);
  add_action('wp', array($this, 'check_page'));
  add_filter('body_class', array($this, 'extend_wp_search_page_class'));
 }

 public function extend_wp_search_page_class($classes)
 {
  if (in_array(get_the_ID(), extend_wp_search_pages())) {
   $classes[] = 'mtv-search-page-results';
  }
  return $classes;
 }





 public function check_page()
 {
  $pages = array();
  $all = false;
  $include = get_option('extend_wp_search_include_script') ?: '';/* pages to include script */
  if (empty($include)) {
   $all = true;
  }
  if (!empty($include)) {
   $pages = explode(',', $include);
  }
  $extra_pages = extend_wp_search_pages();
  foreach ($extra_pages as $page) {
   $pages[] = $page;
  }
  if ($all || in_array(get_the_ID(), $pages)) {
   add_action('wp_enqueue_scripts', array($this, 'addScripts'), 100);
   add_action('wp_footer', array($this, 'loading_effect'));
   add_action('wp_body_open', array($this, 'extend_wp_search_add_hidden_divs'), 100);
  }
 }

 /**
  * register styles and script for tippy
  */
 public function registerScripts()
 {

  wp_register_script('mtv-search-script', extend_wp_search_url . 'assets/js/extend_wp_search.js', array(), false, 1);
  wp_register_style('mtv-search-style', extend_wp_search_url . 'assets/css/full-screen.min.css', false, '1.0.0');
 }

 /**
  * add scripts to run for admin and frontened
  */
 public function addScripts()
 {
  wp_enqueue_style('mtv-search-style');
  wp_localize_script('mtv-search-script', 'extend_wp_search_vars', apply_filters('extend_wp_search_vars_filter', array('trigger' => get_option('extend_wp_search_trigger_element'))));
  wp_enqueue_script('mtv-search-script');
 }



 public function extend_wp_search_add_hidden_divs()
 {
  global $post;
  $pages = extend_wp_search_pages();
  if (in_array($post->ID, $pages)) {
   return;
  }
  echo extend_wp_search_template_part('search-full-screen.php');
 }


 public function extend_wp_search_settings($options)
 {
  $options['extend_wp_search_settings'] = array(
   'parent' => 'extend-wp',
   'title' => __('Search Interface', 'extend-wp-search'),
   'callback' => 'extend_wp_search_admin_settings',
   'explanation' => __('Here you configure all the settings regarding the search functionallity. It is <b>important</b> to create a page with the shortocode [extend_wp_search results="1"] and declare it below.')
  );
  return $options;
 }


 /**
  * 
  */
 public function loading_effect()
 {
  echo extend_wp_search_template_part('loading.php');
 }
 /**
  * register rest endpoints
  */
 public function extend_wp_search_rest_endpoints()
 {
  /*check here*/
  register_rest_route('extend-wp-search', '/search', array(
   'methods' => 'GET',
   'callback' => array($this, 'extend_wp_search_results'),
  ));
 }

 /**
  * make the query and gather the results
  */
 public function extend_wp_search_results($request)
 {
  $response = '';
  if (empty($request)) {
   return;
  }
  $params = $request->get_params();
  if (empty($params)) {
   return;
  }
  global $extend_wp_search_results;
  global $extend_wp_search_action;
  global $extend_wp_search_params;

  $extend_wp_search_params = $params;
  $extend_wp_search_action = true;
  $extend_wp_search_results = $this->construct_post_query();
  $response = extend_wp_search_template_part('results.php');
  return rest_ensure_response(new WP_REST_Response($response), 200);
 }
 /**
  * construct the query based on the request
  */

 public function construct_post_query()
 {
  global $extend_wp_search_params;
  $title = array();
  $tax_query = $meta_query = array();
  $default_order = get_option('extend_wp_search_default_order') ?: 'publish_date';
  $default_order_type = get_option('extend_wp_search_default_order_type') ?: 'DESC';
  $args = array(
   'post_status' => 'publish',
   'suppress_filters' => false,
   'post_type' => explode(',', $extend_wp_search_params['post_types']),
   'numberposts' => $extend_wp_search_params['numberposts'],
   'orderby' => $default_order,
   'order' => $default_order_type
  );
  if (isset($extend_wp_search_params['searchtext'])) {
   $args['s'] = sanitize_text_field($extend_wp_search_params['searchtext']);
   $title[] = sprintf(__('Results for %s', 'extend-wp-search'), '<span class="searched">"' . sanitize_text_field($extend_wp_search_params['searchtext']) . '"</span>');
  }



  if (isset($extend_wp_search_params['awm_custom_meta'])) {
   $taxonomies = $extend_wp_search_params['awm_custom_meta'];
   foreach ($taxonomies as $key) {
    if (isset($extend_wp_search_params[$key]) && !empty($extend_wp_search_params[$key])) {
     $tax_query[] =
      array(
       'taxonomy' => $key,
       'terms' => $extend_wp_search_params[$key],
       'field' => 'id',
       'operator' => 'IN',
      );
     $termTitle = array();
     if (isset($extend_wp_search_params['searchtext'])) {
      $title[] = __('at', 'extend-wp-search');
     }
     foreach ($extend_wp_search_params[$key] as $term) {
      $termData = get_term($term, $key);

      if ($termData) {
       $termTitle[] = $termData->name;
      }
     }
     $title[] = implode(', ', $termTitle);
    }
   }
  }
  if (!empty($tax_query)) {
   $tax_query['relation'] = 'OR';
   $args['tax_query'] = $tax_query;
  }

  if (isset($extend_wp_search_params['extend_wp_search_year']) && !empty($extend_wp_search_params['extend_wp_search_year'])) {
   $args['date_query'] = array(
    'relation' => 'OR',
   );
   $years = array();
   foreach ($extend_wp_search_params['extend_wp_search_year'] as $year) {
    $args['date_query'][] = array('year' => $year);
    $years[] = $year;
   }
   $title[] = sprintf(__('for the year(s) %s', 'extend-wp-search'), implode(', ', $years));
  }
  global $search_title;
  $search_title = implode(' ', $title);
  return get_posts($args);
 }
}

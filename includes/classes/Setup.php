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
  add_filter('awm_add_customizer_settings_filter', [$this, 'register']);
  add_action('wp_enqueue_scripts', [$this, 'inline_styles'], 20);
  add_shortcode('extend_wp_search', array($this, 'extend_wp_search_shortcode'));
 }


 public function extend_wp_search_shortcode($atts)
 {
  $variables = shortcode_atts(array(
   'method' => 'get',
   'clean_view' => true,
   'post_types' => array(),
   'taxonomies' => array(),
   'action' => '',
   'years' => array(),
   'results' => 0,
   'placeholder' => __('Search', 'motivar-search'),
   'filter_icon' => extend_wp_search_url . 'assets/img/filter.svg',
   'close_icon' => extend_wp_search_url . 'assets/img/close.svg',
   'search_icon' => extend_wp_search_url . 'assets/img/search.svg',
  ), $atts);
  global $post;
  $pages = extend_wp_search_pages();
  if ($post && isset($post->ID) && in_array($post->ID, $pages)) {
   $variables['clean_view'] = false;
  }
  $variables['action'] = get_permalink(extend_wp_search_get_translation(get_option('extend_wp_search_search_results_page')));
  $variables['method'] = 'post';
  $variables['main-class'] = array();
  $variables['main-class'][] = $variables['results'] == 1 ? 'show-filter' : '';
  $variables['main-class'][] = $variables['clean_view'] == 1 ? 'show-close' : '';
  if (empty($variables['post_types'])) {
   $variables['post_types'] = get_option('extend_wp_search_post_types') ?: array();
  }
  if (empty($variables['taxonomies'])) {
   $variables['taxonomies'] = get_option('extend_wp_search_taxonomies') ?: array();
  }
  $variables['exclude_ids'] = get_option('extend_wp_search_exclude_taxonomies') ?: array();
  if (empty($variables['years'])) {
   $variables['years'] = get_option('extend_wp_search_years') ?: array();
  }


  global $extend_wp_search_parameters;
  $extend_wp_search_parameters = $variables;
  return extend_wp_search_template_part('body.php');
 }


 /**
  * Customizer & frontend custom color variables.
  *
  * @return void
  */
 public function inline_styles()
 {
  wp_add_inline_style('ewps-search-style', $this->generate_inline_style());
 }
 public  function register($boxes)
 {
  $boxes['filox'] =
   array(
    'title' => __('EWP Search Interface', 'filox'),
    'priority' => 100,
    'sections' => array(
     'flx_colors' => array(
      'title' => __('Colors', 'filox'),
      'description' => __('Set colors/font sizes etc ', 'filox'),
      'priority' => 10,
      'capability' => 'edit_theme_options',
      'library' => $this->customizer_settings()
     )
    )
   );
  return $boxes;
 }

 public function customizer_settings()
 {
  return array(
   'main-color' => array(
    'label'   => __('Main Color', 'filox'),
    'case' => 'input',
    'type' => 'color',
    'attributes' => array('customizer_default' => '#002642', 'customizer_sanitize_callback' => 'sanitize_hex_color', 'customizer_transport' => 'postMessage')
   ),
   'secondary-color' => array(
    'label'   => __('Secondary Color', 'filox'),
    'case' => 'input',
    'type' => 'color',
    'attributes' => array('customizer_default' => '#840032', 'customizer_sanitize_callback' => 'sanitize_hex_color', 'customizer_transport' => 'postMessage')
   ),
   'third-color' => array(
    'label'   => __('Third Color', 'filox'),
    'case' => 'input',
    'type' => 'color',
    'attributes' => array('customizer_default' => '#E59500', 'customizer_sanitize_callback' => 'sanitize_hex_color', 'customizer_transport' => 'postMessage')
   ),
   'fourth-color' => array(
    'label'   => __('Fourth Color', 'filox'),
    'case' => 'input',
    'type' => 'color',
    'attributes' => array('customizer_default' => '#ffffff', 'customizer_sanitize_callback' => 'sanitize_hex_color', 'customizer_transport' => 'postMessage')
   ),
   'fifth-color' => array(
    'label'   => __('Fifth Color', 'filox'),
    'case' => 'input',
    'type' => 'color',
    'attributes' => array('customizer_default' => '#F2F4F5', 'customizer_sanitize_callback' => 'sanitize_hex_color', 'customizer_transport' => 'postMessage')
   ),
  );
 }

 public function extend_wp_search_page_class($classes)
 {
  if (in_array(get_the_ID(), extend_wp_search_pages())) {
   $classes[] = 'ewps-search-page-results';
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
  $version = 0.2;
  wp_register_script('ewps-search-script', extend_wp_search_url . 'assets/js/extend_wp_search.js', array(), false, $version);
  wp_register_style('ewps-search-style', extend_wp_search_url . 'assets/css/full-screen.min.css', false, $version);
 }

 /**
  * add scripts to run for admin and frontened
  */
 public function addScripts()
 {

  wp_enqueue_style('ewps-search-style');
  wp_localize_script('ewps-search-script', 'extend_wp_search_vars', apply_filters('extend_wp_search_vars_filter', array('trigger' => get_option('extend_wp_search_trigger_element'))));
  wp_enqueue_script('ewps-search-script');
 }

 public function generate_inline_style()
 {
  $all_options = $this->customizer_settings();
  $properties = array();
  foreach ($all_options as $key => $value) {
   $value = get_theme_mod($key, $value['attributes']['customizer_default']);
   $properties[] = '--ewps-search-' . $key . ':' . $value . ';';
  }
  return ':root{' . implode('', $properties) . '}';
 }


 public function extend_wp_search_add_hidden_divs()
 {
  global $post;
  $pages = extend_wp_search_pages();
  if ($post && isset($post->ID) &&  in_array($post->ID, $pages)) {
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
  return rest_ensure_response(new \WP_REST_Response($response), 200);
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
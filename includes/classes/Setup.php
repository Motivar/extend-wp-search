<?php

namespace EWP_Search;

// Ensure the script is not accessed directly outside of WordPress
if (!defined('ABSPATH')) {
 exit;
}

// Main class for setting up the Extend WP Search functionality
class Setup
{
 // Constructor method where various WordPress hooks are added
 public function __construct()
 {
  // Register custom REST API endpoints
  add_action('rest_api_init', array($this, 'extend_wp_search_rest_endpoints'));

  // Add options to the admin settings box
  add_filter('awm_add_options_boxes_filter', array($this, 'extend_wp_search_settings'), 100);

  // Register custom scripts and styles
  add_action('init', array($this, 'registerScripts'), 10);

  // Check which pages the plugin should operate on
  add_action('wp', array($this, 'check_page'));

  // Add a custom class to the body element on specific pages
  add_filter('body_class', array($this, 'extend_wp_search_page_class'));

  // Register custom customizer settings
  add_filter('awm_add_customizer_settings_filter', [$this, 'register']);

  // Inject inline styles dynamically generated from the customizer
  add_action('wp_enqueue_scripts', [$this, 'inline_styles'], 20);

  // Register a shortcode for the search interface
  add_shortcode('extend_wp_search', array($this, 'extend_wp_search_shortcode'));

  // Add Gutenberg block related to the search interface
  add_filter('ewp_gutenburg_blocks_filter', [$this, 'extend_wp_search_block'], 10);
 }

 /**
  * Add custom Gutenberg block for the search interface
  */
 public function extend_wp_search_block($boxes)
 {
  // Define the block attributes and meta data
  $boxes += array(
   'extend_wp_search_block' => array(
    'namespace' => 'ewp-block',
    'name' => 'extend_wp_search_block',
    'title' => __('Extend WP Search Interface', 'extend-wp'),
    'version' => 1712916074,
    'style' => array('ewps-search-style'),
    'script' => array('ewps-search-script'),
    'dependencies' => array(),
    'render_callback' => [
     $this,
     'extend_wp_search_shortcode'
    ],
    'attributes' => array(
     // Block attributes for search interface customization
     'post_types' => array(
      'key' => 'post_types',
      'label' => __('Post Types', 'extend-wp'),
      'case' => 'input',
      'type' => 'text',
      'class' => array(),
      'explanation' => __('Separate post types slugs with a comma.', 'extend-wp'),
     ),
     'taxonomies' => array(
      'key' => 'taxonomy',
      'label' => __('Taxonomy', 'extend-wp'),
      'case' => 'input',
      'type' => 'text',
      'class' => array(),
      'explanation' => __('Separate taxonomies slugs with a comma.', 'extend-wp'),
     ),
     'results' => array(
      'key' => 'results',
      'label' => __('Show Results', 'extend-wp'),
      'case' => 'input',
      'type' => 'checkbox',
      'class' => array(),
      'explanation' => __('Show search results on the same page.', 'extend-wp'),
     ),
    ),
    'category' => 'design', // Gutenberg block category
   )
  );
  return $boxes;
 }

 /**
  * Shortcode handler for rendering the search interface
  */
 public function extend_wp_search_shortcode($atts)
 {
  // Set default attributes and merge with provided ones
  $variables = shortcode_atts(array(
   'clean_view' => true,
   'post_types' => array(),
   'taxonomies' => array(),
   'action' => '',
   'results' => 0,
   'placeholder' => __('Search', 'motivar-search'),
   'filter_icon' => extend_wp_search_url . 'assets/img/filter.svg',
   'close_icon' => extend_wp_search_url . 'assets/img/close.svg',
   'search_icon' => extend_wp_search_url . 'assets/img/search.svg',
  ), $atts);

  // Handle specific conditions based on the post and search pages
  global $post;
  $pages = extend_wp_search_pages();
  if ($post && isset($post->ID) && in_array($post->ID, $pages)) {
   $variables['clean_view'] = false;
  }

  // Set the search form action URL
  $variables['action'] = get_permalink(extend_wp_search_get_translation(get_option('extend_wp_search_search_results_page')));
  $variables['method'] = 'post';

  // Handle class attributes and search parameters
  $variables['main-class'] = array();
  $variables['main-class'][] = $variables['results'] == 1 ? 'show-filter' : '';
  $variables['main-class'][] = $variables['clean_view'] == 1 ? 'show-close' : '';

  // Set default post types and taxonomies if not provided
  if (empty($variables['post_types'])) {
   $variables['post_types'] = get_option('extend_wp_search_post_types') ?: array();
  }
  if (empty($variables['taxonomies'])) {
   $variables['taxonomies'] = get_option('extend_wp_search_taxonomies') ?: array();
  }

  // Set other variables for exclusion and years
  $variables['exclude_ids'] = get_option('extend_wp_search_exclude_taxonomies') ?: array();
  if (empty($variables['years'])) {
   $variables['years'] = get_option('extend_wp_search_years') ?: array();
  }

  // Pass the search parameters globally
  global $extend_wp_search_parameters;
  $extend_wp_search_parameters = $variables;
  // Render the search interface template
  return extend_wp_search_template_part('body.php');
 }

 /**
  * Add inline styles dynamically generated from customizer settings
  */
 public function inline_styles()
 {
  wp_add_inline_style('ewps-search-style', $this->generate_inline_style());
 }

 /**
  * Register customizer settings related to colors
  */
 public function register($boxes)
 {
  $boxes['ewps'] = array(
   'title' => __('EWP Search Interface', 'extend-wp-search'),
   'priority' => 100,
   'sections' => array(
    'flx_colors' => array(
     'title' => __('Colors', 'extend-wp-search'),
     'description' => __('Set colors/font sizes etc ', 'extend-wp-search'),
     'priority' => 10,
     'library' => $this->customizer_settings() // Reference to customizer settings
    )
   )
  );
  return $boxes;
 }

 /**
  * Define customizer settings such as color options
  */
 public function customizer_settings()
 {
  return array(
   'main-color' => array(
    'label' => __('Main Color', 'extend-wp-search'),
    'case' => 'input',
    'type' => 'color',
    'attributes' => array('customizer_default' => '#002642', 'customizer_sanitize_callback' => 'sanitize_hex_color')
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

 /**
  * Add custom class to body on certain pages
  */
 public function extend_wp_search_page_class($classes)
 {
  if (in_array(get_the_ID(), extend_wp_search_pages())) {
   $classes[] = 'ewps-search-page-results';
  }
  return $classes;
 }

 /**
  * Check which pages should include search scripts and functionality
  */
 public function check_page()
 {
  $pages = array();
  $all = false;
  $include = get_option('extend_wp_search_include_script') ?: ''; // Pages to include script

  if (empty($include)) {
   $all = true;
  }
  if (!empty($include)) {
   $pages = explode(',', $include);
  }

  // Extend pages from additional settings
  $extra_pages = extend_wp_search_pages();
  foreach ($extra_pages as $page) {
   $pages[] = $page;
  }

  // Add the necessary scripts if the page is included
  if ($all || in_array(get_the_ID(), $pages)) {
   add_action('wp_enqueue_scripts', array($this, 'addScripts'), 100);
   add_action('wp_footer', array($this, 'loading_effect'));
   add_action('wp_body_open', array($this, 'extend_wp_search_add_hidden_divs'), 100);
  }
 }

 /**
  * Register custom styles and scripts
  */
 public function registerScripts()
 {
  $version = 0.2;
  wp_register_script('ewps-search-script', extend_wp_search_url . 'assets/js/extend_wp_search.js', array('awm-global-script'), false, $version);
  wp_register_style('ewps-search-style', extend_wp_search_url . 'assets/css/full-screen.min.css', false, $version);
 }

 /**
  * Add scripts to both frontend and admin
  */
 public function addScripts()
 {
  wp_enqueue_style('ewps-search-style');
  wp_localize_script('ewps-search-script', 'extend_wp_search_vars', apply_filters('extend_wp_search_vars_filter', array('trigger' => get_option('extend_wp_search_trigger_element'))));
  wp_enqueue_script('ewps-search-script');
 }

 /**
  * Generate dynamic inline styles from customizer settings
  */
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

 /**
  * Add hidden divs required for search functionality
  */
 public function extend_wp_search_add_hidden_divs()
 {
  global $post;
  $pages = extend_wp_search_pages();
  if ($post && isset($post->ID) && in_array($post->ID, $pages)) {
   return;
  }
  echo extend_wp_search_template_part('search-full-screen.php');
 }

 /**
  * Add settings for search interface in the admin panel
  */
 public function extend_wp_search_settings($options)
 {
  $options['extend_wp_search_settings'] = array(
   'parent' => 'extend-wp',
   'title' => __('Search Interface', 'extend-wp-search'),
   'callback' => 'extend_wp_search_admin_settings',
   'explanation' => __('Configure all the settings regarding the search functionality.')
  );
  return $options;
 }

 /**
  * Display loading effect when fetching search results
  */
 public function loading_effect()
 {
  echo extend_wp_search_template_part('loading.php');
 }

 /**
  * Register custom REST API endpoints for searching
  */
 public function extend_wp_search_rest_endpoints()
 {
  register_rest_route('extend-wp-search', '/search', array(
   'methods' => 'GET',
   'callback' => array($this, 'extend_wp_search_results'),
  ));
 }

 /**
  * Handle search results fetched via REST API
  */
 public function extend_wp_search_results($request)
 {
  $response = '';
  if (empty($request)) {
   return;
  }

  // Get the request parameters
  $params = $request->get_params();
  if (empty($params)) {
   return;
  }

  global $extend_wp_search_results;
  global $extend_wp_search_action;
  global $extend_wp_search_params;

  $extend_wp_search_params = $params;
  $extend_wp_search_action = true;

  // Perform the post query and render the results
  $extend_wp_search_results = $this->construct_post_query();
  $response = extend_wp_search_template_part('results.php');

  return rest_ensure_response(new \WP_REST_Response($response), 200);
 }

 /**
  * Construct the post query based on search parameters
  */
 public function construct_post_query()
 {
  global $extend_wp_search_params;
  $title = array();
  $tax_query = $meta_query = array();
  $default_order = get_option('extend_wp_search_default_order') ?: 'publish_date';
  $default_order_type = get_option('extend_wp_search_default_order_type') ?: 'DESC';

  // Basic query arguments for fetching posts
  $args = array(
   'post_status' => 'publish',
   'suppress_filters' => false,
   'post_type' => explode(',', $extend_wp_search_params['post_types']),
   'numberposts' => $extend_wp_search_params['numberposts'],
   'orderby' => $default_order,
   'order' => $default_order_type
  );

  // Add search term if provided
  if (isset($extend_wp_search_params['searchtext'])) {
   $args['s'] = sanitize_text_field($extend_wp_search_params['searchtext']);
   $title[] = sprintf(__('Results for %s', 'extend-wp-search'), '<span class="searched">"' . sanitize_text_field($extend_wp_search_params['searchtext']) . '"</span>');
  }

  // Add taxonomy filters if provided
  if (isset($extend_wp_search_params['awm_custom_meta'])) {
   $taxonomies = $extend_wp_search_params['awm_custom_meta'];
   foreach ($taxonomies as $key) {
    if (isset($extend_wp_search_params[$key]) && !empty($extend_wp_search_params[$key])) {
     $tax_query[] = array(
      'taxonomy' => $key,
      'terms' => $extend_wp_search_params[$key],
      'field' => 'id',
      'operator' => 'IN',
     );
    }
   }
  }

  // Add taxonomy query to args if it exists
  if (!empty($tax_query)) {
   $tax_query['relation'] = 'OR';
   $args['tax_query'] = $tax_query;
  }

  // Add date filtering based on years
  if (isset($extend_wp_search_params['extend_wp_search_year']) && !empty($extend_wp_search_params['extend_wp_search_year'])) {
   $args['date_query'] = array('relation' => 'OR');
   foreach ($extend_wp_search_params['extend_wp_search_year'] as $year) {
    $args['date_query'][] = array('year' => $year);
   }
  }

  global $search_title;
  $search_title = implode(' ', $title);

  // Return the constructed query
  return get_posts($args);
 }
}
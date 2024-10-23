<?php
if (!defined('ABSPATH')) {
 exit;
}

if (!function_exists('extend_wp_search_pages')) {
 function extend_wp_search_pages()
 {
  $pages = (array)get_option('extend_wp_search_search_results_page') ?: array(); /*search result page*/
  if (!empty($pages)) {
   foreach ($pages as $page) {
    $tran_id = extend_wp_search_get_translation($page);
    if ($tran_id != $page) {
     $pages[] = $tran_id;
    }
   }
  }
  return $pages;
 }
}



if (!function_exists('extend_wp_search_admin_settings')) {
 /**
  * set the admin settings
  */
 function extend_wp_search_admin_settings()
 {
  return apply_filters('extend_wp_search_admin_settings_filter', array(
   'extend_wp_search_trigger_element' => array(
    'case' => 'input',
    'type' => 'text',
    'label' => __('Element class/id to trigger search on click', 'extend-wp-search'),
    'explanation' => __('valid query selector like #main,.class', 'extend-wp-search')
   ),
   'extend_wp_search_search_results_page' => array(
    'case' => 'input',
    'type' => 'text',
    'label' => __('Search results page id', 'extend-wp-search'),
    'label_class' => array('awm-needed'),
    'explanation' => __('The page where the results will be shown', 'extend-wp-search')
   ),
   'extend_wp_search_include_script' => array(
    'case' => 'input',
    'type' => 'text',
    'label' => __('Include scripts', 'extend-wp-search'),
    'explanation' => __('Leave empty to include it everywhere, otherwise write the ids of the page, seperated by comma', 'extend-wp-search')
   ),
   'extend_wp_search_img_id' => array(
    'case' => 'image',
    'label' => __('Default featured image', 'extend-wp-search'),
   ),
   'extend_wp_search_post_types' => array(
    'label' => __('Post types to search in', 'extend-wp-search'),
    'case' => 'post_types',
    'attributes' => array('multiple' => true),
    'label_class' => array('awm-needed'),
   ),
   'extend_wp_search_taxonomies' => array(
    'label' => __('Taxonomies to filter', 'extend-wp-search'),
    'case' => 'taxonomies',
    'attributes' => array('multiple' => true),
   ),
   'extend_wp_search_exclude_taxonomies' => array(
    'label' => __('Taxonomies to exlude (ids)', 'extend-wp-search'),
    'case' => 'input',
    'type' => 'text',
   ),
   'extend_wp_search_years' => array(
    'label' => __('Years', 'extend-wp-search'),
    'case' => 'input',
    'type' => 'text',
    'explanation' => __('Leave empty not to show date search. Use comma to separate years', 'extend-wp-search')
   ),
   'extend_wp_search_default_order' => array(
    'label' => __('Default order', 'extend-wp-search'),
    'case' => 'select',
    'removeEmpty' => true,
    'options' => array(
     'publish_date' => array('label' => __('Publish date', 'extend-wp-search')),
     'modified' => array('label' => __('Modified date', 'extend-wp-search')),
     'title' => array('label' => __('Post title', 'extend-wp-search'))
    ),
   ),
   'extend_wp_search_default_order_type' => array(
    'label' => __('Default order type', 'extend-wp-search'),
    'case' => 'select',
    'removeEmpty' => true,
    'options' => array(
     'ASC' => array('label' => __('ASC', 'extend-wp-search')),
     'DESC' => array('label' => __('DESC', 'extend-wp-search')),

    ),
   ),
   'extend_wp_search_default_limit' => array(
    'label' => __('Default results limit', 'extend-wp-search'),
    'case' => 'input',
    'type' => 'number'
   ),
   'extend_wp_search_default_pagination' => array(
    'label' => __('Pagination types', 'extend-wp-search'),
    'case' => 'select',
    'removeEmpty' => true,
    'options' => array(
     'numbers' => array('label' => __('Numbers', 'extend-wp-search')),
     'button' => array('label' => __('Button', 'extend-wp-search')),

    ),
   ),

  ));
 }
}




if (!function_exists('extend_wp_search_template_part')) {
 /**
  * this function is used to get parts for the template of the project
  * @param string $file the full file path to get
  */
 function extend_wp_search_template_part($file)
 {
  $active_theme = apply_filters('ewps_active_theme_folder_filter', get_stylesheet_directory());
  $template_over_write = $active_theme . '/templates/ewp-search/' . $file;
  $file = file_exists($template_over_write) ? $template_over_write : extend_wp_search_path . 'templates/' . $file;
  ob_start();
  include $file;
  $content = ob_get_clean();
  return apply_filters('extend_wp_search_template_part_filter', $content, $file);
 }
}


if (!function_exists('extend_wp_search_get_translation')) {
 /**
  * this functions get the translations of the objects
  */
 function extend_wp_search_get_translation($id)
 {
  if (function_exists('icl_object_id')) {
   global $sitepress;
   $postType = get_post_type($id);
   $id = (int) icl_object_id($id, $postType, false, ICL_LANGUAGE_CODE);
  }
  return $id;
 }
}


if (!function_exists('extend_wp_search_seach_limit_text')) {
 /**
  * limits the text of a certain element
  * @param string $text 
  * @param int $limit how many words
  * @param boolean $strip  strip tags
  */
 function extend_wp_search_seach_limit_text($text, $limit = 10, $strip = false)
 {
  if ($limit != 0) {
   if ($strip) {
    $text = strip_tags($text, '<br>');
    $text = strip_shortcodes($text);
    $text = str_replace(array("\n", "\r", "\t"), ' ', $text);
    $text = str_replace('&nbsp;', ' ', preg_replace('#<[^>]+>#', ' ', $text));
   }
   $words = explode(' ', $text);
   $c = count($words);
   if ($c > $limit) {
    $textt = array();
    for ($i = 0; $i < $limit; ++$i) {
     $textt[] = $words[$i];
    }

    $text = implode(' ', $textt);
    $text .= '...';
   }
  }

  return $text;
 }
}


if (!function_exists('extend_wp_search_hidden_inputs')) {
 function extend_wp_search_hidden_inputs($parameters)
 {
  $inputs = array();
  $vars = array(
   'numberposts' => $parameters['number'],
   'lang' => function_exists('icl_object_id') ? ICL_LANGUAGE_CODE : '',
   'post_types' => is_array($parameters['post_types']) ? implode(',', $parameters['post_types']) : $parameters['post_types'],
   'extend_wp_search' => 1
  );

  foreach ($vars as $key => $value) {
   $inputs[$key] = array(
    'case' => 'input',
    'type' => 'hidden',
    'attributes' => array('value' => $value, 'exclude_meta' => true,)
   );
  }

  return $inputs;
 }
}


if (!function_exists('extend_wp_search_prepare_filters')) {
 function extend_wp_search_prepare_filters($parameters, $option)
 {
  $arrs = array();
  $exclude_ids = array();
  if (isset($parameters['taxonomies']) && !empty($parameters['taxonomies'])) {
   if (!empty($parameters['exclude_ids'])) {
    $exclude_ids = explode(',', $parameters['exclude_ids']);
   }

   foreach ($parameters['taxonomies'] as $taxonomy) {
    $tax = get_taxonomy($taxonomy);
    $arrs[$taxonomy] = array(
     'label' => $tax->label,
     'case' => 'term',
     'taxonomy' => $taxonomy,
     'args' => array('hide_empty' => true, 'exclude' => $exclude_ids),
     'view' => 'checkbox_multiple',
     'attributes' => array('value' => array($option)),
    );
   }
  }
  if (!empty($parameters['years'])) {
   $years = explode(',', $parameters['years']);
   $labels = array();
   foreach ($years as $year) {
    $labels[$year] = array('label' => $year);
   }
   $arrs['extend_wp_search_year'] = array(
    'label' => __('Year', 'motivar'),
    'case' => 'checkbox_multiple',
    'options' => $labels,
    'attributes' => array('exclude_meta' => true),
   );
  }
  return $arrs;
 }
}



/**
 * Function to safely load and embed SVG content into HTML
 *
 * @param string $svg_file_path Path to the SVG file.
 * @return string Sanitized SVG content.
 */
function ewps_inject_svg($svg_file_path)
{
 // Check if the file exists and is readable
 if (file_exists($svg_file_path) && is_readable($svg_file_path)) {
  // Get the SVG content
  $svg_content = file_get_contents($svg_file_path);

  // Sanitize the SVG (ensure no harmful code is injected)
  $allowed_tags = ['svg', 'path', 'rect', 'circle', 'line', 'polygon', 'polyline', 'g']; // Add more tags as necessary
  $allowed_attributes = ['xmlns', 'viewBox', 'fill', 'stroke', 'd', 'id', 'class', 'line', 'stroke-linejoin', 'stroke-linecap', 'stroke-width', 'x1', 'x2', 'y1', 'y2']; // Add more attributes as necessary

  // Remove any script or foreign objects inside the SVG
  $svg_content = wp_kses($svg_content, [
   'svg' => array_flip($allowed_attributes),
   'path' => array_flip($allowed_attributes),
   'line' => array_flip($allowed_attributes),
   'rect' => array_flip($allowed_attributes),
   'circle' => array_flip($allowed_attributes),
   // Add other tags and attributes here as necessary
  ]);

  return $svg_content; // Return the sanitized SVG content
 }

 return ''; // Return empty string if file does not exist
}

/**
 * Function to display an image or inline SVG depending on the file type
 *
 * @param string $image_url The URL of the image.
 * @return string HTML output with either the inline SVG or an img tag.
 */
function ewps_display_image_or_svg($image_url)
{
 // Check if the URL is pointing to an SVG
 if (strpos($image_url, '.svg') !== false) {
  // Get the path to the SVG file
  $svg_file_path = str_replace(home_url(), ABSPATH, $image_url);

  // Inject the SVG inline
  return ewps_inject_svg($svg_file_path);
 }

 // Return the image tag for non-SVG images
 return '<img src="' . esc_url($image_url) . '" alt="Icon"/>';
}
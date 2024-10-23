<?php
if (!defined('ABSPATH')) exit;
global $extend_wp_search_parameters;
global $extend_wp_search_archive_option;
global $post;

$searchtext = isset($_REQUEST['searchtext']) ? sanitize_text_field($_REQUEST['searchtext']) : '';
$autotrigger = $searchtext != '' ? 1 : 0;
$limit = get_option('extend_wp_search_default_limit') ?: 10;
$number = $autotrigger == 1 ? -1 : $limit;
$pages = extend_wp_search_pages();
$extend_wp_search_parameters['number'] = $number;
$hidden_inputs = extend_wp_search_hidden_inputs($extend_wp_search_parameters);
$extend_wp_search_parameters['filters'] = extend_wp_search_prepare_filters($extend_wp_search_parameters, $extend_wp_search_archive_option);

if (!empty($extend_wp_search_parameters['filters']) && $extend_wp_search_parameters['show_filters']) {
  $extend_wp_search_parameters['main-class'][] = 'show-filter';
}


?>
<div class="ewps-search-interface" id="<?php echo uniqid(); ?>"
 full-screen="<?php echo $extend_wp_search_parameters['full_screen']; ?>">
 <div id="search_form" data-trigger="<?php echo $autotrigger; ?>">
  <form id="ewps-search-form" method="get" action="<?php echo $extend_wp_search_parameters['action'] ?>">

   <div class="search-bar <?php echo implode(' ', $extend_wp_search_parameters['main-class']); ?>">
    <div class="inputs"><input type="hidden" name="searchpage" value="<?php echo get_the_ID(); ?>" />
     <input type="hidden" name="paged" value="1" /><input type="hidden" name="pagination"
      value="<?php echo get_option('extend_wp_search_default_pagination') ?: 'numbers'; ?>" /><input type="text"
      placeholder="<?php echo $extend_wp_search_parameters['placeholder']; ?>" id="searchtext" name="searchtext"
      class="highlight" value="<?php echo $searchtext; ?>"
      required="true"><?php echo awm_show_content($hidden_inputs); ?>
    </div>
    <div class="search-icon"><span
      id="search-trigger"><?php echo ewps_display_image_or_svg($extend_wp_search_parameters['search_icon']);  ?></span>
    </div>

    <?php
        if (!empty($extend_wp_search_parameters['filters']) && $extend_wp_search_parameters['show_filters']) {
        ?>
    <div class="search-icon"><span
      id="filter-trigger"><?php echo ewps_display_image_or_svg($extend_wp_search_parameters['filter_icon']); ?></span>
    </div>
    <?php
        }
        if ($extend_wp_search_parameters['show_close']) {
        ?>
    <div class="search-icon"><span
      id="close-trigger"><?php echo ewps_display_image_or_svg($extend_wp_search_parameters['close_icon']); ?></span>
    </div>
    <?php
        }
        ?>
   </div>


   <?php
      if ($extend_wp_search_parameters['results'] == 1) {
      ?>
   <div id="search_form_body">
    <div id="search_form_resutls" class="active">
     <div id="search-results">
      <?php echo extend_wp_search_template_part('results.php'); ?>
     </div>
    </div>
    <?php if (!empty($extend_wp_search_parameters['filters'])) {
          ?>
    <div id="search_form_filter">
     <?php echo extend_wp_search_template_part('filters.php'); ?>
    </div>
    <?php
          } ?>

   </div>
   <?php
      }
      ?>

   <input type="submit" id="submit" value="submit">
  </form>
 </div>
</div>
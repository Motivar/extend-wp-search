<?php
if (!defined('ABSPATH')) exit;
global $result_post;
global $search_default_img;
$permalink = get_permalink($result_post->ID);
$featured_img_url = get_the_post_thumbnail_url($result_post->ID, 'medium');
if (empty($featured_img_url) && $search_default_img) {
 $featured_img_url = wp_get_attachment_image_url($search_default_img);
}
?>
<div id="<?php echo $result_post->ID ?>" class="result-wrapper">
 <div class="featured"><a href="<?php echo $permalink; ?>"><img src="<?php echo $featured_img_url; ?>" /></a></div>
 <div class="details">
  <div class="text"><?php echo mtv_seach_limit_text($result_post->post_title . '.<br>' . $result_post->post_content, 20, true); ?></div>
  <div class="more"><span><a href="<?php echo $permalink; ?>"><?php echo __('Read more', 'mtv-search'); ?></a></span></div>
 </div>
</div>
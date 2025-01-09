<?php

/*--------------------------------------------------------------
Advancement-specific page templates
--------------------------------------------------------------*/

add_filter('theme_page_templates', 'advance_add_page_template_to_dropdown');
add_filter('template_include', 'advance_change_page_template', 99);

/**
 * Add page templates.
 *
 * @param  array  $templates  The list of page templates
 * @return array  $templates  The modified list of page templates
 */
function advance_add_page_template_to_dropdown($templates) {
	$templates[ ADVANCE_PATH . 'templates/page-jobs.php'] = __('Job Opportunities', 'uscadvance');
	$templates[ ADVANCE_PATH . 'templates/page-release.php'] = __('Image Release Form', 'uscadvance');
	$templates[ ADVANCE_PATH . 'templates/page-pdf.php'] = __('Image Release PDF', 'uscadvance');
	$templates[ ADVANCE_PATH . 'templates/page-signed.php'] = __('Signed Image Release Form', 'uscadvance');
	return $templates;
}

/**
 * Change the page template to the selected template on the dropdown
 * 
 * @param $template
 * @return mixed
 */
function advance_change_page_template($template) {
	if (is_page()) {
		$meta = get_post_meta(get_the_ID());
		
		if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
			$template = $meta['_wp_page_template'][0];
		}
	}
	return $template;
}

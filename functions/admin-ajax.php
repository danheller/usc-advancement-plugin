<?php

add_action( 'wp_ajax_feature_job', 'uscadvance_feature_job' );
add_action( 'wp_ajax_nopriv_feature_job', 'uscadvance_feature_job' );
add_action( 'wp_ajax_block_job', 'uscadvance_block_job' );
add_action( 'wp_ajax_nopriv_block_job', 'uscadvance_block_job' );
add_action( 'wp_ajax_scrape_keyword', 'uscadvance_scrape_keyword' );
add_action( 'wp_ajax_nopriv_scrape_keyword', 'uscadvance_scrape_keyword' );
add_action( 'wp_ajax_fetch_job', 'uscadvance_fetch_job' );
add_action( 'wp_ajax_nopriv_fetch_job', 'uscadvance_fetch_job' );
add_action( 'wp_ajax_start_scraper', 'uscadvance_start_scraper' );
add_action( 'wp_ajax_nopriv_start_scraper', 'uscadvance_start_scraper' );
add_action( 'wp_ajax_add_release', 'uscadvance_add_release' );
add_action( 'wp_ajax_nopriv_add_release', 'uscadvance_add_release' );


function uscadvance_feature_job() {
	check_ajax_referer( 'uscadvance' );
	$data = array();
	$vars = array('program','feature');
	$featured = get_term_by('slug', 'featured', 'category');
	$featured_id = $featured->term_id;
	foreach($vars as $v) {
		if( isset($_POST[$v]) && $_POST[$v] ) {
			$data[$v] = $_POST[$v];
		}
	}
	if( get_post( intval( $data['program'] ) ) ) {
		$job = get_post( intval( $data['program'] ) );
		$postarr = array(
			'ID' => intval( $data['program'] ),
			'post_category' => array( 0 ),
		);

		if( isset($data['feature']) && $data['feature'] && 1 == intval( $data['feature'] ) ) {
			$postarr['post_category'] = array( $featured_id );
		} else {
			$data['feature'] = 0;
		}
		
		wp_update_post( $postarr );
		echo $data['feature'];
	}
	die();
}

function uscadvance_block_job() {
	check_ajax_referer( 'uscadvance' );
	if( isset($_POST['program']) && $_POST['program'] ) {
		$keyword = get_the_title( intval( $_POST['program'] ) );
	}
	if( $keyword ) {

		$options = advance_get_options();
		$new_options = $options;
		$blacklist = $options['advance-blacklist-array'];
		$blacklist = json_decode( stripslashes( $blacklist ), true );
		$blacklist[] = sanitize_text_field( str_replace('"','&quot;',$keyword) );
		$new_options['advance-blacklist-array'] = json_encode( $blacklist );
		
		advance_update_options( $new_options );
		echo '1';
	} else {
		echo '0';
	}
	die();
}

function uscadvance_scrape_keyword() {
	check_ajax_referer( 'uscadvance' );
	if( isset($_POST['keyword']) && $_POST['keyword'] ) {
		$keyword = $_POST['keyword'];
	}
	if( $keyword ) {

		$options = advance_get_options();
		$new_options = $options;
		$whitelist = $options['advance-keywords-array'];
		$whitelist = json_decode( stripslashes( $whitelist ), true );
		$whitelist[] = sanitize_text_field( str_replace('"','&quot;',$keyword) );
		$new_options['advance-keywords-array'] = json_encode( $whitelist );
		
		advance_update_options( $new_options );
		echo '1';
	} else {
		echo '0';
	}
	die();
}

function uscadvance_fetch_job() {
	check_ajax_referer( 'uscadvance' );
	if( isset($_POST['req']) && $_POST['req'] ) {
		$req = $_POST['req'];
	}
	if( $req ) {
		uscadvance_get_job_req( $req );
		echo '1';
	} else {
		echo '0';
	}
	die();
}

function uscadvance_start_scraper() {
	check_ajax_referer( 'uscadvance' );
	as_schedule_single_action( strtotime('+30 seconds'), 'get_jobs' );
	echo '1';
	die();
}


/**
 * Add new release
 */
function uscadvance_add_release() {
	check_ajax_referer( 'uscadvance' );
	$fields = array('date','signedby','address','city','state','zip','phone','email','signature','parent','parentsignature');
	$opts = advance_get_options();
	$notifications = $opts['advance-release-notifications-array'];
	$notifications = json_decode( stripslashes( $notifications ), true );
	if( is_array( $notifications ) ) {
		$notifications = implode( ',', $notifications );
	}
	$data = array();
	foreach( $fields as $f ) {
		if( isset( $_POST[ $f ] ) ) {
//			if( false != strpos($f,'signature') ) { 
//				$data[$f] = $_POST[ $f ];
//			} else {
				$data[$f] = sanitize_text_field( $_POST[ $f ] );
//			}
		}
	}
	if( count( $data ) ) {
		echo $data['signedby'];
		$postarr = array(
			'post_type' => 'release',
			'post_date' => date('c',time()),
			'post_date_gmt' => date('c',time()),
			'post_title' => 'Image release for '.$data['signedby'],
			'post_status' => 'publish',
			'post_author' => 1,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'meta_input' => $data,
		);
		$newid = wp_insert_post( $postarr );

		$message = '<p>A new signed image release was just submitted. To view the release, you must <a href="'.home_url().'/wp-admin/">sign in to Wordpress</a>.</p>';
		$message .= '<p><a href="'.home_url() . '/signed/?r=' . $newid . '">Image release for '.$data['signedby'].'</a></p>';
		$message .= '<p><a href="'.home_url() . '/wp-admin/options-general.php?page=advance-settings&tab=releases">Manage image releases</a></p>';

		wp_mail(
			$notifications,
			'Image release for ' . $data['signedby'],
			uscadvance_html_email( $message )
		);


		echo 'You have successfully added an image release.';
	} else {
		echo 'no data';
	}

	die();
}



<?php
require_once( WP_PLUGIN_DIR . '/action-scheduler/action-scheduler.php' );

function uscadvance_set_content_type(){
	return "text/html";
}
add_filter( 'wp_mail_content_type','uscadvance_set_content_type' );	

// Please edit the address and name below.
// Change the From address.
add_filter( 'wp_mail_from', function ( $original_email_address ) {
    return 'uscadvance@usc.edu';
} );
 
// Change the From name.
add_filter( 'wp_mail_from_name', function ( $original_email_from ) {
    return 'USC Advancement';
} );

function uscadvance_schedule_build_news_feeds() {
	if ( false === as_has_scheduled_action( 'rebuild_news_feeds' ) ) {
		as_schedule_recurring_action( strtotime('tomorrow'), DAY_IN_SECONDS, 'rebuild_news_feeds' );
	}
//	if ( false === as_has_scheduled_action( 'rebuild_news_topics' ) ) {
//		as_schedule_recurring_action( strtotime('tomorrow'), DAY_IN_SECONDS, 'rebuild_news_topics' );
//	}
}
add_action( 'init', 'uscadvance_schedule_build_news_feeds' );

function uscadvance_rebuild_priority_rss_feeds() {
	$feeds = array(
		'',
		'alumni',
		'arts',
		'athletics',
		'computing',
		'faculty',
		'health',
		'research',
		'scholarships',
		'sustainability',
		'usc-capital-campus',
	);
	foreach( $feeds as $key => $f ) {
		if( $f ) {
			uscadvance_build_feed( $f, 6 );
//			error_log( 'Finished building feed for ' . $f . ' at ' . date( 'Y-m-d g:ia' ) );
		} else {
			uscadvance_build_feed( $f, 50 );
//			error_log( 'Finished building feed for homepage at ' . date( 'Y-m-d g:ia' ) );		
		}
	}
}
add_action( 'rebuild_news_feeds', 'uscadvance_rebuild_priority_rss_feeds' );

/*
function uscadvance_rebuild_topics_json() {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$upload_dir = wp_upload_dir();
	$cats = array();
	$filename = $upload_dir['basedir'] . '/rss/alltopics.json';
	$alltopics = array();
	$topics = array();
	for( $n = 1; $n <= 8; $n++ ) {
		$url = 'https://news.usc.edu/wp-json/wp/v2/tags?per_page=100&page='.$n;
		$topics[$n] = wp_safe_remote_get($url);
		if(!is_wp_error($topics[$n]) && isset($topics[$n]) && isset($topics[$n]['body'])) {
			$topics[$n] = $topics[$n]['body'];
			$topics[$n] = json_decode($topics[$n]);
			if( count( $topics[$n] ) ) {
				$alltopics = array_merge( $alltopics, $topics[$n] );
			}
		}
	}
	foreach ( $alltopics as $key => $t ) {
		$cats[ $t->slug ] = $t->name;
	}
	$data = wp_json_encode( $cats );

	$fp = fopen( $filename, 'w' );
	if ( $fp ) {
		fwrite( $fp, $data );
		fclose( $fp );
	}
}
add_action( 'rebuild_news_topics', 'uscadvance_rebuild_topics_json' );
*/

if( ! function_exists('cmpdate') ) {
	function cmpdate($a, $b) {
		return $b['date'] - $a['date'];
	}
}

/*
if( ! function_exists('news_topics') ) {
	function news_topics() {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload_dir = wp_upload_dir();
		
		$data = file_get_contents( $upload_dir['basedir'] . '/rss/' . $news_topics_cache . '.json' );
		$data = json_decode( $data );
		return $data;
	}
}
*/

if (!function_exists('handleartwork')) {
	function handleartwork($url,$filename,$post_id,$desc) {
		$upload_dir = wp_upload_dir();

		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ){
			// download failed, handle error
			return false;
		}
		$file_array = array();

		// Set variables for storage
		$file_array['name'] = $filename;
		$file_array['tmp_name'] = $tmp;
		$file_array['type'] = 'image/jpeg';

		$args = array(
			's' => str_replace('featured image for ','',$desc),
			'posts_per_page' => 1,
			'order' => 'desc',
			'fields' => 'ids',
		);
		$matching = get_posts($args);
		if( isset( $matching[0] ) ) {
			$post_id = $matching[0];
		}


		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink
		if ( is_wp_error($id) ) {
			@unlink($file_array['tmp_name']);
			return false;
		} else {
			update_post_meta( $post_id, '_thumbnail_id', $id );
			update_post_meta( $id, '_wp_attachment_image_alt', $desc );
			return $upload_dir['baseurl'].'/'.get_the_date('Y/m',$post_id).'/'.$filename;
		}
	}
}

if( ! function_exists('uscadvance_build_feed') ) {
	function uscadvance_build_feed( $topics, $count = 100 ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$upload_dir = wp_upload_dir();
		$cachename = 'news';
		$options = get_option('uscadvance_homepage_news');
		if( $topics ) {
			$cachename .= '-'.$topics;
		}

		$tags = get_terms( array(
			'taxonomy' => 'post_tag',
			'hide_empty' => false,
		) );

		$categories = get_terms( array(
			'taxonomy' => 'category',
			'hide_empty' => false,
		) );

/*		
		if( 'diversity' == $topics ) {
			$topics = 'diversity-equity-and-inclusion';
		} else if( 'arts' == $topics ) {
			$topics = 'cinema|cinematic-arts|dance|dramatic-arts|iovine-and-young-academy|museums|music|television|theatre|visual-arts';
		}
*/

		$filename = $upload_dir['basedir'] . '/rss/' . $cachename . '.rss';
//		if ( ! file_exists( $filename ) ) {
			ob_start();
			$options = get_option('uscadvance_homepage_news');
			$urls = array();
			$stories = array();
			$suppress = explode(PHP_EOL,$options['suppress']);
//			$topic_options = news_topics();
			if( $topics ) {
				$topics = explode('|',$topics);
				foreach( $topics as $topic ) {
					if( 'health' == $topic || 'arts' == $topic || 'athletics' == $topic ) {
						$urls[] = 'https://today.usc.edu/category/'.$topic.'/feed/';
					} else {
						$urls[] = 'https://today.usc.edu/tag/'.$topic.'/feed/';
					}
				}
			} else if( $options['topics'] ) {
				foreach( $options['topics'] as $topic ) {
					$urls[] = 'https://today.usc.edu/tag/'.$topic.'/feed/';
				}
			}
	
			if( count( $urls ) ) {
				$stories = fetch_feed($urls);
				if( ! is_array( $stories ) ) {
//					$maxitems = $stories->get_item_quantity( 100 ); 
					$stories = $stories->get_items( 0, $count );
				}

				$feed = array();
				$excluded = array();

				foreach ($stories as $node) {

					$item = array();
					$artscats = array('Cinematic Arts', 'Dance', 'Dramatic Arts', 'Iovine and Young Academy', 'Music', 'Visual Arts');
					$computingcats = array('Frontiers of Computing');
					$facrescats = array('Faculty','Research');

					$healthcats = array('Health Care', 'Health Policy', 'Herman Ostrow School of Dentistry', 'Keck School of Medicine', 'Medicine');

					if( $node->get_permalink() ) {
						$add = true;
						if ($node->get_title()) { $item['title'] = $node->get_title(); }
						if ($node->get_description()) {
							$item['desc'] = $node->get_description();
							if( $item['desc'] && false !== strpos( $item['desc'], '<p>The post <a rel="' ) ) {
								$item['desc'] = substr($item['desc'], 0, strpos( $item['desc'], '<p>The post <a rel="' ) );
							}							
						}
						if ($node->get_permalink()) { 
							$item['link'] = $node->get_permalink();
							if( in_array( $item['link'], $excluded ) ) {
								$add = false;
							}
							$excluded[] = $node->get_permalink(); 
						}
						if ($node->get_date()) {
							$item['date'] = $node->get_date('U'); 
						}
						$item['media'] = '';
						if ($node->get_enclosure()) {
							$enclosure = $node->get_enclosure();
							$thumbnail = $enclosure->get_link();
							if ($thumbnail) {
								$item['media'] = str_replace('https://today.usc.eduhttps//','https://',$thumbnail);
							}
						}
						$item['content'] = $item['desc'];
/*						if ($node->get_content()) {
							$item['content'] = $node->get_content();
							
							if( $item['content'] && false !== strpos( $item['content'], 'The post <a rel="' ) ) {
								$item['content'] = substr($item['content'], 0, strpos( $item['content'], 'The post <a rel="' ) );
							}
						}
*/		
						$item['categories'] = array();
						if( $node->get_categories() ) {
							$cats = $node->get_categories();
							if( is_array( $cats ) ) {
								foreach( $cats as $cat) {
									$item['categories'][] = $cat->get_term();
								}
							} else {
								$item['categories'][] = $cats;
							}
							if( array_intersect($artscats,$item['categories']) ) {
								$item['categories'][] = 'Arts';
							}
							if( array_intersect($computingcats,$item['categories']) ) {
								$item['categories'][] = 'Computing';
							}

							if( array_intersect($healthcats,$item['categories']) ) {
								$item['categories'][] = 'Health';
							}
							if( array_intersect($facrescats,$item['categories']) ) {
								$item['categories'][] = 'Faculty & Research';
							}
						}
		
						foreach($suppress as $sup) {
							if( strpos(strtolower(' '.$item['title']),strtolower($sup)) || in_array($sup,$item['categories']) ) {
								$add = false;
							}
						}
		
						if($add) {
							array_push($feed, $item);
						}
					}

				}

				usort($feed, "cmpdate");

//				header('Content-type: application/xml'); 

				echo '<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:media="http://search.yahoo.com/mrss/" version="2.0"><channel><title>USC News</title><atom:link href="https://news.usc.edu/tag/scholarships/feed/" rel="self" type="application/rss+xml"/><link>https://news.usc.edu</link><description>University of Southern California News</description><lastBuildDate>'.date('c').'</lastBuildDate><language>en-us</language><sy:updatePeriod>hourly</sy:updatePeriod><sy:updateFrequency>1</sy:updateFrequency><image><url>https://news.usc.edu/wp-content/themes/news-tfm-2018/images/usc-logo-feed.png</url><title>USC News</title><link>http://news.usc.edu</link></image><generator>'.$cachename.'</generator>';

				if( isset( $count ) && is_numeric( $count ) ) {
					$feed = array_slice( $feed, 0, $count );
				}

				foreach($feed as $item) {
					echo "<item>\n";
					echo "<title>".$item['title']."</title>\n";
					echo "<description>".$item['desc'];
					if( $item['categories'] ) {
						if( is_array( $item['categories'] ) ) {
							echo '|';
							foreach( $item['categories'] as $cat ) {
								echo "| $cat ";
							}
						} else {
							$cat = $item['categories'];
							echo "| $cat ";
						}
					}	
					echo "</description>\n";
					echo "<pubDate>".date('D, d M Y H:i:s',$item['date'])." GMT</pubDate>\n";
					if( $item['categories'] ) {
						if( is_array( $item['categories'] ) ) {
							foreach( $item['categories'] as $cat ) {
								echo "<category>\n<![CDATA[ $cat ]]>\n</category>\n";
							}
						} else {
							$cat = $item['categories'];
							echo "<category>\n<![CDATA[ $cat ]]>\n</category>\n";
						}
					}
					echo "<link>".$item['link']."</link>\n";
					echo "<guid>".$item['link']."</guid>\n";
					echo "<atom:link href='".$item['link']."' rel='self' type='application/rss+xml'/>\n";
					echo "<content:encoded>\n<![CDATA[";
					echo $item['desc'];
					echo "]]></content:encoded>\n";

					echo '<media:thumbnail url="'.$item['media'].'" width="480" height="320"/>';
					echo "</item>\n";

					// is there a post with this link?
					$args = array(
						'meta_key' => 'external_url',
						'meta_query' => array(
							array(
								'key' => 'external_url',
								'value' => $item['link'],
								'compare' => '=',
							)
						)
					);
					$itemexists = new WP_Query($args);
					if( ! $itemexists->have_posts() ) {
						$postarr = array(
							'post_date' => date('c',$item['date']),
							'post_date_gmt' => date('c',$item['date']),
							'post_content' => $item['content'],
							'post_excerpt' => $item['desc'],
							'post_title' => $item['title'],
							'post_status' => 'publish',
							'post_author' => 1,
							'post_category' => array(),
							'tags_input' => array(),
							'meta_input' => array('external_url'=>$item['link'],'url'=>$item['link']),
						);

						if( $item['categories'] ) {
							if( is_array( $item['categories'] ) ) {
								foreach( $item['categories'] as $cat ) {
									$cat_term = get_term_by('name',trim($cat),'category');
									if( $cat_term ) {
										$postarr['post_category'][] = $cat_term->term_id;
									}
								}
							} else {
								$cat = $item['categories'];
								$cat_term = get_term_by('name',$cat,'category');
								if( $cat_term ) {
									$postarr['post_category'][] = $cat_term->term_id;
								}
							}
						}
						wp_insert_post( $postarr );
						if( $item['media'] ) {
							if( strpos($item['media'],'-480x320') ) {
								$item['media'] = str_replace('-480x320','',$item['media']);
							}
							handleartwork($item['media'],basename(strtolower($item['media'])),0,'featured image for '.$item['title']);
						}
					}
				} 

				echo "</channel></rss>\n";



				$data = ob_get_clean();
//				if( ! strpos( $data, 'Error ' ) ) {
					$fp = fopen( $filename, 'w' );
					if ( $fp ) {
						fwrite( $fp, $data );
						fclose( $fp );
					}
//				}
			}
//		}

	}
}

function uscadvance_jobs_scraper() {
	$upload_dir = wp_upload_dir();
	if ( ! file_exists( $upload_dir['basedir'] . '/jobs' ) ) {
		mkdir( $upload_dir['basedir'] . '/jobs', 0775 );
	}

	$fetch_frequency = DAY_IN_SECONDS; // every day

	// pull jobs less frequently if not in production
//	if( false === strpos( get_home_url(), 'giving.usc.edu' ) ) {
//		$fetch_frequency = DAY_IN_SECONDS * 7; // once a week
//	}

	if ( false === as_has_scheduled_action( 'get_jobs' ) ) {
		as_schedule_recurring_action( strtotime('tomorrow') + ( HOUR_IN_SECONDS * 3 ), $fetch_frequency, 'get_jobs' );
	}

	if ( false === as_has_scheduled_action( 'cull_jobs' ) ) {
		as_schedule_recurring_action( strtotime('tomorrow') + ( HOUR_IN_SECONDS * 6 ), DAY_IN_SECONDS * 2, 'cull_jobs' );
	}

	if ( false === as_has_scheduled_action( 'summarize_jobs' ) ) {
		as_schedule_recurring_action( strtotime('next Monday') + HOUR_IN_SECONDS, DAY_IN_SECONDS * 7, 'summarize_jobs' );
	}

	$arrays = array(
		'advance-keywords-array'  => __('Keywords for jobs scraper', 'advance'),
		'advance-blacklist-array' => __('Blacklist for jobs scraper', 'advance'),
	);

	$options = get_option('advance-options');
	
	foreach( $arrays as $varname => $field ) {
		$values = $options[$varname];
		if( ! $values ) {
			$values = array();
		} else {
			$values = json_decode( stripslashes( $values ) );
		}
		$varname = str_replace( '-', '_', $varname );
		${ $varname } = $values;
	}

	$options = advance_get_options();
	$whitelist = $options['advance-keywords-array'];
	$scr = json_decode( stripslashes( $whitelist ), true );
	if( ! is_array( $scr ) ) {
		$scr = array( $scr );
	}
	$searches = array_map('trim', $scr);
	$searches = array_map('html_entity_decode', $searches);
	
	foreach( $searches as $search ) {
		$key = 'page-count-' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search);
		if( file_exists( $upload_dir['basedir'] . '/jobs/' . $key ) ) {
			$count = file_get_contents( $upload_dir['basedir'] . '/jobs/' . $key );
			if( 3 > strlen( $count ) ) {
				$count = intval( $count );
				for( $c = 1; $c <= $count; $c++ ) {
					add_action('get_jobs_' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search) . '_page_' . $c, function() use ( $search, $c ) { uscadvance_job_data( $search, $c ); });
				}
			}
		} else {
			add_action('get_jobs_' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search), function() use ( $search ) { uscadvance_job_data( $search, false ); });
		}
	}
}
add_action( 'init', 'uscadvance_jobs_scraper', 10, 0 );

function uscadvance_get_jobs() {
	$upload_dir = wp_upload_dir();
	if ( ! file_exists( $upload_dir['basedir'] . '/jobs' ) ) {
		mkdir( $upload_dir['basedir'] . '/jobs', 0775 );
	}

	$options = advance_get_options();
	$whitelist = $options['advance-keywords-array'];
	$scr = json_decode( stripslashes( $whitelist ), true );
	if( ! is_array( $scr ) ) {
		$scr = array( $scr );
	}
	$searches = array_map('trim', $scr);
	$searches = array_map('html_entity_decode', $searches);
//	foreach( $searches as $skey => $se ) {
//		$searches[$skey] = str_replace( '&quot;', '"', $se );
//	}

	foreach( $searches as $index => $search ) {
		//  1. get count
		$counturl = 'https://usc-adv-jobs.onrender.com/count/' . urlencode( $search );
		$key = 'page-count-' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search);
		error_log( 'Counting jobs at: ' . $counturl );
		$request = wp_remote_get( $counturl, ['timeout' => 400] );
		if( is_wp_error( $request ) ) {
			$error_string = $request->get_error_message();
			error_log( 'Error: ' . $error_string );
			return false;
		}
		$body = wp_remote_retrieve_body( $request );
		if( 3 > strlen( $body ) ) {

			// store in uploads directory
			$filename = $upload_dir['basedir'] . '/jobs/' . $key;
			if( file_exists( $filename ) ) {
				chmod( $filename, 0777 );
			}

			$fp = fopen( $filename, 'w' );
			if ( $fp ) {
				fwrite( $fp, $body );
				fclose( $fp );
			}

			// schedule scripts to fetch the jobs
			$count = intval($body);

			for( $co = 1; $co <= $count; $co++ ) {
				$delay = 60 + ( intval( $index ) * 120 ) + ( $co * 240 );
				as_schedule_single_action( strtotime('+'.$delay.' seconds'), 'get_jobs_' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search) . '_page_' . $co );
			}
		}
	}
}
add_action( 'get_jobs', 'uscadvance_get_jobs', 10, 0 );


function uscadvance_get_job_req( $req ) {
	if( ! strpos( ' ' . strtolower($req), 'req' ) ) {
		$req = 'req' . intval( $req );
	}
	uscadvance_job_data( strtolower( $req ), false, true );
//	$hook = 'get_job_' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])";
//	add_action($hook, array('', $req), function() use ( $req ) { uscadvance_job_data( $req, false, true ); });
//	as_enqueue_async_action( $hook, $args, $group, $unique, $priority );
//	as_schedule_single_action( strtotime('+5 seconds'), 'get_job_' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $req) );
}


function uscadvance_cull_jobs() {
	$args['post_type'] = 'job';
	$args['post_status'] = array( 'pending', 'draft', 'publish' );
	$args['posts_per_page'] = -1;
	$alljobs = new WP_Query($args);
	if( $alljobs->have_posts() ) {
		error_log( 'Checking ' . $alljobs->found_posts . ' jobs.' );
		while ( $alljobs->have_posts() ) {
			$alljobs->the_post();

			$joburl = get_post_meta( get_the_ID(), 'link', true );
			$request = wp_remote_get( $joburl, ['timeout' => 400,'redirection' => 0]);	
//			error_log( 'Checking job: ' . get_the_title() . ', ' . $joburl );

			if( is_wp_error( $request ) ) {
				$error_string = $request->get_error_message();
				error_log( 'Error fetching job: ' . get_the_title() . ': '. $error_string );
				return false;
			} else {
				$status = wp_remote_retrieve_response_code( $request );
				if( 200 == intval( $status ) ) {
//					error_log( 'Job OK: ' . get_the_title() );
				} else {
					error_log( 'Job appears gone: ' . get_the_title() . ', '. $status . ', culling.' );
					wp_delete_post( get_the_ID() );
				}
			}
		}
	}

	$args['category_name'] = 'featured';
	$featuredjobs = new WP_Query($args);
	if( ! $featuredjobs->have_posts() ) {
		$message = '<p>There are currently no featured jobs on the University Advancement website.</p>';
		$message .= '<p><a href="'.home_url().'/wp-admin/options-general.php?page=advance-settings" class="manage">Manage Featured Jobs</a><span class="note">Please log in to Wordpress to access this page.</span></p>';

		wp_mail(
			'dheller@usc.edu',
			'Advancement jobs: No jobs are featured',
			uscadvance_html_email( $message )
		);
	}
}
add_action( 'cull_jobs', 'uscadvance_cull_jobs', 10, 0 );


function uscadvance_summarize_jobs() {
	$args['post_type'] = 'job';
	$args['post_status'] = array( 'publish' );
	$args['posts_per_page'] = -1;

	$opts = advance_get_options();
	$notifications = $opts['advance-job-notifications-array'];
	$notifications = json_decode( stripslashes( $notifications ), true );
	if( is_array( $notifications ) ) {
		$notifications = implode( ',', $notifications );
	}
	$alljobs = new WP_Query($args);
	$pastweek = [];
	$featured = [];
	$jobs = [];
	if( $alljobs->have_posts() ) {
		while ( $alljobs->have_posts() ) {
			$alljobs->the_post();
			$this_id = get_the_ID();
			$filtered = false;
			foreach( $advance_blacklist_array as $term ) {
				if( strpos( ' ' . get_the_title(), $term ) ) {
					$filtered = true;
					$jobs_filtered++;
				}
			}

			if( ! $filtered ) {
				$joburl = get_post_meta( $this_id, 'link', true );
				$division = get_post_meta( $this_id, 'division', true );
				$industry = get_post_meta( $this_id, 'industry', true );
				$location = get_post_meta( $this_id, 'location', true );
				$categories = get_the_category();
				if ( ! empty( $categories ) ) {
					foreach( $categories as $cat ) {
						if( 'featured' == $cat->slug ) {
							$featured[] = $this_id;
						}
					}
				}
				$posted = get_post_meta( get_the_ID(), 'posted', true );
				if( strtotime( $posted ) > strtotime( '-8 days' ) ) {
					$pastweek[] = $this_id;
				}
				$jobs[$this_id] = array(
					'title' => get_the_title(),
					'link' => $joburl,
					'division' => $division,
					'industry' => $$industry,
					'location' => $location,
					'posted' => $posted,
				);
			}
		}
	}
	$plural = 's';
	if( 1 == count($pastweek) ) {
		$plural = '';
	}
	$message = '<p>In the past week (' . date('M. j',strtotime('-8 days')) . ' to ' . date('M. j',strtotime('-1 day')) . '), there ';
	if( $plural ) {
		$message .= 'have';
	} else {
		$message .= 'has';	
	}
	$message .= ' been <strong>' . count( $pastweek ) . ' job'.$plural.'</strong> posted that may be advancement-related.</p><p>Please review the list below and choose which jobs to feature on the USC Advancement website.</p>';

	if( count($pastweek) ) {
		$hide_info = array('link','block','title');
		$message .= '<hr><h3>Posted in the past week</h3><ul class="jobs-list">';
		foreach( $pastweek as $jobid ) {
			$job = $jobs[$jobid];
			$message .=	'<li><a href="'.$job['link'].'">'.$job['title'].'</a><br>';
			foreach( $job as $k => $info ) {
				if( $info && ! in_array( $k, $hide_info ) ) {
					$message .= '<span class="field-'.$k.'" style="font-size:16px;">'.$info.'</span><br>';
				}
			}
			$message .= '</li>';
		}
		$message .= '</ul>';
	}

	if( count($featured) ) {
		$hide_info = array('link','block','title');
		$message .= '<hr><h3>Currently featured on the site</h3><ul class="jobs-list">';
		foreach( $featured as $jobid ) {
			$job = $jobs[$jobid];
			$message .=	'<li><a href="'.$job['link'].'">'.$job['title'].'</a><br>';
			foreach( $job as $k => $info ) {
				if( $info && ! in_array( $k, $hide_info ) ) {
					$message .= '<span class="field-'.$k.'" style="font-size:16px;">'.$info.'</span><br>';
				}
			}
			$message .= '</li>';
		}
		$message .= '</ul>';
	} else {
		$message .= '<p>There are no jobs currently featured on the site.</p>';
	}

	$message .= '<p><a href="'.home_url().'/wp-admin/options-general.php?page=advance-settings#recent" class="manage">Manage Featured Jobs</a><span class="note">Please log in to Wordpress to access this page.</span></p>';

	if( count($pastweek) ) {
		wp_mail(
			$notifications,
			'Advancement jobs: '.count($pastweek).' new, '.count($featured).' featured',
			uscadvance_html_email( $message )
		);
	}

}
add_action( 'summarize_jobs', 'uscadvance_summarize_jobs', 10, 0 );



function uscadvance_job_data( $search, $page, $req = false ) {
	$upload_dir = wp_upload_dir();
	if ( ! file_exists( $upload_dir['basedir'] . '/jobs' ) ) {
		mkdir( $upload_dir['basedir'] . '/jobs', 0775 );
	}
	$job_ids = array();

//  1. search jobs
	if( $req ) {
		if( ! strpos( ' ' . strtolower($search), 'req' ) ) {
			$req = 'req' . intval( $search );
		}
		$jobsurl = 'https://usc-adv-jobs.onrender.com/req/' . urlencode( $search );
		$key = 'req-' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search);
	} else if( $page ) {
		$jobsurl = 'https://usc-adv-jobs.onrender.com/page/' . urlencode( $search ) . '/' . $page;
		$key = 'page-' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search) . '-' . $page;
	} else {
		$jobsurl = 'https://usc-adv-jobs.onrender.com/all/' . urlencode( $search );
		$key = 'all-' . mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search);
	}

	$request = wp_remote_get( $jobsurl, ['timeout' => 400]);
	if( is_wp_error( $request ) ) {
		$error_string = $request->get_error_message();
		error_log( 'Error: ' . $error_string );
		return false;
	}

	$body = wp_remote_retrieve_body( $request );

	error_log( 'Retrieved: ' . $jobsurl );

//  2. extract list of jobs
	$data = array();
	$data = json_decode( $body, true );

//  3. store as JSON
	$filename = $upload_dir['basedir'] . '/jobs/' . $key;
	if( file_exists( $filename ) ) {
		chmod( $filename, 0775 );
	}
	$fp = fopen( $filename, 'w' );
	if ( $fp ) {
		fwrite( $fp, $body );
		fclose( $fp );
	}

//  4. create new posts for unrecognized jobs, update recognized ones if they have changed
	if( $data && ! empty( $data ) && is_array( $data ) ) {
		foreach( $data as $job ) {
			// is this post already here?
			$args = array();
			$args['post_type'] = 'job';
			$args['post_status'] = array( 'pending', 'draft', 'publish' );
			$args['meta_key'] = 'usc_hr_id';
			$args['meta_value'] = $job['id'];
		
		
			$req = substr( $job['posted'], 0, strpos($job['posted'],'<b>') );
			$posted = substr( $job['posted'], strpos($job['posted'],'</b>') + 4 );

			$itemexists = new WP_Query($args);
			if( ! $itemexists->have_posts() ) { // add new job
				$postarr = array(
					'post_type' => 'job',
					'post_content' => '',
					'post_excerpt' => '',
					'post_title' => $job['title'],
					'post_status' => 'publish',
					'post_author' => 1,
					'comment_status' => 'closed',
					'ping_status' => 'closed',
					'meta_input' => array( 
						'usc_hr_id' => $job['id'],
						'division' => $job['division'],
						'industry' => $job['industry'],
						'link' => $job['link'],
						'location' => $job['location'],
						'posted' => $posted,
						'req' => $req,
						'metadata' => json_encode( $job ),
					),
				);
				$new_job_id = wp_insert_post( $postarr );
	
				$job_ids[] = $new_job_id;
			} else { 
				while ( $itemexists->have_posts() ) {
					$itemexists->the_post();
					$job_ids[] = get_the_ID();
					$metadata = get_post_meta( get_the_ID(), 'metadata', true );
					if( $metadata != json_encode($job) ) { // update the job, if changed

						$req = substr( $job['posted'], 0, strpos($job['posted'],'<b>') );
						$posted = substr( $job['posted'], strpos($job['posted'],'</b>') + 4 );

						$postarr = array(
							'ID' => get_the_ID(),
							'post_title' => $job['title'],
							'meta_input' => array(
								'usc_hr_id' => $job['id'],
								'division' => $job['division'],
								'industry' => $job['industry'],
								'location' => $job['location'],
								'posted' => $posted,
								'req' => $req,
								'metadata' => json_encode( $job ),
							),
						);
						wp_update_post( $postarr );
					}
				}
			}
		}
	}
}


if ( ! function_exists( 'uscadvance_html_email' ) ) {

	function uscadvance_html_email( $message, $background = '#f4f4f4' ) {
		$output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta name="viewport" content="width=device-width"/>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="color-scheme" content="light dark">
				<meta name="supported-color-schemes" content="light dark">
				<style type="text/css">
				ul.jobs-list { list-style: none; margin: 1rem 0 3rem; padding: 0; }
				ul.jobs-list li { padding: 1rem; margin: 0 0 .75rem; background: #fff !important; font-size: 16px; box-shadow: 0 .1rem .35rem rgba(0,0,0,0.15); }
				ul.jobs-list li a { font-size: 20px !important; font-weight: 700; color: #900; text-decoration: none; }
				ul.jobs-list li a:hover { color: #c00; }
				.note { margin: .5rem 0 0; display: block; font-size: 1.25rem; }
				a.manage { display: block; text-align: center; font-size: 24px; font-weight: 700; background: #900; text-decoration: none; padding: 1rem; color: #fff; }
				a.manage:hover { background: #c00; color: #fff; }
				body, p, table, td, th { font-family: Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif  !important; font-size: 20px; line-height: 1.4; }
				body a { color: #900; font-weight: 700; }
				span.posted:before { content: \'Posted \'; }
				@media (prefers-color-scheme: dark ) {
					body { background-color:#000 !important;}
					body a { color: #fc0; }
					.dark-img { display:block !important; width: auto !important; overflow: visible !important; float: none !important; max-height:inherit !important; max-width:inherit !important; line-height: auto !important; margin-top:0px !important; visibility:inherit !important; }
					.light-img { display:none; display:none !important; }
					.darkmode { background-color: #900 !important; }
					ul.jobs-list li { padding: 0; background: none; box-shadow: none; }
					ul.jobs-list li a { color: #fc0; }
					ul.jobs-list li a:hover { color: #fff; }
				}
				[data-ogsc] .dark-img { display:block !important; width: auto !important; overflow: visible !important; float: none !important; max-height:inherit !important; max-width:inherit !important; line-height: auto !important; margin-top:0px !important; visibility:inherit !important; }
				</style>
			</head>
			<body bgcolor="' . $background . '" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" style="margin: 0; padding: 0; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; height: 100%; width: 100%!important;">
			<table class="head-wrap" style="width: 100%;">
				<tbody>
					<tr>
						<td class="header container" style="margin: 0 auto!important; padding: 0; clear: both!important; display: block!important; max-width: 600px!important;">
							<div class="content" style="margin: 0 auto; padding: 15px; display: block; max-width: 600px; margin-top: 20px!important;">
								<table style="margin: 0; padding: 0; width: 100%;">
									<tbody style="margin: 0; padding: 0;">
										<tr style="margin: 0; padding: 0;">
											<td style="margin: 0; padding: 0;">
												<div style="margin: 0; padding: 0; width: 320px;">
													<a href="' . home_url() . '" style="margin: 0; padding: 0; color: #2BA6CB;">
														<img class="light-img" src="'.home_url().'/wp-content/uploads/2023/11/usc-advancement.png?v=1" width="484" height="80" border="0" alt="USC Advancement" style="margin: 0 auto; padding: 0; width: 100%; height: auto;" />
														<div class="dark-img" style="margin: 0; padding: 0; display: none; overflow: hidden; float: left; width: 0px; max-height: 0px; max-width: 0px; line-height: 0px; visibility: hidden;" align="center"><img src="' . home_url() . '/wp-content/uploads/2023/11/usc-advancement-darkmode.png" width="484" height="80" alt="USC Advancement" border="0" style="margin: 0; padding: 0; width: 100%; height: auto;" /></div>
													</a>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="body-wrap" style="margin: 0; padding: 0; width: 100%;">
				<tbody>
					<tr style="margin: 0; padding: 0;">
						<td class="container" style="margin: 0 auto!important; padding: 0; clear: both!important; display: block!important; max-width: 600px!important;">
							<div style="margin: 0; padding: 0;">
								<div class="content" style="margin: 0 auto; padding: 15px; display: block; max-width: 600px; padding-top: 0!important;">
									<table style="margin: 0; padding: 0; width: 100%;">
										<tbody>
											<tr style="margin: 0; padding: 0;">
												<td style="margin: 0; padding: 0; padding-top: 0!important;">
													' . $message . '
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</body>
		</html>';
		return $output;	
	}
}

function uscadvance_schedule_verify_directory() {
	$fetch_frequency = DAY_IN_SECONDS * 2; // once every two days

	if ( false === as_has_scheduled_action( 'verify_directory' ) ) {
		as_schedule_recurring_action( strtotime('tomorrow') + ( HOUR_IN_SECONDS * 9 ), $fetch_frequency, 'verify_directory' );
	}
}
add_action( 'init', 'uscadvance_schedule_verify_directory', 10, 0 );

function uscadvance_verify_directory() {
	// 1. create an array of names and pvids
	$ad_page_query = new WP_Query(
		array(
			'post_type' => 'page',
			'title' => 'Advancement Directory',
			'posts_per_page' => 1,
			'ignore_sticky_posts' => true,
		)
	);
	
	if ( ! empty( $ad_page_query->post ) ) {
		$ad_page = $ad_page_query->post;
	} else {
		$ad_page = null;
	}

	$directory_content = $ad_page->post_content;
	$pvids = array();
	$pvid_list = '';
	$directory_content = str_replace( array("\r","\n",'</a>', '<a ' ), array( '', '', "</a>\n", "\n<a " ), $directory_content );
	$lines = preg_split("/\r\n|\n|\r/", $directory_content);
	foreach( $lines as $line ) {
		if(	false !== strpos( $line, 'pvid=' ) ) {
			$pvid = substr( $line, strpos( $line, 'pvid=' ) + 5 );
			$pvid = trim( substr( $pvid, 0, strpos( $pvid, '"') ) );
			$name = substr( $line, strpos( $line, '">' ) + 2 );
			$name = trim( substr( $name, 0, strpos( $name, '</a>') ) );
			if( false !== strpos( $name, '<strong' ) ) {
				$name = substr( $name, strpos( $name, '<strong>' ) + 8 );
				$name = trim( substr( $name, 0, strpos( $name, '</str') ) );
			}
			$pvids[$pvid] = $name;
		}
	}
	foreach( $pvids as $k => $p ) {
		$pvid_list .= $k . ', ' . $p . "\n";
	}
/*
	preg_match_all( '#<a href="/.*?/(\d+)">(.*?)</a>#i', $directory_content, $matches );
	if( $matches && is_array( $matches ) ) {
		foreach( $matches as $match ) {
			$pvid_list .= $match[0] .', '.$match[1] . "\n";	
		}
	}

	print_r($matches);

	$directory_dom = str_get_html( $directory_content );
	foreach($directory_dom->find('a') as $dirlink) {
		$href = $dirlink->href;
		if( strpos( $href, 'pvid=' ) ) {
			$pvid = substr( $href, strpos( $href, 'pvid=' ) + 5 );	
			if( $dirlink->find('strong') ) {
				$name = $dirlink->find('strong', 0)->plaintext;
			} else {
				$name = $dirlink->plaintext;
			}
			$pvids[$pvid] = $name;
			$pvid_list .= $pvid . ' | ' . $name . "\n";
			error_log( 'Found ' . $name . ', PVID ' . $pvid );
		}
	}
*/
	
	// upload list
	$upload_dir = wp_upload_dir();
	if ( ! file_exists( $upload_dir['basedir'] . '/jobs' ) ) {
		mkdir( $upload_dir['basedir'] . '/jobs', 0775 );
	}

	$filename = $upload_dir['basedir'] . '/jobs/pvids.txt';
	if( file_exists( $filename ) ) {
		chmod( $filename, 0777 );
	}

	$fp = fopen( $filename, 'w' );
	if ( $fp ) {
		fwrite( $fp, $pvid_list );
		fclose( $fp );
	}

	// check the list
	$pvindex = 0;
	foreach( $pvids as $k => $p ) {
		$delay = 60 + ( intval( $pvindex ) * 240 );
		as_schedule_single_action( strtotime('+'.$delay.' seconds'), 'verify_person_action', array( $k . ',' . $p ) );
		$pvindex = $pvindex + 1;
	}
}
add_action( 'verify_directory', 'uscadvance_verify_directory', 10, 0 );


add_action( 'verify_person_action', 'uscadvance_verify_person', 10, 1 );

function uscadvance_verify_person( $var ) {
	$vars = explode( ',', $var );
	$pvid = $vars[0];
	$name = $vars[1];
	$secondary = false;
	if( isset( $vars[2] ) ) {
		$secondary = $vars[2];
	}
	$verifyurl = 'https://usc-adv-directory-verify.onrender.com/' . $pvid;
	$key = 'verify-' . $pvid;
//		error_log( 'Counting jobs at: ' . $counturl );
	$request = wp_remote_get( $verifyurl, ['timeout' => 400] );
	if( is_wp_error( $request ) ) {
		$error_string = $request->get_error_message();
		error_log( 'Error: ' . $error_string );
		return false;
	}
	$body = wp_remote_retrieve_body( $request );
	if( '1' !== trim( strval( $body ) ) ) {
		if( $secondary ) {
			$message = 'There was a problem verifying ' . $name . ' (pvid: ' . $pvid . ') in the USC directory.';
			error_log( $message );
			$message = '<p>'.$message.'</p><p>USC Directory: <a href="https://uscdirectory.usc.edu/web/directory/faculty-staff/#pvid='.$pvid.'">'.$name.'</a><br><a href="https://giving.usc.edu/our-team/advancement-directory/">Advancement Directory</a></p>';
			wp_mail(
				'dheller@usc.edu',
				'Advancement Directory: Could not verify ' . $name,
				uscadvance_html_email( $message )
			);
		} else {
			as_schedule_single_action( strtotime('+60 seconds'), 'verify_person_action', array( $pvid . ',' . $name . ',1' ) );		
		}
	}
}
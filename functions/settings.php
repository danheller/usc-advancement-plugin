<?php

/*--------------------------------------------------------------
Advancement plugin settings
--------------------------------------------------------------*/

/**
 * Add options page
 */
function advance_options_menu() {
	add_options_page( __('Advancement', 'advance'), __('Advancement', 'advance'), 'manage_options', 'advance-settings', 'advance_options_page' );
}

/**
 * Update options
 *
 * @param Array $new_options: an array of new settings
 */
function advance_update_options( $new_options ) {
	$options = advance_get_options();
	
	foreach( $options as $key => $f ) {
		if( isset( $new_options[$key] ) ) {
			$options[$key] = $new_options[$key];
		}
	}
	update_option('advance-options', $options);
}

/**
 * Get option values
 */
function advance_get_options() {
	// default field values
	$fields = advance_get_fields();
	$options = get_option('advance-options');
	foreach( $fields as $key => $f ) {
		if( isset( $options[$key] ) ) {
			$fields[$key] = $options[$key];
		} else {
			$fields[$key] = '';
		}
	}
	return $fields;
}

/**
 * Register javascript and stylesheets for admin screens
 *
 * @param String $hook: The hook we're currently on
 */
function advance_load_scripts( $hook ) {
	if ( isset( $_GET['page'] ) && 'advance-settings' == $_GET['page'] ) {
		wp_enqueue_script( 'advance-admin', ADVANCE_URL . 'js/advance-admin.js', false, time(), true );
		wp_enqueue_style( 'advance-style', ADVANCE_URL . 'css/advance-style.css', false, time(), 'all' );
	}
}

/**
 * Get list of setting fields and their types
 */
function advance_get_fields() {
	$fields = array(
		'advance-keywords-array'  => __('Keywords for jobs scraper', 'advance'),
		'advance-blacklist-array' => __('Blacklist for jobs scraper', 'advance'),
		'advance-job-notifications-array' => __('Send notifications to', 'advance'),
		'advance-release-notifications-array' => __('Send notifications to', 'advance'),
	);
	return $fields;
}

/**
 * Build settings (plugin options) page
 */
function advance_options_page() {

	$fields = advance_get_fields();

	$tab_options = array(
		'jobs'=>'Jobs',
		'releases'=>'Image Releases',
//		'news'=>'News',
	);

	$tab = 'jobs';
	if( isset( $_GET['tab'] ) ) {
		$gettab = $_GET['tab'];
		if( isset( $tab_options[$gettab] ) ) {
			$tab = $gettab;
		}
	}


	echo '<div class="advancement-page-title"><h2>' . __('Advancement Settings', 'advance') . '</h2>';

	echo '<ul class="advancement-plugin-menu">';
	foreach( $tab_options as $slug => $to ) {
		echo '<li><a href="?page=advance-settings&tab=' . $slug . '"';
		if( $slug == $tab ) {
			echo ' class="current"';
		}
		echo '>' . $to . '</a></li>';
	}
	
	echo '</ul></div><div class="wrap">';


	// tabs

	if( isset( $_POST['advance-update-settings'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if( !wp_verify_nonce( $nonce, 'advance-settings' ) ) {
			wp_die( __('Security check failed. Please try again.', 'advance') );
		}
		$new_options = array();
		foreach( $fields as $key => $f ) {
			if( isset( $_POST[$key] ) ) {
				if( strpos( $key, '-array' ) ) {
					$arrayfield = array();
					$values = stripslashes( $_POST[$key] );
					$values = json_decode( $values, true );
					foreach( $values as $value ) {
						$arrayfield[] = sanitize_text_field( str_replace('"','&quot;',$value) );
					}
					$new_options[$key] = json_encode( $arrayfield );
				} else {
					$new_options[$key] = stripslashes( sanitize_text_field( $_POST[$key] ) );
				}
			}
		}
		advance_update_options( $new_options );
		echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.', 'advance') . '</strong></p></div>';
	}
	$options = advance_get_options();

	if( 'jobs' == $tab ) {

		$html = '<div class="subsubsub-container"><ul class="subsubsub"><li><a href="#job-scraper">Job Scraper Settings</a></li><li><a class="" href="#recent">Recently Added Jobs</a></li><li><a class="" href="#current">Currently Featured Jobs</a></li><li><a class="" href="#scrape">Start the Job Scraper</a></li></ul></div><div class="inner-container has-sub-menu">';
		$html .= '<h3 id="job-scraper">' . __('Job Scraper Settings', 'advance') . '</h3>';

		$jobfields = $fields;
		unset( $jobfields['advance-release-notifications-array'] );

		ob_start();
	?>
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<script type="text/javascript">
					var nonce = "<?php echo wp_create_nonce( 'uscadvance' ); ?>";
					var	ajax  = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
				</script>
				<?php wp_nonce_field('advance-settings'); ?>
	
				<table class="form-table" role="presentation">
					<tbody> 
					<?php
					foreach( $jobfields as $key => $f ) {
						$input_class = '';
						if( strpos( $key, '-on' ) ) {
							$fieldtype = 'checkbox';
						} else if( strpos( $key, '-color' ) || strpos( $key, '-background' ) ) {
							$fieldtype = 'color';
						} else if( strpos( $key, '-array' ) ) {
							$fieldtype = 'array';
							$input_class = 'regular-text';
						} else if( strpos( $key, '-text' ) ) {
							$fieldtype = 'text';
							$input_class = 'regular-text';
						} else {
							$fieldtype = 'textarea';
							$input_class = 'regular-text';
						}
			
						if( 'checkbox' == $fieldtype ) {
							$checked = '';
							if( ! isset( $options[$key] ) || $options[$key] ) {
								$checked = 'checked="checked"';
							}
						?>
						<tr data-type="checkbox">
							<td colspan="2"><label for="<?php echo esc_attr( $key ); ?>"><input name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php echo $checked; ?> type="<?php echo esc_attr( $fieldtype ); ?>"> <?php _e($f, 'advance');?></label></td>
						</tr>
						<?php			
						} else if( 'text' == $fieldtype ) {
						?>
						<tr data-type="text">
							<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>"><?php _e($f, 'advance');?></label></th>
							<?php echo '<td valign="top"><input name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $options[$key] ) . '" type="' . esc_attr( $fieldtype ) . '" class="' . $input_class . '"></td>'; ?>
						</tr>
						<?php			
						} else if( 'array' == $fieldtype ) {
							$values = $options[$key];
							if( ! $values ) {
								$values = array();
							} else {
								$values = json_decode( stripslashes( $values ) );
							}
						?>
						<tr data-type="array">
							<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>-0"><?php _e($f, 'advance');?></label><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( json_encode( $values ) ); ?>"></th>
							<td valign="top" data-count="<?php echo count( $values ); ?>">
							<?php
								$values[] = '';
								foreach( $values as $itemnumber => $value ) {
									$value = str_replace( '"', '&quot;', $value );
									echo '<div class="item" data-item="' . $itemnumber . '"><input name="' . esc_attr( $key ) . '-' . $itemnumber . '" id="' . esc_attr( $key ) . '-' . $itemnumber . '" value="' . esc_attr( $value ) . '" type="text" class="' . $input_class . '"><a href="#" class="delete-item">Delete</a><a href="#" class="add-item">Add</a></div>'; 
								}
							?>
							</td>
						</tr>
						<?php
						} else {
						?>
						<tr data-type="textarea">
							<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>"><?php _e($f, 'advance');?></label></th>
							<?php echo '<td valign="top"><textarea rows="8" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" class="' . $input_class . '">'. nl2br( esc_html( $options[$key] ) ) .'</textarea></td>'; ?>
						</tr>
					<?php
						}
					}
					?>
					</tbody>
				</table>
	
				<p class="submit"><input type="submit" name="advance-update-settings" class="button button-primary" value="<?php _e('Update Settings', 'advance');?>"></p>
			</form>
	<?php
		$html .= ob_get_clean();
	
		$html .= '<h3 id="recent">' . __('Recently Added Jobs', 'advance');
	
		$since_opts = array('week','month','all');
		$since = 'week';
		if( isset( $_GET['since'] ) && in_array( $_GET['since'], $since_opts ) ) {
			$since = $_GET['since'];
		}
		$feat = get_term_by('slug', 'featured', 'category');
		$feat_id = $feat->term_id;

		$options = get_option('advance-options');
		
		foreach( $fields as $varname => $field ) {
			$values = $options[$varname];
			if( ! $values ) {
				$values = array();
			} else {
				$values = json_decode( stripslashes( $values ) );
			}
			$varname = str_replace( '-', '_', $varname );
			${ $varname } = $values;
		}
	
		$args['post_type'] = 'job';
		$args['post_status'] = array( 'publish' );
		$args['posts_per_page'] = -1;
		$jobs_filtered = 0;
		$alljobs = new WP_Query($args);
		$recent = [];
		$featured = [];
		$jobs = [];
		
		if( $alljobs->have_posts() ) {
			while ( $alljobs->have_posts() ) {
				$alljobs->the_post();
				$this_id = get_the_ID();
	
				$filtered = false;
				foreach( $advance_blacklist_array as $term ) {
					if( strpos( strtolower(' ' . get_the_title()), strtolower($term) ) ) {
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
					
					if( 'all' == $since ) {
						$recent[] = $this_id;
					} else if( 'week' == $since && strtotime( $posted ) > strtotime( '-7 days' ) ) {
						$recent[] = $this_id;
					} else if( 'month' == $since && strtotime( $posted ) > strtotime( '-1 month' ) ) {
						$recent[] = $this_id;
					}
					
					$jobs[$this_id] = array(
						'title' => get_the_title(),
						'link' => $joburl,
						'division' => $division,
						'industry' => $industry,
						'location' => $location,
						'posted' => $posted,
					);
				}
			}
		}
	
//		if( count($recent) ) {
			$hide_info = array('link','block','title');
			$html .= '<em class="recent">Posted ';
			if( 'all' != $since ) {
				if( 'week' == $since ) {
					$html .= ' in the past week (' . date('M j',strtotime('-7 days')) . ' to ' . date('M j',strtotime('now')) . ')';
				} else if( 'month' == $since ) {
					$html .= ' in the past month (' . date('M j',strtotime('-1 month')) . ' to ' . date('M j',strtotime('now')) . ')';
				}
			}
			if( 'all' != $since ) {
				$html .= '<a class="since" href="?page=advance-settings&since=all#recent">Show all</a>';
			}
			if( 'month' != $since ) {
				$html .= '<a class="since" href="?page=advance-settings&since=month#recent">Past month</a> ';
			}
			if( 'week' != $since ) {
				$html .= '<a class="since" href="?page=advance-settings&since=week#recent">Past week</a> ';
			}
	
			$plural = 's';
			if( 1 == count($recent) ) {
				$plural = '';
			}
			$html .= '</em></h3>';
	
			$html .= '<p class="intro">There ';
			if( $plural ) {
				$html .= 'have';
			} else {
				$html .= 'has';	
			}
			$html .= ' been <strong>' . count( $recent ) . ' job'.$plural.'</strong> posted';
		
			if( 'all' != $since ) {
				if( 'week' == $since ) {
					$html .= ' in the past week';
				} else if( 'month' == $since ) {
					$html .= ' in the past month';
				}
			}
			$html .= ' that may be advancement-related. Please review the list below and choose which jobs to feature.</p>';
	
			
			$html .= '<ul class="jobs-list">';
			foreach( $recent as $jobid ) {
				$job = $jobs[$jobid];
				$html .=	'<li data-job="'.$jobid.'">';
				
				$html .= '<div class="button-group">';
				if( in_category( $feat_id, $jobid ) ) {
					$html .=	'<a href="#" class="feature-job on" data-id="'.$jobid.'">Don\'t Feature</a>';
				} else {
					$html .=	'<a href="#" class="feature-job" data-id="'.$jobid.'">Feature</a>';								
				}
				$html .= '<a href="#" class="block-job" data-id="'.$jobid.'">Block this job title</a></div><a href="'.$job['link'].'">'.$job['title'].'</a><br>';
				foreach( $job as $k => $info ) {
					if( $info && ! in_array( $k, $hide_info ) ) {
						$html .= '<span class="'.$k.'">'.$info.'</span><br>';
					}
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
//		}
	
	
		if( count($featured) ) {
			$hide_info = array('link','block','title');
			$html .= '<h3 id="current">Currently Featured Jobs</h3><ul class="jobs-list current">';
			foreach( $featured as $jobid ) {
				$job = $jobs[$jobid];
				$html .=	'<li data-job="'.$jobid.'"><div class="button-group"><a href="#" class="feature-job on" data-id="'.$jobid.'">Don\'t Feature</a></div><a href="'.$job['link'].'">'.$job['title'].'</a><br>';
				foreach( $job as $k => $info ) {
					if( $info && ! in_array( $k, $hide_info ) ) {
						$html .= '<span class="'.$k.'">'.$info.'</span><br>';
					}
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
		} else {
			$html .= '<p>There are no jobs currently featured on the site.</p>';
		}
	/*
		$html .= '<h3 id="add-by-req">Add a Job by REQ Number</h3><form class="req-fetch" action="#"><input type="text" class="req" name="req" value="REQ"><button type="submit">Add job</button></form><p class="note"><strong>Please note:</strong> Jobs added by REQ number will be fetched and appear in the list of <a href="#recent">jobs to review</a> above if you reload the page in a few minutes. The REQ value should be entered in the format <code>req12345678</code>.</p>';
	*/
		$html .= '<h3 id="scrape">'.__('Start the Job Scraper', 'advance').'</h3>';
		$html .= '<p class="intro">'.__('The process runs automatically once a day. You can use this button to start it at any time. It may take up to an hour to complete depending on the number of results.', 'advance').'</p>';
		$html .= '<button class="scrape" type="button">Scrape Jobs Now</button>';
		$html .= '</div></div></div>';
	
	} else if( 'releases' == $tab ) {


		$html = '<div class="subsubsub-container"><ul class="subsubsub"><li><a class="" href="#release-forms">Image Release Forms</a></li><li><a class="" href="#form">Sign a Release Form</a></li><li><a href="#notifications">Notifications</a></li></ul></div><div class="inner-container has-sub-menu">';	
	
		$html .= '<h3 id="release-forms">' . __('Image Release Forms', 'advance');
	
		$since_opts = array('week','month','all');
		$since = 'week';
		if( isset( $_GET['since'] ) && in_array( $_GET['since'], $since_opts ) ) {
			$since = $_GET['since'];
		}
	
		$args['post_type'] = 'release';
		$args['post_status'] = array( 'publish' );
		$args['posts_per_page'] = -1;
		$allreleases = new WP_Query($args);
		$recent = [];
		$releases = [];

		if( $allreleases->have_posts() ) {
			while ( $allreleases->have_posts() ) {
				$allreleases->the_post();
				$this_id = get_the_ID();
	
				$posted = get_post_meta( $this_id, 'date', true );
				$signedby = get_post_meta( $this_id, 'signedby', true );
				$signature = get_post_meta( $this_id, 'signature', true );
				if( 'all' == $since ) {
					$recent[] = $this_id;
				} else if( 'week' == $since && strtotime( $posted ) > strtotime( '-7 days' ) ) {
					$recent[] = $this_id;
				} else if( 'month' == $since && strtotime( $posted ) > strtotime( '-1 month' ) ) {
					$recent[] = $this_id;
				}
				
				$releases[$this_id] = array(
					'title' => get_the_title(),
					'date' => $posted,
					'signedby' => $signedby,
					'signature' => $signature,
					'link' => get_permalink( $this_id ),
					'ID' => $this_id,
				);
			}
		}
//		if( count($recent) ) {
			$hide_info = array('link','block','title');
			$html .= '<em class="recent">Posted ';
			if( 'all' != $since ) {
				if( 'week' == $since ) {
					$html .= ' in the past week (' . date('M j',strtotime('-7 days')) . ' to ' . date('M j',strtotime('now')) . ')';
				} else if( 'month' == $since ) {
					$html .= ' in the past month (' . date('M j',strtotime('-1 month')) . ' to ' . date('M j',strtotime('+1 day')) . ')';
				}
			}
			if( 'all' != $since ) {
				$html .= '<a class="since" href="?page=advance-settings&tab=releases&since=all">Show all</a>';
			}
			if( 'month' != $since ) {
				$html .= '<a class="since" href="?page=advance-settings&tab=releases&since=month">Past month</a> ';
			}
			if( 'week' != $since ) {
				$html .= '<a class="since" href="?page=advance-settings&tab=releases&since=week">Past week</a> ';
			}
	
			$plural = 's';
			if( 1 == count($recent) ) {
				$plural = '';
			}
			$html .= '</em></h3>';
	
			$html .= '<p class="intro">There ';
			if( $plural ) {
				$html .= 'have';
			} else {
				$html .= 'has';	
			}
			$html .= ' been <strong>' . count( $recent ) . ' release'.$plural.'</strong> submitted';
		
			if( 'all' != $since ) {
				if( 'week' == $since ) {
					$html .= ' in the past week';
				} else if( 'month' == $since ) {
					$html .= ' in the past month';
				}
			}
			$html .= '.</p>';
	
			
			$html .= '<table class="release-list"><tr><thead><th>Signed By</th><th>Date</th><th>Signature</th><th>Form Link</th></tr><tbody>';
			foreach( $recent as $releaseid ) {
				$rel = $releases[$releaseid];
				$html .=	'<tr data-release="'.$releaseid.'"><td class="signedby"><a href="' . home_url() . '/signed/?r=' .$rel['ID'].'">'.$rel['signedby'].'</a></td>';
				$html .=	'<td class="date">'.date( 'F d, Y', strtotime( $rel['date'] ) ).'</td>';
				$html .=	'<td class="signature"><img src="'.$rel['signature'].'"></td>';
				$html .=	'<td class="link"><a href="' . home_url() . '/signed/?r=' . $rel['ID'] . '">Signed Form</a> <a href="' . home_url() . '/pdf/?r=' . $rel['ID'] . '">Signed PDF</a></td></tr>';
			}
			$html .= '</tbody></table>';
//		}

		$html .= '<h3 id="form">' . __('Sign a Release Form', 'advance') . '</h3>';
		$html .= '<p class="intro">'.__('The release form can be filled out at ', 'advance') . '<a href="'.home_url() .'/release/">' . home_url() . '/release</a></p>';


		$html .= '<h3 id="notifications">' . __('Notifications', 'advance') . '</h3>';


		$releasefields = array( 'advance-release-notifications-array' => $fields['advance-release-notifications-array'] );
		ob_start();
	?>
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<script type="text/javascript">
					var nonce = "<?php echo wp_create_nonce( 'uscadvance' ); ?>";
					var	ajax  = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
				</script>
				<?php wp_nonce_field('advance-settings'); ?>
	
				<table class="form-table" role="presentation">
					<tbody> 
					<?php
					foreach( $releasefields as $key => $f ) {
						$input_class = '';
						if( strpos( $key, '-on' ) ) {
							$fieldtype = 'checkbox';
						} else if( strpos( $key, '-color' ) || strpos( $key, '-background' ) ) {
							$fieldtype = 'color';
						} else if( strpos( $key, '-array' ) ) {
							$fieldtype = 'array';
							$input_class = 'regular-text';
						} else if( strpos( $key, '-text' ) ) {
							$fieldtype = 'text';
							$input_class = 'regular-text';
						} else {
							$fieldtype = 'textarea';
							$input_class = 'regular-text';
						}
			
						if( 'checkbox' == $fieldtype ) {
							$checked = '';
							if( ! isset( $options[$key] ) || $options[$key] ) {
								$checked = 'checked="checked"';
							}
						?>
						<tr data-type="checkbox">
							<td colspan="2"><label for="<?php echo esc_attr( $key ); ?>"><input name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php echo $checked; ?> type="<?php echo esc_attr( $fieldtype ); ?>"> <?php _e($f, 'advance');?></label></td>
						</tr>
						<?php			
						} else if( 'text' == $fieldtype ) {
						?>
						<tr data-type="text">
							<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>"><?php _e($f, 'advance');?></label></th>
							<?php echo '<td valign="top"><input name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $options[$key] ) . '" type="' . esc_attr( $fieldtype ) . '" class="' . $input_class . '"></td>'; ?>
						</tr>
						<?php			
						} else if( 'array' == $fieldtype ) {
							$values = $options[$key];
							if( ! $values ) {
								$values = array();
							} else {
								$values = json_decode( stripslashes( $values ) );
							}
						?>
						<tr data-type="array">
							<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>-0"><?php _e($f, 'advance');?></label><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( json_encode( $values ) ); ?>"></th>
							<td valign="top" data-count="<?php echo count( $values ); ?>">
							<?php
								$values[] = '';
								foreach( $values as $itemnumber => $value ) {
									$value = str_replace( '"', '&quot;', $value );
									echo '<div class="item" data-item="' . $itemnumber . '"><input name="' . esc_attr( $key ) . '-' . $itemnumber . '" id="' . esc_attr( $key ) . '-' . $itemnumber . '" value="' . esc_attr( $value ) . '" type="text" class="' . $input_class . '"><a href="#" class="delete-item">Delete</a><a href="#" class="add-item">Add</a></div>'; 
								}
							?>
							</td>
						</tr>
						<?php
						} else {
						?>
						<tr data-type="textarea">
							<th scope="row" valign="top"><label for="<?php echo esc_attr( $key ); ?>"><?php _e($f, 'advance');?></label></th>
							<?php echo '<td valign="top"><textarea rows="8" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" class="' . $input_class . '">'. nl2br( esc_html( $options[$key] ) ) .'</textarea></td>'; ?>
						</tr>
					<?php
						}
					}
					?>
					</tbody>
				</table>
	
				<p class="submit"><input type="submit" name="advance-update-settings" class="button button-primary" value="<?php _e('Update Settings', 'advance');?>"></p>
			</form>
	<?php
		$html .= ob_get_clean();

	
		$html .= '</div></div></div>';
		
	}
	$html .= '</div>';
	echo $html;

}

if ( is_admin() ) {
	add_action( 'admin_menu', 'advance_options_menu');
	add_action( 'admin_enqueue_scripts', 'advance_load_scripts', 10, 1 );
}


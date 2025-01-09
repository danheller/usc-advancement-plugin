<?php
/**
 * Template for the job opportunities page
 *
 * @package advance
 */

	$bodyclass = 'jobs ';

	// ACF fields that add options to the body tag's class
	$bodyclassoptions = array( 'transparent' );
	foreach( $bodyclassoptions as $bco ) {
		if( get_post_meta( get_the_ID(), $bco, true ) ) {
			$bodyclass .= str_replace( '_', '-', $bco ) . ' ';
		}
	}
	// ACF fields that may have values
	$pagefields = array( 'page_menu', 'featured_image_format', 'intro', 'hide_page_title', 'top_shadow', 'bottom_shadow' );
	foreach( $pagefields as $pf ) {
		${ $pf } = get_post_meta(get_the_ID(), $pf, true);
	}
	if( isset( $top_shadow ) && 'default' != $top_shadow ) {
		$bodyclass .= 'top-shadow-' . $top_shadow . ' ';
	}

	if( isset( $bottom_shadow ) && 'default' != $bottom_shadow ) {
		$bodyclass .= 'bottom-shadow-' . $bottom_shadow . ' ';
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

	get_header();

	if( current_user_can('edit_pages') ) {
		get_template_part( 'customizer-widget' );
	}

	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post(); 
			$postclass = 'show-page-menu ';

			// ACF fields that may have values
			$pagefields = array( 'page_menu', 'featured_image_format', 'intro', 'hide_page_title');
			foreach( $pagefields as $pf ) {
				${ $pf } = get_post_meta(get_the_ID(), $pf, true);
			}
		?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( $postclass ); ?>>
		<?php
			// cover photo option (TO DO: add select field/s to choose featured photo layout)
			if ( has_post_thumbnail() && 'full' == $featured_image_format ) {
				echo '<header class="page-header full-image">';
				echo '<div class="wp-block-cover short" style="min-height:430px;aspect-ratio:unset;"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>';
				the_post_thumbnail( 'full', array( 'itemprop' => 'image', 'fetchpriority' => 'high', 'decoding' => 'async', 'class' => 'wp-block-cover__image-background', 'data-object-fit' => 'cover' ) );
				echo '<div class="wp-block-cover__inner-container is-layout-constrained wp-block-cover-is-layout-constrained"><h1 class="wp-block-heading">' . get_the_title() . '</h1>';
				if( $intro ) {
					echo '<h2 class="subhead">' . $intro . '</h2>';
				}
				echo '</div></div></header>';
			} else if( ! isset( $hide_page_title ) || ! $hide_page_title ) {
			?>
					<header class="page-header">
					<h1 class="entry-title" itemprop="name"><?php the_title(); ?></h1>
					</header>
			<?php			
			}

			$args['post_type'] = 'job';
			$args['posts_per_page'] = -1;
			$args['category_name'] = 'featured';
			$args['orderby'] = 'title';
			$args['order'] = 'asc';

			$items = new WP_Query($args);
			if( $items->have_posts() ) {
				$number_of_jobs = $items->found_posts;

				$jobs = array();
				$divisions = array();
				$hide_info = array('link','block','title');
				$jobs_filtered = 0;
				while ( $items->have_posts() ) {
					$items->the_post();
					$filtered = false;
					foreach( $advance_blacklist_array as $term ) {
						if( strpos( ' ' . get_the_title(), $term ) ) {
							$filtered = true;
							$jobs_filtered++;
						}
					}

					if( ! $filtered ) {
						$item['link'] = get_post_meta(get_the_ID(), 'link', true );
						$item['division'] = get_post_meta(get_the_ID(), 'division', true );
						if( 'Keck School of Medicine of USC' == $item['division'] ) {
							$item['division'] = 'Keck School of Medicine';
						}
						$item['industry'] = get_post_meta(get_the_ID(), 'industry', true );
						$item['location'] = get_post_meta(get_the_ID(), 'location', true );
						$item['posted'] = get_post_meta(get_the_ID(), 'posted', true );
						$division = $item['division'];
						if( ! isset( $divisions[ $division ] ) ) {
							$divisions[$division] = 1;
						} else {
							$divisions[$division]++;
						}
						$item['block'] = '<p><a href="' . $item['link'] . '">' . get_the_title() . '</a>';
						foreach( $item as $k => $info ) {
							if( $info && ! in_array( $k, $hide_info ) ) {
								$item['block'] .= '<span class="'.$k.'">'.$info.'</span>';
							}
						}

						$item['block'] .= '</p>';
						$item['title'] = get_the_title();
						$jobs[] = $item;
					}
				}
			}
			?>
			<aside class="sidebar"><div class="page-menu"><h2 class="screen-reader-text">On this page</h2><button class="page-menu-toggle" aria-controls="sections" has-popup="true" aria-label="Jump to a section" aria-hidden="true">Jump to a section</button>
			<nav id="sections">
			<?php
				if( is_array( $divisions ) ) {
					echo '<ul>';
					ksort( $divisions );
					foreach( $divisions as $key => $div ) {
						echo '<li><a href="#' . strtolower(str_replace(array(' ',','),array('-',''), $key)) . '"><span>' . $div .'</span> ' . $key . '</a></li>';
					}
					echo '</ul>';
				}
			?>
			</nav></div></aside>

		<div class="entry-content" itemprop="mainContentOfPage">
		<?php
			wp_reset_query();
			the_content(); 
		?>
		<div class="wp-block-group directory is-layout-flow wp-block-group-is-layout-flow margin-top-4">
			<div class="wp-block-group__inner-container">


				<div class="wp-block-group groups is-layout-flow wp-block-group-is-layout-flow">
					<div class="wp-block-group__inner-container">
						<?php
							foreach( $divisions as $key => $div ) {
						?>
						<div id="<?php echo strtolower(str_replace(array(' ',','),array('-',''), $key)); ?>" class="wp-block-group group show is-layout-flow wp-block-group-is-layout-flow">
							<div class="wp-block-group__inner-container">
								<h2 class="wp-block-heading"><?php echo $key; ?></h2>

								<?php 
									foreach($jobs as $job) {
										if( $job['division'] == $key ) {
											echo $job['block'];										    		
										}
									}
								?>
							</div>
						</div>
						<?php
							}
							
							
						?>
					</div>
					<p>
						<em>This page features a sample of positions currently open in USC Advancement. To see all current job opportunities at USC, please visit</em> <a href="https://usccareers.usc.edu/" target="_blank"><strong>Careers at USC</strong></a>.
					</p>
				</div>
			</div>
		</div>
			<div class="entry-links"><?php wp_link_pages(); ?></div>
			<?php edit_post_link( 'Edit “' . get_the_title() . '”' ); ?>
		</div>
	</article>
<?php 
		}
	}
	wp_enqueue_style( 'jobs-style', ADVANCE_URL . 'css/jobs.css', false, null, 'all' );

	get_footer();


<?php
/**
 * Template for the image release page
 *
 * @package advance
 */
 
	$bodyclass = 'page-template-release-form ';

	get_header();
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post(); 
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="page-header">
			<h1 class="entry-title" itemprop="name"><?php the_title(); ?></h1>
		</header>
		<div class="entry-content" itemprop="mainContentOfPage">
		<?php
			the_content(); 
		?>
		<div class="entry-links"><?php wp_link_pages(); ?></div>
		<?php edit_post_link( 'Edit “' . get_the_title() . '”' ); ?>
		</div><!-- .entry-content -->
	</article><!-- #post-<?php the_ID(); ?> -->

<?php
		}
	}
	?>
	<script type="text/javascript">
		var nonce = "<?php echo wp_create_nonce( 'uscadvance' ); ?>";
		var	ajax  = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
	</script>
	<?php
	wp_enqueue_script('jquery', "https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js", false, null, true );
	wp_enqueue_script( 'release-script', ADVANCE_URL . 'js/release.js', false, 10241, true );
	wp_enqueue_style( 'release-style', ADVANCE_URL . 'css/release.css', false, 10241, 'all' );

	get_footer();

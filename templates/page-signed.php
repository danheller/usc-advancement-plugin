<?php
/**
 * Template Name: Signed image release
 *
 * @package uscadvance
 */

if( !session_id() ) { session_start(); }

if( isset( $_GET['r'] ) && $_GET['r'] && 'release' == get_post_type( intval( $_GET['r'] ) ) && ! isset( $_SESSION['release'] ) ) {
	$_SESSION['release'] = intval( $_GET['r'] );
}

if ( 
	( isset( $_SESSION['release'] ) && $_SESSION['release'] && 'release' == get_post_type( intval( $_SESSION['release'] ) ) )
  ) {
	$release_id = intval( $_SESSION['release'] );
	$_SESSION['signedby'] = get_post_meta($release_id,'signedby',true);
	$_SESSION['advpath'] = ADVANCE_PATH;
	if( ! $_SESSION['uploaded'] ) {
		$_SESSION['uploaded'] = array();
	}

	require_once( ADVANCE_PATH . 'functions/gdrive/config.php');

	$bodyclass = 'page-template-release-form ';

	get_header();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( ); ?>>
			<?php 
			while ( have_posts() ) {
				the_post();
//				$release_id      = intval( $_GET['r'] );

	?>
	<header class="page-header">
		<h1 class="entry-title" itemprop="name"><?php the_title(); ?></h1>
	</header>
	<div class="entry-content" itemprop="mainContentOfPage">
			<?php
				if( in_array( $release_id, $_SESSION['uploaded'] ) ) {
					echo '<p class="is-style-note">This file has been uploaded to your <a href="https://drive.google.com/drive/home">Google Drive</a> folder with the filename "Image release for '.$_SESSION['signedby'].'."</p>';
				} else {
					if(isset($_SESSION['access_token']) && !empty($_SESSION['access_token']) && isset($_SESSION['upload']) && $_SESSION['upload'] ) { 
						$_SESSION['upload'] = false;
						$_SESSION['uploaded'][] = $release_id;
						require_once( ADVANCE_PATH . 'functions/gdrive/upload.php');
					} else {
						$_SESSION['upload'] = true;
					?>
						<a class="btn btn-primary rounded-pill w-100" href="<?= $gOauthURL ?>">Sign in with Google <span class="description">to add file to Google Drive</span></a>
					<?php } 
				}
			?>

			<form action="#" type="post" class="release" onsubmit="returnfalse()">
			<div class="field date"><label for="date"><span>Date</span><input type="text" id="date" name="date" value="<?php echo esc_attr( date('F j, Y', strtotime(get_post_meta($release_id,'date',true) ) ) ); ?>" disabled="disabled"></label></div>
			<div class="field"><label for="name">Name<input type="text" name="name" id="name" value="<?php echo esc_attr( get_post_meta($release_id,'signedby',true) ); ?>" disabled="disabled"></label></div>
			<div class="field address"><label for="address">Address<input type="text" name="address" id="address" value="<?php echo esc_attr( get_post_meta($release_id,'address',true) ); ?>" disabled="disabled"></label></div>
			<div class="field three"><label for="city"><span>City</span><input type="text" name="city" id="city" value="<?php echo esc_attr( get_post_meta($release_id,'city',true) ); ?>" disabled="disabled"></label>
			<label for="state"><span>State</span><input type="text" name="state" id="state" value="<?php echo esc_attr( get_post_meta($release_id,'state',true) ); ?>" disabled="disabled"></label>
			<label for="zip"><span>Zip Code</span><input type="text" name="zip" id="zip" value="<?php echo esc_attr( get_post_meta($release_id,'zip',true) ); ?>" disabled="disabled"></label></div>
			<div class="field two"><label for="phone">Phone Number:<input type="tel" name="phone" id="phone" value="<?php echo esc_attr( get_post_meta($release_id,'phone',true) ); ?>" disabled="disabled"></label>
			<label for="email">Email<input type="email" name="email" id="email" value="<?php echo esc_attr( get_post_meta($release_id,'email',true) ); ?>" disabled="disabled"></label></div>
			<div class="field signature"><label for="signature"><span>Signature</span>
					
			
			
			  <div id="signature-pad" class="signature-pad">
				<div id="canvas-wrapper" class="signature-pad--body">
					<?php 
						$signature = get_post_meta($release_id,'signature',true);
						if( $signature ) {
							echo '<img src="' . esc_attr( get_post_meta($release_id,'signature',true) ) . '">';
						} else {
							echo 'No signature';
						}
					?>
				</div>
				<div class="signature-pad--footer" style="display:none;">
				  <div class="signature-pad--actions">
					<div class="column">
					  <button type="button" class="button save" data-action="save-png">Save as PNG</button>
					  <button type="button" class="button save" data-action="save-jpg">Save as JPG</button>
					  <button type="button" class="button save" data-action="save-svg">Save as SVG</button>
					</div>
				  </div>
				</div>
			  </div>
			</label></div>
			<hr />
			<p>If above named is a minor child, a parent/guardian must sign.</p>
			<div class="field"><label for="parent">Parent/Guardian Name<input type="text" name="parent" id="parent" value="<?php echo esc_attr( get_post_meta($release_id,'parent',true) ); ?>" disabled="disabled"></label></div>
			<div class="field parent signature"><label for="parent-signature"><span>Parent/Guardian Signature</span>
			  <div id="parent-signature-pad" class="signature-pad">
				<div id="parent-canvas-wrapper" class="signature-pad--body">
					<?php 
						$parentsignature = get_post_meta($release_id,'parentsignature',true);
						if( $parentsignature ) {
							echo '<img src="' . esc_attr( get_post_meta($release_id,'parentsignature',true) ) . '">';
						} else {
							echo 'No signature';
						}
					?>
				</div>
			  </div>
			</label></div></form>
		<div class="entry-links"><?php wp_link_pages(); ?></div>
		<?php edit_post_link( 'Edit “' . get_the_title() . '”' ); ?>
		</div><!-- .entry-content -->
	</article><!-- #post-<?php the_ID(); ?> -->
	<script type="text/javascript">
		var nonce = "<?php echo wp_create_nonce( 'uscadvance' ); ?>";
		var	ajax  = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
	</script>
	<?php
	wp_enqueue_script('jquery', "https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js", false, null, true );
	wp_enqueue_script( 'release-script', ADVANCE_URL . 'js/release.js', false, 1025, true );
	wp_enqueue_style( 'release-style', ADVANCE_URL . 'css/release.css', false, 1025, 'all' );
	}
	get_footer();
} else {
	die();
}
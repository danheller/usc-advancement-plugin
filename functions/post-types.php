<?php

/*--------------------------------------------------------------
Advancement-specific custom post types
--------------------------------------------------------------*/

/*
 * job
 */
function advancement_job_post_type() {

	$labels = array(
		'name'               => _x( 'Jobs', 'Post Type General Name', 'advance' ),
		'singular_name'      => _x( 'Job', 'Post Type Singular Name', 'advance' ),
		'menu_name'          => __( 'Jobs', 'advance' ),
		'all_items'          => __( 'All Jobs', 'advance' ),
		'view_item'          => __( 'View Job', 'advance' ),
		'add_new_item'       => __( 'Add New Job', 'advance' ),
		'add_new'            => __( 'Add New', 'advance' ),
		'edit_item'          => __( 'Edit Job', 'advance' ),
		'update_item'        => __( 'Update Job', 'advance' ),
		'search_items'       => __( 'Search Jobs', 'advance' ),
		'not_found'          => __( 'Not found', 'advance' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'advance' ),
	);
	$args = array(
		'label'               => 'job',
		'description'         => __( 'Advancement Jobs', 'advance' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'revisions', 'excerpt' ),
		'hierarchical'        => false,
		'public'              => false,
		'query_var'           => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 4,
		'menu_icon'           => 'dashicons-networking',
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'rewrite'             => array(
			'slug'         => 'job',
			'with_front'   => false,
			'hierarchical' => false,
		),
	);

	register_post_type( 'job', $args );
	register_taxonomy_for_object_type( 'category', 'job' );
	register_taxonomy_for_object_type( 'post_tag', 'job' );
}
add_action( 'init', 'advancement_job_post_type', 0 );


/*
 * photo release
 */
function photo_release_post_type() {

	$labels = array(
		'name'               => _x( 'Photo Releases', 'Post Type General Name', 'advance' ),
		'singular_name'      => _x( 'Release', 'Post Type Singular Name', 'advance' ),
		'menu_name'          => __( 'Releases', 'advance' ),
		'all_items'          => __( 'All Releases', 'advance' ),
		'view_item'          => __( 'View Release', 'advance' ),
		'add_new_item'       => __( 'Add New Release', 'advance' ),
		'add_new'            => __( 'Add New', 'advance' ),
		'edit_item'          => __( 'Edit Release', 'advance' ),
		'update_item'        => __( 'Update Release', 'advance' ),
		'search_items'       => __( 'Search Releases', 'advance' ),
		'not_found'          => __( 'Not found', 'advance' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'advance' ),
	);
	$args = array(
		'label'               => 'release',
		'description'         => __( 'Signed photo release forms', 'advance' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'revisions', 'excerpt' ),
		'hierarchical'        => false,
		'public'              => false,
		'query_var'           => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 4,
		'menu_icon'           => 'dashicons-camera',
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
		'rewrite'             => array(
			'slug'         => 'release',
			'with_front'   => false,
			'hierarchical' => false,
		),
	);

	register_post_type( 'release', $args );
	register_taxonomy_for_object_type( 'post_tag', 'release' );
}
add_action( 'init', 'photo_release_post_type', 0 );

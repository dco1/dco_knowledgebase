<?php

/*
 * Plugin Name: DCo Knowledgebase 
 * Version: May 31, 2016
 * Description: Manage Knowledgebase articles, search, and user submissions.
 * Author: Danny Cohen
 */



// Custom Post Type Setup

add_action( 'init', 'register_cpt_dco_knowledgebase' );
function register_cpt_dco_knowledgebase() {

	$labels = array(
		'name' => __( 'Knowledgebase Articles', 'dco_knowledgebase' ),
		'singular_name' => __( 'KB Article', 'dco_knowledgebase' ),
		'add_new' => __( 'Add New Article', 'dco_knowledgebase' ),
		'add_new_item' => __( 'Add New Knowledgebase Article', 'dco_knowledgebase' ),
		'edit_item' => __( 'Edit KB Article', 'dco_knowledgebase' ),
		'new_item' => __( 'New KB Article', 'dco_knowledgebase' ),
		'view_item' => __( 'View KB Articles', 'dco_knowledgebase' ),
		'search_items' => __( 'Search Knowledgebase', 'dco_knowledgebase' ),
		'not_found' => __( 'Nothing found', 'dco_knowledgebase' ),
		'not_found_in_trash' => __( 'Nothing found in Trash', 'dco_knowledgebase' ),
		'parent_item_colon' => __( 'Parent Articles:', 'dco_knowledgebase' ),
		'menu_name' => __( 'Knowledgebase', 'dco_knowledgebase' ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'page-attributes' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 20,
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => true,
		'has_archive' => false,
		'query_var' => true,
		'can_export' => true,
		'taxonomies' => array( 'post_tag'),
		'menu_icon' => 'dashicons-lightbulb',
		'rewrite' => array(
			'slug' => 'knowledgebase',
			'with_front' => true,
			'feeds' => true,
			'pages' => true
		),
		'capability_type' => 'post'
	);

	register_post_type( 'dco_knowledgebase', $args );
}

add_action('init', 'dco_kb_rewrite_basic');
function dco_kb_rewrite_basic() {
    add_rewrite_rule('^knowledgebase/([^/]*)/([^/]*)/?','index.php?$matches[1]=$matches[2]&post_type=dco_knowledgebase','top');
}



///////////
// Setup //
///////////

add_action( 'init', 'dco_kb_register_resources' );
function dco_kb_register_resources() {
	wp_register_style('dco_kb_style', plugins_url( '/dco_knowledgebase.css' , __FILE__ ) );
    wp_register_script( 'dco_kb_script', plugins_url( '/dco_knowledgebase.js' , __FILE__ ), array( 'jquery', 'jquery-ui-autocomplete'  ) );
	wp_localize_script( 'dco_kb_script', 'ajax_url',  admin_url( 'admin-ajax.php' ));


}

function dco_kb_setup_kb_page(){
	wp_enqueue_script( 'dco_kb_script');
	wp_enqueue_style('dco_kb_style');
	add_filter('term_link', 'dco_kb_rewrite_term_edit_link', 10, 3);
}


add_action('wp_head', 'dco_kb_setupfrontend_scripts');
function dco_kb_setupfrontend_scripts(){
	global $post;
	if ( isset($post) &&  $post->post_type == 'dco_knowledgebase'){
		wp_localize_script( 'dco_kb_script', 'post_id',  "$post->ID" );
		dco_kb_setup_kb_page();
	}
}

function dco_kb_rewrite_term_edit_link(  $termlink, $term, $taxonomy  ){
	return str_replace( site_url() , site_url() . '/knowledgebase', $termlink);
}



////////////////////////
// ARTICLE MANAGEMENT //
////////////////////////



///////////////
// Front End //
///////////////


add_shortcode('knowledgebase', 'dco_kb_render_front');
function dco_kb_render_front(){
	dco_kb_setup_kb_page();	
	?>
	<div class="dco_kb_header">
		<h2>Knowledgebase</h2>
		<?php
			dco_kb_search_form();
		?>
	</div>
	<div class="dco_kb_toc clearfix">
		<?php
			dco_kb_toc();
		?>
	</div>
	<div class="dco_kb_tags">
		<h4>Tags</h4>
		<?php
			dco_kb_tag_cloud();
		?>
	</div>
	<div class="dco_kb_new">
		<h4><a href="#" class"button" id="dco_ask_a_question">Ask a Question</a></h4>
	</div>
	<?php 
	
	dco_kb_render_new_user_submission_form();

}


function dco_kb_search_form(){
	?>
	<div id="dco_search_form_wrapper">
	<form id="dco_kb_search_form">
		<label for="s"><input type="search" name="s" id="dco_kb_search_form_search_field" placeholder="What are you looking for?" /></label>
		<input type="hidden" name="post_type" value="dco_knowledgebase" />

	</form>
	</div>
	<?php
}


function dco_kb_toc(){
	$terms = get_terms_id_by_post_type( array('post_tag'), array('dco_knowledgebase') );
	$i = 0;
	foreach($terms as $term)  if ($i++ < 10) {
		dco_kb_toc_group($term);
	}
}

function dco_kb_toc_group($term_id){
	$term = get_term($term_id, 'post_tag');
	?>
	<div class="dco_kb_toc_group">
		<h4 class="dco_kb_toc_group_title"><a href="<?php echo get_term_link($term); ?>"><?php echo $term->name ?></a></h4>
		<ul>
			<?php
				$articles_for_tag = new WP_Query(array( 'post_type' => 'dco_knowledgebase' , 'post_status' => 'publish',  'tag' => $term->slug ));
				if ( $articles_for_tag->have_posts() ) {
					while ( $articles_for_tag->have_posts() ) { $articles_for_tag->the_post();
						echo "<li><a href='". get_permalink() ."'>". get_the_title() ."</a></li>";
					}
				} 
			?>
		</ul>
	</div>	
	<?php
}

function dco_kb_tag_cloud( $delimiter = ', ' ){
	$terms = get_terms_id_by_post_type( array('post_tag'), array('dco_knowledgebase') );
		$output = array();
		foreach( $terms as $term ) {
			$term = get_term($term, 'post_tag');
			$output[] = '<a class="kb-tag" href="'. get_term_link($term) .'">'. $term->name .'</a>';
			
		}
	echo implode( $delimiter , $output );
}

function get_terms_id_by_post_type( $taxonomies, $post_types ) {
    global $wpdb;
    $query = $wpdb->get_col( "SELECT t.term_id from $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id WHERE p.post_type IN('" . join( "', '", $post_types ) . "') AND tt.taxonomy IN('" . join( "', '", $taxonomies ) . "') GROUP BY t.term_id");
    return $query;
}

////////////
// SEARCH //
////////////
add_action('wp_ajax_dco_kb_article_search', 'dco_kb_article_search_ajax_cb');
add_action('wp_ajax_nopriv_dco_kb_article_search', 'dco_kb_article_search_ajax_cb');
function dco_kb_article_search_ajax_cb(){
	 if ( ! isset( $_GET['q'] ) ) {
        wp_die( 0 );
    }

	$new_search = new WP_Query(array( 's' => $_GET['q'], 'post_type' => 'dco_knowledgebase' , 'post_status' => 'publish' ));
	
	// The Loop
	if ( $new_search->have_posts() ) {
		while ( $new_search->have_posts() ) {
			$new_search->the_post();
			add_filter( 'excerpt_length', function(){ return 20;}, 999 );
			add_filter( 'excerpt_more', function(){"&hellip;";});
			$results[] =  array("label" =>  get_the_title() , "value" => get_permalink(), 'excerpt' => get_the_excerpt() );
		}
	} 
	wp_send_json($results);
}


//////////////////////
// USER SUBMISSIONS //
//////////////////////


function dco_kb_render_new_user_submission_form(){
	?>
	<div id="dco_kb_new_question_wrapper">
	<form id="dco_kb_new_user_submission_form">
		<fieldset>
			<label for="question">What question do you have?</label>
			<input name="question" class="user-question" type="text" />
		</fieldset>
		<fieldset>
			<p>Your Information (Optional)</p>
			<label for="user['name']">Your Name </label>
			<input type="text" class="user-name" name="user['name']" />
			<label for="user['email']">Your Email</label>
			<input type="text" class="user-email" name="user['email']" />   
		</fieldset>
		<fieldset>
			<?php wp_nonce_field( 'ask_a_question' , 'ask_a_question_nonce' ) ?>
			<input type="submit" value="Submit Your Question" />
		</fieldset>
	</form>
	</div>
	<?php
}

add_action('wp_ajax_dco_kb_question_submit', 'dco_kb_new_user_submission_ajax_cb');
add_action('wp_ajax_nopriv_dco_kb_question_submit', 'dco_kb_new_user_submission_ajax_cb');
function dco_kb_new_user_submission_ajax_cb(){
	check_admin_referer( 'ask_a_question' , 'ask_a_question_nonce');
	if ( !isset($_POST['question']) ) return wp_send_json_error('empty question');
				
	$post_title = sanitize_text_field( $_POST['question'] );
	$name = sanitize_text_field( $_POST['name'] );
	$email = sanitize_email( $_POST['email'] );
	
	
	$new_post_return = dco_kb_insert_new_unanswered_article( $post_title , $name, $email );
	
	if ( is_numeric($new_post_return)){
		wp_send_json_success('question submitted');
	}
	
	wp_send_json( $new_post_return );
	
}

function dco_kb_insert_new_unanswered_article( $question, $name = null, $email = null){
	$new_question_into_post = wp_insert_post(array(
		'post_title' => $question,
		'post_type' => 'dco_knowledgebase',
		'meta_input' => array(
        	'submitter_name' => $name,
			'submitter_email' => $email,
		),
	));
	
	if ( $new_question_into_post > 0 ){
		do_action('dco_kb_new_user_submission_added', $new_question_into_post );
		return $new_question_into_post;
	} elseif ( is_wp_error( $new_question_into_post ) ) {
		return $new_question_into_post->get_error_message();
	}
	return false;	
}

add_action( 'add_meta_boxes', 'dco_kb_register_submission_info_metabox' );
function dco_kb_register_submission_info_metabox() {
    add_meta_box( 'dco_kb_submission_info', __( 'Submitter', 'textdomain' ), 'dco_kb_submission_info_cb', 'dco_knowledgebase', 'side', 'high' );
}
 
function dco_kb_submission_info_cb( $post ) {
	echo "<p><strong>Name</strong>: " . get_post_meta( $post->ID, 'submitter_name', true) . "</p>";
	echo "<p><strong>Email</strong>: " . get_post_meta( $post->ID, 'submitter_email', true) . "</p>";
}


///////////////////////////
// UPVOTING / DOWNVOTING //
///////////////////////////

add_filter('the_content', 'dco_kb_add_article_vote_buttons_end_of_content');
function dco_kb_add_article_vote_buttons_end_of_content( $content ){
	if (!is_singular('dco_knowledgebase')) return $content;
	global $post;
	
	$upvotes = get_post_meta( $post->ID, 'upvote_count', true);
	$downvotes = get_post_meta ( $post->ID , 'downvote_count', true);

	$content .= '<div class="dco_kb_vote_buttons" data-upvotes="'.$upvotes.'" data-downvotes="'.$downvotes.'">' .  wp_nonce_field( 'article-vote_'.$post->ID , 'vote_nonce' ) . '</div>';
	return $content;
}

add_action('wp_ajax_dco_kb_article_vote', 'dco_kb_article_vote_cb');
add_action('wp_ajax_nopriv_dco_kb_article_vote', 'dco_kb_article_vote_cb');
function dco_kb_article_vote_cb(){
	if (!isset($_POST['post_id'] ) || !is_numeric($_POST['post_id'])) return false;
	check_admin_referer( 'article-vote_'.$_POST['post_id'] , 'vote_nonce');
	if (!isset($_POST['vote'])) return false;
	
	$post_id = $_POST['post_id'];
	
	switch ( $_POST['vote'] ){
		case "upvote" :
			$current_count = (int) get_post_meta(  $post_id , 'upvote_count', true);
			if ( empty( $current_count ) ) $current_count = 0;
			$current_count++;
			$return = update_post_meta( $post_id, 'upvote_count', $current_count );
		break;
		case "downvote" :
			$current_count = (int) get_post_meta( $post_id , 'downvote_count', true);
			if ( empty( $current_count ) ) $current_count = 0;
			$current_count++;
			$return = update_post_meta( $post_id , 'downvote_count', $current_count);
		break;
		
	}
	
	wp_send_json( array('new_count' => $current_count ));
}

///////////////////////////
// The Unanswered Digest //
///////////////////////////

function dco_kb_build_and_send_unanswered_digest(){
	
	$results = array();
	$unanswered_digest = new WP_Query(array( 'post_type' => 'dco_knowledgebase' , 'post_status' => 'draft' ));
	
	if ( $unanswered_digest->have_posts() ) {
		while ( $unanswered_digest->have_posts() ) {
			$unanswered_digest->the_post();
			if($post->post_content != "") $results[] =  array("title" =>  get_the_title() , "edit_link" => get_edit_post_link( $post->ID ) );
		}
	} 
	
	add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
	
	$params = array();
	$params['to'] = get_bloginfo('admin_email');
	$params['subject'] = "[" . get_bloginfo('name') . "] - Unanswered Knowledgebase Questions - " .$unanswered_digest->found_posts;
	$params['message'] = '<h3>Unanswered Knowledgebase Questions</h3><ul>';
	foreach( $results as $result){
		$params['message'] .= "<li><a href='" . $result['edit_link'] . "'>" . $result['title'] ."</a></li>";
	}
	$params['message'] .= '</ul>';
	$params['headers'] = '';
	
	$params = apply_filters('dco_kb_unanswered_digest_params', $params);
	
	$mailer = wp_mail( $params['to'], $params['subject'], $params['message'], $params['headers'] );
	
	if ( $mailer ){
		
	} else {
		
	}
}



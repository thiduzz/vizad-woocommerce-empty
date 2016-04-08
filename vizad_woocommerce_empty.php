<?php

/*
 *	Plugin Name: Vizad - WooCommerce Empty Easy
 *	Plugin URI: http://vizad.com.br/vizad-cleaner-integration/
 *	Description: Clean everything, quickly
 *	Version: 1.0
 *	Author: Thiago Mello
 *	Author URI: http://vizad.com.br
 *	License: GPL2
 * 	token: 35AA2CCB99DE4BB4B4275DA3B3A50E02
*/

/************************************************/
/*** VARIAVEIS GLOBAIS DO PLUGIN 			*****/
/************************************************/

$options = array();
$vizad_display_json = false;

/************************************************/
/*** ESTILOS DO PLUGIN 						*****/
/************************************************/

function vizad_cleaner_enqueue_icon_stylesheet() {
	wp_register_style( 'fontawesome', 'http:////maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
	wp_enqueue_style( 'fontawesome');
}
add_action( 'admin_enqueue_scripts', 'vizad_cleaner_enqueue_icon_stylesheet' );

function vizad_cleaner_backend_styles()
{
	wp_enqueue_style('vizad_cleaner_backend_styles', plugins_url('vizad-woocommerce-empty/vizad-cleaner.css'));
}

add_action('admin_head', 'vizad_cleaner_backend_styles');

function vizad_cleaner_frontend_scripts_styles()
{
	wp_enqueue_style('vizad_cleaner_frontend_styles', plugins_url('vizad-woocommerce-empty/vizad-cleaner.css'));
	wp_enqueue_script('vizad_cleaner_frontend_js', plugins_url('vizad-woocommerce-empty/vizad-cleaner.js'), array('jquery'),'', true);
}

add_action('wp_enqueue_scripts', 'vizad_cleaner_frontend_scripts_styles');

function vizad_cleaner_integration_menu(){
	add_options_page(
		'WooCommerce Cleaner',
		'WooCommerce Cleaner',
		'manage_options',
		'woocommerce-cleaner',
		'vizad_cleaner_integrations_options_page'
		);
}

add_action('admin_menu','vizad_cleaner_integration_menu');

/************************************************/
/*** PAGINA DO PLUGIN 						*****/
/************************************************/

function vizad_cleaner_integrations_options_page()
{
	if(!current_user_can('manage_options'))
	{
		wp_die('Você não tem permissão para acessar essa página!');
	}

	$vizad_plugin_message = null;

	if(isset( $_POST['vizad_form_submitted']))
	{
		$hidden_field = esc_html( $_POST['vizad_form_submitted'] );
		if($hidden_field == 'Y')
		{
			
				$vizad_plugin_message = vizad_cleaner_get_woocommerce_sanitize_products();	
		}
	}
	require('inc/options-page-wrapper.php');
}





function vizad_cleaner_get_woocommerce_sanitize_products() {

			$args = array( 
				'post_type'   => array( 'product', 'product_variation' ),
				'post_status' => get_post_stati(),
				'numberposts' => 500, 
			);
			$products = get_posts( $args );

			foreach( $products as $product ) {

				wp_delete_post( $product->ID, $force_delete = true );

			}
	        $terms = get_terms( 'pa_color', array('hide_empty' => false ) );
			foreach ( $terms as $value ) {
               wp_delete_term( $value->term_id, 'pa_color' );
			}
			$terms = get_terms( 'pa_size', array('hide_empty' => false ) );
			foreach ( $terms as $value ) {
               wp_delete_term( $value->term_id, 'pa_size' );
			}
			
			$terms = get_terms( 'product_cat', array( 'hide_empty' => false ) );
			foreach ( $terms as $value ) {
               wp_delete_term( $value->term_id, 'product_cat' );
			}
			$vizad_plugin_message = array('success' => true, 'msg'=>'Remoção realizada com sucesso!');
			return $vizad_plugin_message;
}



?>


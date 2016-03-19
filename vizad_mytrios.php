<?php

/*
 *	Plugin Name: Vizad - Mytrius Integration
 *	Plugin URI: http://vizad.com.br/vizad-mytrius-integration/
 *	Description: Provides integration with the mytrius ERP API for WooCommerce.
 *	Version: 1.0
 *	Author: Thiago Mello
 *	Author URI: http://vizad.com.br
 *	License: GPL2
 * 	token: 35AA2CCB99DE4BB4B4275DA3B3A50E02
*/


function wmpudev_enqueue_icon_stylesheet() {
	wp_register_style( 'fontawesome', 'http:////maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
	wp_enqueue_style( 'fontawesome');
}
add_action( 'admin_enqueue_scripts', 'wmpudev_enqueue_icon_stylesheet' );

add_action( 'init', 'vizad_product_cat_register_meta' );

function vizad_product_cat_register_meta() {
	register_meta( 'term', 'mytrius-id', 'vizad_sanitize_mytrius_id' );
}

function vizad_sanitize_mytrius_id( $mytrius_id ) {
	return $mytrius_id;
}

/** Designa variaveis globais **/

$options = array();
$vizad_display_json = false;

function vizad_mytrius_backend_styles()
{
	wp_enqueue_style('vizad_mytrius_backend_styles', plugins_url('vizad-mytrius-integration/vizad-mytrius.css'));
}

add_action('admin_head', 'vizad_mytrius_backend_styles');

function vizad_mytrius_frontend_scripts_styles()
{
	wp_enqueue_style('vizad_mytrius_frontend_styles', plugins_url('vizad-mytrius-integration/vizad-mytrius.css'));
	wp_enqueue_script('vizad_mytrius_frontend_js', plugins_url('vizad-mytrius-integration/vizad-mytrius.js'), array('jquery'),'', true);
}

add_action('wp_enqueue_scripts', 'vizad_mytrius_frontend_scripts_styles');

add_action( 'product_cat_edit_form_fields', 'vizad_edit_feature_group_field', 10, 2 );
function vizad_edit_feature_group_field( $term, $taxonomy ){
                
    $mytrius_id = get_term_meta( intval($term->term_id), 'mytrius-id', true );      
    ?><tr class="form-field term-group-wrap">
        <th scope="row"><label for="feature-group"><?php _e( 'mytrius ID', 'vizad' ); ?></label></th>
        <td>
        <input type="text" disabled="disabled" value="<?php echo $mytrius_id; ?>">
        </td>
    </tr><?php
}

// add custom interval
function cron_add_minute( $schedules ) {
	// Adds once every minute to the existing schedules.
    $schedules['mytriuscooldown'] = array(
	    'interval' => 180,
	    'display' => __( 'A cada 3 minutos' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'cron_add_minute' );

function vizad_integration_menu(){
	/*Adiciona a página (titulo, titulo do menu, capacidade, slug, funcao)*/
	add_options_page(
		'Vizad - Mytrius Integration',
		'Mytrius Integration',
		'manage_options',
		'mytrius-integration',
		'vizadplug_integrations_options_page'
		);
}

add_action('admin_menu','vizad_integration_menu');


function vizadplug_integrations_options_page()
{
	if(!current_user_can('manage_options'))
	{
		wp_die('Você não tem permissão para acessar essa página!');
	}

	global $options;
	global $vizad_display_json;
	$vizad_plugin_message = null;

	if(isset( $_POST['vizad_form_submitted']))
	{
		$hidden_field = esc_html( $_POST['vizad_form_submitted'] );
		if($hidden_field == 'Y')
		{
			if(isset($_POST['vizad_submit_update']))
			{

					$options = get_option('vizad_mytrius');
					if(isset($_POST['vizad_shop_cron']))
					{
						if($_POST['vizad_shop_cron'] == 'on')
						{
							$options['vizad_shop_cron'] = true;
						}else{
							$options['vizad_shop_cron'] = false;
						}			
					}else{
						$options['vizad_shop_cron'] = false;						
					}
					if(isset($_POST['vizad_shop_token']))
					{
						$options['vizad_shop_token'] = esc_html($_POST['vizad_shop_token']);
					}else{
						$options['vizad_shop_token'] = '';					
					}

					if(isset($_POST['vizad_shop_min_stock']))
					{
						$options['vizad_shop_min_stock'] = esc_html($_POST['vizad_shop_min_stock']);
					}else{
						$options['vizad_shop_min_stock'] = 0;					
					}
					update_option('vizad_mytrius', $options);
					$vizad_plugin_message = array('success' => true, 'msg'=>'Atualização de config. realizada com sucesso!');

			}elseif(isset($_POST['vizad_submit_import']))
			{

				$vizad_shop_token = esc_html($_POST['vizad_shop_token']);
				$options = get_option('vizad_mytrius');
				if(isset($_POST['vizad_shop_cron']))
				{
					if($_POST['vizad_shop_cron'] == 'on')
					{
						$options['vizad_shop_cron'] = true;
					}else{
						$options['vizad_shop_cron'] = false;
					}			
				}else{
					$options['vizad_shop_cron'] = false;						
				}

				if(isset($_POST['vizad_shop_min_stock']))
				{
					$options['vizad_shop_min_stock'] = esc_html($_POST['vizad_shop_min_stock']);
				}else{
					$options['vizad_shop_min_stock'] = 0;					
				}
				$vizad_shop = vizad_mytrius_get_shop($vizad_shop_token, $options, false);
				if(is_string($vizad_shop))
				{
					$vizad_plugin_message = array('success' => false, 'msg'=>$vizad_shop);
				}else{
					$options['vizad_shop_token'] = $vizad_shop_token;	
					$options['vizad_shop_products'] = $vizad_shop->Produtos;
					$options['vizad_shop_colors'] = $vizad_shop->Cores;
					$options['vizad_shop_sizes'] = $vizad_shop->Tamanhos;
					if(isset($_POST['vizad_shop_cron']))
					{
						if($_POST['vizad_shop_cron'] == 'on')
						{
							$options['vizad_shop_cron'] = true;
						}else{
							$options['vizad_shop_cron'] = false;
						}			
					}else{
						$options['vizad_shop_cron'] = false;						
					}
					$options['last_updated'] = time();	
					update_option('vizad_mytrius', $options);
					//vizad_mytrius_get_woocommerce_sanitize_products(vizad_mytrius_get_all_mytrius_products($vizad_shop->Produtos));
					foreach ($vizad_shop->Produtos as $key => $product) {
						vizad_mytrius_woocommerce_checks($product);
					}
					$vizad_plugin_message = array('success' => true, 'msg'=>'Importação realizada com sucesso!');
				}
			}
		}
	}

	$options = get_option('vizad_mytrius');

	if($options != '')
	{
		$vizad_shop_token = $options['vizad_shop_token'];
		$vizad_shop_min_stock = $options['vizad_shop_min_stock'];
		$vizad_shop_products = $options['vizad_shop_products'];
		$vizad_shop_colors = $options['vizad_shop_colors'];
		$vizad_shop_sizes = $options['vizad_shop_sizes'];
		$vizad_shop_cron = $options['vizad_shop_cron'];
		$vizad_last_updated = $options['last_updated'];
	}
	$log_files = vizad_mytrius_current_debug_logs();
	require('inc/options-page-wrapper.php');
}

// unschedule event upon plugin deactivation
function vizadplug_cron_deactivate() {	
	// find out when the last event was scheduled
	$timestamp = wp_next_scheduled ('mytrius-sync');
	// unschedule previous event if any
	wp_unschedule_event ($timestamp, 'mytrius-sync');
} 
register_deactivation_hook (__FILE__, 'vizadplug_cron_deactivate');

// create a scheduled event (if it does not exist already)
function vizadplug_cron_activation() {
	if( !wp_next_scheduled( 'mytrius-sync' ) ) {  
	   wp_schedule_event( time(), 'mytriuscooldown', 'mytrius-sync' );  
	}
}
// and make sure it's called whenever WordPress loads
add_action('wp', 'vizadplug_cron_activation');

function vizadplug_integrations_cron_call()
{

	global $options;
	$options = get_option('vizad_mytrius');
	if($options['vizad_shop_cron'] == true)
	{
		$vizad_shop_token = $options['vizad_shop_token'];
		$vizad_shop = vizad_mytrius_get_shop($vizad_shop_token, $options, false);
		if(!is_string($vizad_shop))
		{
			$options['vizad_shop_products'] = $vizad_shop->Produtos;
			$options['vizad_shop_colors'] = $vizad_shop->Cores;
			$options['vizad_shop_sizes'] = $vizad_shop->Tamanhos;
			$options['last_updated'] = time();	
			update_option('vizad_mytrius', $options);
			foreach ($vizad_shop->Produtos as $key => $product) {
				vizad_mytrius_woocommerce_checks($product);
			}
		} 
	}
}

add_action ('mytrius-sync', 'vizadplug_integrations_cron_call'); 


/*****************************************************
Get entire shop with the Mitryus API and call 

*****************************************************/
//
function vizad_mytrius_get_shop($vizad_shop_token, $options = null, $trash_sanitization = false)
{
	$json_feed_url = 'http://ws.mitryusweb.com.br/Api/PacoteDados?apikey='.$vizad_shop_token.'&type=json';
	$args = array('timeout'=>120);
	$json_feed = wp_remote_get($json_feed_url, $args);
	$body = json_decode($json_feed['body']);
	if($body->Message != null)
	{
		return $body->Message;
	}else{
		$vizad_shop = $body;
		vizad_mytrius_add_categories($vizad_shop->Grupos);
		$vizad_shop->Cores = vizad_mytrius_colors_db($vizad_shop->Cores, $options['vizad_shop_colors']);
		$vizad_shop->Tamanhos = vizad_mytrius_sizes_db($vizad_shop->Tamanhos, $options['vizad_shop_sizes']);
		$vizad_shop->Produtos = vizad_mytrius_products_db($vizad_shop->Produtos, $vizad_shop->CodigosBarra, $vizad_shop->Tamanhos, $vizad_shop->Cores, $options['vizad_shop_products']);
		if($trash_sanitization == true)
		{
			vizad_mytrius_get_woocommerce_sanitize_products(vizad_mytrius_get_all_mytrius_products($vizad_shop->Produtos),$delete_sanitization);
		}
		return $vizad_shop;
	}
}

function vizad_mytrius_colors_db($colors = null, $old_colors = null)
{
	if($old_colors == null)
	{
		$old_colors = array();			
	}
	if($colors != null)
	{
		foreach ($colors as $key => $color) {
				$old_colors[$color->cod_cor] = $color->nome_cor;
		}
		
	}
	return $old_colors;
}

function vizad_mytrius_products_db($products = null, $estoques = null, $tamanhos, $cores, $old_products = null)
{
	if($old_products == null)
	{
		$old_products = array();
	}
	if($products != null)
	{
		foreach ($products as $key => $product) {
				if(!property_exists($product, 'variaveis'))
				{
					$product->variaveis = array();					
				}
				$old_products[$product->cod_produto] = $product;
		}
		
	}


	if($estoques != null && !empty($estoques))
	{
		foreach ($estoques as $key_e => $estoque) {
			foreach ($old_products as $key_p => $product) {
				if($estoque->cod_produto == $product->cod_produto)
				{
					$old_products[$key_p]->variaveis[$estoque->cod_barra] = vizad_apply_barcode_relations($estoque, $tamanhos, $cores);
				}
			}

		}
	}
	return $old_products;
	/**
	foreach ($products as $key => $product) {
		$product->variaveis = array();
		foreach ($estoques as $key => $estoque) {
			if($estoque->cod_produto == $product->cod_produto)
			{
				$product->variaveis[] = vizad_apply_barcode_relations($estoque, $tamanhos, $cores);

			}
		}
		$db_products[$product->cod_produto] = $product;
	}
	return $db_products;
	**/
}

function vizad_mytrius_sizes_db($sizes = null, $old_sizes = null)
{

	if($old_sizes == null)
	{
		$old_sizes = array();			
	}
	if($sizes != null)
	{
		foreach ($sizes as $key => $size) {
				$old_sizes[$size->cod_tamanho] = $size->nome_tamanho;
		}
		
	}
	return $old_sizes;
}

function vizad_apply_barcode_relations($estoque, $tamanhos, $cores)
{
	for ($i=1; $i <= 3; $i++) { 
		if($estoque->{"cod_cor_".$i} > 0)
		{
			$estoque->{"cor_".$i} = $cores[$estoque->{"cod_cor_".$i}];		
		}
	}
	$estoque->tamanho = $tamanhos[$estoque->cod_tamanho];
	return $estoque;
}

function vizad_mytrius_woocommerce_checks($product = null)
{
	$args = array(
     'post_type' => 'product',
     'post_status' => 'publish',
     'posts_per_page'=>-1,
     'meta_query' => array(
		array(
			'key' => '_sku',
			'value' => $product->dsc_referencia,
			'compare' => '=',
			'type' => 'CHAR'
		))
	);

	$products_query = new WP_Query( $args );
	if( $products_query->have_posts() ) {
	   while ($products_query->have_posts()) : $products_query->the_post(); 

		add_to_debug('----------------Refresh Product Method-------------------');
		vizad_mytrius_refresh_product($products_query->post->ID, $product);
	   endwhile;
	   wp_reset_postdata();
	}else{

		add_to_debug('----------------Add Product Method-------------------');
		vizad_mytrius_add_product($product);
	}

	wp_reset_query();
}

function vizad_mytrius_get_all_mytrius_products($products)
{
	$full_product_list = array();
	$full_product_list['product'] = array();
	$full_product_list['product_variation'] = array();
	foreach ($products as $key => $product) {
		if($product->dsc_referencia != null && $product->dsc_referencia != '')
		{


			$full_product_list['product'][] = 	$product->dsc_referencia;			
			if(count($product->variaveis))
			{
				foreach ($product->variaveis as $key => $variavel) {
						if($variavel->cod_barra != null && $variavel->cod_barra != '')
						{
							$full_product_list['product_variation'][] = $variavel->cod_barra;
						}
				}
			}		
		}
	}
	return $full_product_list;
}

function vizad_mytrius_refresh_product($original_id, $product)
{

		//não é variacao
		add_to_debug('--------------------------------------------------------');
		add_to_debug('Atualizando Produto ID ' .$product->dsc_referencia. '-'. $product->nome_produto .'------');

		$args = array(
		'hide_empty' => false,
		'meta_query' => array(
	    array(
	       'key'       => 'mytrius-id',
	       'value'     =>  $product->cod_grupo,
	       'compare'   => '='
	    )));
		//Remove categoria antiga
		wp_delete_object_term_relationships( $original_id, 'product_cat');
		$terms = get_terms( 'product_cat', $args );	
		if(!empty($terms))
		{
			wp_set_object_terms( $original_id, $terms[0]->term_id, 'product_cat' );
		}
		//Remove Tipo de Produto antigo		
		wp_delete_object_term_relationships( $original_id, 'product_type');
		//Verifica se as variações ainda existem para definir o tipo de produto
		if(count($product->variaveis) || count($product->variaveis) > 1)
		{
			wp_set_object_terms ($original_id, 'variable', 'product_type');
			vizad_mytrius_refresh_variations($original_id, $product);
		}else{
			//define atributos de produto simples
		    wp_set_object_terms($original_id, 'simple', 'product_type');
			update_post_meta( $original_id,  '_sale_price', $product->vl_venda_vista );
			update_post_meta( $original_id,  '_regular_price', $product->vl_venda_prazo);
			update_post_meta( $original_id,  '_weight', $product->peso_bruto );
			update_post_meta( $$original_id,  '_manage_stock', "yes" );
		   	update_post_meta( $original_id,  '_visibility', 'visible' );	
		    if(count($product->variaveis))
		    {
		    	//pega a única variável 
		    	$variavel = reset($product->variaveis);

				update_post_meta( $original_id,  '_stock',  $variavel->qnt_estoque_disponivel);
		    	if($variavel->qnt_estoque_disponivel >= get_option('vizad_mytrius')['vizad_shop_min_stock'])
		    	{
	 			update_post_meta( $original_id,  '_stock_status', 'instock');	
		    	}else{
	 				update_post_meta( $original_id,  '_stock_status', 'outofstock');
		    	}
				//update_post_meta( $variations_query->post->ID, '_sku', $variavel->cod_barra );
				update_post_meta( $original_id,  '_length', "" ); //TODO
				update_post_meta( $original_id,  '_width', "" );	//TODO
				update_post_meta( $original_id,  '_height', "" );	//TODO		    	
		    }else{
	 			update_post_meta( $original_id,  '_stock_status', 'outofstock');
		    }
		}
		if($product->is_ativo == true && $product->is_fora_linha == false)
		{
	    	update_post_meta( $original_id, '_visibility', 'visible' );		
		}else{
	    	update_post_meta( $original_id, '_visibility', 'hidden' );
		}
}

function vizad_mytrius_get_posts_children($parent_id){
    $children = array();
    $posts = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish', 'post_type' => 'product_variation', 'post_parent' => $parent_id, 'suppress_filters' => false ));
    foreach( $posts as $child ){
        $gchildren = vizad_mytrius_get_posts_children($child->ID);
        if( !empty($gchildren) ) {
            $children = array_merge($children, $gchildren);
        }
    }
    $children = array_merge($children,$posts);
    return $children;
}

function vizad_mytrius_refresh_variations($parent_product_id, $product)
{

    $variable_id = $parent_product_id + 1;
	foreach ($product->variaveis as $key => $variavel) {
		$args = array(
		     'post_type' => 'product_variation',
		     'post_status' => 'publish',
		     'posts_per_page'=>-1,
		     'meta_query' => array(
				array(
					'key' => '_sku',
					'value' => $variavel->cod_barra,
					'compare' => '=',
					'type' => 'CHAR'
				))
			);

		$variations_query = new WP_Query( $args );
		if( $variations_query->have_posts() ) {
		   while ($variations_query->have_posts()) : $variations_query->the_post(); 
				add_to_debug('--------Variaveis de Produto - Inicializando Atualização--------');
				update_post_meta( $variations_query->post->ID, '_sale_price', $product->vl_venda_vista );
			    update_post_meta( $variations_query->post->ID, '_regular_price', $product->vl_venda_prazo);
			    update_post_meta( $variations_query->post->ID, '_manage_stock', "yes" );
			    update_post_meta( $variations_query->post->ID,  '_stock',  $variavel->qnt_estoque_disponivel);
			    if($variavel->qnt_estoque_disponivel >= get_option('vizad_mytrius')['vizad_shop_min_stock'])
			    {
		 			update_post_meta( $variations_query->post->ID,  '_stock_status', 'instock');	
			    }else{
		 			update_post_meta( $variations_query->post->ID,  '_stock_status', 'outofstock');
			    }
			    update_post_meta( $variations_query->post->ID, '_sku', $variavel->cod_barra );
			    update_post_meta( $variations_query->post->ID, '_weight', $product->peso_bruto );
			    update_post_meta( $variations_query->post->ID, '_length', "" ); //TODO
			    update_post_meta( $variations_query->post->ID, '_width', "" );	//TODO
			    update_post_meta( $variations_query->post->ID, '_height', "" );	//TODO

				add_to_debug('--------Variaveis de Produto - Parâmetros da Atualização de Variável - ' . $variavel->cor_1 .' e ' . $variavel->tamanho . ' ------');	
				$res = update_post_meta( $variable_id, 'attribute_'. wc_attribute_taxonomy_name( 'color' ), slugify($variavel->cor_1));
				$res = update_post_meta( $variable_id, 'attribute_'. wc_attribute_taxonomy_name( 'size' ), slugify($variavel->tamanho));
				WC_Product_Variable::sync( $parent_product_id );
		   endwhile;
		}else{
			add_to_debug('--------Variaveis de Produto - Iniciando Adição de Nova Variável------');
			$descendants = vizad_mytrius_get_posts_children($parent_product_id);
			$var_post = array(
		    'post_title'=> 'Variação #' . (count($descendants)+1) . ' de ' . count($product->variaveis). ' do produto #'. $parent_product_id,
		    'post_name' => 'product-' . $parent_product_id . '-variacao-' . ($key+1),
		    'post_status' => 'publish',
		    'post_parent' => $parent_product_id,//post is a child post of product post
		    'post_type' => 'product_variation',//set post type to product_variation
		    'guid'=>home_url() . '/?product_variation=product-' . $parent_product_id . '-variation-' . (count($descendants)+1)
		    );
	    	$new_id = wp_insert_post( $var_post );
			update_post_meta( $new_id, '_sale_price', $product->vl_venda_vista );
		    update_post_meta( $new_id, '_regular_price', $product->vl_venda_prazo);
		    update_post_meta( $new_id, '_manage_stock', "yes" );
		    update_post_meta( $new_id,  '_stock',  $variavel->qnt_estoque_disponivel);
		    if($variavel->qnt_estoque_disponivel >= get_option('vizad_mytrius')['vizad_shop_min_stock'])
		    {
	 			update_post_meta( $new_id,  '_stock_status', 'instock');	
		    }else{
	 			update_post_meta( $new_id,  '_stock_status', 'outofstock');
		    }
		    update_post_meta( $new_id, '_sku', $variavel->cod_barra );
		    update_post_meta( $new_id, '_weight', $product->peso_bruto );
		    update_post_meta( $new_id, '_length', "" ); //TODO
		    update_post_meta( $new_id, '_width', "" );	//TODO
		    update_post_meta( $new_id, '_height', "" );	//TODO
			add_to_debug('--------Variaveis de Produto - Parâmetros da Nova Variável - ' . $variavel->cor_1 .' e ' . $variavel->tamanho . ' ------');	
			update_post_meta( $new_id, 'attribute_'. wc_attribute_taxonomy_name( 'color' ), slugify($variavel->cor_1));
			update_post_meta( $new_id, 'attribute_'. wc_attribute_taxonomy_name( 'size' ), slugify($variavel->tamanho));
			WC_Product_Variable::sync( $parent_product_id );
		}
	}
}

function vizad_mytrius_add_product($product)
{
	add_to_debug('--------------------------------------------------------');
	add_to_debug('Adicionando Produto ID ' .$product->dsc_referencia. '-'. $product->nome_produto .'------');
	if($product->dsc_referencia != null && $product->dsc_referencia != '')
	{
		$post = array(
	     'post_author' => get_current_user_id(),
	     'post_content' => $product->dsc_produto_web,
	     'post_status' => "publish",
	     'post_title' => $product->nome_produto,
	     'post_parent' => '',
	     'post_type' => "product"
	    );
	      //Create post
	    $post_id = wp_insert_post( $post, true );

		update_post_meta($post_id, '_sku', $product->dsc_referencia );
		//define categoria do produto
		$args = array(
			    'hide_empty' => false,
			    'meta_query' => array(
		        array(
		           'key'       => 'mytrius-id',
		           'value'     =>  $product->cod_grupo,
		           'compare'   => '='
		       )
		    )
		);
		$terms = get_terms( 'product_cat', $args );	
		if(!empty($terms))
		{
			wp_set_object_terms( $post_id, $terms[0]->term_id, 'product_cat' );
		}
		//fim-define categoria do produto
		if(count($product->variaveis) && count($product->variaveis) > 1)
		{		
				//define variaveis do produto
				wp_set_object_terms ($post_id, 'variable', 'product_type');
				$colors = array();
				$sizes = array();
				foreach ($product->variaveis as $key => $p_variable) {
					if(property_exists($p_variable, 'cod_cor_1'))
			  		{
			  			if($p_variable->cod_cor_1 != null)
			  			{
			  				$colors[$p_variable->cod_cor_1] =  $p_variable->cor_1; 				
			  			}
			  		}
					if(property_exists($p_variable, 'cod_cor_2'))
			  		{
			  			if($p_variable->cod_cor_2 != null)
			  			{
			  				$colors[$p_variable->cod_cor_2] =  $p_variable->cor_2; 				
			  			}
			  		}
					if(property_exists($p_variable, 'cod_cor_3'))
			  		{
			  			if($p_variable->cod_cor_3 != null)
			  			{
			  				$colors[$p_variable->cod_cor_3] =  $p_variable->cor_3; 				
			  			}
			  		}
			  		if(property_exists($p_variable, 'cod_tamanho'))
			  		{
			  			if($p_variable->cod_tamanho != null)
			  			{
			  				$sizes[$p_variable->cod_tamanho] =  $p_variable->tamanho; 				
			  			}
			  		}
				}
				wp_set_object_terms( $post_id, $colors, 'pa_color' );
				wp_set_object_terms( $post_id, $sizes, 'pa_size' );
				$attributes = Array(
					wc_attribute_taxonomy_name( 'color' )=>Array(
						'name'=> wc_attribute_taxonomy_name( 'color' ),
						'value'=>'',
						'is_visible' => '1',
						'is_variation' => '1',
						'is_taxonomy' => '1'
						), 
					wc_attribute_taxonomy_name( 'size' )=>Array(
						'name'=>wc_attribute_taxonomy_name( 'size' ),
						'value'=>'',
						'is_visible' => '1',
						'is_variation' => '1',
						'is_taxonomy' => '1'
						), 
				);
				update_post_meta( $post_id,'_product_attributes',$attributes);
				vizad_mytrius_create_variations($post_id, $product);

				//fim-define variaveis do produto	
		}else{
			//define atributos de produto simples
		    wp_set_object_terms($post_id, 'simple', 'product_type');
			update_post_meta( $post_id, '_sale_price', $product->vl_venda_vista );
			update_post_meta( $post_id, '_regular_price', $product->vl_venda_prazo);
			update_post_meta( $post_id, '_weight', $product->peso_bruto );
			update_post_meta( $post_id, '_manage_stock', "yes" );
		   	update_post_meta( $post_id, '_visibility', 'visible' );	
		    if(count($product->variaveis))
		    {
		    	//pega a única variável 
		    	$variavel = reset($product->variaveis);
		    	update_post_meta( $post_id, '_stock',  $variavel->qnt_estoque_disponivel);
		    	if($variavel->qnt_estoque_disponivel >= get_option('vizad_mytrius')['vizad_shop_min_stock'])
		    	{
	 			update_post_meta( $post_id, '_stock_status', 'instock');	
		    	}else{
	 				update_post_meta( $post_id, '_stock_status', 'outofstock');
		    	}
				//update_post_meta( $variations_query->post->ID, '_sku', $variavel->cod_barra );
				update_post_meta( $post_id, '_length', "" ); //TODO
				update_post_meta( $post_id, '_width', "" );	//TODO
				update_post_meta( $post_id, '_height', "" );	//TODO		    	
		    }else{
	 			update_post_meta( $post_id, '_stock_status', 'outofstock');
		    }
		    //fim-define atributos de produto simples
		}
		if($product->is_ativo == true && $product->is_fora_linha == false)
		{
	    	update_post_meta( $post_id, '_visibility', 'visible' );		
		}else{
	    	update_post_meta( $post_id, '_visibility', 'hidden' );
		}

		}else{
			add_to_debug(' WARNING - Código Produto (mytrius) ' . $product->cod_produto . ' - Produto sem referência foi pulado!');
		}


}

function slugify($text)
{
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
        {return 'n-a';}
        return $text;
}

function vizad_mytrius_add_categories($groups)
{
	//global $wpdb;
   	//$table = $wpdb->prefix . 'termmeta';
	foreach ($groups as $key => $group) {

		$args = array(
		    'hide_empty' => false, // also retrieve terms which are not used yet
		    'meta_query' => array(
		        array(
		           'key'       => 'mytrius-id',
		           'value'     =>  $group->cod_grupo,
		           'compare'   => '='
		        )
		    )
		);
		                
		$terms = get_terms( 'product_cat', $args );		
		if(term_exists($group->nome_grupo, 'product_cat') && ! empty( $terms ))
		{
			$test = wp_update_term($terms[0]->term_id, 'product_cat', array(
			  'name' => $group->nome_grupo
			));
		}else{

			$term_tax = wp_insert_term(	$group->nome_grupo,	'product_cat',	null );
		    //$rows = $wpdb->insert($table, array('term_id' => intval(), 'meta_key' => 'mytrius-id', 'meta_value' => $group->cod_grupo),
		    //    array('%d', '%s', '%d'));
		  	update_term_meta(intval($term_tax['term_id']), 'mytrius-id',	$group->cod_grupo);
	
		}

	}
}


function vizad_mytrius_create_variations($parent_product_id, $product)
{

	add_to_debug('--------Variaveis de Produto - Iniciando Adição de Nova Variável------');
    $variable_id = $parent_product_id + 1;
	foreach ($product->variaveis as $key => $variavel) {
		$var_post = array(
	    'post_title'=> 'Variação #' . ($key+1) . ' de ' . count($product->variaveis). ' do produto #'. $parent_product_id,
	    'post_name' => 'product-' . $parent_product_id . '-variacao-' . ($key+1),
	    'post_status' => 'publish',
	    'post_parent' => $parent_product_id,//post is a child post of product post
	    'post_type' => 'product_variation',//set post type to product_variation
	    'guid'=>home_url() . '/?product_variation=product-' . $parent_product_id . '-variation-' . ($key+1)
	    );

    	wp_insert_post( $var_post );
		update_post_meta( $variable_id, '_sale_price', $product->vl_venda_vista );
	    update_post_meta( $variable_id, '_regular_price', $product->vl_venda_prazo);
	    update_post_meta( $variable_id, '_manage_stock', "yes" );
	    update_post_meta( $variable_id, '_stock', $variavel->qnt_estoque_disponivel );
	    update_post_meta( $variable_id, '_sku', $variavel->cod_barra );
	    update_post_meta( $variable_id, '_weight', $product->peso_bruto );
	    update_post_meta( $variable_id, '_length', "" ); //TODO
	    update_post_meta( $variable_id, '_width', "" );	//TODO
	    update_post_meta( $variable_id, '_height', "" );	//TODO
    	update_post_meta( $variable_id, '_visibility', 'visible' );	
		add_to_debug('--------Variaveis de Produto - Parâmetros da Nova Variável - ' . $variavel->cor_1 .' e ' . $variavel->tamanho . ' ------');	
		$res = update_post_meta( $variable_id, 'attribute_'. wc_attribute_taxonomy_name( 'color' ), slugify($variavel->cor_1));
		$res = update_post_meta( $variable_id, 'attribute_'. wc_attribute_taxonomy_name( 'size' ), slugify($variavel->tamanho));


		WC_Product_Variable::sync( $parent_product_id );

	    $variable_id++;
	}
}

function vizad_mytrius_get_woocommerce_sanitize_products($mytrius_product_list) {

	add_to_debug('---------Iniciando TRASH SANITIZATION----------');	
	add_to_debug('----------------Lista de SKUs-------------------');
	add_to_debug(json_encode($mytrius_product_list).'');
	add_to_debug('----------------Fim Lista de SKUs-------------------');
	$loop = new WP_Query( array(
     'post_status' => 'publish', 
     'post_type' => array('product', 'product_variation'), 
    'posts_per_page' => -1 ) );
 	$current_products = array();

 	if($loop->have_posts())
 	{
		foreach((array) $loop->get_posts() as $post) {
			$current_products[$post->ID] = false;
		}
	}

	add_to_debug('----------------Lista de IDs-------------------');
	add_to_debug(json_encode($current_products).'');
	add_to_debug('----------------Fim Lista de IDs-------------------');

	while ( $loop->have_posts() ) : $loop->the_post();
		$theid = get_the_ID();
		$product = new WC_Product($theid);
		$sku = get_post_meta($theid, '_sku', true );
		// its a variable product
		if( get_post_type() == 'product_variation' ){
	    // ****** Some error checking for product database *******
	            // check if variation sku is set
	            if ($sku == '') {
	            		$false_post = array();
	                    $false_post['ID'] = $theid;
	                    $false_post['post_status'] = 'auto-draft';
	                    wp_update_post( $false_post );
	                    if (function_exists(add_to_debug))
	                    {
	                    	add_to_debug('false post_type set to auto-draft. id='.$theid);                    	
	                    } 
	                
	        	}else{	        		
		        	foreach ($mytrius_product_list['product_variation'] as $key => $variation_sku) {
		        		if($sku == $variation_sku)
		        		{
		        			$current_products[$theid] = true;
		        			break;
		        		}
		        	}
	        	}
	    }else{
		    foreach ($mytrius_product_list['product'] as $key => $product_sku) {
			    if($sku == $product_sku)
			    {
		        	$current_products[$theid] = true;
			    	break;
			    }
		    }
	    }
	 	// ****************** end error checking ***************** 

    endwhile; 

	add_to_debug('----------------Lista de IDs - After Sanitize-----------');
	add_to_debug(json_encode($current_products).'');
	add_to_debug('----------------Fim-Lista de IDs - After Sanitize-------');
    //Remove all the others beside the $current_products[]

		foreach ($current_products as $key => $prod) {
			if($prod == false)
			{
				add_to_debug('----------------Produto movido para Lixeira: '.$key.'----------');
				wp_trash_post( $key  );
			}
		}
	wp_reset_query();

	add_to_debug('-----------Fim TRASH SANITIZATION----------');	
}

function add_to_debug ($msg) {
	if (is_array($msg) || is_object($msg)) $msg = serialize($msg);
	$upload_dir = wp_upload_dir();
	$filepath = $upload_dir['basedir']. '/mytrius_imports_debug_'.date("Y-m-d",time()).'.txt';
	if (! file_exists($filepath)) file_put_contents($filepath,  current_time('Y/m/d H:i:s') . " - INICIO DO ARQUIVO -----");
    // tag debug message to front of file.
    $filein = file_get_contents($filepath);
    $fileout = $filein. "". current_time('Y/m/d H:i:s').' - '.$msg."\n";
    // trim the debug file if its getting too big
    if (strlen($fileout) > 200000) {
        $fileout = substr($fileout, 0, 80000);
        $fileout .= current_time('Y/m/d H:i:s') . " ----- ARQUIVO CORTADO ---- \n";
    }
    file_put_contents($filepath,$fileout);
}


function vizad_mytrius_current_debug_logs()
{
	$log_files = array();
	$upload_dir = wp_upload_dir();
	$upload_url = $upload_dir['baseurl'];
	$filepath = $upload_dir['basedir'];
   	if ($handle = opendir($filepath))
   	{
       while (false !== ($file = readdir($handle)))
       {
           if ($file != "." && $file != ".." && strpos($file, 'mytrius_imports_debug_') !== false)
           {
               	$fName  = $file;
               	$file   = $path.'/'.$file;
			   	$filepath = $upload_dir['basedir'];
			  	if ($now - filemtime($filepath.'/'.$file) >= 60 * 60 * 24 * 15) 
		      	{
		        	unlink($filepath.'/'.$file);
		      	}else{		      		
               		$log_files[] = array('name'=>$fName, 'creation_dt' => date ('d-m-Y H:i:s', filemtime($filepath.'/'.$file)), 'size'=> filesize($filepath.'/'.$file)." bytes",'url'=> $upload_url.'/'.$file);
		      	}
           }
       }

       closedir($handle);
   }
   return $log_files;    
}

/**
	 //update_post_meta( $post_id, '_stock_status', 'instock');
     //update_post_meta( $post_id, 'total_sales', '0');
     //update_post_meta( $post_id, '_downloadable', 'yes');
     //update_post_meta( $post_id, '_virtual', 'yes');
     //update_post_meta( $post_id, '_regular_price', "1" );
     //update_post_meta( $post_id, '_sale_price', "1" );
     //update_post_meta( $post_id, '_purchase_note', "" );
     //update_post_meta( $post_id, '_featured', "no" );
     //update_post_meta( $post_id, '_product_attributes', array());
     //update_post_meta( $post_id, '_sale_price_dates_from', "" );
     //update_post_meta( $post_id, '_sale_price_dates_to', "" );
     //update_post_meta( $post_id, '_price', "1" );
     //update_post_meta( $post_id, '_sold_individually', "" );
     //update_post_meta( $post_id, '_backorders', "no" );
	// file paths will be stored in an array keyed off md5(file path)
    //$downdloadArray =array('name'=>"Test", 'file' => $uploadDIR['baseurl']."/video/".$video);
    //$file_path =md5($uploadDIR['baseurl']."/video/".$video);
    //$_file_paths[  $file_path  ] = $downdloadArray;
    // grant permission to any newly added files on any existing orders for this product
    //do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $downdloadArray );
    //update_post_meta( $post_id, '_downloadable_files ', $_file_paths);
    //update_post_meta( $post_id, '_download_limit', '');
    //update_post_meta( $post_id, '_download_expiry', '');
    //update_post_meta( $post_id, '_download_type', '');
    //update_post_meta( $post_id, '_product_image_gallery', '');

**/


?>


<style>
.vizad-with-icon
{
	margin: 0 auto;
	display: block;
	text-align: center !important;
}
</style>

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php esc_attr_e( 'Mytrius Integration', 'vizad-mytrius' ); ?></h1>
	<?php if($vizad_plugin_message != null): ?>
		<?php if($vizad_plugin_message['success'] == true): ?>
			<div class="notice notice-success"><p><?php echo $vizad_plugin_message['msg']; ?></p></div>
		<?php else: ?>
			<div class="notice notice-error"><p><?php echo $vizad_plugin_message['msg']; ?></p></div>
		<?php endif; ?>

	<?php endif; ?>
	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">

						<div class="inside">
							
							<p><strong><?php esc_attr_e( 'Credenciais do Mytrius', 'vizad-mytrius' ); ?></strong></p>
								<form name="vizad_mytrius_form" method="post" action="">
									<input type="hidden" name="vizad_form_submitted" value="Y"/>
									<table class="form-table">
										<tr valign="top">								

											<td scope="row">
												<label for="vizad_shop_token"><?php esc_attr_e( 'Token', 'vizad-mytrius' ); ?></label>
											</td>
											<td>
												<input type="text" name="vizad_shop_token" placeholder="<?php esc_attr_e( 'Insira seu Token do mytrius', 'vizad-mytrius' ); ?>" id="vizad_shop_token" value=<?php echo "'".$vizad_shop_token."'" ?> class="regular-text" />
											</td>
										</tr>
										<tr valign="top">							

											<td scope="row">
												<label for="vizad_shop_min_stock"><?php esc_attr_e( 'Estoque mínimo para exibição', 'vizad-mytrius' ); ?></label>
											</td>
											<td>
												<input type="number" name="vizad_shop_min_stock" min="0" id="vizad_shop_min_stock" value=<?php echo "'".$vizad_shop_min_stock."'" ?> class="regular-number" />
											</td>
										</tr>
										<tr valign="top">								

											<td scope="row">
												<fieldset>
													<legend class="screen-reader-text"><span>Habilitar atualizações via rotina automática</span></legend>
													<label for="vizad_shop_cron">
														<?php if($vizad_shop_cron == true): ?>
															<input name="vizad_shop_cron" type="checkbox" id="vizad_shop_cron" checked />
														<?php else: ?>
															<input name="vizad_shop_cron" type="checkbox" id="vizad_shop_cron" />													
														<?php endif; ?>	
														<span><?php esc_attr_e( 'Habilitar atualizações via rotina automática', 'vizad-mytrius' ); ?></span>
													</label>
												</fieldset>
											</td>
											<td>
											</td>
										</tr>


										<tr valign="top">
											<td>

												<input class="button-primary" type="submit" name="vizad_submit_import" value="<?php esc_attr_e( 'Atualizar Configurações e Importar Manualmente' ); ?>" />
											</td>
											<td>											
												<input class="button-primary" type="submit" name="vizad_submit_update" value="<?php esc_attr_e( 'Somente Atualizar Configurações' ); ?>" />
											</td>
										</tr>
									</table>
								</form>

						</div>
						<!-- .inside -->

					</div>
					<p><strong>Logs Diários de Importação</strong>(Últimos 15 dias)</p>
					<table class="widefat">
						<tr>
							<th class="row-title"><?php esc_attr_e( 'Arquivo', 'vizad-mytrius' ); ?></th>
							<th><?php esc_attr_e( 'Tamanho', 'vizad-mytrius' ); ?></th>
							<th><?php esc_attr_e( 'Data de Criação', 'vizad-mytrius' ); ?></th>
							<th class="vizad-with-icon"><?php esc_attr_e( 'Download', 'vizad-mytrius' ); ?></th>
						</tr>
						<?php foreach ($log_files as $key => $vizad_file): ?>
							<?php if($key % 2 == 0): ?>
							<tr class="alternate">
							<?php else: ?>
							<tr>
							<?php endif; ?>		
								<td class="row-title"><label for="tablecell"><?php echo $vizad_file['name']; ?></label></td>
								<td><?php echo $vizad_file['size']; ?></td>
								<td><?php echo $vizad_file['creation_dt']; ?></td>
								<td class="vizad-with-icon"><a href="<?php echo $vizad_file['url']; ?>"><div class="fa fa-file-text-o"></div></a></td>
							</tr>
						<?php endforeach; ?>

						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</table>



					<?php if($vizad_display_json == true): ?>
					<div class="postbox">

						<h2><span><?php esc_attr_e( 'JSON Feed', 'vizad-mytrius' ); ?></span></h2>

						<div class="inside">
							
							<pre><code>
								<?php var_dump($vizad_shop_products); ?>
							</code></pre>

						</div>
						<!-- .inside -->

					</div>
					<?php endif; ?>

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

				<?php if(isset($vizad_shop_token) && $vizad_shop_token != ''): ?>	
					<div class="postbox">
						<a href="http://www.vizad.com.br">
							<p><img style="width: 50%;display: block;margin: 0 auto;" class="vizad-logo" src="<?php echo plugins_url() . '/vizad-mytrios-integration/images/bold-black-vizad.png' ?>" alt="<?php esc_attr_e( 'Agência Vizad Logo', 'vizad-mytrius' ); ?>"></p>
						</a><br>
						<h3><span><?php esc_attr_e(
									'Informações da Integração', 'vizad-mytrius'
								); ?></span></h3>

						<div class="inside">


						<ul class="">							

								<li><?php esc_attr_e( 'Última Atualização', 'vizad-mytrius' ); ?>: <br><strong><?php echo date('m/d/Y H:i:s', $vizad_last_updated); ?></strong></li>
								<li><?php esc_attr_e( 'Produtos Importados', 'vizad-mytrius' ); ?>: 
									<?php if(count($vizad_shop_products)): ?>
										<strong><?php echo count($vizad_shop_products); ?></strong>
									<?php else: ?>
										<strong>Não disponível</strong>
									<?php endif;?>	
								</li>

						</ul>
						</div>

					</div>
					<!-- .postbox -->
				<?php endif; ?>	

				</div>
				<!-- .meta-box-sortables -->

			</div>


			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->

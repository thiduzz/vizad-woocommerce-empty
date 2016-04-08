<style>
.vizad-with-icon
{
	margin: 0 auto;
	display: block;
	text-align: center !important;
}

.postbox .inside {
	display: table;
}
</style>

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php esc_attr_e( 'WooCommerce Cleaner', 'vizad-cleaner' ); ?></h1>
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
							
								<form name="vizad_mytrius_form" method="post" action="">
									<input type="hidden" name="vizad_form_submitted" value="Y"/>
									<table class="form-table">

										<tr valign="top">
											<td>
												<?php esc_attr_e( 'This is a really simple plugin (I spent around 10 minutes developing it - just as a tool for other project), that allows the user to eliminate all WooCommerce data (Terms, Products, Variations and Categories). I decided not to eliminate attributes because it was not my intention. But you may use this plugin as reference for your plugin.', 'vizad-cleaner' ); ?>
											</td>
										</tr>
										<tr valign="top">
											<td>

												<input class="button-primary" type="submit" name="vizad_submit_import" value="<?php esc_attr_e( 'Delete All Products, Variations, Terms and Categories' ); ?>" />
											</td>
										</tr>
									</table>
								</form>

						</div>
						<!-- .inside -->

					</div>


				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">
						<a href="http://www.vizad.com.br">
							<p><img style="width: 50%;display: block;margin: 0 auto;" class="vizad-logo" src="<?php echo plugins_url() . '/vizad-woocommerce-empty/images/bold-black-vizad.png' ?>" alt="<?php esc_attr_e( 'Agência Vizad Logo', 'vizad-cleaner' ); ?>"></p>

						</a><br>
						<br>
						<p style="text-align: center;"><a href="http://www.vizad.com.br">Website</a></p>
						<p style="text-align: center;"><a href="https://www.facebook.com/thizaom">Facebook</a></p>

					</div>
					<!-- .postbox -->

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

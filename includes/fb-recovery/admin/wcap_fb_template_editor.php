<?php

?>
<div class="modal wcap-preview-modal" id="wcap-fb-modal">
	
	<!-- Modal content -->
	<div class="wcap-preview-contents">

		<div class="wcap-preview-header">

			<div class="wcap-preview-header-title modal-title">
				<h1 class="product_title entry-title">
					<?php _e( 'Abandoned Cart Template', 'woocommerce-ac' ); ?>
				</h1>
			</div>
			<div class="wcap-preview-header-close" data-dismiss="modal">
			</div>

			<div style="clear: both;"></div>
		</div>


		<div id="modal-body" class="modal-body row">

			<div class="col-lg-6 col-md-6">

				<div class="form-group">
					<label for="wcap_send_num"> <?php _e( 'Send', 'woocommerce-ac' );?> </label>
					<select class="form-control-sm" name="wcap_send_num" id="wcap_send_num" v-model="wcap_send_num">
						<option v-for="n in 60" :value="n+1">{{n+1}}</option>
					</select>
					
					<select class="form-control-sm" name="wcap_send_freq" id="wcap_send_freq" v-model="wcap_send_freq">
						<option value="minutes"><?php _e( 'Minute(s)', 'woocommerce-ac' ); ?></option>
						<option value="hours"><?php _e( 'Hour(s)', 'woocommerce-ac' ); ?></option>
						<option value="days"><?php _e( 'Day(s)', 'woocommerce-ac' ); ?></option>
					</select>
					<label><?php _e( 'After cart is abandoned', 'woocommerce-ac' ); ?></label>
				</div>

				<div class="form-group">
					<label for="wcap_subject"> <?php _e( 'Subject', 'woocommerce-ac' ); ?> </label>
					<input type="text" name="wcap_subject" id="wcap_subject" class="form-control" v-model="wcap_subject">
					<small class="form-text text-muted">
						<?php _e( 'Use this as an identifier and an introduction message', 'woocommerce-ac' ); ?>
					</small>
				</div>

				<!-- <div class="form-group">
					<label for="wcap_header"> <?php //_e( 'Header Text', 'woocommerce-ac' ); ?> </label>
					<input type="text" name="wcap_header" id="wcap_header" class="form-control" v-model="wcap_header">
				</div>

				<div class="form-group">
					<label for="wcap_subheader"> <?php //_e( 'Sub Header Text', 'woocommerce-ac' ); ?> </label>
					<input type="text" name="wcap_subheader" id="wcap_subheader" class="form-control" v-model="wcap_subheader">
				</div>

				<div class="form-group">
					<label for="wcap_header_image"> <?php //_e( 'Header Image' ) ?> </label>
					<div class="wcap_header_image">
						<img v-bind:src="wcap_image_url" width="100%" height="100%" id="wcap_header_image" name="wcap_header_image">
						<button class="wcap_image_selector" v-on:click="wcap_select_image"><?php //_e( 'Edit Image', 'woocommerce-ac' ); ?></button>
					</div> -->
					<!-- <input type="file" name="wcap_header_image" id="wcap_header_image" class="form-control"> -->
				<!-- </div> -->

				<div class="form-group">
					<label for="wcap_checkout"> <?php _e( 'Checkout Label', 'woocommerce-ac' ); ?> </label>
					<input type="text" name="wcap_checkout" id="wcap_checkout" class="form-control" v-model="wcap_checkout">
				</div>

				<div class="form-group">
					<label for="wcap_unsubscribe_text"> <?php _e( 'Unsubscribe Text', 'woocommerce-ac' ); ?> </label>
					<input type="text" name="wcap_unsubscribe_text" id="wcap_unsubscribe_text" class="form-control" v-model="wcap_unsubscribe_text">
				</div>

			</div>

			<div class="col-lg-6 col-md-6">

				<div class="rounded" v-if="wcap_subject">
					<div class="wcap_preview_subject">
						{{wcap_subject}}
					</div>
				</div>

				<div class="rounded wcap_preview">
					<div class="wcap_fb_header center">
						<!-- <img v-bind:src="wcap_image_url" width="100%" height="100%" name="wcap_header_image_preview" id="wcap_header_image_preview" > -->
						<img width="120" height="120" src="https://staging.tychesoftwares.com/woo_dhruvin/wp-content/uploads/2018/07/cblue-324x343.png">
					</div>

					<div class="wcap_fb_list row">
						<div class="col-lg-12 col-md-12 col-sm-12 wcap_product_details">
							<h2 class="wcap_product_title"><?php _e( 'Cool Blue T-Shirt', 'woocommerce-ac' ); ?></h2>
							<span class="wcap_product_subdetails"><?php _e( '1 x $100', 'woocommerce-ac' ); ?></span>
							<br>
							<a class="wcap_product_subdetails" v-bind:href="wcap_checkout" ><?php echo get_site_url();?></a> 
						</div>
					</div>
					<div class="wcap_button">
						<button class="wcap_unsubscribe_button">{{wcap_checkout}}</button>
					</div>
					<div class="wcap_button wcap_button_radius">
						<button class="wcap_unsubscribe_button">{{wcap_unsubscribe_text}}</button>
					</div>
				</div>

			</div>

		</div>

		<div class="modal-footer">
			
			<input 
				type="button" 
				name="wcap_save_template" 
				id="wcap_save_template"
				v-on:click="wcap_save_template"
				value="<?php _e( "Save", 'woocommerce-ac' ); ?>" 
				class="button-primary button" 
			/>

			<input 
				type="button" 
				name="cancel_modal" 
				id="cancel_modal"
				v-on:click="wcap_destroy_template"
				value="<?php _e( "Cancel", 'woocommerce-ac' ); ?>" 
				class="button-secondary button"
				data-dismiss="modal"
			/>
		</div>

	</div>
</div>
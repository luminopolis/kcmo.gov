<div class="wrap">
	<h2><?php _e( 'Multisite Shared Options', 'mu-shared-options' ); ?></h2>
	
	<?php if( count($success) ): ?>
		<div id="setting-error-settings_updated" class="updated settings-error">
			<p><strong><?php echo $success[0]; ?></strong></p>
		</div>
	<?php endif; ?>
	
	<form method="post" class="shared-settings">
		<?php wp_nonce_field( 'luminopolis-mu-shared-options' ); ?>
		
		<p><?php _e( 'option name, comma or new line separated' ); ?></p>
		<textarea name="mu-shared-options"><?php echo esc_html( $shared_options ); ?></textarea>
		
		<button type="submit" class="button button-primary button-large">Submit</button>
	</form>
</div>
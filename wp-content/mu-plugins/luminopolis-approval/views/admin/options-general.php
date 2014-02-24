<div class="wrap">
	<h2><?php _e( 'Approval Workflow Settings', 'luminopolis-approval' ); ?></h2>
	
	<?php echo $message; ?>
	
	<form method="post">
		<?php wp_nonce_field( 'luminopolis-approval-settings' ); ?>
		
		<fieldset class="approval">
			<legend><?php _e( 'Excluded Post Types', 'luminopolis-approval' ); ?></legend>
			
			<?php foreach( $options['post_types'] as $post_type => $selected ): ?>
				<label><input type="checkbox" name="post_types[<?php echo $post_type; ?>]" <?php echo $selected ? 'checked="checked"' : ''; ?> value="1"/><?php echo $post_type; ?></label>
			<?php endforeach; ?>
		</fieldset>
		
		<?php /*
		<fieldset class="approval">
			<legend><?php _e( 'Excluded User Capabilities', 'luminopolis-approval' ); ?></legend>
			
			<?php foreach( $options['caps'] as $role => $selected ): ?>
				<label><input type="checkbox" name="caps[<?php echo $role; ?>]" value="1" <?php echo $selected ? 'checked="checked"' : ''; ?>/><?php echo $role; ?></label>
			<?php endforeach; ?>
		</fieldset>
		*/ ?>
		
		<fieldset class="approval">
			<legend>Misc Options</legend>
			
			<?php /* <label><input type="checkbox" name="misc[enabled]" value="1" <?php echo $options['misc']['enabled'] ? 'checked="checked"' : ''; ?>/>Enabled</label> */ ?>
			<label><input type="checkbox" name="misc[log_mail]" value="1" <?php echo $options['misc']['log_mail'] ? 'checked="checked"' : ''; ?>/><?php _e( 'Log All Outgoing Mail', 'luminopolis-approval' ); ?></label>
			
			<label><?php _e( 'Send From Address', 'luminopolis-approval' ); ?><input type="email" name="misc[send_from]" value="<?php echo esc_html( $options['misc']['send_from'] ); ?>"/></label>
			
			<label><?php _e( 'Rejection Letter From', 'luminopolis-approval' ); ?>:<input type="text" name="misc[rejection_from]" value="<?php echo esc_html( $options['misc']['rejection_from'] ); ?>"/></label>
			
			<label><?php _e( 'Rejection Letter Email', 'luminopolis-approval' ); ?>:<input type="email" name="misc[rejection_email]" value="<?php echo esc_html( $options['misc']['rejection_email'] ); ?>"/></label>
		</fieldset>
		
		<button type="submit" class="button button-primary">Submit</button>
	</form>
</div>
<div class="wrap luminopolis_mailchimp_settings">
	<h2>Mailchimp Settings</h2>
	
	<form action="" method="post">
		<input name="_wp_nonce" type="hidden" value="<?php echo wp_create_nonce( 'mailchimp-settings' ); ?>"/>
		
		<fieldset>
			<label>API key <a target="_blank" href="https://us3.admin.mailchimp.com/account/api/">Find yours</a></label>
			<input name="mailchimp_api_key" value="<?php echo $mailchimp_api_key; ?>"/>
		</fieldset>
		
		<fieldset>
			<label>From Email</label>
			<input name="mailchimp_from_email" value="<?php echo $mailchimp_from_email; ?>"/>
		</fieldset>
		
		<fieldset>
			<label>From Name</label>
			<input name="mailchimp_from_name" value="<?php echo $mailchimp_from_name; ?>"/>
		</fieldset>
		
		<?php /*
		<fieldset>	
			<label></label>
			<input name="" value=""/>
		</fieldset>
		*/ ?>
		<input class="button button-primary" type="submit" value="Update"/>
	</form>
</div>
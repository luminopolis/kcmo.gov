<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>Google Calendar Settings</h2>
	<form method="post">
 
		<?php wp_nonce_field( 'luminopolis-gcal-settings' ); ?>
		 
		<ul>
			<li>
				<label for="url">Google Calendar Public URL</label>
				<input type="text" name="url" id="" value="<?php echo esc_html( $url ); ?>" placeholder="<?php echo GOOGLE_CALENDAR_DEFAULT_URL; ?>" size="100"/>
			</li>
			
			<li>
				<label for="max-results">Number of Events to show</label>
				<input type="text" name="max-results" id="" value="<?php echo esc_html( $max_results ); ?>" placeholder="<?php echo GOOGLE_CALENDAR_DEFAULT_RESULTS; ?>"/>
			</li>
		</ul>
		
		<input type="submit" name="" id="" value="Update" class="button-primary"/>
	</form>
</div>
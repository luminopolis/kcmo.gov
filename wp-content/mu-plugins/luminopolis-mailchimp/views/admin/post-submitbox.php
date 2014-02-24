<div class="misc-pub-section luminopolis_mailchimp">
	<h4>Mailchimp / Twitter</h4>
	
	<?php if( isset($errors['malichimp']) && count($errors['malichimp']) ): ?>
	<p class="error">Mailchimp returned the following errors:</p>
	<ul class="error">
		<?php foreach( $errors['mailchimp'] as $error ): ?>
			<li><?php echo $error; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	
	<?php if( isset($errors['twitter']) && count($errors['twitter']) ): ?>
	<p class="error">Twitter returned the following errors:</p>
	<ul class="error">
		<?php foreach( $errors['twitter'] as $error ): ?>
			<li><?php echo $error; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	
	<fieldset>
		<label>Status:</label><div class="text"><?php echo $status_msg; ?></div>
	</fieldset>
	
	<fieldset>
		<label>List:</label>
		<select name="mc_list_id" <?php if( $status == 'sent' ) echo 'disabled="disabled"'; ?>>
			<option></option>
			<?php foreach( $lists as $list ): ?>
			<option <?php if( $selected_list == $list->id ) echo 'selected="selected"'; ?> value="<?php echo $list->id; ?>"><?php echo $list->name; ?></option>
			<?php endforeach; ?>
		</select>
	</fieldset>
	
	<fieldset class="meta">
		<label>Campaign ID:</label>
		<input type="text" disabled="disabled" value="<?php echo $campaign_id; ?>"/>
	</fieldset>
	
	<?php if( $twitter_integration && in_array($status, array('sent', 'save')) ): ?>
	<fieldset class="twitter">
		<?php if( $twitter_name ): ?>
			<label>Tweet it<br/><?php echo $twitter_name; ?></label>
			<input type="checkbox" name="mailchimp_tweet_do" value="1" <?php echo $tweeted; ?>/>
			<?php echo $tweet_link; ?>
			<textarea id="mailchimp_tweet" name="mc_tweet_text"><?php echo esc_html( $tweet ); ?></textarea>
			<span id="mailchimp_tweet_character">140 characters left</span>
		<?php else: ?>
			<h2>Twitter not configured</h2>
		<?php endif; ?>
	</fieldset>
	
	<script type="text/javascript">
		// short_url_length is the length that new t.co links will be.  replace links in tweet with placeholder
		// to get accurate count of characters available.
		jQuery( document ).ready( function(){
			"use strict";
			var $mailchimp_tweet = jQuery( 'textarea#mailchimp_tweet' );
			var $mailchimp_tweet_character = jQuery( '#mailchimp_tweet_character' );
			var replace = 'http://t.co/'+( new Array(<?php echo $twitter_integration->short_url_length; ?> - 11).join('x') );
			var post_length = 140;
			var url = /(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/;
			
			function luminopolis_update_twitter(){
				post_length = 140 - $mailchimp_tweet.val().replace( url, replace ).length;
				$mailchimp_tweet_character.html( post_length+' characters left' );
				
				if( post_length < 0 )
					$mailchimp_tweet_character.addClass( 'error' );
				else
					$mailchimp_tweet_character.removeClass( 'error' );
			};
			
			$mailchimp_tweet.on( 'keyup', luminopolis_update_twitter );
			luminopolis_update_twitter();
		} );
	</script>
	<?php elseif( !$twitter_integration ): ?>
	<fieldset>
		<label>Tweeting requires Latest Tweets plugin to be enabled.</label>
	</fieldset>
	<?php endif; ?>
</div>
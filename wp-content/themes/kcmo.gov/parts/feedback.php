<div class="wrong custom_bk"><a href="<?php echo add_query_arg( array('toggle' => 'feedback') ); ?>#feedback"> IS THERE ANYTHING WRONG WITH THIS PAGE?</a></div>
<div id="feedback" <?php if( get('toggle') == 'feedback' ) echo 'style="display:block;" '; ?>>
	<form action="<?php echo add_query_arg( array('toggle' => 'feedback') ); ?>#feedback" method="post" <?php if( isset($feedback->success) ) echo 'class="has-success" '; ?>>
		<?php if( isset($feedback->success) ): ?>
			<p class="help-block"><?php echo $feedback->success; ?></p>
		<?php else: ?>
			<h2>Help improve KCMO.gov by telling us</h2>
			<br style="clear:both"/>
			
			<fieldset <?php if( isset($feedback->error) ) echo 'class="has-error"';?>>
				<label>What you were doing</label>
				<textarea name="activity"><?php echo esc_html( $feedback->user_data['activity'] ); ?></textarea>
				<p class="help-block"><?php if( isset($feedback->error) ) echo $feedback->error; ?></p>
			</fieldset>
			
			<fieldset>
				<label>What went wrong</label>
				<textarea name="issue"><?php echo esc_html( $feedback->user_data['issue'] ); ?></textarea>
			</fieldset>
			
			<div>
				<label style="display:block">&nbsp;</label>
				<button type="submit" class="custom_bk">Send Feedback</button>
				<button type="reset">Cancel</button>
			</div>
			
			<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'feedback-form' ); ?>"/>
			<input type="hidden" name="" value=""/>
			
			<br style="clear:both"/>
		<?php endif; ?>
	</form>
</div>
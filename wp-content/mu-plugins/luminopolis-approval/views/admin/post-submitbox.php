<div id="OB_REMOVEpublishing-action">
	<fieldset id="luminopolis_approval">
		<?php if( $can_approve_initial ): ?>
			<button class="button primary-secondary" type="submit" name="luminopolis_publish[pending]" value="<?php echo wp_create_nonce( 'pending' ); ?>"><?php _e( 'Approve( pending )', 'luminopolis-approval' ); ?></button>
		<?php endif; ?>
		
		<?php if( $can_approve_final ): ?>
			<button class="button primary-secondary" type="submit" name="luminopolis_publish[final]" value="<?php echo wp_create_nonce( 'final' ); ?>"><?php _e( 'Approve( final )', 'luminopolis-approval' ); ?></button>
		<?php endif; ?>
		
		<?php if( $can_approve_final || $can_approve_initial ): ?>
		<button class="button primary-secondary" type="submit" name="luminopolis_publish[reject]" value="<?php echo wp_create_nonce( 'reject' ); ?>"><?php _e( 'Reject', 'luminopolis-approval' ); ?></button>
		<?php endif; ?>
	</fieldset>
</div>

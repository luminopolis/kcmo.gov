<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>Problem Report</h2>
	
	<form method="post">
 		<h3>Reported On: <?php echo $problem->report_date; ?></h3>
 		
 		<input type="hidden" name="key_id" value="<?php echo $problem->key_id; ?>"/>
		<input type="hidden" name="_wpnonce" value="<?php echo $wpnonce; ?>"/>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">URL</th>
					<td><a href="<?php echo $problem->URL; ?>" target="_blank"><?php echo $problem->URL; ?></a></td>
				</tr>
				
				<tr>
					<th scope="row">Activity:</th>
					<td><p><?php echo $problem->activity; ?></p></td>
				</tr>
				
				<tr>	
					<th scope="row">Issue:</th>
					<td><p><?php echo $problem->issue; ?></p></td>
				</tr>
				
			</tbody>
		</table>
		
		<h3>Resolution Date: <?php echo $problem->resolution_date; ?></h3>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Resolution</th>
					<td><textarea name="resolution"><?php echo esc_html( $problem->resolution ); ?></textarea>
				</tr>
				
				<tr>
					<th scope="row">Other Notes</th>
					<td><textarea name="other_notes"><?php echo esc_html( $problem->other_notes ); ?></textarea>
				</tr>
			</tbody>
		</table>
		
		<p class="submit">
			<input type="submit" name="" id="" value="Update" class="button-primary"/>
		</p>
	</form>
</div>
<?php
/*
*	Admin settings screen
*	Version: 0.85
*/
?>
<div class="wrap">
	<h2>aitch ref!</h2>
	
	<?php echo $messages; ?>
	
	<p>possible urls seperated by space or new line (include http/s, no trailing slash)</p>

	<form method="post">
		<textarea name="urls" cols="60" style="background-image:url(<?php echo $path; ?>/ref.jpg);background-repeat:no-repeat;background-position:bottom right; height:377px"><?php echo $urls; ?></textarea>
		
		<a href="http://www.flickr.com/photos/avinashkunnath/2402114514/in/photostream/" style="font-size:.6em">photo by Avinash Kunnath</a>
		
		<div>
			<input type="submit" value="Update"/>
		</div>
	</form>
</div>
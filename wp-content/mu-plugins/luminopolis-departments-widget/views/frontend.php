<h2 class="custom_heading"><?php echo $title; ?></h2>
<p><?php echo $contact; ?></p>
<ul class="social">
	<?php foreach( $social as $k=>$v ): ?>
		<?php echo $v['html']; ?>
	<?php endforeach; ?>
</ul>
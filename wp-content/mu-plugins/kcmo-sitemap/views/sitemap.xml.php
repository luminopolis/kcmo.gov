<?php 
header( 'HTTP/1.0 200 OK' );
header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="UTF-8"?>'; 
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" 
	xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
	<?php foreach( $sitemap_items as $item ): ?>
	<url> 
		<loc><?php echo esc_attr( $item->permalink ); ?></loc> 
		<lastmod><?php echo esc_attr( $item->lastmod ); ?></lastmod>
		<?php /*
		<image:image>
		<image:loc>http://example.com/image.jpg</image:loc> 
		</image:image>
		<video:video>     
		<video:content_loc>
		http://www.example.com/video123.flv
		</video:content_loc>
		<video:player_loc allow_embed="yes" autoplay="ap=1">
		http://www.example.com/videoplayer.swf?video=123
		</video:player_loc>
		<video:thumbnail_loc>
		http://www.example.com/thumbs/123.jpg
		</video:thumbnail_loc>
		<video:title>Grilling steaks for summer</video:title>  
		<video:description>
		Get perfectly done steaks every time
		</video:description>
		</video:video>
		*/ ?> 
		
	</url>
	<?php endforeach; ?>
</urlset>
<h4><a href="<?php echo get_edit_post_link( $reject->ID ); ?>"><?php echo $reject->post_title; ?></a></h4>
<p></p>

<input name="reject-id" type="hidden" value="<?php echo $reject->ID; ?>"/>

<input id="save" class="button button-primary button-large" type="submit" value="Save" name="publish"><br/><br/>
<input id="save_send" class="button button-primary button-large" type="submit" value="Save and send to <?php echo $author_email; ?>" name="publish_and_send">
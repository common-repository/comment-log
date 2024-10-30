<style>
#comment-log {
	padding: 1em;
	margin-left: auto;
	margin-right: auto;
	width: 45em;
}
#comment-body {
	padding: .5em;
	border: 1px solid gray;
	background-color: lightblue;
	width: 45em;
}
</style>
<div id="comment-log">
<h2>Confirm Comment</h2>
<h4>From: <?php echo $from; ?></h4>
<div id="comment-body">
<?php echo nl2br($comment);?>
</div>
<br />
<div id="comment-confirm-form">
<form name="input" action="" method="post">
<input type="hidden" name="comment" value="<?php echo $comment;?>">
<input type="hidden" name="from" value="<?php echo $from;?>">
<input type="hidden" name="clognonce" value="<?php echo $nonce;?>">
<input type="submit" name="confirm_submit" value="Add Comment">
</form>
</div>

</div>

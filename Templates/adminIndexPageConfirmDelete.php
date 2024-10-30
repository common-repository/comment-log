<style>
#comment-log {
	padding: 1em;
	margin-left: auto;
	margin-right: auto;
	width: 45em;
}
</style>
<div id="comment-log">
<h2>Confirm Delete</h2>
<div id="comment-confirm-form">
<?php 
unset($url->querystring['delete_id']);
?>
<form name="input" action="<?php echo $url->getUrl();?>" method="post">
<input type="hidden" name="clogdeletenonce" value="<?php echo $_SESSION['clogdeletenonce'];?>">
<input type="hidden" name="page_id" value="<?php echo $_GET['delete_id'];?>">
<input type="submit" name="confirm_delete_submit" value="Confirm">
<input type="submit" name="cancel_delete_submit" value="Cancel">
</form>
</div>
</div>
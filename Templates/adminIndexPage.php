<style>
.comment-head {
	padding: .1em;
	padding-top: 1em;
}
.comment-body {
	padding: .5em;
	border: 1px solid gray;
	background-color: #F4F4F4;
	width: 45em;
}

#comments-wrapper {
	margin-left: auto;
	margin-right: auto;
	width: 45em;
}
</style>


<div id="comments-wrapper">
<h2>Comment Log</h2>
<h3>Options</h3>

<?php if ($update_page_id): ?>
<p> 
Page ID updated.
</p>
<?php endif; ?>

<?php if ($confirm_delete): ?>
<p> 
Comment Deleted.
</p>
<?php endif; ?>

<?php if ($pages): ?>
<form name="input" action="" method="post">
<label>Page:</label>
<select name="page_id">
<?php foreach($pages AS $page): ?>
<option value="<?php echo $page->ID; ?>" <?php echo ($page->ID == $page_id) ? 'selected = "selected"' : ''; ?>><?php echo $page->post_name; ?></option>
<?php endforeach; ?>
</select>
<input type="submit" name="pageset_submit" value="Display comments on this page">
</form>
<?php else : ?>
<p>
No pages found, please create a page to display your comments on.
</p>
<?php endif; ?>

<h3>Comments</h3>
<?php if (is_array($comments['comments']) && count($comments['comments'])): ?>
<table id="comment-table">
	<tr>
		<th>
		<?php echo $comments['pager']->links; ?>
		</th>
	</tr>
	
	<?php foreach ($comments['comments'] as $comment): ?>
	<tr>
		<td class="comment-head">
			<div style="float:left" >
			Posted on: <?php echo date($date_format, $comment->date); ?>
			</div>
			<?php 
				$url->querystring['delete_id'] = $comment->id;
				
				$delete_url = $url->getUrl();
			?>
			<div style="float:right">
				<a href="<?php echo $comment->url; ?>" target="_blank">&gt;&gt; View Context</a>
				<a href="<?php echo $delete_url; ?>">&gt;&gt; Delete</a>
			</div>
		</td>
	</tr>
	<tr>
		<td class="comment-body"><?php echo nl2br($comment->comment); ?></td>
	</tr>
	<?php endforeach; ?>
	
	<tr>
		<th>
		<?php echo $comments['pager']->links; ?>
		</th>
	</tr>
	
</table>

<?php else: ?>

<p>There are no comments to display.</p>

<?php endif; ?>
</div>

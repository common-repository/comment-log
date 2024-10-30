<style>
.comment-head {
	padding: .1em;
	padding-top: 1em;
}
.comment-body {
	padding: .5em;
	border: 1px solid gray;
	background-color: #F4F4F4;
}

#comments-wrapper {
	margin-left: auto;
	margin-right: auto;
}
</style>


<div id="comments-wrapper">

<?php if (is_array($comments['comments']) && count($comments['comments'])): ?>
<table id="comment-table" width="100%">
	<tr>
		<th align="center">
		<?php echo $comments['pager']->links; ?>
		</th>
	</tr>
	
	<?php foreach ($comments['comments'] as $comment): ?>
	<tr>
		<td class="comment-head">
			<div style="float:left" >
			Posted on: <?php echo date($date_format, $comment->date); ?>
			</div>
			<div style="float:right">
				<a href="<?php echo $comment->url; ?>" target="_self">&gt;&gt; View Context</a>
			</div>
		</td>
	</tr>
	<tr>
		<td class="comment-body"><?php echo nl2br($comment->comment); ?></td>
	</tr>
	<?php endforeach; ?>
	
	<tr>
		<th align="center">
		<?php echo $comments['pager']->links; ?>
		</th>
	</tr>
	
</table>

<?php else: ?>

<p>There are no comments to display.</p>

<?php endif; ?>
</div>

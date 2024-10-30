<?php get_header(); ?>
	
	<div id="content" class="narrowcolumn">
	<h2><?php echo $error_name; ?></h2>
	<p>
		<?php echo $error_body; ?>
	</p>
	<a  class="center" href="<?php echo $redirect; ?>">CONTINUE</a>
	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
<footer class="footer">
	<div class="container">
		<p class="text-muted">Place sticky footer content here.</p>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script>var base_url = "<?php echo base_url(); ?>"</script>
	<?php  global $js_path; ?>
	<?php foreach ($js as $k => $v): ?>
		<script src="<?php echo $js_path.$v.".js"; ?>"></script>
	<?php endforeach ?>
</footer>

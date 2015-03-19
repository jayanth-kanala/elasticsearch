<?php  global $js_path; ?>
<footer class="footer">
	<div class="container">
		<p class="text-muted">Place sticky footer content here.</p>
	</div>
	<script src="<?php echo $js_path ?>jquery.min.js"></script>
	<script src="<?php echo $js_path ?>bootstrap.min.js"></script>
	<script>var base_url = "<?php echo base_url(); ?>"</script>
	<?php foreach ($js as $k => $v): ?>
		<script src="<?php echo $js_path.$v.".js"; ?>"></script>
	<?php endforeach ?>
</footer>

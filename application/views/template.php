<?php
global $js_path,$css_path;
$js_path 	= base_url("assets/js")."/";
$css_path 	= base_url("assets/css")."/";
?>
<?php $this->load->view("header"); ?>
<body>
	<div class="container">
		<?php $this->load->view("nav"); ?>
		<?php $this->load->view($content); ?>
		<?php $this->load->view("footer"); ?>
	</div> <!-- /container -->
</body>

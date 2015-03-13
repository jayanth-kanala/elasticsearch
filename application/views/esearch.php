<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
	<h1>Welcome to Elasticsearch!</h1>
	<p>
		This example is a quick exercise to illustrate how the Elasticsearch
		is used for searching users
	</p>
	<div class="form-group">
		<div class="input-group">
			<input type="text" class="form-control" id="exampleInputAmount" placeholder="Search" autocomplete="off">
			<div class="input-group-addon" id="esearch">Go!</div>
		</div>
	</div>
	Showing <span id="eslimit">0</span> Of <span id="escount">0</span>
</div>
<div class="result col-md-12"></div>
<div class="dump"></div>

<!-- Large modal -->
<div class="modal fade bs-example-modal-lg modal-user">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Modal title</h4>
			</div>
			<div class="modal-body">
			<div class="details hide"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<!-- <button type="button" class="btn btn-primary">Save changes</button> -->
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

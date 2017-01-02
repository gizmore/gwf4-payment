<gwf-payment-button>
	<form action="<?php echo $action; ?>" method="post">
		<div><?php echo $hidden; ?></div>
		<?php echo GWF_Form::buttonImage($button_name, sprintf('img/'.GWF_DEFAULT_DESIGN.'/buy_%s.png', $site_token)); ?>
	</form>
</gwf-payment-button>

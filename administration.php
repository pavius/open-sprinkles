<?php include('layout.inc'); ?>
<?php echo layout_getPageHeader(); ?>

<?php

	// include stuff
	include('logger.inc');

	//		
	// check if anything has been posted
	//
	if ($_POST)
	{
		// check which button pressed
		if ($_POST['logClear'])
		{
			// clear the log
			logger_clear();
		}
	}
?>

<?php echo layout_getBodyHeader(); ?>

	<h1>Log</h1><br />
	<div>
		<form action="administration.php" method="post">
			<textarea readonly="readonly" id="logarea"><?php echo logger_getLog(); ?></textarea>
			<br />
			<input type="submit" name="logClear" value=" Clear " class="button" style="margin:15px 0 0 650px;"/>
		</form>
	</div>	      	

<?php echo layout_getBodyFooter(); ?>


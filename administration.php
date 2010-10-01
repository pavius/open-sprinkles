<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
<title>
OpenSprinkles
</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="images/Envision.css" type="text/css" />

</head>

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

<body>
	<div id="wrap">
	  <div id="header">
	    <h1 id="logo-text">OpenSprinkles</h1>
	    <h2 id="slogan">freeware irrigation</h2>
	    <div id="header-links">
	      <p> version 0.0.1 | By Eran "pavius" Duchan | <a href="http://www.free-css.com/">More Info</a> </p>
	    </div>
	    <div id="current-time">
	      <p> <?php echo date('D M j Y, G:i:s'); ?> </p>
	    </div>
	  </div>
	  <div  id="menu">
	    <ul>
	      <li><a href="schedule.php">Schedule</a></li>
	      <li><a href="status.php">Status</a></li>
	      <li id="current"><a href="administration.php">Administration</a></li>
	    </ul>
	  </div>
	  <div id="content-wrap">
	    <div id="main"> <a name="TemplateInfo"></a>
	      <h1>Log</h1><br />
	      	<div>
				<form action="administration.php" method="post">
					<textarea readonly="readonly" id="logarea"><?php echo logger_getLog(); ?></textarea>
					<br />
					<input type="submit" name="logClear" value=" Clear " class="button" style="margin:15px 0 0 650px;"/>
				</form>
			</div>	      	
	    </div>
	  </div>
	  <div id="footer">
	    <p> Based on a CSS style by: <a href="http://www.styleshout.com/">styleshout</a>  </p>
	  </div>
	</div>
</body>
</html>
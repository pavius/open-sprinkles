<?php

	// get anything above the php
	function layout_getPageHeader()
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
			<html xmlns="http://www.w3.org/1999/xhtml"> 
			<head>
			<title>
			OpenSprinkles
			</title>
		
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
			<link rel="stylesheet" href="images/Envision.css" type="text/css" />
			</head>';
	}

	// get menu
	function layout_getMenu()
	{
		// the menu html
		$menuString = "";

		// get the menu items
		$menuItems = array("/schedule.php" => "Schedule", 
				   "/status.php" => "Status",
				   "/administration.php" => "Administration",
				   );

		// for selecting current page
		$menuItemId = "";

		// iterate over menu items
		foreach ($menuItems as $menuItemLink => $menuItemLabel)
		{
			// check if this is current page so we can highlight the title
			if ($menuItemLink == $_SERVER['SCRIPT_NAME']) $menuItemId = ' id="current"';
			else $menuItemId = "";

			// add menu item
			$menuString .= '<li'.$menuItemId.'><a href="'.$menuItemLink.'">'.$menuItemLabel.'</a></li>';
		}

		// return it
		return $menuString;
	}

	// get body header, below php and above main div
	function layout_getBodyHeader()
	{
		echo '<body>
				<div id="wrap">
				<div id="header">
					<h1 id="logo-text">OpenSprinkles</h1>
					<h2 id="slogan">freeware irrigation</h2>
					<div id="header-links">
					  <p> version 0.0.1 | By Eran "pavius" Duchan | <a href="http://www.free-css.com/">More Info</a> </p>
					</div>
					<div id="current-time">
					  <p>'.date("D M j Y, G:i:s").'</p>
					</div>
				  </div>
				  <div  id="menu">
					<ul>'.layout_getMenu().'</ul>
				  </div>
				  <div id="content-wrap">
					<div id="main"> <a name="TemplateInfo"></a>';
	}

	// get body header, below php and below main div 
	function layout_getBodyFooter()
	{
		echo '</div>
			  </div>
			  <div id="footer">
				<p> Based on a CSS style by: <a href="http://www.styleshout.com/">styleshout</a>  </p>
			  </div>
			</div>
		</body>
		</html>';
	}
?>

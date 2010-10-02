<?php include('layout.inc'); ?>
<?php echo layout_getPageHeader(); ?>

<?php

	// include stuff
	include_once('schedule.inc');
	include_once('logger.inc');
	include_once('operateValves.php');

	// valve configuration object
	$schedule_storedConfiguration = new Schedule();

	// gets days of week into a valve
	function schedule_getValveDays($storedSchedule, $valveIndex, $dayName)
	{
		$resultString = "";	
		$dayIndex = 0;	
		
		// iterate through days
		foreach ($dayName as $day)
		{		
			$checkboxIsChecked = "";
			
			// get if checked
			if ($storedSchedule->valves[$valveIndex]->daysToOperate[$dayIndex] != 0)
			{
				// set checked attribute
				$checkboxIsChecked = 'checked="checked"';
			}			
			
			// echo it
			$resultString .= '<div style="float:left;margin-left:4px;">
									<div style="float:top;text-align:center">'.$day.'</div>
									<div style="float:bottom;"><input type="checkbox" name="valve'.$valveIndex.'_day'.$dayIndex.'" '.$checkboxIsChecked.'/></div>
					  			  </div>';
					  			  
			// next day
			$dayIndex++;
		}
		
		return $resultString;
	}

	// get master control on/off
	function schedule_getMasterControl($storedSchedule, $mode)
	{
		// check if equal
		if ($mode == $storedSchedule->masterControl)
		{
			echo 'checked="checked"';
		}
		else echo '';
	}

	// creates a dropdown menue for a certain valve
	function common_createDropDownListOptions($format, $startNumber, $endNumber, $increment, $selectedNumber)
	{
		// add items
	   for ($currentNumber = $startNumber; $currentNumber <= $endNumber; $currentNumber += $increment) 
		{
			// selected
			$selected = ($currentNumber == $selectedNumber ? "selected" : "");

			// format the number
			$formattedCurrentNumber = sprintf($format, $currentNumber);
	
			// add item
	      echo '<option value='.$formattedCurrentNumber.' '.$selected.'>'.$formattedCurrentNumber.'</option>';
		}
	}

	// outputs a valve
	function schedule_outputValve($storedSchedule, $style, $valveIndex, $valve)
	{
		// get days of week checkboxes
		$daysOfWeek = schedule_getValveDays($storedSchedule, $valveIndex, array("S", "M", "T", "W", "T", "F", "S"));	
				
		// echo it
		echo '<div class="valve_wrapper" style="'.$style.'">		
				<fieldset class="base">
				<legend class="base_legend">Valve #'.($valveIndex + 1).'</legend>
				<div>				
				<table border="0">
				<tr>
					<td class="valve_value_name">Name:</td>
					<td><input type="text" size="16" name="valve'.$valveIndex.'_name" value="'.$storedSchedule->valves[$valveIndex]->name.'" class = "styled_input"/></td>
				</tr>
				<tr>
					<td class="valve_value_name">Days:</td>
					<td>
					<div style="float:left;">'.$daysOfWeek.'</div>
					<div style="clear:both;"></div>		
					</td>
				</tr>
				<tr>
					<td class="valve_value_name">On for:</td>
					<td><input type="text" size="2" name="valve'.$valveIndex.'_duration" value="'.$storedSchedule->valves[$valveIndex]->operationDurationInMinutes.'" class = "styled_input"/>  minutes</td>
				</tr>
				</table>
				</div>			
				</fieldset>
			  </div>';
	}

	// outputs all valves
	function schedule_outputValves($storedSchedule)
	{
		// iterate through all valves
		for ($valveIndex = 0; $valveIndex < count($storedSchedule->valves); $valveIndex++)
		{
			// style for valve
			$valveStyle = "float: left";		
			 
			// output valve
			schedule_outputValve($storedSchedule, $valveStyle, $valveIndex, 0);
		}  
	}
	
	//		
	// check if anything has been posted
	//
	if ($_POST)
	{
		// iterate over all valves
		for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++)
		{
			// new valve
			$valve = new ScheduleValve();				
			
			// set name and duration
			$valve->name = $_POST['valve'.$valveIndex.'_name'];
			$valve->operationDurationInMinutes = $_POST['valve'.$valveIndex.'_duration'];
			
			// iterate dow
			for ($dayIndex = 0; $dayIndex < 7; $dayIndex++)
			{
				// shove if set
				$valve->daysToOperate[$dayIndex] = isset($_POST['valve'.$valveIndex.'_day'.$dayIndex]);
			}
			
			// shove to array
			$schedule_storedSchedule->valves[$valveIndex] = $valve; 
		} 		

		// get master control
		$schedule_storedSchedule->masterControl = ($_POST['masterControl'] == 'controlOn');
		
		// get start hour
		$schedule_storedSchedule->startHour = $_POST['startHour'];
		$schedule_storedSchedule->startMinutes = $_POST['startMinutes'];
	
		// concurrent open valves
		$schedule_storedSchedule->maxConcurrentOpenValves = $_POST['maxConcurrentOpenValves'];
		
		// save to file
		$fp = fopen($schedule_configurationFileName, 'w');
		
		// check
		if ($fp) 
		{
			// log
			logger_logEvent('Updating configuration');
			
			// write
			fwrite($fp, serialize($schedule_storedSchedule));
			fclose($fp);

			// operate the valves accordingly, don't wait for cron
			operateValves_performActions();
		}
	}
	else 
	{
		//
		// Try to load the data from the saved configuration
		//		

		// check if valve configuration exists
		if (file_exists($schedule_configurationFileName)) 
		{
		    // load from file
		    $schedule_storedSchedule = unserialize(file_get_contents($schedule_configurationFileName));
		}
		else
		{
			// iterate over valves
			for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++)
			{
				// create default valve
				$valve = new ScheduleValve();
					$valve->name = 'Area #'.($valveIndex + 1);
					$valve->daysToOperate = array(0, 0, 0, 0, 0, 0, 0);   
					$valve->operationDurationInMinutes = 0;
					
				// shove to array
				$schedule_storedSchedule->valves[$valveIndex] = $valve;
				
				// init master mode and start hour
				$schedule_storedSchedule->masterControl = false; // off
				$schedule_storedSchedule->startHour = 0;
				$schedule_storedSchedule->startMinutes = 0;
				$schedule_storedSchedule->maxConcurrentOpenValves = 1;
			}
		}
	}
?>

<?php echo layout_getBodyHeader(); ?>

	<form action="schedule.php" method="post">
	  <h1>General Stuff</h1>
		<div>
			<table border="0" style="margin=0 0 0 0; padding=0 0 0 0;">
			<tr>
				<td class="general_config_name">Schedule is:</td>
				<td>
					<input type="radio" id="masterControlOn" name="masterControl" value="controlOn" <?php schedule_getMasterControl($schedule_storedSchedule, true); ?> /> Active  
					<input type="radio" id="masterControlOff" name="masterControl" value="controlOff" <?php schedule_getMasterControl($schedule_storedSchedule, false); ?> /> Inactive 
				</td>
			</tr>
			<tr>
				<td class="general_config_name">Start at:</td>
				<td>
					<select name="startHour">
						<?php	common_createDropDownListOptions('%02d', 0, 23, 1, $schedule_storedSchedule->startHour); ?>				  
					</select> :
					<select name="startMinutes">
						<?php	common_createDropDownListOptions('%02d', 0, 45, 15, $schedule_storedSchedule->startMinutes); ?>
					</select>
				</td>
				<td>
					<input type="submit" id="scheduleSave" value=" Save " class="button" style="margin-left:375px;"/>
				</td>
			</tr>
			<tr>
				<td class="general_config_name">At most, open:</td>
				<td>
					<select name="maxConcurrentOpenValves">
						<?php	common_createDropDownListOptions('%d', 1, $schedule_numberOfValves, 1, $schedule_storedSchedule->maxConcurrentOpenValves); ?>
					</select>
					 &nbsp valve(s) at a time
				</td>
			</tr>
			</table>
		</div>	      	
		<div style="clear:both;"></div>

		<h1>Irrigation Schedule</h1><br>
		<div class="content">	      	
			<?php schedule_outputValves($schedule_storedSchedule); ?>
		</div>
	</form>

<?php echo layout_getBodyFooter(); ?>
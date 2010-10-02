<?php

	// include stuff
	include('schedule.inc');	
	include('logger.inc');

	//
	// Class definitions
	//

	// a single time range
	class TimeRange
	{
		// ctor
		function TimeRange($_start, $_end)
		{
			// save
			$this->start = $_start;
         $this->end = $_end;
		}
		
		// members
      var $start;
      var $end;
	}

	// time ranges
	class TimeRanges
	{
		// add a range
		function addRange($start, $end)
		{
			// shove to list
			$this->timeRanges[] = new TimeRange($start, $end);
		}
		
		// holds ranges of time
		var $timeRanges = array();
	}

	//
	// Global variables
	//

	// temporary valve state filename
	$operateValves_currentlyOpenValvesFileName = 'operateValves_currentlyOpenValves.cfg';

	//
	// Local routines
	//

	// initialize slots
	function valveSchedulingSlotsInit($dayIndex, $storedSchedule)
	{
		// generate the start time, as offset in minutes into week
		$startTimeOffsetMinutesIntoWeek = $storedSchedule->startMinutes;
		$startTimeOffsetMinutesIntoWeek += ($storedSchedule->startHour * 60);
		$startTimeOffsetMinutesIntoWeek += ($dayIndex * 24 * 60);

		// valve slots
		$valveSlots = array();

		// create slots
		for ($slotIndex = 0; $slotIndex < $storedSchedule->maxConcurrentOpenValves; $slotIndex++) 
		{
			// intiailize valve slot
			$valveSlots[$slotIndex] = $startTimeOffsetMinutesIntoWeek;
		}

		// return the slots
		return $valveSlots;
	}

	// find the earlist slot
	function valveSchedulingSlotsFindEarliestSlot($valveSlots)
	{
		// marks the slot with the earlist time
		$earliestSlotIndex = 0;
		$currentEarliestTime = ((7 * 24 * 60) + 1); // week minutes + 1

		// create slots
		for ($slotIndex = 0; $slotIndex < count($valveSlots); $slotIndex++) 
		{
			// mark the earlist slot
			if ($currentEarliestTime > $valveSlots[$slotIndex]) 
			{
				// mark the earlist time
				$earliestSlotIndex = $slotIndex;
				$currentEarliestTime = $valveSlots[$slotIndex];
			}
		}

		// return the index holding the earlist slot time
		return $earliestSlotIndex;
	}

	// take the stored schedule and convert it to time ranges
	function getWeeklyValveSchedule($storedSchedule)
	{
		// number of valves
		global $schedule_numberOfValves;		

		// the result array
		$weeklyValveSchedule = array();	
		
		// add one TimeRanges to each valve
		for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++) 
		{
			// add time range
			$weeklyValveSchedule[$valveIndex] = new TimeRanges(); 
		}

		// number of valves
		global $schedule_numberOfValves;		
		
		// check if master control is on
		if ($storedSchedule->masterControl)
		{
			// for each day of the week
			for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) 
			{
				// initialize valve scheduling slots
				$valveSchedulingSlots = valveSchedulingSlotsInit($dayIndex, $storedSchedule);

				// iterate through each valve and shove it to schedule
				for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++) 
				{
					// get current valve
					$currentValve = $storedSchedule->valves[$valveIndex];
	
					// check if this day is on for this valve and
					// check if a duration is set, don't add if day set but duration not
					if ($currentValve->daysToOperate[$dayIndex] && $currentValve->operationDurationInMinutes)
					{
						// find the earlist scheduling slot for this valve, allowing the max concurrent valves
						$valveSlotIndex = valveSchedulingSlotsFindEarliestSlot($valveSchedulingSlots);

						// schedule the valve at this time
						$weeklyValveSchedule[$valveIndex]->addRange($valveSchedulingSlots[$valveSlotIndex], 
																	$valveSchedulingSlots[$valveSlotIndex] + $currentValve->operationDurationInMinutes);

						// add the duration to the valve slot
						$valveSchedulingSlots[$valveSlotIndex] += $currentValve->operationDurationInMinutes;	
					}
				}
			}
		}
		
		// return result array
		return $weeklyValveSchedule;
	}

	// get current time, check which valves need to be open right now		
	function getListOfValvesCurrentlyRequiredOpen($weeklyValveSchedule)
	{
		// globals
		global $schedule_numberOfValves;		

		// result array
		$valvesRequiredToBeOpen = array();

		// add one TimeRanges to each valve
		for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++) 
		{
			// set default valves to off
			$valvesRequiredToBeOpen[$valveIndex] = 0; 
		}

		// get current time
		$currentTime = localtime(time(), true);
		
		//
		// generate current offset into week
		//
		
		// get minutes/hours/days (0 = sunday, like in frontend)
		$currentOffsetMinutesIntoWeek = $currentTime['tm_min'];
		$currentOffsetMinutesIntoWeek += ($currentTime['tm_hour'] * 60);
		$currentOffsetMinutesIntoWeek += ($currentTime['tm_wday'] * 24 * 60);
		
		// print
		print 'current offset: '.$currentOffsetMinutesIntoWeek.NEW_LINE;
		
		// iterate through valves, find valves with a range holding the current offset
		for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++)
		{
			// iterate over valve ranges
			for ($timeRangeIndex = 0; $timeRangeIndex < count($weeklyValveSchedule[$valveIndex]->timeRanges); $timeRangeIndex++)
			{
				// get current range
				$currentRange = $weeklyValveSchedule[$valveIndex]->timeRanges[$timeRangeIndex];
				
				// check if current offset falls inside the range
				if (($currentOffsetMinutesIntoWeek >= $currentRange->start) && 
					 ($currentOffsetMinutesIntoWeek <= $currentRange->end))
				{
					// add this valve to the list
					$valvesRequiredToBeOpen[$valveIndex] = 1;
				}	
			}	
		}

		// return result array
		return $valvesRequiredToBeOpen;
	}
	
	// get list of currently open valves
	function getCurrentlyOpenValves()
	{
		// globals
		global $schedule_numberOfValves;		
		global $operateValves_currentlyOpenValvesFileName;

		// TODO: get from hardware
		
		// right now get from persistent storage
		$openValves = array();
		
		// check if valve configuration exists
		if (file_exists($operateValves_currentlyOpenValvesFileName)) 
		{
			// load from file
			$openValves = unserialize(file_get_contents($operateValves_currentlyOpenValvesFileName));
		}
		else 
		{
			// set all to off
			for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++)
				$openValves[] = 0; 
		}
		
		// return the open valves
		return $openValves;
	} 	
	
	// get command lists
	function getValveCommands($valvesRequiredToBeOpen, $valvesCurrentlyOpen, &$valvesToOpen, &$valvesToClose)
	{
		// number of valves
		global $schedule_numberOfValves;		

		// iterate through valves, find valves with a range holding the current offset
		for ($valveIndex = 0; $valveIndex < $schedule_numberOfValves; $valveIndex++)
		{
			// check if the current state is not equal to the required state
			if ($valvesRequiredToBeOpen[$valveIndex] != $valvesCurrentlyOpen[$valveIndex])
			{
				// check if we need to close or open
				if ($valvesRequiredToBeOpen[$valveIndex])
				{
					// add to list of valves that are requird to be open
					$valvesToOpen[] = $valveIndex;
				}
				else
				{
					// add to list of valves that are requird to be closed
					$valvesToClose[] = $valveIndex;
				}
			}
		}
	}

	// execute the commands
	function executeValveCommands($valvesToOpen, $valvesToClose)
	{
	}

	// udpate the commands
	function updateCurrentlyOpenValves($valvesRequiredToBeOpen)
	{
		global $operateValves_currentlyOpenValvesFileName;

		// save to file
		$fp = fopen($operateValves_currentlyOpenValvesFileName, 'w');

		// check
		if ($fp) 
		{
			// write
			fwrite($fp, serialize($valvesRequiredToBeOpen));
			fclose($fp);
		}
	}

	// convert an array to string, separated by delimiter
	function arrayToString($arrayToPrint, $delimiter)
	{
		$resultString = "";

		// check if empty
		if (!empty($arrayToPrint))
		{
			// iterate over array
			for ($itemIndex = 0; $itemIndex < count($arrayToPrint); $itemIndex++)
			{
				// add to result string
				$resultString .= $arrayToPrint[$itemIndex];

				// add delimter
				$resultString .= $delimiter;
			}
		}
		else
		{
			// set empty as result
			$resultString = "None";
		}

		// return result
		return $resultString;
	}

	//
	// Entry
	//

	// log entry
	logger_logEvent('Checking required valve commands');

	// check if valve configuration exists
	if (file_exists($schedule_configurationFileName)) 
	{
		// load from file
		$storedSchedule = unserialize(file_get_contents($schedule_configurationFileName));
	    
		// convert schedule to a per-valve array of time ranges (start/end)
		// each range is represented by offset, in minutes, into the week
		$weeklyValveSchedule = getWeeklyValveSchedule($storedSchedule);

		// generate a list of valves that need to be open right now
		$valvesRequiredToBeOpen = getListOfValvesCurrentlyRequiredOpen($weeklyValveSchedule);
		
		// get a list of valves that are currently open
		$valvesCurrentlyOpen = getCurrentlyOpenValves();
		
		//
		// get command lists
		// 

		// command lists
		$valvesToOpen = array();
		$valvesToClose = array();

		// get the commands
		getValveCommands($valvesRequiredToBeOpen, $valvesCurrentlyOpen, $valvesToOpen, $valvesToClose);

		// execute the commands
		executeValveCommands($valvesToOpen, $valvesToClose);

		// update currently open valves. TODO get from hardware
		updateCurrentlyOpenValves($valvesRequiredToBeOpen);

		// print
		logger_logEvent('Currently open valves: '.arrayToString($valvesCurrentlyOpen, ", "));
		logger_logEvent('Valves required to be open: '.arrayToString($valvesRequiredToBeOpen, ", "));
		logger_logEvent('Opening these valves: '.arrayToString($valvesToOpen, ", "));
		logger_logEvent('Closing these valves: '.arrayToString($valvesToClose, ", "));

		/*$foo = array();
		$foo[0] = 2312;
		$foo[1] = 222212;
		echo arrayToString($foo, ", ");*/


		// print
		/* print 'Valve schedule: '.NEW_LINE;
		print_r($weeklyValveSchedule); */


		/*print 'Currently open valves: '.NEW_LINE;
		print_r($valvesCurrentlyOpen);

		// print
		print ''.NEW_LINE;
		print_r();

		// print
		print ''.NEW_LINE;
		print_r();

		// print
		print ''.NEW_LINE;
		print_r($valvesToClose);*/

	}
?>


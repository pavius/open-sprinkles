<?php

	//
	// Abstracts a schedule
	// 
	
	// a schedule
	class Schedule
	{
		// master control
		var $masterControl;
		
		// start hour, minutes
		var $startHour;
		var $startMinutes;
				
		// valves
		var $valves = array();
		
		// max number of valves concurrently open
		var $maxConcurrentOpenValves;			
	}

	// valve class
	class ScheduleValve
	{
	    // name of valve
	    var $name;
	
	    // which days to operate
	    var $daysToOperate = array();
	
	    // duration, in minutes of operation
	    var $operationDurationInMinutes;

	    // mode (0 = auto, 1 = manual)
	    // TODO: enum
	    var $mode;

	    // in case mode is manual, this indicates the time that the
	    // the valve was set to be open
	    var $manualStartTime;
	}
	
	// the configuration of the valves
	$schedule_configurationFileName = "state/schedule_weeklySchedule.cfg";
	
	// number of valves in the system
	$schedule_numberOfValves = 8;

?>
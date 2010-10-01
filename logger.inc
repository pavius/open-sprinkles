<?php
	// until i understand how to do this normally
	define("NEW_LINE", '
');

	// set the logfile name
	$logFileName = 'openSprinkles.log';

	// log an event
	function logger_logEvent($event)
	{
		global $logFileName;

		// set the logfile name
		$logFileName = 'openSprinkles.log';

		// add the date to the string
		$logLine = '['.date('Ymd G:i:s').'] '.$event.NEW_LINE;

		// open a file for appending
		$logFileHandle = fopen($logFileName, 'a');
		
		// check if success
		if ($logFileHandle)
		{
			// try to lock the file for writing
			if (flock($logFileHandle, LOCK_EX))
			{
				// write the date to the file
				fwrite($logFileHandle, $logLine);

				// unlock
				flock($logFileHandle, LOCK_UN);
			}

			// close the file
			fclose($logFileHandle);
		}
	}

	// get the log
	function logger_getLog()
	{
		global $logFileName;

		// will hold log
		$logString = "";

		// check if file exists
		if (file_exists($logFileName))
		{
			// open a file for appending
			$logFileHandle = fopen($logFileName, 'r');
	
			// check if success
			if ($logFileHandle)
			{
				// try to lock the file for reading
				if (flock($logFileHandle, LOCK_SH))
				{
					// read data into log string
					$logString = fread($logFileHandle, filesize($logFileName)); 
	
					// do the unlock
					flock($logFileHandle, LOCK_UN);
				}
	
				// close the file
				fclose($logFileHandle);
			}
		}

		// return the log
		return $logString;
	}

	// clear
	function logger_clear()
	{
		global $logFileName;

		// will hold log
		$logString = "";

		// check if file exists
		if (file_exists($logFileName))
		{
			// delete the file
			unlink($logFileName);
		}
	}
?>
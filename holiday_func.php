<?php
##
## holiday module - A dotProject module for keeping track of holidays
##
## Sensorlink AS (c) 2006
## Vegard Fiksdal (fiksdal@sensorlink.no)
##

require_once 'PEAR/Holidays.php';

function isHoliday( $date=0 ){
	// Query database for settings
	$holiday_manual = db_loadResult( 'SELECT holiday_manual FROM holiday_settings' );
	$holiday_auto = db_loadResult( 'SELECT holiday_auto FROM holiday_settings' );
	$holiday_driver = db_loadResult( 'SELECT holiday_driver FROM holiday_settings' );

	if(!$date)
	{
		$date=new CDate;
	}

	if($holiday_manual)
	{
		// Check whether the date is blacklisted
		$sql = "SELECT * FROM holiday ";
		$sql.= "WHERE holiday_start_date <= '";
		$sql.= $date->format( FMT_DATETIME_MYSQL );
		$sql.= "' AND holiday_end_date >= '";
		$sql.= $date->format( FMT_DATETIME_MYSQL );
		$sql.= "' AND holiday_white=0";
		if(db_loadResult($sql))
		{
			// Wimp out 
//echo "<td>Blacklist hit</td>";
			return 0;
		}

                // Check if we have a whitelist item for this date 
                $sql = "SELECT * FROM holiday ";
                $sql.= "WHERE holiday_start_date <= '";
                $sql.= $date->format( FMT_DATETIME_MYSQL );
                $sql.= "' AND holiday_end_date >= '";
                $sql.= $date->format( FMT_DATETIME_MYSQL );
                $sql.= "' AND holiday_white=1";
                if(db_loadResult($sql))
                {
			// Kick the bucket, we have a hit
//echo "<td>Whitelist hit</td>";
                        return 1;
                }
	}

	if($holiday_auto)
	{
		// Still here? Ok, lets poll the automatic system
		$drivers_alloc = Date_Holidays::getInstalledDrivers();
		$driver_object = Date_Holidays::factory($drivers_alloc[$holiday_driver]['title'],$date->getYear(),'en_EN');
		if (!Date_Holidays::isError($driver_object)) {
			if($driver_object->getHolidayForDate($date)){
				// Woha! Its a holiday!
//echo "<td>autohit</td>";
				return 1;
			}
		}
	}

	// No hits, must be a working day
	return 0;
}
?>

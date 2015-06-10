<?php

include_once '../backend/loggingMoers.php';
include_once '../backend/loggingMoersAppointments.php';

function getWeekData( $week, $year)
{
	if( $week < 10) {
		$week = '0'.$week;
	}

	$logfile = '../backend/data' . '/' . $year . '-json' . '/' . $year . '-' . $week . '.json';

	if( file_exists( $logfile)) {
		return file_get_contents( $logfile);
	}

	return '';
}

$date = time();
$ret = '';

for( $i = 0; $i < 8; ++$i, $date -= 7 * 24 * 60 * 60) {
	$year = intVal( date( 'Y', $date));
	$week = intVal( date( 'W', $date));
	$month = intVal( date( 'm', $date));
	if(( 12 == $month) && ($week < 30)) {
		++$year;
	}

	$ret .= getWeekData( $week, $year);
}

$current = getCurrentWaitDataJSON();
if( '' == $current) {
	$current = getCurrentAppointmentDataJSON();
} else {
	$current = rtrim( $current, "}\n,");
	$current .= ', ' . substr( getCurrentAppointmentDataJSON(), 1);
}

$ret .= $current;
$ret = rtrim( $ret, "\n,");

echo '[' . $ret . ']';

?>


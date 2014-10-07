<?php

include_once '../backend/loggingMoers.php';

function getWeekData( $week, $year)
{
	$logfile = '../backend/data' . '/' . $year . '-json' . '/' . $year . '-' . $week . '.json';

	if( file_exists( $logfile)) {
		$ret .= ': ' . $logfile;
		return file_get_contents( $logfile);
	}

	return '';
}

$date = time();
$ret = '';

for( $i = 0; $i < 8; ++$i, $date -= 7 * 24 * 60 * 60) {
	$year = intVal( date( 'Y', $date));
	$week = intVal( date( 'W', $date));

	$ret .= getWeekData( $week, $year);
}

$ret .= getCurrentWaitDataJSON();
$ret = rtrim( $ret, "\n,");

echo '[' . $ret . ']';

?>


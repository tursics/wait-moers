<?php

function getCurrentWaitDataXML()
{
	// http://download.moers.de/Wartezeiten/wartezeiten.txt
	$dataURL = 'http://www.moers.de/www/webio.nsf/generateViewFromTemplate?OpenAgent&layout=Wartezeiten&nocache=1';
	return file_get_contents( $dataURL);
}

function getCurrentWaitDataJSON( $xmlContent)
{
	return convertWaitDataJSON( getCurrentWaitDataXML());
}

function convertWaitDataJSON( $xmlContent)
{
	if( '' == $xmlContent) {
		return '';
	}

	$xml = simplexml_load_string( $xmlContent);
	$json = json_encode( $xml);
	$array = json_decode( $json, TRUE);

	$timestamp = $array['eintrag'][0]['zeitstempel'];
	$datastr = '';
	$count = 0;
	$min = 0;
	$h = intval( substr( $timestamp, strpos( $timestamp, ' ') + 1, 2));

	$day = intval( substr( $timestamp, 0, 2));
	$month = intval( substr( $timestamp, strpos( $timestamp, '.') + 1, 2));
	$year = intval( substr( $timestamp, strpos( $timestamp, ' ') - 4, 4));
	$datetime = mktime( 0, 0, 0, $month, $day, $year);
	$average = 0;

	foreach( $array['eintrag'] as $value) {
		$wait = intval( $value['wartezeit']);
		$hour = $value['zeitstempel'];
		$hour = intval( substr( $hour, strpos( $hour, ' ') + 1, 2));

		if( $h != $hour) {
			$average = $count == 0 ? 0 : round( $min / $count);
			$datastr .= '{"wait": ' . $average . ', "hour": ' . $h . ', "year": ' . $year . ', "week": ' . date( 'W', $datetime) . ', "weekday": ' . (date( 'N', $datetime) - 1) . '},';
			$h = $hour;
			$count = 0;
			$min = 0;
		}
		++$count;
		$min += $wait;
	}

	$datastr .= '{"wait": ' . $average . ', "hour": ' . $h . ', "year": ' . $year . ', "week": ' . date( 'W', $datetime) . ', "weekday": ' . (date( 'N', $datetime) - 1) . '},' . "\n"	;

	return $datastr;
}

function loggingMoersWaitingJSON( $content)
{
	$datastr = convertWaitDataJSON( $content);

	$xml = simplexml_load_string( $content);
	$json = json_encode( $xml);
	$array = json_decode( $json, TRUE);
	$timestamp = $array['eintrag'][0]['zeitstempel'];
	$day = intval( substr( $timestamp, 0, 2));
	$month = intval( substr( $timestamp, strpos( $timestamp, '.') + 1, 2));
	$year = intval( substr( $timestamp, strpos( $timestamp, ' ') - 4, 4));
	$datetime = mktime( 0, 0, 0, $month, $day, $year);

	$logfile = "../backend/data";
	mkdir( $logfile, 0777);
	$logfile .= "/".$year.'-json';
	mkdir( $logfile, 0777);
	$logfile .= "/".$year.'-'.date( 'W', $datetime).".json";

	$log_fp = fopen( $logfile, "a");

	if( flock( $log_fp, 2)) {
		fputs( $log_fp, $datastr);
	} else {
		return; // flock write failure!
	}
	if( flock( $log_fp, 3)) {
		fclose( $log_fp);
	} else {
		return; // flock release failure!
	}
}

function loggingMoersWaitingXML()
{
	$err = ini_get( 'error_reporting');
	ini_set ( 'error_reporting', 0);
	$logfile = "../backend/data";
	mkdir( $logfile, 0777);
	$logfile .= "/".date( "Y-m");
	mkdir( $logfile, 0777);
	$logfile .= "/".date( "Y-m-d").".xml";
	$hour = date( 'H');

	if(( $hour >= 19) && !file_exists( $logfile)) {
		$content = getCurrentWaitDataXML();

		$log_fp = fopen( $logfile, "w");

		if( flock( $log_fp, 2)) {
			fputs( $log_fp, $content);
		} else {
			return; // flock write failure!
		}
		if( flock( $log_fp, 3)) {
			fclose( $log_fp);
		} else {
			return; // flock release failure!
		}

		loggingMoersWaitingJSON( $content);
	}

	ini_set( 'error_reporting', $err);
}

loggingMoersWaitingXML();

?>


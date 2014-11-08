<?php

include_once( 'loggingMoers.php');

function loggingMoersWaitingXMLFilelist_bugfix( $path)
{
	$ret = array();
	$dir = opendir( $path);

	while(( $file = readdir( $dir)) !== false) {
		if( "." == $file[0]) {
			continue;
		}

		$fullpath = $path . "/" . $file;
		if( is_dir( $fullpath)) {
			$ret = array_merge( $ret, loggingMoersWaitingXMLFilelist_bugfix( $fullpath));
		} else {
			if( "xml" == substr( $file, -3)) {
				$ret[] = $fullpath;
			}
		}
	}

	return $ret;
}

function loggingMoersWaitingJSON_bugfix( $content)
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

	$logfile = "../backend";
	if( !file_exists( $logfile)) {
		$logfile = "../../wartezeit.tursics.de/backend";
	}
	$logfile .= "/data";
	mkdir( $logfile, 0777);
	$logfile .= "/".$year.'-json-bugfix';
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

function loggingMoersWaitingXML_bugfix()
{
	$logfile = "../backend";
	if( !file_exists( $logfile)) {
		$logfile = "../../wartezeit.tursics.de/backend";
	}
	$logfile .= "/data";
	mkdir( $logfile, 0777);

	$files = loggingMoersWaitingXMLFilelist_bugfix( $logfile);

	foreach( $files as $file) {
		$content = file_get_contents( $file);

		loggingMoersWaitingJSON_bugfix( $content);
	}
}

loggingMoersWaitingXML_bugfix();

?>

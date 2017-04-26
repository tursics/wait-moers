<?php
	$url = 'http://wartezeit.tursics.de/harvest.php';

	$json = file_get_contents( $url);
	$data = json_decode( $json, true);

	foreach( $data as $item) {
		echo '<div style="padding:0 0 1em 0;">';
		echo $item['title'] . '<br>';
		echo '<span style="display:inline-block;width:7em;">Kapazität: ' . $item['capacity'] . '</span>';
		echo '<span style="display:inline-block;width:5em;">Frei: ' . $item['available'] . '</span>';
		if( $item['trend'] == 'fuller') {
			echo '<span style="background:LightPink;">Belegung: steigt</span>';
		} else if( $item['trend'] == 'emptier') {
			echo '<span style="background:LightGreen;">Belegung: fällt</span>';
		} else {
			echo '<span style="background:white;">Belegung: unverändert</span>';
		}
		echo '</div>';
	}
?>

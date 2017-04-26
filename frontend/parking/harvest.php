<?php
	$url = 'http://www.verkehr.nrw.de/karte';                                               // domain
	$url .= '?p_p_id=ivtabelleportletadvanced_WAR_IVTabellePortletAdvancedportlet';         // unknown parameter but needed
	$url .= '&p_p_lifecycle=2';                                                             // unknown parameter but needed
//	$url .= '&p_p_state=normal';                                                            // unknown parameter
//	$url .= '&p_p_mode=view';                                                               // unknown parameter
//	$url .= '&p_p_cacheability=cacheLevelPage';                                             // unknown parameter
//	$url .= '&p_p_col_id=column-1';                                                         // unknown parameter
//	$url .= '&p_p_col_pos=5';                                                               // unknown parameter
//	$url .= '&p_p_col_count=6';                                                             // unknown parameter
//	$url .= '&sEcho=1';                                                                     // unknown parameter
//	$url .= '&iColumns=5';                                                                  // unknown parameter (-,Stadt,Name,Typ,Parkplaetze ?)
	$url .= '&iDisplayStart=0';                                                             // data range: start number of dataset
	$url .= '&iDisplayLength=20';                                                           // data range: maximum number of datasets (there are 13 car parks in Moers)
//	$url .= '&mDataProp_0=0';                                                               // unknown parameter
//	$url .= '&mDataProp_1=1';                                                               // unknown parameter
//	$url .= '&mDataProp_2=2';                                                               // unknown parameter
//	$url .= '&mDataProp_3=3';                                                               // unknown parameter
//	$url .= '&mDataProp_4=4';                                                               // unknown parameter
	$url .= '&action=getBoundingBox';                                                       // use a rectangle to define filtered area
	$url .= '&boundingBox=332555.86044312,5699533.0042267,338085.1878357,5705772.3230743';  // this is the bounding box of moers, approximately
	$url .= '&trafficMessageType=Parking';                                                  // show car parks
//	$url .= '&prognosisPeriod=0';                                                           // unknown parameter
//	$url .= '&_=1432911987174';                                                             // 1432911987 is the unix timestamp of 2015-05-29T15:06:27+00:00
//	                                                                                        // 174 is unknown
	$url .= '&_=' . time() . '000';                                                         // hack: current unix timestamp plus '000'

	$json = file_get_contents( $url);
	$data = json_decode( $json, true);
	$aaData = $data['aaData'];
	$result = array();

	foreach( $aaData as $value) {
		// $value[0]  - image details button
		// $value[1]  - address
		// $value[2]  - name
		// $value[3]  - image parking type
		// $value[4]  - parking slots
		// $value[10] - fee
		// $value[11] - additions and restrictions
		$item = array(
			'title' => '',
			'info' => '',
			'street' => '',
			'zip' => 0,
			'city' => '',
			'type' => '',
			'capacity' => 0,
			'available' => 0,
			'trend' => '',
			'fee' => ''
		);

		$val1 = explode( '<br/>', $value[1]);
		$item['street'] = trim( $val1[0]);
		$val1 = explode( ' ', $val1[1]);
		$item['zip'] = (int) $val1[0];
		$item['city'] = $val1[1];

		$item['title'] = $value[2];

		$val3 = explode( '.', $value[3]);
		$val3 = explode( '/', $val3[ count( $val3) - 2]);
		$val3 = $val3[ count( $val3) - 1];
		if( $val3 == 'parkplatz') {
			$item['type'] = 'parking lot';
		} else if( $val3 == 'parkhaus') {
			$item['type'] = 'parking garage';
		}

		$val4 = explode( '<br/>', $value[4]);
		$trend = explode( ' ', $val4[1]);
		if( 'Belegung' == $trend[0]) {
			if( $trend[1] == 'steigt') {
				$item['trend'] = 'fuller';
			} else if( $trend[1] == 'fällt') {
				$item['trend'] = 'emptier';
			} else {
				$item['trend'] = 'unchanged';
			}
		}

		$val4 = explode( '(', $val4[0]);
		$available = explode( ' ', $val4[1]);
		if( 'Frei)' == $available[1]) {
			$item['available'] = (int) $available[0];
		}

		$capacity = explode( ' ', trim( $val4[0]));
		$item['capacity'] = (int) $capacity[1];

		$val10 = explode( '<br/>', $value[10]);
		$item['fee'] = $val10[1];

		$val11 = explode( '<br/>', $value[11]);
		if( count( $val11) > 1) {
			$item['info'] = $val11[1];
		}

		$result[] = $item;
	}

	echo json_encode( $result);
?>

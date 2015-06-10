<?php
	// get the appointments for the city of Moers

	function getCurrentAppointmentDataDate()
	{
		// create the session cookie (the service id will be saved in this cookie)
		$page = getPage( 1);

		// choose the services (1 x Personalausweis => 20 minutes)
		$services = array(
			"casetype_5810" => "0", // Abholung
			"casetype_5816" => "0", // Kinderreisepass
			"casetype_5820" => "1", // Personalausweis
			"casetype_5821" => "0", // Reisepass, auch bei Einbürgerung, Namensänderung
			"casetype_5824" => "0", // Verlängerung Kindereisepass
			"casetype_5827" => "0", // Verlustmeldung
			"casetype_5825" => "0", // Vorläufiger Personalausweis
			"casetype_5826" => "0", // Vorläufiger Reisepass
			"casetype_5809" => "0", // Abmeldung Wohnsitz ins Ausland
			"casetype_5811" => "0", // Anmeldung Wohnsitz
			"casetype_5823" => "0", // Ummeldung Wohnsitz innerhalb
			"casetype_5812" => "0", // Beglaubigung
			"casetype_7332" => "0", // Führerscheinangelegenheiten
			"casetype_5814" => "0", // Führungszeugnis
			"casetype_5815" => "0", // Gewerbezentralregister
			"casetype_5817" => "0", // Melde / Aufenthaltsbescheinigung
			"casetype_5818" => "0", // Melderegisterauskunft
			"casetype_5819" => "0", // Moers-Pass
			"casetype_7048" => "0", // Steuer-ID
			"casetype_5822" => "0", // Untersuchungsberechtigungsschein
			"sentcasetypes" => "Weiter",
		);
		$page = getPage( 2, null, $services);

		$date = getFirstDate( $page);

		if( null == $date) {
			$month = intval( date( 'n'));
			$year = intval( date( 'Y'));

			++$month;
			if( $month > 12) {
				$month = 1;
				++$year;
			}

			$page = getPage( 3, null, null, $month, $year);
			$date = getFirstDate( $page);
		}

		return $date;
	}

	function getCurrentAppointmentDataJSON()
	{
		$today = strtotime( date( 'Y-m-d'));
		$date = getCurrentAppointmentDataDate();

		if( null != $date) {
			$diff = intval(( $date - $today) / 24 / 60 / 60);
			return '{"lastappointment": ' . $diff . '},';
		}

		return '{"lastappointment": null},';
	}

	function getPage( $step, $get = null, $post = null, $month = null, $year = null)
	{
//		$url_ref = 'http://termine.moers.de/';
		$client = 'stadtmoers';
		$ua = 'Mozilla/5.0 (Windows NT 5.1; rv:16.0) Gecko/20100101 Firefox/16.0 (appointment robot)';
		$cookiefile = '../backend/cookiefile.txt';

		$url = 'http://netappoint.de/ot/' . $client . '/index.php?company=' . $client;

		$url .= '&step=' . $step;
		if( $step > 2) {
			$url .= '&cur_cause=0';
		}
		if( $month != null) {
			$url .= '&month=' . $month;
		}
		if( $year != null) {
			$url .= '&year=' . $year;
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL,            $url);
//		curl_setopt( $ch, CURLOPT_REFERER,        $url_ref);
		curl_setopt( $ch, CURLOPT_USERAGENT,      $ua);
		curl_setopt( $ch, CURLOPT_COOKIEFILE,     $cookiefile);
		curl_setopt( $ch, CURLOPT_COOKIEJAR,      $cookiefile);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt( $ch, CURLOPT_NOBODY,         false);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true);

		if( $post != null) {
			$coded = array();
			foreach( $post as $key => $value) {
				$coded[] = $key . '=' . urlencode( $value);
			}
			$str = implode( '&', $coded);
			curl_setopt( $ch, CURLOPT_POST,       true);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $str);
		} else {
			curl_setopt( $ch, CURLOPT_POST,       false);
		}

		$ret = curl_exec( $ch);
		curl_close( $ch);

		return $ret;
	}

	function parseDate( $str)
	{
		$months = array(
			'Januar'    => 'January',
			'Februar'   => 'February',
			'März'      => 'March',
			'April'     => 'April',
			'Mai'       => 'May',
			'Juni'      => 'June',
			'Juli'      => 'July',
			'August'    => 'August',
			'September' => 'September',
			'Oktober'   => 'October',
			'November'  => 'November',
			'Dezember'  => 'December'
		);
		return strtotime( strtr( $str, $months));
	}

	function getFirstDate( $page)
	{
		$table = explode( 'table', $page);
		$cells = explode( '<td', $table[1]);

		for( $i = 1; $i < count( $cells); ++$i) {
			$cell = $cells[ $i];
			$href = explode( 'href=', $cell);
			if( count( $href) > 1) {
				$cell = substr( $cell, strpos( $cell, '>') + 1);
				$cell = substr( $cell, strpos( $cell, '</span>') + 7);
				$cell = strip_tags( $cell);

				return parseDate( $cell);
			}
		}

		return null;
	}
?>

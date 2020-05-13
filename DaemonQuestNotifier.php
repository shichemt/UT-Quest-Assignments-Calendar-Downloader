<?php
	require_once dirname(__FILE__).'/vendor/autoload.php';
	date_default_timezone_set('America/Chicago');
	
	

	// Pushbullet stuff
	define("PUSHBULLET_API_KEY", "");
	define("PUSHBULLET_CHANNEL", "");
	
  // Can be retrieved, once you log in, from a link that looks like this: https://quest.cns.utexas.edu/student/assignments/list?courseuser=XXXXXXXXX
	define("QCOURSE_ID", "");
  
  // UTEID & Password
	define("QLOGIN_ID", "");
	define("QPASSWORD", "");

  // Google API stuff
  define("CALENDAR_ID", "");
	define("GOOGLE_CLIENT_ID", "");
	define("GOOGLE_CLIENT_SECRET", "");
	define("GOOGLE_REDIR_URI", "");
	define("GOOGLE_ACCESS_TOKEN", "");
	define("GOOGLE_REFRESH_TOKEN", "");
	define("GOOGLE_APP_NAME", "");
	
	

	
	$pb = new Pushbullet\Pushbullet(PUSHBULLET_API_KEY);
	
	$client_id = GOOGLE_CLIENT_ID;
	$client_secret = GOOGLE_CLIENT_SECRET;
	$redirect_uri = GOOGLE_REDIR_URI;
	$credentials = array (
	"access_token" => GOOGLE_ACCESS_TOKEN, 
	"token_type" => "Bearer", 
	"expires_in" => "3600", 
	"refresh_token" => GOOGLE_REFRESH_TOKEN, 
	"created" => "1452223972"
	);
	
	$client = new Google_Client();
	$client->setApplicationName(GOOGLE_APP_NAME);
	$client->setClientId($client_id);
	$client->setClientSecret($client_secret);
	$client->setRedirectUri($redirect_uri);
	$client->setAccessType('offline');   // Gets us our refreshtoken
	$client->setScopes(array('https://www.googleapis.com/auth/calendar'));
	$client->setAccessToken($credentials);
	$service = new Google_Service_Calendar($client);
	
	
	// Functions
	
	function getAssignmentName ($str) {
		
		$name = "";
		if (preg_match("/\<td class=\"flushLeft\"\>(.*)<\/td>/iUs", $str, $matches)) {
			
			$name = strip_tags($matches[0]);
			
		}	
		
		return $name;
	}
	
	function getAssignmentDueDate($str) {
		$dueDate = "";
		
		if (preg_match("/\<td align=\"left\">(.*)<\/td>/iUs", $str, $matches)) {
			
			$dueDate = (preg_replace("/Due in <span class=\"student_when_due\">(.*)<\/span><br\/>/iUs", "", trim($matches[0])));
			
			
			$dueDate = trim(strip_tags($dueDate));
			
			$dueDate = str_ireplace(" at ", " ", $dueDate);
		}
		
		$dueDate = strtotime($dueDate); 
		
		return $dueDate;
	}
	
	function displayTime ($dueDate) {
		
		return date('g:iA', $dueDate)." on ".date('l F, jS, Y', $dueDate); 
	}
	
	function timeToGMT ($dueDate) {
		
		return str_replace('+00:00', '.000Z', gmdate('c', $dueDate));	
	}
	
	function getQuestAssignments() {
		
		fopen(dirname(__FILE__) ."/cookie.txt", "wb");
		
		

		
		// Step 1: Get login the page.
		
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_URL, "https://quest.cns.utexas.edu/dispatch/main/student");  
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0');
		curl_setopt($ch, CURLOPT_FILETIME, 1);  
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);  
		
		$loginPage = curl_exec( $ch );
		curl_close ( $ch );
		
		preg_match("/\<input type=\"hidden\" name=\"goto\" value=\"(.*)\" \/>/iUs", $loginPage, $gotoMatch);
		preg_match("/\<input type=\"hidden\" name=\"SunQueryParamsString\" value=\"(.*)\" \/>/iUs", $loginPage, $SunQueryParamsStringMatch);
		preg_match("/\<input type=\"hidden\" name=\"encoded\" value=\"(.*)\" \/>/iUs", $loginPage, $encodedMatch);
		
		$SunQueryParamsString = $SunQueryParamsStringMatch[1];
		$goto = $gotoMatch[1];
		$encoded = $encodedMatch[1];
		$gx_charset = "UTF-8";
		
		
		
		// Step 2: Login using my credentials 
		
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_URL, "https://login.utexas.edu/login/UI/Login");  
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE,  dirname(__FILE__) . '/cookie.txt');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
		curl_setopt($ch, CURLOPT_POST, 6);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "IDToken1=".QLOGIN_ID."&IDToken2=".QPASSWORD."&Login.Submit=Log+In&SunQueryParamsString=".$SunQueryParamsString."&goto=".$goto."&encoded=".$encoded."&gx_charset=".$gx_charset."");
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0');
		curl_setopt($ch, CURLOPT_FILETIME, 1);  
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);  
		curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
		$signInPage1 = curl_exec( $ch );
		curl_close ( $ch );	
		unset($ch);
		
		
		
		// Step 3a: Get Cookie from file.
		
		$handle = fopen(dirname(__FILE__) ."/cookie.txt", "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				
				if (preg_match("/utlogin-prod.*/", $line, $matches)) {
					
					$cookieLoggedIn = (trim(str_replace("utlogin-prod", "", $matches[0])));
					break;
				}
				
			}
			
			fclose($handle);
		}
		
		
		
		// Step 3b: Get Assignments Page List
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
		curl_setopt($ch, CURLOPT_URL, "https://quest.cns.utexas.edu/student/assignments/list?courseuser=".QCOURSE_ID);  
		
		curl_setopt($ch, CURLOPT_COOKIE, 'utlogin-prod='.$cookieLoggedIn.'; path=/');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_FILETIME, 1);  
		curl_setopt($ch, CURLOPT_HEADER, 1);  
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);  
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);  
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		$assignmentsPage = curl_exec( $ch );
		curl_close ( $ch );
		
		$assignmentsList = array();
		$assignmentsName = array();
		$assignmentsDueDate = array();
		$assignmentsCounter = 0;
		
		if (preg_match_all("/<tr id=\"elementheader_(.*)\>(.*)<\/tr>/iUs", $assignmentsPage, $homeworks)) {
			
			foreach ($homeworks[0] as $homework) {
				
				$assignmentsName[$assignmentsCounter] = getAssignmentName($homework);
				$assignmentsDueDate[$assignmentsCounter] = getAssignmentDueDate($homework);
				
				$assignmentsCounter++;
			} 	
		}
		
		
		
		
		for ($outer = 0; $outer < count($assignmentsDueDate); $outer++) {
			
			for ($inner = 0; $inner < count($assignmentsDueDate); $inner++) {
				if ($assignmentsDueDate[$outer] > $assignmentsDueDate[$inner]) {
					
					
					$nameminValue	 = $assignmentsName[$outer];
					$ddminValue		 = $assignmentsDueDate[$outer];
					
					$assignmentsName[$outer] = $assignmentsName[$inner];
					$assignmentsDueDate[$outer] = $assignmentsDueDate[$inner];
					
					$assignmentsName[$inner] = $nameminValue;
					$assignmentsDueDate[$inner] = $ddminValue;
					
				}
			}
		}
		
		$assignmentsCounter = 0;
		
		for ($assignmentsCounter = 0;$assignmentsCounter<count($assignmentsDueDate);$assignmentsCounter++) {
			
			$assignmentsList[$assignmentsCounter]['title'] = trim($assignmentsName[$assignmentsCounter]);
			$assignmentsList[$assignmentsCounter]['dueDate'] = $assignmentsDueDate[$assignmentsCounter];
		}
		
		unlink(dirname(__FILE__) . "/cookie.txt");
		
		return $assignmentsList;
		
	}
	
	function getGoogleAssignments() {
		
		global $service;
		$optParams = array('singleEvents' => 'true', 'orderBy'=> 'startTime');
		$events = $service->events->listEvents(CALENDAR_ID, $optParams);
		
		$counter = 0;
		$assignmentsList = array();
		foreach ($events->getItems() as $event) {
			$assignmentsList[$counter]['id'] =  $event->getId();
			$assignmentsList[$counter]['title'] =  $event->getSummary();
			$assignmentsList[$counter]['endTime'] = strtotime($event->getEnd()->getDateTime());
			$counter++;
		}
		
		return $assignmentsList;
	}	
	
	function addEvent($eventTitle, $beginGMT, $endTimeGMT) {
		
		global $service;
		
		$event = new Google_Service_Calendar_Event();
		$event->setSummary($eventTitle);
		$start = new Google_Service_Calendar_EventDateTime();
		$start->setTimeZone("GMT");
		$start->setDateTime($beginGMT);
		
		$event->setStart($start);
		$end = new Google_Service_Calendar_EventDateTime();
		$end->setTimeZone("GMT");
		$end->setDateTime($endTimeGMT);
		
		$event->setEnd($end);
		$createdEvent = $service->events->insert(CALENDAR_ID, $event);
		
		return $createdEvent->getId();
	}
	
	
	$questSchedule = getQuestAssignments();
	$googleSchedule = getGoogleAssignments();
	
	
	
	
	foreach ($questSchedule as $qEvent) {
	
		$exist = false;
		
		foreach ($googleSchedule as $gEvent) {
			
			if ($qEvent['title'] == $gEvent['title']) {
				

				if ($qEvent['dueDate'] != $gEvent['endTime']) {
					
					
					
					// pushbullet to phone.
					$pb->channel(PUSHBULLET_CHANNEL)->pushNote("Physics Homework", "Good News! ".ucwords($qEvent['title'])." has been extended and it is now due at ".displayTime($qEvent['dueDate']));
		
					
					// delete event
					$service->events->delete(CALENDAR_ID, $gEvent['id']);
					// addEvent
					$startTime = timeToGMT(($qEvent['dueDate'])-(5*60));
					$endTime = timeToGMT((($qEvent['dueDate'])));
					addEvent($qEvent['title'],$startTime ,$endTime);
					
				}
				
				$exist = true;
			}

		}
		
		if (!$exist) {
		
			
		//addEvent
		$startTime = timeToGMT((($qEvent['dueDate'])-(3*60*60)));
		$endTime = timeToGMT(($qEvent['dueDate']));
		addEvent($qEvent['title'],$startTime ,$endTime);
		
		//pushbullet
		$pb->channel(PUSHBULLET_CHANNEL)->pushNote("Physics Homework", ucwords($qEvent['title'])." was added to Quest and it is due on ".displayTime($qEvent['dueDate']));
			
		}
		
	}
	
	
	
	
	
?>

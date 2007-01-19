<?php
include "lib/dbaccess.php";
require_once "layout/error.php";
require_once "layout/adminchecker.php";

$username = GetParam("username");
$rightname = GetParam("rightname");

$RightLevel = HasRight('Checker'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the suffcient <b>Checker</b> rights<br>";
	exit (0);
}

$scope = RightScope('Checker');

$lastaction = "";
switch (GetParam("action")) {
	case "logout" :
		Logout("main.php");
		exit (0);
		break;
	case "check" :
		// Load the Message list
		$ii = 0;
		$str = "select messages.*,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where messages.Status='ToCheck' and messages.WhenFirstRead='0000-00-00 00:00:00' and mSender.id=IdSender and mReceiver.id=IdReceiver";
		$qry = sql_query($str);
		$count = 0;
		while ($rr = mysql_fetch_object($qry)) {
			if (GetParam("IdMess_" . $ii) == $rr->id) { // If this message is in the list of checked message
				//				  echo "Approve_",$ii,"=",GetParam("Approve_".$ii),"<br>" ;
				$SpamChange = "";
				if (($rr->SpamInfo == "NotSpam") and (GetParam("Mark_Spam_" . $ii) == "on")) { // If it was not considered as spam, but checker say it is a spam
					$SpamChange = ",SpamInfo='SpamSayChecker'";
				}
				if (($rr->SpamInfo == "SpamBlkWord") and (GetParam("Mark_Spam_" . $ii) == "")) { // If it was t considered as spam, but checker say it is not
					$SpamChange = ",SpamInfo='NotSpam'";
				}
				if (GetParam("Approve_" . $ii) == "on") {
					$count++;
					$str = "update messages set IdChecker=" . $_SESSION['IdMember'] . ",Status='ToSend'" . $SpamChange . " where id=" . $rr->id;
					//						echo "str=$str","<br>" ;
					sql_query($str);

				}
			} // end of If this message is in the list of checked message
			$ii++;
		}
		$sResult = $count . " Message processed";
		if ($count > 0)
			LogStr($sResult, "checking"); // Log the number of checked message if any
		// end of Load the Message list

		break;
	case "update" :
		break;
}

$TMess = array ();

// Load the Message list
$str = "select messages.*,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where messages.Status='ToCheck' and messages.WhenFirstRead='0000-00-00 00:00:00' and mSender.id=IdSender and mReceiver.id=IdReceiver";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	//	  if not scope test continue ; // Skip not allowed rights  todo manage an eventual scope test
	array_push($TMess, $rr);
}
// end of Load the Message list

DisplayMessages($TMess, $sResult); // call the layout
?>
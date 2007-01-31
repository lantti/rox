<?php
include "lib/dbaccess.php";
require_once "layout/error.php";
require_once "prepare_profile_header.php";

// Return the crypting criteraia according of IsHidden_* field of a checkbox
function ShallICrypt($ss) {
	//  echo "GetParam(IsHidden_$ss)=",GetParam("IsHidden_".$ss),"<br>" ;
	if (GetParam("IsHidden_" . $ss) == "on")
		return ("crypted");
	else
		return ("not crypted");
} // end of ShallICrypt

// test if is logged, if not logged and forward to the current page
// exeption for the people at confirm signup state
if ((!IsLogged()) and (GetParam("action") != "confirmsignup") and (GetParam("action") != "update")) {
	Logout($_SERVER['PHP_SELF']);
	exit (0);
}

if (!isset ($_SESSION['IdMember'])) {
	$errcode = "ErrorMustBeIndentified";
	DisplayError(ww($errcode));
	exit (0);
}

// Find parameters
$IdMember = $_SESSION['IdMember'];


$CanTranslate=CanTranslate(GetParam("cid", $_SESSION['IdMember'])) ;
$ReadCrypted = "MemberReadCrypted"; // Usually member read crypted is used
if ((IsAdmin())or($CanTranslate)) { // admin or CanTranslate can alter other profiles 
	$IdMember = GetParam("cid", $_SESSION['IdMember']);
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
}

// Try to load groups and caracteristics where the member belong to
$str = "select membersgroups.id as id,membersgroups.Comment as Comment,groups.Name as Name from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=" . $IdMember;
$qry = sql_query($str);
$TGroups = array ();
while ($rr = mysql_fetch_object($qry)) {
	array_push($TGroups, $rr);
}

$profilewarning = ""; // No warning to display

switch (GetParam("action")) {
	case ww("TestThisEmail") :
		// Send a test mail
		$date=date("Y-m-d H:i:s") ;
		$subj = ww("TestThisEmailSubject", $_SYSHCVOL['SiteName']);
		$text = ww("TestThisEmailText", GetParam("Email")). "sent at ".$date;
		hvol_mail(GetParam("Email"), $subj, $text, "", $_SYSHCVOL['TestMail'], 0, "yes", "", "");
		$profilewarning = "Mail sent to " . GetParam("Email"). "<br>sent at ".$date;
		break;

	case "update" :

		$m = LoadRow("select * from members where id=" . $IdMember);
		MakeRevision($m->id, "members"); // create revision
		if (GetParam("HideBirthDate") == "on") {
			$HideBirthDate = "Yes";
		} else {
			$HideBirthDate = "No";
		}

		if (GetParam("HideGender") == "on") {
			$HideGender = "Yes";
		} else {
			$HideGender = "No";
		}

		// Analyse Restrictions list
		$TabRestrictions = mysql_get_set("members", "Restrictions");
		$max = count($TabRestrictions);
		$Restrictions = "";
		for ($ii = 0; $ii < $max; $ii++) {
			if (GetParam("check_" . $TabRestrictions[$ii]) == "on") {
				if ($Restrictions != "")
					$Restrictions .= ",";
				$Restrictions .= $TabRestrictions[$ii];
			}
		} // end of for $ii

		if (!is_numeric(GetParam(MaxGuest))) {
			$MaxGuest = 0;
			$profilewarning = ww("MaxGuestNumericOnly");
		} else {
			$MaxGuest = GetParam(MaxGuest);
		}

		$str = "update members set HideBirthDate='" . $HideBirthDate . "'";
		$str .= ",HideGender='" . $HideGender . "'";
		$str .= ",MotivationForHospitality=" . ReplaceInMTrad(GetParam(MotivationForHospitality), $m->MotivationForHospitality, $IdMember);
		$str .= ",ProfileSummary=" . ReplaceInMTrad(GetParam(ProfileSummary), $m->ProfileSummary, $IdMember);
		$str .= ",WebSite='" . GetParam("WebSite") . "'";
		$str .= ",Accomodation='" . GetParam(Accomodation) . "'";
		$str .= ",Organizations=" . ReplaceInMTrad(GetParam(Organizations), $m->Organizations, $IdMember);
		$str .= ",ILiveWith=" . ReplaceInMTrad(GetParam(ILiveWith), $m->ILiveWith, $IdMember);
		$str .= ",MaxGuest=" . $MaxGuest;
		$str .= ",MaxLenghtOfStay=" . ReplaceInMTrad(GetParam(MaxLenghtOfStay), $m->MaxLenghtOfStay, $IdMember);
		$str .= ",AdditionalAccomodationInfo=" . ReplaceInMTrad(GetParam(AdditionalAccomodationInfo), $m->AdditionalAccomodationInfo, $IdMember);
		$str .= ",Restrictions='" . $Restrictions . "'";
		$str .= ",OtherRestrictions=" . ReplaceInMTrad(GetParam(OtherRestrictions), $m->OtherRestrictions, $IdMember);
		
		if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data		
		    $str .= ",HomePhoneNumber=" . ReplaceInCrypted(GetParam(HomePhoneNumber), $m->HomePhoneNumber, $IdMember, ShallICrypt("HomePhoneNumber"));
			$str .= ",CellPhoneNumber=" . ReplaceInCrypted(GetParam(CellPhoneNumber), $m->CellPhoneNumber, $IdMember, ShallICrypt("CellPhoneNumber"));
			$str .= ",WorkPhoneNumber=" . ReplaceInCrypted(GetParam(WorkPhoneNumber), $m->WorkPhoneNumber, $IdMember, ShallICrypt("WorkPhoneNumber"));
			$str .= ",chat_SKYPE=" . ReplaceInCrypted(GetParam(chat_SKYPE), $m->chat_SKYPE, $IdMember, ShallICrypt("chat_SKYPE"));
			$str .= ",chat_MSN=" . ReplaceInCrypted(GetParam(chat_MSN), $m->chat_MSN, $IdMember, ShallICrypt("chat_MSN"));
			$str .= ",chat_AOL=" . ReplaceInCrypted(GetParam(chat_AOL), $m->chat_AOL, $IdMember, ShallICrypt("chat_AOL"));
			$str .= ",chat_YAHOO=" . ReplaceInCrypted(GetParam(chat_YAHOO), $m->chat_YAHOO, $IdMember, ShallICrypt("chat_YAHOO"));
			$str .= ",chat_ICQ=" . ReplaceInCrypted(GetParam(chat_ICQ), $m->chat_ICQ, $IdMember, ShallICrypt("chat_ICQ"));
			$str .= ",chat_Others=" . ReplaceInCrypted(GetParam(chat_Others), $m->chat_Others, $IdMember, ShallICrypt("chat_Others"));
		}

		$str .= " where id=" . $IdMember;
		sql_query($str);

		if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data		
		    // Only update hide/unhide for identity fields
		    ReplaceInCrypted(addslashes($ReadCrypted($m->FirstName)), $m->FirstName, $IdMember, ShallICrypt("FirstName"));
			ReplaceInCrypted(addslashes($ReadCrypted($m->SecondName)), $m->SecondName, $IdMember, ShallICrypt("SecondName"));
			ReplaceInCrypted(addslashes($ReadCrypted($m->LastName)), $m->LastName, $IdMember, ShallICrypt("LastName"));

			// if email has changed
			if (GetParam("Email") != $ReadCrypted($m->Email)) {
			   ReplaceInCrypted(GetParam("Email"), $m->Email, $IdMember, true);
			   LogStr("Email updated (previous was " . $ReadCrypted($m->Email) . ")", "Email Update");
			}
		}


		// updates groups
		$max = count($TGroups);
		for ($ii = 0; $ii < $max; $ii++) {
			$ss = addslashes($_POST["Group_" . $TGroups[$ii]->Name]);
			//				 echo "replace $ss<br> for \$TGroups[",$ii,"]->Comment=",$TGroups[$ii]->Comment," \$IdMember=",$IdMember,"<br> " ; continue ;

			$IdTrad = ReplaceInMTrad($ss, $TGroups[$ii]->Comment, $IdMember);
			//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>� ; ;
			if ($IdTrad != $TGroups[$ii]->Comment) {
				MakeRevision($TGroups[$ii]->id, "membersgroups"); // create revision
				sql_query("update membersgroups set Comment=" . $IdTrad . " where id=" . $TGroups[$ii]->id);
			}
		}

		// Process languages
		// first  the language the member knows
		$str = "select memberslanguageslevel.IdLanguage as IdLanguage,memberslanguageslevel.id as id,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=" . $IdMember . " and memberslanguageslevel.IdLanguage=languages.id";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$str = "update memberslanguageslevel set Level='" . GetParam("memberslanguageslevel_level_id_" . $rr->id) . "' where id=" . $rr->id;
			sql_query($str);
		}
		if (GetParam("memberslanguageslevel_newIdLanguage") != "") {
			$str = "insert into memberslanguageslevel (IdLanguage,Level,IdMember) values(" . GetParam("memberslanguageslevel_newIdLanguage") . ",'" . GetParam("memberslanguageslevel_newLevel") . $rr->id . "'," . $IdMember . ")";
			sql_query($str);
		}

		if ($IdMember == $_SESSION['IdMember'])
			LogStr("Profil update by member himself", "Profil update");
		else
			LogStr("update of another profil", "Profil update");
		break;
	case "logout" :
		Logout("main.php");
		exit (0);
}

$m = prepare_profile_header($IdMember," and (Status='Active' or Status='Pending')") ; // pending members can edit their profile 

// Load the language the member knows
$TLanguages = array ();
$str = "select memberslanguageslevel.IdLanguage as IdLanguage,memberslanguageslevel.id as id,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=" . $IdMember . " and memberslanguageslevel.IdLanguage=languages.id";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($TLanguages, $rr);
}
$m->TLanguages = $TLanguages;

// Load the language the member does'nt know
$m->TOtherLanguages = array ();
$str = "select languages.Name as Name,languages.id as id from languages where id not in (select IdLanguage from memberslanguageslevel where memberslanguageslevel.IdMember=" . $IdMember . ")";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($m->TOtherLanguages, $rr);
}


if ($m->Status == "Pending") {
	$profilewarning = ww("YouCanCompleteProfAndWait", $m->Username);
}
elseif ($m->Status != "Active") {
	$profilewarning = "WARNING the status of " . $m->Username . " is set to " . $m->Status;
}

$m->MyRestrictions = explode(",", $m->Restrictions);
$m->TabRestrictions = mysql_get_set("members", "Restrictions");
include "layout/editmyprofile.php";
DisplayEditMyProfile($m, $profilewarning, $TGroups,$CanTranslate);
?>

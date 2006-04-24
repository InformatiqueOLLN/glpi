<?php
/*
 * @version $Id$
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/
 
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

include ("_relpos.php");
$NEEDED_ITEMS=array("phone","infocom","contract","state","user","link","networking","document","tracking","reservation","computer");
include ($phproot . "/inc/includes.php");


if(isset($_GET)) $tab = $_GET;
if(empty($tab) && isset($_POST)) $tab = $_POST;
if(empty($tab["ID"])) $tab["ID"] = "";
if(!isset($tab["withtemplate"])) $tab["withtemplate"] = "";

$ph=new Phone();

if (isset($_POST["add"]))
{
	checkRight("phone","w");

	$newID=$ph->add($_POST);
	logEvent($newID, "phones", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][20]." ".$_POST["name"].".");
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["delete"]))
{
	checkRight("phone","w");

	if (!empty($tab["withtemplate"]))
		$ph->delete($tab,1);
	else $ph->delete($tab);

	logEvent($tab["ID"], "phones", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][22]);
	if(!empty($tab["withtemplate"])) 
		glpi_header($cfg_glpi["root_doc"]."/setup/setup-templates.php");
	 else 
	glpi_header($cfg_glpi["root_doc"]."/phones/");
}
else if (isset($_POST["restore"]))
{
	checkRight("phone","w");

	$ph->restore($_POST);
	logEvent($tab["ID"], "phones", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][23]);
	glpi_header($cfg_glpi["root_doc"]."/phones/");
}
else if (isset($tab["purge"]))
{
	checkRight("phone","w");

	$ph->delete($tab,1);
	logEvent($tab["ID"], "phones", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][24]);
	glpi_header($cfg_glpi["root_doc"]."/phones/");
}
else if (isset($_POST["update"]))
{
	checkRight("phone","w");

	$ph->update($_POST);
	logEvent($_POST["ID"], "phones", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][21]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["disconnect"]))
{
	checkRight("phone","w");

	Disconnect($tab["ID"]);
	logEvent(0, "phones", 5, "inventory", $_SESSION["glpiname"]." ".$lang["log"][27]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if(isset($tab["connect"])&&isset($tab["item"])&&$tab["item"]>0)
{

	checkRight("phone","w");

	Connect($_SERVER["PHP_SELF"],$tab["sID"],$tab["item"],PHONE_TYPE);
	logEvent($tab["sID"], "phones", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][26]);
	glpi_header($cfg_glpi["root_doc"]."/phones/phones-info-form.php?ID=".$tab["sID"]);


}
else
{
	checkRight("phone","r");
	
	if (!isset($_SESSION['glpi_onglet'])) $_SESSION['glpi_onglet']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_onglet']=$_GET['onglet'];
//		glpi_header($_SERVER['HTTP_REFERER']);
	}
	
	
	commonHeader($lang["title"][41],$_SERVER["PHP_SELF"]);
	
	if ($ph->getFromDB($tab["ID"]))
		$ph->showOnglets($_SERVER["PHP_SELF"]."?ID=".$tab["ID"], $tab["withtemplate"],$_SESSION['glpi_onglet'] );
		
	if (!empty($tab["withtemplate"])) {

		if (showPhoneForm($_SERVER["PHP_SELF"],$tab["ID"], $tab["withtemplate"])){
		if (!empty($tab["ID"])){

			switch($_SESSION['glpi_onglet']){
				case 4 :
					showInfocomForm($cfg_glpi["root_doc"]."/infocoms/infocoms-info-form.php",PHONE_TYPE,$tab["ID"],1,$tab["withtemplate"]);
					showContractAssociated(PHONE_TYPE,$tab["ID"],$tab["withtemplate"]);
					break;
				case 5 :
					showDocumentAssociated(PHONE_TYPE,$tab["ID"],$tab["withtemplate"]);
					break;
				
				default :
					if (!display_plugin_action(PHONE_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"])){
						showPorts($tab["ID"], PHONE_TYPE,$tab["withtemplate"]);
						if ($tab["withtemplate"]!=2)	showPortsAdd($tab["ID"],PHONE_TYPE);
					}

					break;
			}
		}
		}
		
	} else {

		if (haveRight("delete_ticket","1")&&isset($_POST["delete_inter"])&&!empty($_POST["todel"])){
			$job=new Job();
			foreach ($_POST["todel"] as $key => $val){
				if ($val==1) {
					$job->delete(array("ID"=>$key));
				}
			}
		}

		if (showPhoneForm($_SERVER["PHP_SELF"],$tab["ID"])){
			switch($_SESSION['glpi_onglet']){
				case -1:
					showConnect($_SERVER["PHP_SELF"],$tab["ID"],PHONE_TYPE);
					showPorts($tab["ID"], PHONE_TYPE,$tab["withtemplate"]);
					showPortsAdd($tab["ID"],PHONE_TYPE);
					showInfocomForm($cfg_glpi["root_doc"]."/infocoms/infocoms-info-form.php",PHONE_TYPE,$tab["ID"]);
					showContractAssociated(PHONE_TYPE,$tab["ID"]);
					showDocumentAssociated(PHONE_TYPE,$tab["ID"]);
					showJobListForItem($_SESSION["glpiname"],PHONE_TYPE,$tab["ID"]);
					showOldJobListForItem($_SESSION["glpiname"],PHONE_TYPE,$tab["ID"]);
					showLinkOnDevice(PHONE_TYPE,$tab["ID"]);
					display_plugin_action(PHONE_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"]);
					break;
				case 4 :
					showInfocomForm($cfg_glpi["root_doc"]."/infocoms/infocoms-info-form.php",PHONE_TYPE,$tab["ID"]);
					showContractAssociated(PHONE_TYPE,$tab["ID"]);
					break;
				case 5 :
					showDocumentAssociated(PHONE_TYPE,$tab["ID"]);
					break;
				case 6 :
					showJobListForItem($_SESSION["glpiname"],PHONE_TYPE,$tab["ID"]);
					showOldJobListForItem($_SESSION["glpiname"],PHONE_TYPE,$tab["ID"]);
					break;
				case 7 :
					showLinkOnDevice(PHONE_TYPE,$tab["ID"]);
					break;	
				case 10 :
					showNotesForm($_SERVER["PHP_SELF"],PHONE_TYPE,$tab["ID"]);
					break;	
				case 11 :
					showDeviceReservations($_SERVER["PHP_SELF"],PHONE_TYPE,$tab["ID"]);
					break;
				case 12 :
					showHistory(PHONE_TYPE,$tab["ID"]);
				break;		
				default :
					if (!display_plugin_action(PHONE_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"])){
						showConnect($_SERVER["PHP_SELF"],$tab["ID"],PHONE_TYPE);
						showPorts($tab["ID"], PHONE_TYPE,$tab["withtemplate"]);
						showPortsAdd($tab["ID"],PHONE_TYPE);
					}
					break;
			}
			
			
			
			
			
		}
	}
	commonFooter();
}


?>

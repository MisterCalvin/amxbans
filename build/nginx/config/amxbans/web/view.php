<?php

/* 	

	AMXBans v6.0
	
	Copyright 2009, 2010 by SeToY & |PJ|ShOrTy

	This file is part of AMXBans.

    AMXBans is free software, but it's licensed under the
	Creative Commons - Attribution-NonCommercial-ShareAlike 2.0

    AMXBans is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

    You should have received a copy of the cc-nC-SA along with AMXBans.  
	If not, see <http://creativecommons.org/licenses/by-nc-sa/2.0/>.

*/

// Start session
require_once("include/init_session.php");

# SourceQuery
require __DIR__ . '/include/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;

// Require basic site files
require_once("include/config.inc.php");
require_once("include/access.inc.php");
require_once("include/menu.inc.php");
require_once("include/functions.inc.php");
//require_once("include/logfunc.inc.php");
//require_once("include/sql.inc.php");

//fetch server_information
$resource2	= $mysql->query("SELECT * FROM ".$config->db_prefix."_serverinfo ORDER BY hostname ASC") or die ($mysql->error);

$server_array = array();
$addons_array = array();
$rules_array = array();
$anticheat_array = array();
$Rules = "";

while($result2 = $resource2->fetch_object()) {

	$split_address = explode (":", $result2->address);
	$ip = $split_address['0'];
	$port = $split_address['1'];

	$Query = new SourceQuery( );
	
	$Info    = [];
	$Rules   = [];
	$Players = [];
	$Exception = null;

	if($ip && $port) {

			try
			{
				$Query->Connect( $split_address['0'], $split_address['1'], 3, SourceQuery::GOLDSOURCE );
				
				$Info    = $Query->GetInfo( );
				$Players = $Query->GetPlayers( );
				$Rules   = $Query->GetRules( );

				//copy rules to rules array for template
			if(is_array($Rules)) {
				foreach($Rules as $k => $v){
					$rules_array[] =array("name"=>$k,"value"=>$v);
				}
			}
			//check if mappic exists
			if(file_exists("images/maps/". $Info['ModDir'] ."/" . $Info['Map'] . ".jpg")) {
				$mappic = $Info['Map'];
			} else {
				$mappic = "noimage";
			}
			
			//create addons array
			if(is_array($Rules)) {
				if(isset($Rules['amxmodx_version'])) $addons_array[]=array("name"=>"AmxModx","version"=>$Rules['amxmodx_version'],"url"=>"http://www.amxmodx.org");
				if(isset($Rules['amxbans_version'])) $addons_array[]=array("name"=>"AmxBans","version"=>$Rules['amxbans_version'],"url"=>"http://www.amxbans.de");
				if(isset($Rules['metamod_version'])) $addons_array[]=array("name"=>"MetaMod","version"=>$Rules['metamod_version'],"url"=>"http://www.metamod.org");
				if(isset($Rules['hltv_report'])) $addons_array[]=array("name"=>"HLTV Report","version"=>$Rules['hltv_report'],"url"=>"http://forums.alliedmods.net/showthread.php?t=66506");
				if(isset($Rules['atac_version'])) $addons_array[]=array("name"=>"ATAC","version"=>$Rules['atac_version'],"url"=>"http://forums.alliedmods.net/showthread.php?t=61572");
				
				//create anticheat array
				if(isset($Info['secure'])) $anticheat_array[]=array("name"=>"VAC","version"=>"2","url"=>"");
				if(isset($Rules['sbsrv_version'])) $anticheat_array[]=array("name"=>"Steambans","version"=>$Rules['sbsrv_version'],"url"=>"http://www.steambans.com");
				if(isset($Rules['hlg_version'])) $anticheat_array[]=array("name"=>"HLGuard","version"=>$Rules['hlg_version'],"url"=>"");
			}
			//main server info
			$server_info = array(
				"sid"			=> $result2->id,
				"type"			=> $Info['Protocol'], # Originally 'type'
				"version"		=> isset($Info['Version']) ? $Info['Version'] : "",
				"hostname"      => $Info['HostName'],
				"map"         	=> $Info['Map'],
				"mod"        	=> $Info['ModDir'],
				"game"         	=> $Info['ModDesc'],
				"appid"        	=> isset($Info['AppID']) ? $Info['AppID'] : "",
				"cur_players"	=> $Info['Players'],
				"max_players"	=> $Info['MaxPlayers'],
				"bot_players"	=> $Info['Bots'],
				"dedicated"		=> ($Info['Dedicated']=="d")?"Dedicated":"Listen",
				"os"			=> ($Info['Os']=="l")?"Linux":"Windows",
				"password"		=> $Info['Password'],
				"secure"		=> $Info['Secure'],
				"sversion"		=> isset($Info['sversion']) ? $Info['sversion'] : "",
				"timeleft"		=> isset($Rules['amx_timeleft']) ? $Rules['amx_timeleft'] : "00:00",
				"maxrounds"		=> isset($Rules['mp_maxrounds']) ? $Rules['mp_maxrounds'] : "0",
				"timelimit"		=> isset($Rules['mp_timelimit']) ? $Rules['mp_timelimit'] : "00",
				"nextmap"		=> isset($Rules['amx_nextmap']) ? $Rules['amx_nextmap'] : "",
				"friendlyfire"	=> isset($Rules['mp_friendlyfire']) ? $Rules['mp_friendlyfire'] : "",
				"address"		=> $result2->address,
				"mappic"		=> $mappic,
				"players"		=> ""
			);

			//get the players
			$player_array	= array();
			$int = $Info['Players'];
			for ($i=0; $i<$int; $i++) {
				$player = $Players[$i];
				$player['Name'] = html_safe($player['Name']);

				$player_info = array(
					"name"		=> $player['Name'],
					"frag"		=> $player['Frags'],
					"time"		=> $player['TimeF'],
					);

				$player_array[] = $player_info;
			}
			
			$server_info['players'] = $player_array;
			$server_array[] = $server_info;

			}
			catch( Exception $e )
			{
				$Exception = $e;
			}
			finally
			{
				$Query->Disconnect( );
			}
	} else {
		$server_info = array(
			"sid"			=> $result2->id,
			"type"			=> "",
			"version"		=> "",
			"hostname"    	=> $result2->hostname, 
			"map"         	=> "",
			"mod"        		=> $result2->gametype,
			"game"         	=> "",
			"appid"        	=> "",
			"cur_players"		=> "0", 
			"max_players"		=> "0",
			"bot_players"		=> "0",
			"dedicated"		=> "",
			"os"			=> "",
			"password"		=> "",
			"secure"		=> "",
			"sversion"		=> "",
			"timeleft"		=> "00:00",
			"maxrounds"		=> "0",
			"timelimit"		=> "00",
			"nextmap"		=> "",
			"friendlyfire"	=> "",
			"address"		=> $result2->address,
			"mappic"		=> "noimage",
			"players"		=> ""
		);
		$server_array[] = $server_info;
	}
	$Query->Disconnect( );
}
/*
 *
 * 		Stats
 *
 */
$stats['total']		= $mysql->query("SELECT bid FROM ".$config->db_prefix."_bans")->num_rows;
$stats['permanent']	= $mysql->query("SELECT bid FROM ".$config->db_prefix."_bans WHERE ban_length = 0")->num_rows;
$stats['active']	= $mysql->query("SELECT bid FROM ".$config->db_prefix."_bans WHERE ((ban_created+(ban_length*60)) > ".time()." OR ban_length = 0)")->num_rows;
$stats['temp']		= $stats['active'] - $stats['permanent'];
$stats['admins']	= $mysql->query("SELECT id FROM ".$config->db_prefix."_amxadmins")->num_rows;
$stats['servers']	= $mysql->query("SELECT id FROM ".$config->db_prefix."_serverinfo")->num_rows;
/*
 *
 * 		Latest Ban
 *
 */
// Fetch the latest active ban that has not been pruned
$lb = $mysql->query("SELECT player_id, player_nick, ban_reason, ban_created, ban_length, ban_type 
                     FROM " . $config->db_prefix . "_bans 
                     WHERE expired = 0 
                     ORDER BY ban_created DESC LIMIT 1") or die($mysql->error);

$lb = $lb->fetch_object();

if ($lb) {
    if ($lb->ban_length == 0) {
        $ban_length = 0;
    } else {
        $ban_length = ($lb->ban_created + ($lb->ban_length * 60));
    }
    if ($lb->ban_type == "SI") {
        $steamid = "SI";
    } else {
        $steamid = $lb->player_id;
    }

    $last_ban_arr = array(
        "steamid" => $steamid,
        "nickname" => html_safe(_substr($lb->player_nick, 15)),
        "reason" => html_safe(_substr($lb->ban_reason, 15)),
        "created" => $lb->ban_created,
        "length" => $ban_length,
        "time" => time()
    );
} else {
    $last_ban_arr = array(
        "steamid" => "",
        "nickname" => "",
        "reason" => "",
        "created" => "",
        "length" => 0,
        "time" => 0
    );
}



/*
 *
 * 		Template parsing
 *
 */

// Header
$title = "_TITLEVIEW";

// Section
$section = "live";

// Parsing
$smarty = new dynamicPage;

$smarty->assign("meta","");
$smarty->assign("title",$title);
$smarty->assign("section",$section);
$smarty->assign("version_web",$config->v_web);

$smarty->assign("true", true);

$smarty->assign("server",$server_array);
$smarty->assign("stats",$stats);
$smarty->assign("last_ban",$last_ban_arr);
$smarty->assign("addons",$addons_array);
$smarty->assign("rules",$Rules);
$smarty->assign("rules_array",$rules_array);
$smarty->assign("anticheat_array",$anticheat_array);
$smarty->assign("players", isset($player_array) ? $player_array : NULL);
$smarty->assign("empty_result",isset($empty_result) ? $empty_result : NULL);
$smarty->assign("error", false);

$smarty->assign("design", "");
// amxbans.css available in design? if not, take default one.
if(file_exists("templates/".$config->design."/amxbans.css")) {
	$smarty->assign("design",$config->design);
}
$smarty->assign("dir",$config->document_root);
$smarty->assign("this",$_SERVER['PHP_SELF']);
$smarty->assign("menu",$menu);
$smarty->assign("banner",$config->banner);
$smarty->assign("banner_url",$config->banner_url);

$smarty->display('main_header.tpl');
      echo "<script type=\"text/javascript\">
		function jumpMenu(selection, target)
		{
			var url = selection.options[selection.selectedIndex].value;
			
			if (url == \"\")
			{
				return false;
			}
			else
			{
				window.location = url;
			}
		}
	</script>";
$smarty->display('view.tpl');
$smarty->display('main_footer.tpl');

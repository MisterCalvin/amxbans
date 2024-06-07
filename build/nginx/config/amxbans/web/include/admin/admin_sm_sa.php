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

if(!$_SESSION["loggedin"]) {
    header("Location:index.php");
}

$admin_site = "sa";
$title2 = "_TITLESERVERADMINS";
$smsg = null;

$editadmins = array("sidname" => "", "sid" => 0);

$sid = isset($_POST["sid"]) ? (int)$_POST["sid"] : 0;

# SourceQuery
require __DIR__ . '/../SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;

// Default timeout value if not set
$SERVER_TIMEOUT = 3;

function rcon_send($command, $sid, $max_response_pages = 0) {
    // get server info
    global $config, $mysql, $SERVER_TIMEOUT;
    $resource = $mysql->query("SELECT address, rcon, timeout FROM " . $config->db_prefix . "_serverinfo WHERE id=" . $sid) or die ($mysql->error);
    $result = $resource->fetch_object();
    if ($result) {
        $server_address = explode(":", trim($result->address));
        $server_rcon = $result->rcon;
        $timeout = isset($result->timeout) ? $result->timeout : $SERVER_TIMEOUT;

        $server = new SourceQuery();
        try {
            // Ensure you're using the correct engine type for your server
            $server->Connect($server_address[0], $server_address[1], (int)$timeout, SourceQuery::GOLDSOURCE);

            $server->SetRconPassword($server_rcon);
            $response = $server->Rcon($command, $max_response_pages);

            if ($response !== null) {
                return trim($response);
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Handle connection errors
            error_log("RCON Error: " . $e->getMessage());
            return false;
        } finally {
            $server->Disconnect();
        }
    }
    return false;
}

if(isset($_POST["save"]) && $_SESSION["loggedin"]) {
    $aktiv = $_POST["aktiv_new"];
    $custom_flags = $_POST["custom_flags"];
    $use_static_bantime = $_POST["use_static_bantime"];
    $user_id = $_POST["hid_uid"];
    //delete all admins for this server
    $query = $mysql->query("DELETE FROM `".$config->db_prefix."_admins_servers` WHERE `server_id`=".$sid) or die ($mysql->error);
    //search for the new settings
    if(is_array($aktiv)) {
        foreach($aktiv as $k => $aid) {
            if((int)$aid) {
                $cflags = sql_safe(trim($custom_flags[$k]));
                $sban = sql_safe(trim($use_static_bantime[$k]));
                $uid = sql_safe(trim($user_id[$k]));
                //save the admin to the db
                $query = $mysql->query("INSERT INTO `".$config->db_prefix."_admins_servers` 
                            (`admin_id`,`server_id`,`custom_flags`,`use_static_bantime`) 
                            VALUES 
                            ('".(int)$uid."','".$sid."','".trim($cflags)."','".$sban."')
                            ") or die ($mysql->error);
            }
        }
    }
    $user_msg = '_SADMINSAVED';
    $smarty->assign("msg", $user_msg);
    log_to_db("Server Admin config", "Edited admins on server: ".sql_safe($_POST["sidname"]));

    // Reload admins on the server after saving
    $reload_response = rcon_send("amx_reloadadmins", $sid);
    if ($reload_response !== false) {
        $smsg = $reload_response;
    } else {
        $smsg = "Failed to reload admins.";
    }
    $smarty->assign("smsg", $smsg);
}

if(isset($_POST["admins_edit"]) && $_SESSION["loggedin"]) {
    $editadmins = array("sidname" => html_safe($_POST["sidname"]), "sid" => $sid);

    $admins = sql_get_amxadmins_server($sid);
    $smarty->assign("admins", $admins);
}

// Get servers
$servers = sql_get_server();

$smarty->assign("editadmins", $editadmins);
$delay_choose = array(1, 2, 5, 10);
$yesno_choose = array("yes", "no");
$yesno_output = array("_YES", "_NO");
$onetwo_choose = array(1, 0);
$smarty->assign("onetwo_choose", $onetwo_choose);
$smarty->assign("delay_choose", $delay_choose);
$smarty->assign("yesno_choose", $yesno_choose);
$smarty->assign("yesno_output", $yesno_output);
$smarty->assign("servers", $servers);
?>

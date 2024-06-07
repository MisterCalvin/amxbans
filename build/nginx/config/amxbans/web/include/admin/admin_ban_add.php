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
    
if (!$_SESSION["loggedin"]) {
    header("Location:index.php");
    exit;
}

$admin_site = "ban_add";
$title2 = "_TITLEBANADD";

$inputs = array("name" => '', "steamid" => '', "ip" => '', "reason" => '', "reason_custom" => 0, "length" => 0, "type" => '');
$reason_custom = 0;

$set_id_query = $mysql->query("SELECT id FROM `".$config->db_prefix."_reasons_set` WHERE `default_set`=1 LIMIT 1") or die ($mysql->error);
$set_id_result = $set_id_query->fetch_object();
$set_id = isset($set_id_result->id) ? (int)$set_id_result->id : 0;

if ($set_id == 0) {
    $set_id = 1;
}

$ban_length = isset($_POST["ban_length"]) ? (int)$_POST["ban_length"] : 0;

// Save ban
if (isset($_POST["save"]) && $_SESSION["loggedin"]) {
    // Determine the reason to use
    $reason = isset($_POST["user_reason"]) && !empty(trim($_POST["user_reason"])) ? sql_safe(trim($_POST["user_reason"])) : sql_safe(trim($_POST["ban_reason"]));

    if (isset($_POST["perm"]) && $_POST["perm"] == "yes") {
        $ban_length = 0;
    } else {
        $ban_length = isset($_POST["ban_length"]) ? (int)$_POST["ban_length"] : 0;
    }
    if ($ban_length < 0) $ban_length = 0;

    $ban_type = isset($_POST["ban_type"]) ? sql_safe(trim($_POST["ban_type"])) : '';
    $name = sql_safe(trim($_POST["name"]));
    $steamid = sql_safe(trim($_POST["steamid"]));
    $ip = sql_safe(trim($_POST["ip"]));

    // Validate the input
    if ($steamid && !preg_match("/^STEAM_0:(0|1):[0-9]{1,10}$/", $steamid)) $user_msg = "_STEAMIDINVALID";
    if ($ip && !preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)) $user_msg = "_IPINVALID";
    if (!$name) $user_msg = "_NOBANNAME";
    if (!$steamid && $ban_type == "S") $user_msg = "_NOBANSTEAMID";
    if (!$ip && $ban_type == "SI") $user_msg = "_NOIP";

    // Check if an active ban exists
    if (!$user_msg) {
        $current_time = time();
        $query = $mysql->query("SELECT * FROM `" . $config->db_prefix . "_bans` WHERE "
            . (($steamid) ? "`player_id`='" . $steamid . "'" : "")
            . (($steamid && $ip) ? " AND " : "")
            . (($ip) ? "`player_ip`='" . $ip . "'" : "")
            . " AND (`ban_length` = 0 OR (`ban_created` + `ban_length` * 60) > $current_time)"
            . " AND `expired` = 0");
        if ($query->num_rows) $user_msg = "_ACTIVBANEXISTS";
    }

    // Add the ban
    if (!$user_msg) {
        $query = $mysql->query("INSERT INTO `" . $config->db_prefix . "_bans` 
                (`player_ip`, `player_id`, `player_nick`, `admin_nick`, `admin_id`, `ban_type`, `ban_reason`, `ban_created`, `ban_length`, `server_name`) 
                VALUES 
                ('$ip', '$steamid', '$name', '{$_SESSION["uname"]}', '{$_SESSION["uname"]}', '$ban_type', '$reason', UNIX_TIMESTAMP(), '$ban_length', 'website')")
            or die($mysql->error);
        $user_msg = '_BANADDSUCCESS';
        log_to_db("Add ban", "playernick: $name / time: $ban_length");
    } else {
        $inputs = array("name" => $name, "steamid" => $steamid, "ip" => $ip, "reason" => $reason, "reason_custom" => $reason_custom, "length" => $ban_length, "type" => $ban_type);
    }
}


$smarty->assign("inputs", $inputs);

// Get reasons
$reasons = sql_get_reasons_list($set_id);
$smarty->assign("reasons", $reasons);

// Fetch reasons with static_bantime
$reasons_with_times = sql_get_reasons($set_id); // Fetch reasons with static_bantime
$smarty->assign("reasons_with_times", json_encode($reasons_with_times, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS));

$banby_output = array("Steamid", "Steamid & IP");
$banby_values = array("S", "SI");
$smarty->assign("banby_output", $banby_output);
$smarty->assign("banby_values", $banby_values);

?>
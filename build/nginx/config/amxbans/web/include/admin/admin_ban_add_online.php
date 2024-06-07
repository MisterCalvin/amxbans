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

# SourceQuery
require __DIR__ . '/../SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;

require_once("include/steam.inc.php");

if (!$_SESSION["loggedin"]) {
    header("Location:index.php");
    exit;
}

$admin_site = "ban_add_online";
$title2 = "_TITLEBANADDONLINE";
$smsg = "";
$user_msg = "";
$server_msg = "";
$playerscount = 0;
$count = 0;
$players = array();

if (isset($_POST["server"])) {
    $sid = (int)$_POST["server"];
    $_SESSION['last_sid'] = $sid; // Store sid in session
} elseif (isset($_SESSION['last_sid'])) {
    $sid = $_SESSION['last_sid']; // Retrieve sid from session
} else {
    $sid = 0;
}

if (!isset($SERVER_TIMEOUT)) {
    $SERVER_TIMEOUT = 3; // Default timeout duration in seconds
}

$set_id_query = $mysql->query("SELECT id FROM `".$config->db_prefix."_reasons_set` WHERE `default_set`=1 LIMIT 1") or die ($mysql->error);
$set_id_result = $set_id_query->fetch_object();
$set_id = isset($set_id_result->id) ? (int)$set_id_result->id : 0;

if ($set_id == 0) {
    $set_id = 1;
}

// Get servers
$resource = $mysql->query("SELECT * FROM " . $config->db_prefix . "_serverinfo ORDER BY hostname ASC") or die($mysql->error);
$servers_array = array();

while ($result = $resource->fetch_object()) {
    $servers_list[] = $result->id;
    $key = array_keys($servers_list);
    $count = count($key);

    $Query = new SourceQuery();
    $server_address = explode(":", trim($result->address));

    if (isset($server_address[0]) && isset($server_address[1])) {
        try {
            $Query->Connect($server_address[0], (int)$server_address[1], $SERVER_TIMEOUT, SourceQuery::GOLDSOURCE);
            
            if (isset($result->rcon) && !empty($result->rcon)) {
                $Query->SetRconPassword($result->rcon);
                $Info = $Query->GetInfo();

                for ($i = 0; $i < $count; $i++) {
                    $servers_info = array(
                        "id" => $key[$i],
                        "hostname" => $result->hostname,
                        "address" => $result->address,
                        "rcon" => $result->rcon,
                        "map" => $Info['Map'],
                        "mod" => $Info['ModDir'],
                        "os" => ($Info['Os'] == "l") ? "Linux" : "Windows",
                        "cur_players" => $Info['Players'],
                        "max_players" => $Info['MaxPlayers'],
                        "bot_players" => $Info['Bots'],
                        "is_online" => true
                    );
                }
                $servers_array[] = $servers_info;
            } else {
                // RCON password is null or empty, mark as offline
                throw new Exception("_RCONPASSWORDNULL");
            }
        } catch (xPaw\SourceQuery\Exception\InvalidPacketException $e) {
            // Mark the server as offline and set all data to N/A
            $servers_info = array(
                "id" => $result->id,
                "hostname" => $result->hostname,
                "address" => $result->address,
                "rcon" => $result->rcon,
                "map" => "Unknown",
                "mod" => "Unknown",
                "os" => "Unknown",
                "cur_players" => 0,
                "max_players" => 0,
                "bot_players" => 0,
                "is_online" => false
            );
            $servers_array[] = $servers_info;
        } catch (Exception $e) {
            // Handle the exception for RCON password
            $servers_info = array(
                "id" => $result->id,
                "hostname" => $result->hostname,
                "address" => $result->address,
                "rcon" => $result->rcon,
                "map" => "Unknown",
                "mod" => "Unknown",
                "os" => "Unknown",
                "cur_players" => 0,
                "max_players" => 0,
                "bot_players" => 0,
                "is_online" => false
            );
            $servers_array[] = $servers_info;
        } finally {
            $Query->Disconnect();
        }
    } else {
        // Handle case where server address is invalid
        $servers_info = array(
            "id" => $result->id,
            "hostname" => $result->hostname,
            "address" => $result->address,
            "rcon" => $result->rcon,
            "map" => "Unknown",
            "mod" => "Unknown",
            "os" => "Unknown",
            "cur_players" => 0,
            "max_players" => 0,
            "bot_players" => 0,
            "is_online" => false
        );
        $servers_array[] = $servers_info;
    }
}

if (count($servers_array) == 0) {
    $servers_array[] = array(
        "id" => 0,
        "hostname" => "",
        "address" => "",
        "rcon" => "",
        "map" => "Unknown",
        "mod" => "Unknown",
        "os" => "Unknown",
        "cur_players" => 0,
        "max_players" => 0,
        "bot_players" => 0,
        "is_online" => false
    );
}


// Address for $sid exists?
if (!isset($servers_array[$sid]["address"])) $sid = 0;
$hostname = $servers_array[$sid]["hostname"];
$smarty->assign("servers", $servers_array);
$smarty->assign("hostname", $hostname);
$smarty->assign("server_select", $sid);

// Get reasons
$reasons = sql_get_reasons_list($set_id);
$smarty->assign("reasons", $reasons);

// Fetch reasons with static_bantime
$reasons_with_times = sql_get_reasons($set_id);
$smarty->assign("reasons_with_times", json_encode($reasons_with_times, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS));

// Set bantypes
$banby_output = array("Steamid", "Steamid & IP");
$banby_values = array("S", "SI");
$smarty->assign("banby_output", $banby_output);
$smarty->assign("banby_values", $banby_values);

// Ban or kick players
if ((isset($_POST["ban_selected"]) || isset($_POST["kick_selected"])) && !empty($_POST['selected_players'])) {
    $banned_players = array(); // Array to store banned player names
    $kicked_players = array(); // Array to store kicked player names
    foreach ($_POST['selected_players'] as $player_info) {
        list($pl_name, $pl_uid, $pl_steamid, $pl_ip) = explode('|', $player_info);
        $pl_ban_reason = isset($_POST["ban_reason"]) ? sql_safe(trim($_POST["ban_reason"])) : '';
        $pl_user_reason = isset($_POST["user_reason"]) ? sql_safe(trim($_POST["user_reason"])) : '';
        $pl_ban_length = (int)$_POST["ban_length"];
        $pl_perm = (isset($_POST["perm"]) && $_POST["perm"] == "on") ? true : false;
        $pl_silent = isset($_POST["silent"]) ? true : false; // Read the silent ban checkbox value
        // Some var checks
        $steamid_valid = (preg_match("/^STEAM_0:(0|1):[0-9]{1,10}$/", $pl_steamid)) ? true : false;
        $ip_valid = (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $pl_ip)) ? true : false;
        $pl_reason = ($pl_user_reason) ? $pl_user_reason : $pl_ban_reason;
        if (!$pl_reason) $user_msg = "_NOREASON";

        // Ban a player
        if (isset($_POST["ban_selected"]) && $servers_array[$sid]["address"] != "" && !$user_msg) {
            // Get bantime
            $time = ($pl_perm) ? 0 : (($pl_ban_length >= 0) ? $pl_ban_length : 0);
            // Get and check the ban type
            $type = sql_safe(trim($_POST["ban_type"]));

            if (!$steamid_valid && $type == "S") $user_msg = "_STEAMIDINVALID";
            if (!$ip_valid && $type == "SI") $user_msg = "_IPINVALID";

            if (!$pl_silent) {
                $Query = new SourceQuery();
                $server_address = explode(":", trim($servers_array[$sid]["address"]));

                if (isset($server_address[0]) && isset($server_address[1])) {
                    try {
                        $Query->Connect($server_address[0], (int)$server_address[1], $SERVER_TIMEOUT, SourceQuery::GOLDSOURCE);
                        $Query->SetRconPassword($servers_array[$sid]["rcon"]);

                        // Send ban command with RCON
                        $cmd = "amx_ban " . $time . " #" .  $pl_uid . " \"" . $pl_reason . "\"";
                        $response = $Query->Rcon($cmd);

                        //if (substr($response, 1) != "") { // Removed because amx_ban is not returning a response and I need to debug it
                        if (substr($response, 1) == "") {
                            $user_msg = '_ADDBANSUCCESSKICK';
                            $smsg = "Ban command executed successfully.";
                            $banned_players[] = $pl_name; // Add player name to the array
                            log_to_db("Add ban online", "nick: " . $pl_name . " <" . $pl_steamid . "><" . $pl_ip . "> banned for " . $pl_ban_length . " minutes");
                        } else {
                            $smsg = "RCON ban command failed with response: $response";
                            $server_msg = $servers_array[$sid]["address"] . "<br>" . substr($response, 1);
                        }
                    } catch (Exception $e) {
                        $smsg = $e->getMessage();
                    } finally {
                        $Query->Disconnect();
                    }
                } else {
                    $user_msg = "_SERVERINVALID";
                }
            } else {
                // Add the ban to the database if we issued a silent ban
                if (!$user_msg) {
                    $query = $mysql->query("INSERT INTO `" . $config->db_prefix . "_bans`
                            (`player_ip`, `player_id`, `player_nick`, `admin_nick`, `admin_id`, `ban_type`, `ban_reason`, `ban_created`, `ban_length`, `server_name`)
                            VALUES
                            ('" . $pl_ip . "', '" . $pl_steamid . "', '" . $pl_name . "', '" . $_SESSION["uname"] . "', '" . $_SESSION["uname"] . "', '" . $type . "', '" . $pl_reason . "', UNIX_TIMESTAMP(), '" . $pl_ban_length . "', 'website')")
                        or die($mysql->error);
                    $user_msg = '_BANADDSUCCESS';
                    $smsg = "Ban added to database successfully";
                    $banned_players[] = $pl_name; // Add player name to the array
                    log_to_db("Add ban online", "nick: " . $pl_name . " <" . $pl_steamid . "><" . $pl_ip . "> banned for " . $pl_ban_length . " minutes");
                }
            }
        }

        // Kick a player
        if (isset($_POST["kick_selected"]) && $servers_array[$sid]["address"] != "") {
            $server_address = explode(":", trim($servers_array[$sid]["address"]));

            $Query = new SourceQuery();

            if (isset($server_address[0]) && isset($server_address[1])) {
                try {
                    $Query->Connect($server_address[0], (int)$server_address[1], $SERVER_TIMEOUT, SourceQuery::GOLDSOURCE);
                    $Query->SetRconPassword($servers_array[$sid]["rcon"]);
                    $cmd = "kick #" . $pl_uid . " \"" . $pl_reason . "\"";
                    $response = $Query->Rcon($cmd);

                    if (substr($response, 1) != "") {
                        $user_msg = "_PLAYERKICKED";
                        $kicked_players[] = $pl_name; // Add player name to the array
                        log_to_db("Kick online", "nick: " . $pl_name . " <" . $pl_steamid . "><" . $pl_ip . "> kicked");
                    } else {
                        $smsg = "Kick command failed with response: $response";
                        $server_msg = $servers_array[$sid]["address"] . "<br>" . substr($response, 1);
                    }
                } catch (Exception $e) {
                    $smsg = $e->getMessage();
                } finally {
                    $Query->Disconnect();
                }
            } else {
                $user_msg = "_SERVERINVALID";
            }
        }
    }

    // Display banned player names if there are any
    if (!empty($banned_players)) {
        $smsg = "Players banned successfully: " . implode(", ", $banned_players);
    }

    // Display kicked player names if there are any
    if (!empty($kicked_players)) {
        $smsg = "Players kicked successfully: " . implode(", ", $kicked_players);
    }
}

if ($servers_array[$sid]["mod"]) {
    $server_address = explode(":", trim($servers_array[$sid]["address"]));
    $Query = new SourceQuery();

    if (isset($server_address[0]) && isset($server_address[1])) {
        try {
            $Query->Connect($server_address[0], (int)$server_address[1], $SERVER_TIMEOUT, SourceQuery::GOLDSOURCE);
            
            if (isset($servers_array[$sid]["rcon"]) && !empty($servers_array[$sid]["rcon"])) {
                $Query->SetRconPassword($servers_array[$sid]["rcon"]);
                $response = $Query->Rcon('amx_list');

                // Explode packet and get infos
                $re = explode("\x0A", $response);

                // There is a response from amxmodx plugin
                if (strlen($response)) {
                    if ($re[0] != "Bad rcon_password.") {
                        foreach ($re as $k => $v) {
                            $pl = explode("\xFC", $v);
                            if (!is_array($pl)) break;
                            switch ($pl[4]) {
                                case 0:
                                    $statusname = "_PLAYER";
                                    break;
                                case 1:
                                    $statusname = "_BOT";
                                    break;
                                case 2:
                                    $statusname = "_HLTV";
                                    break;
                                default:
                                    $statusname = "_UNKNOWN";
                                    break;
                            }

                            if (!empty($pl[2])) {
                                $steamid = html_safe($pl[2]);
                                $steamcomid = GetFriendId($steamid);
                            }

                            $player = array(
                                "name" => htmlspecialchars($pl[0]),
                                "userid" => $pl[1],
                                "steamid" => $pl[2],
                                "ip" => $pl[3],
                                "status" => $pl[4],
                                "immunity" => $pl[5],
                                "statusname" => $statusname,
                                "steamcomid" => $steamcomid
                            );
                            $count++;
                            $players[] = $player;
                        }
                        $playerscount = $count;
                        $smarty->assign("players_sid", $sid);
                    }
                    $Query->Disconnect();
                } else {
                    $smsg = "_NOPLAYERS";
                }
            } else {
                $smsg = "_RCONPASSWORDNULL";
            }
        } catch (Exception $e) {
            $smsg = $e->getMessage();
        } finally {
            $Query->Disconnect();
        }
    } else {
        $smsg = "_SERVERINVALID";
    }
} else {
    $smsg = "_SERVEROFFLINE";
}


$smarty->assign("playerscount", $count);
$smarty->assign("players", $players);
$smarty->assign("smsg", $smsg);
$smarty->assign("user_msg", $user_msg);
$smarty->assign("server_msg", $server_msg);
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

require_once("include/init_session.php");
require_once("include/config.inc.php");
require_once("include/access.inc.php");
require_once("include/menu.inc.php");
require_once("include/steam.inc.php");
require_once("include/sql.inc.php");
require_once("include/logfunc.inc.php");
require_once("include/functions.inc.php");
require_once("include/thumbs.inc.php");

$title = "_TITLEBANLIST";
$smarty = new dynamicPage;
$user_site="";
if(isset($_GET["bid"]) && is_numeric($_GET["bid"])) {
    $_POST["bid"] = $_GET["bid"];
    $tmp = "bd";
}

if(isset($_POST["bid"])) {
    isset($_POST["details_x"])?$tmp="bd":""; //ban details
    if(file_exists("include/user/user_".$tmp.".php")) {
        $user_site=$tmp;
        include("include/user/user_".$tmp.".php");
    }
}

$ban_page = array(
    "current" => 0,
    "max_page" => 0,
    "per_page" => 0,
    "first_ban" => 0,
    "max_ban" => 0,
    "all_ban" => 0,
    "show_comments" => 0,
    "show_demos" => 0,
    "show_kicks" => 0
);

$set_id_query = $mysql->query("SELECT id FROM `".$config->db_prefix."_reasons_set` WHERE `default_set`=1 LIMIT 1") or die ($mysql->error);
$set_id_result = $set_id_query->fetch_object();
$set_id = isset($set_id_result->id) ? (int)$set_id_result->id : 0;

if ($set_id == 0) {
    $set_id = 1;
}

$ban_reason_old = isset($_POST['ban_reason_old']) ? sql_safe($_POST['ban_reason_old']) : '';
$ban_length_old = isset($_POST["ban_length_old"]) ? (int)$_POST["ban_length_old"] : 0;

if(!$user_site) {
    $page = 0;
    $query = $mysql->query("SELECT COUNT(bid) FROM `".$config->db_prefix."_bans` WHERE `expired`=0") or die ($mysql->error);
    $ban_count[0]=$query->fetch_row()[0];
    $query = $mysql->query("SELECT COUNT(bid) FROM `".$config->db_prefix."_bans`") or die ($mysql->error);
    $ban_count[1]=$query->fetch_row()[0];
    $ban_page_max=ceil($ban_count[0] / $config->bans_per_page);
    if(isset($_REQUEST["site"])) $page=(int)$_REQUEST["site"];
    if(isset($_REQUEST["siteback_x"])) $page=(int)$_REQUEST["site"];
    if(isset($_REQUEST["sitenext_x"])) $page=(int)$_REQUEST["site"];
    if(isset($_REQUEST["sitestart_x"])) $page=1;
    if(isset($_REQUEST["siteend_x"])) $page=$ban_page_max;
    $ban_page_curr=($page==0 || $page>$ban_page_max) ? 1:$page;
    $min=($config->bans_per_page * $ban_page_curr)-$config->bans_per_page;
    $ban_page["current"] = $ban_page_curr;
    $ban_page["max_page"] = ($ban_page_max)? $ban_page_max:1;
    $ban_page["per_page"] = $config->bans_per_page;
    $ban_page["first_ban"] = ($ban_count[0])? $min + 1:$min;
    $ban_page["max_ban"] = $ban_count[0];
    $ban_page["all_ban"] = $ban_count[1];

    $query  = $mysql->query("SELECT ba.*, se.gametype,se.timezone_fixx, aa.nickname FROM `".$config->db_prefix."_bans` AS ba
                            LEFT JOIN `".$config->db_prefix."_serverinfo` AS se ON ba.server_ip=se.address
                            LEFT JOIN `".$config->db_prefix."_amxadmins` AS aa ON (aa.steamid=ba.admin_nick OR aa.steamid=ba.admin_ip OR aa.steamid=ba.admin_id)
                            WHERE ba.expired=0 ORDER BY ban_created DESC LIMIT ".$min.",".$config->bans_per_page) or die($mysql->error);

    $ban_list = array();
    while($result = $query->fetch_object()) {
        if($result->expired==1) continue;
        $steamid="";
        $steamcomid="";
        if(!empty($result->player_id)) {
            $steamid = html_safe($result->player_id);
            $steamcomid = GetFriendId($steamid);
        }
        $ban_row=array(
            "bid"       => $result->bid,
            "player_ip"     => $result->player_ip,
            "player_id"     => $result->player_id,
            "player_comid"  => $steamcomid,
            "player_nick"   => html_safe($result->player_nick),
            "admin_ip"           => $result->admin_ip,
            "admin_id"           => $result->admin_id,
            "admin_nick"    => html_safe($result->admin_nick),
            "ban_type"           => $result->ban_type,
            "ban_reason"    => $result->ban_reason,
            "ban_created"   => ($result->ban_created + ($result->timezone_fixx * 60 * 60)),
            "ban_length"    => $result->ban_length,
            "ban_end"              => ($result->ban_created + ($result->ban_length * 60) + ($result->timezone_fixx * 60 * 60)),
            "server_ip"     => $result->server_ip,
            "server_name"   => html_safe($result->server_name),
            "expired" => $result->expired,
            "nickname" => ""
        );
        $query2 = $mysql->query("SELECT count(player_id) as ban_count FROM `".$config->db_prefix."_bans` WHERE player_id = '".$result->player_id."'") or die($mysql->error);
        while($result2 = $query2->fetch_object()) {
            $ban_row["bancount"] = $result2->ban_count;
        }
        $queryX = $mysql->query("SELECT count(player_id) as ban_count FROM `".$config->db_prefix."_bans` WHERE player_id = '".$result->player_id."' AND (ban_length > 5 OR ban_length = 0)") or die($mysql->error);
        while($resultX = $queryX->fetch_object()) {
            $tmp_bancount = $resultX->ban_count;
        }
        if($config->auto_prune=="1") {
            if($tmp_bancount >= $config->max_offences && $ban_row["ban_length"] >= "0" && !(strlen(strstr($ban_row["ban_reason"],$config->max_offences_reason))>0)) {
                $ban_row["ban_length"] = "0";
                $new_reason = $ban_row["ban_reason"] . ' (' .$config->max_offences_reason.')';
                $ban_row["ban_reason"] = $new_reason;
                $prune_query = $mysql->query("UPDATE `".$config->db_prefix."_bans` SET `expired`=0,`ban_length`=0,`ban_reason`='".$new_reason."' WHERE `bid`=".$result->bid);
                $prune_query = $mysql->query("INSERT INTO `".$config->db_prefix."_bans_edit` (`bid`,`edit_time`,`admin_nick`,`edit_reason`) VALUES (
                                            '".$result->bid."',UNIX_TIMESTAMP(NOW()),'amxbans','".$new_reason."')");
            }
            if($ban_row["ban_end"] < time() && $ban_row["ban_length"] != "0") {
                $prune_query = $mysql->query("UPDATE `".$config->db_prefix."_bans` SET `expired`=1 WHERE `bid`=".$ban_row["bid"]);
                $prune_query = $mysql->query("INSERT INTO `".$config->db_prefix."_bans_edit` (`bid`,`edit_time`,`admin_nick`,`edit_reason`) VALUES (
                                                '".$result->bid."','".$ban_row["ban_end"]."','amxbans','Bantime expired')");
            }
        }
        if($result->server_ip=="") {
            $ban_row["mod"]="html";
        } else {
            $ban_row["mod"]=($result->gametype=="" || $result->gametype=="website")?"html":$result->gametype;
            $ban_row["nickname"]=html_safe($result->nickname);
        }
        if($config->show_kick_count=="1") {
            $ban_row["kick_count"]=$result->ban_kicks;
            $ban_page["show_kicks"]=1;
        }
        if($config->show_demo_count=="1") {
            $ban_row["demo_count"]=sql_get_files_count($result->bid);
            $ban_page["show_demos"]=1;
        }
        if($config->show_comment_count=="1") {
            $ban_row["comment_count"]=sql_get_comments_count($result->bid);
            $ban_page["show_comments"]=1;
        }
        $ban_list[]=$ban_row;
    }
    $smarty->assign("ban_list",$ban_list);
    $smarty->assign("error", count($ban_list) ? false : true);
    $smarty->assign("ban_page",$ban_page);
}

if(isset($_POST["del_ban_x"]) && isset($_POST["bid"]) && $_SESSION["loggedin"]) {
    $query = $mysql->query("SELECT `id`,`demo_file` FROM `".$config->db_prefix."_files` WHERE `bid`=".$bid) or die ($mysql->error);
    while($result = $query->fetch_object()) {
        if(file_exists("include/files/".$result->demo_file)) {
            if(file_exists("include/files/".$result->demo_file."_thumb")) {
                unlink("include/files/".$result->demo_file."_thumb");
            }
            if(unlink("include/files/".$result->demo_file)) {
                $query2 = $mysql->query("DELETE FROM `".$config->db_prefix."_files` WHERE `id`=".$result->id." LIMIT 1") or die ($mysql->error);
            }
        }
    }
    $query = $mysql->query("DELETE FROM `".$config->db_prefix."_comments` WHERE `bid`=".$bid) or die ($mysql->error);
    $ban_row=sql_get_ban_details($bid);
    $query = $mysql->query("DELETE FROM `".$config->db_prefix."_bans` WHERE `bid`=".$bid." LIMIT 1") or die ($mysql->error);
    log_to_db("Ban edit","Deleted ban: ID ".$bid." (<".sql_safe($ban_row["player_nick"])."> <".sql_safe($ban_row["player_id"]).">)");
    if($query) { header("Location:index.php"); exit; }
}

if (isset($_POST['edit_ban']) && $_SESSION['loggedin']) {
    $bid = (int)$_POST['bid'];
    $player_nick = sql_safe($_POST['player_nick']);
    $player_id = sql_safe($_POST['player_id']);
    $player_ip = sql_safe($_POST['player_ip']);
    $ban_type = sql_safe($_POST['ban_type']);
    $ban_reason = isset($_POST['user_reason']) && !empty(trim($_POST['user_reason'])) ? sql_safe(trim($_POST['user_reason'])) : sql_safe(trim($_POST['ban_reason']));
    $ban_length = (int)$_POST['ban_length'];
    $edit_reason = sql_safe($_POST['edit_reason']);
    $unban = isset($_POST['unban']) ? 1 : 0;
    $ban_length_old = (int)$_POST['ban_length_old'];
    $ban_reason_old = isset($_POST['ban_reason_old']) ? sql_safe($_POST['ban_reason_old']) : '';

// Fetch the current reason from the database
$query = $mysql->query("SELECT ban_reason, ban_created FROM `".$config->db_prefix."_bans` WHERE `bid` = ".$bid);
$current_ban_reason_row = $query->fetch_assoc();
$current_ban_reason = isset($current_ban_reason_row['ban_reason']) ? $current_ban_reason_row['ban_reason'] : 'custom reason';
$ban_created = isset($current_ban_reason_row['ban_created']) ? (int)$current_ban_reason_row['ban_created'] : 0;

if ($unban) {
    $ban_length = -1;
    $expired = 1;
} else {
    $expired = ($ban_created + $ban_length * 60) < time() ? 1 : 0;
}

if (empty($edit_reason)) {
        $edit_reason_parts = [];
    
        if ($unban) {
            $edit_reason_parts[] = "_UNBANNEDUSER";
        } else {
            // Use current reason from the database if old reason is empty
            if ($ban_reason != $current_ban_reason) {
                $edit_reason_parts[] = "Changed ban reason from $current_ban_reason to $ban_reason";
            }
            if ($ban_length != $ban_length_old) {
                $edit_reason_parts[] = "Changed ban time from $ban_length_old to $ban_length";
            }
        }
    
        $edit_reason = implode("; ", $edit_reason_parts);
    }
    
    if ($edit_reason === '' && !$unban && $ban_reason == $ban_reason_old && $ban_length == $ban_length_old) {
        // No changes detected, skip the update
        header("Location: ban_list.php?site=".$_POST['site']);
        exit;
    }
    
    $query = $mysql->query("UPDATE `".$config->db_prefix."_bans` SET 
        `player_nick` = '".$player_nick."', 
        `player_id` = '".$player_id."', 
        `player_ip` = '".$player_ip."', 
        `ban_type` = '".$ban_type."', 
        `ban_reason` = '".$ban_reason."', 
        `ban_length` = ".$ban_length.",
        `expired` = ".$expired." 
        WHERE `bid` = ".$bid) or die($mysql->error);
    
        if ($query) {
        // Avoid duplicating the edit reason in the log
        $mysql->query("INSERT INTO `".$config->db_prefix."_bans_edit` 
                (`bid`, `edit_time`, `admin_nick`, `edit_reason`) VALUES 
                (".$bid.", UNIX_TIMESTAMP(NOW()), '".$_SESSION['uname']."', '".$edit_reason."')") or die($mysql->error);
        $user_msg = '_BANEDITED';
        } else {
        $user_msg = '_BANEDITFAILED';
        }
    
header("Location: ban_list.php?site=".$_POST['site']);
exit;
}

$reasons = sql_get_reasons_list($set_id);
$smarty->assign("reasons", $reasons);

$reasons_with_times = sql_get_reasons($set_id);
$smarty->assign("reasons_with_times", json_encode($reasons_with_times, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS));

$banby_output = array("Steamid", "Steamid & IP");
$banby_values = array("S", "SI");
$smarty->assign("banby_output", $banby_output);
$smarty->assign("banby_values", $banby_values);

$smarty->assign("meta","");
$smarty->assign("title",$title);
$smarty->assign("version_web",$config->v_web);
$smarty->assign("design", "");
if(file_exists("templates/".$config->design."/amxbans.css")) {
    $smarty->assign("design",$config->design);
}
$smarty->assign("dir",$config->document_root);
$smarty->assign("this",$_SERVER['PHP_SELF']);
$smarty->assign("vars",$vars);
$smarty->assign("smilies",$smilies);
$smarty->assign("bbcodes",$bbcodes);
$smarty->assign("menu",$menu);
$smarty->assign("banner",$config->banner);
$smarty->assign("banner_url",$config->banner_url);
$smarty->assign("pagenav", construct_vb_page_nav($ban_page['current'], $ban_page['max_page'], 3, array(10, 50, 100, 500, 1000)));
$smarty->assign("true", true);
$smarty->display('main_header.tpl');
if($user_site !== "") {
    $smarty->display("user_".$user_site.".tpl");
} elseif ($config->start_page == "" || $config->start_page == "index.php" || (!file_exists("./$config->start_page"))) {
    $smarty->display('ban_list.tpl');
} else {
    include($config->start_page);
    $start_tpl=str_replace(".php",".tpl",$config->start_page);
    $smarty->display($start_tpl);
}
$smarty->display('main_footer.tpl');
function construct_vb_page_nav($current, $total, $pagenavpages, $pagenavsarr)
{
    $result = array();
    if ($current > 1)
    {
        $result['prev'] = $current - 1;
    }
    else
    {
        $result['prev'] = false;
    }
    if ($current < $total)
    {
        $result['next'] = $current + 1;
    }
    else
    {
        $result['next'] = false;
    }
    $curpage = 0;
    $result['pages'] = array();
    $result['first'] = false;
    $result['last'] = false;
    while ($curpage++ < $total)
    {
        if (abs($curpage - $current) >= $pagenavpages && $pagenavpages != 0)
        {
            if ($curpage == 1)
            {
                $result['first'] = $curpage;
            }
            if ($curpage == $total)
            {
                $result['last'] = $curpage;
            }
            if (in_array(abs($curpage - $current), $pagenavsarr) && $curpage != 1 && $curpage != $total)
            {
                $result['pages'][] = array('number' => $curpage, 'current' => false);
            }
        }
        else
        {
            if ($curpage == $current)
            {
                $result['pages'][] = array('number' => $curpage, 'current' => true);
            }
            else
            {
                $result['pages'][] = array('number' => $curpage, 'current' => false);
            }
        }
    }
    return $result;
}
?>

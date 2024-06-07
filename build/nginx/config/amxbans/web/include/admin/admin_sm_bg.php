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
}

$admin_site = "bg";
$title2 = "_TITLEREASONS";

// Get the set ID
$set_id = isset($_POST["set_id"]) ? (int)$_POST["set_id"] : 0;

// Function to change the position of reasons
function change_reason_pos($rid, $pos, $new_pos) {
    global $mysql, $config;

    // Save the current reason position to a temp position
    $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons` SET `pos`=0 WHERE `id`=".$rid." LIMIT 1") or die ($mysql->error);

    if ($pos == $new_pos - 1 || $pos == $new_pos + 1) {
        // If moving up or down by one position
        $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons` SET `pos`=`pos`".(($new_pos < $pos) ? "+" : "-")."1 WHERE `pos`=".$new_pos." LIMIT 1") or die ($mysql->error);
    } else {
        // If moving by more than one position
        $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons` SET `pos`=`pos`".(($new_pos < $pos) ? "+" : "-")."1 WHERE `pos`".(($new_pos < $pos) ? "<" : ">").$pos." AND `pos`".(($new_pos < $pos) ? ">=" : "<=").$new_pos) or die ($mysql->error);
    }

    // Set the new position for the moved reason
    $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons` SET `pos`=".$new_pos." WHERE `id`=".$rid." LIMIT 1") or die ($mysql->error);
    
    // Reindex positions
    reindex_reasons();
}

$rid = isset($_POST["rid"]) ? (int)$_POST["rid"] : 0;
$pos = isset($_POST["pos"]) ? (int)$_POST["pos"] : 0;

// Move reason position up or down
if ((isset($_POST["pos_up_x"]) || isset($_POST["pos_dn_x"])) && $_SESSION["loggedin"]) {
    $new_pos = $pos;
    if (isset($_POST["pos_up_x"])) $new_pos--;
    if (isset($_POST["pos_dn_x"])) $new_pos++;
    change_reason_pos($rid, $pos, $new_pos);
    $user_msg = '_REASONSSETSAVED';
}

//new set
if (isset($_POST["newset"]) && $_SESSION["loggedin"]) {
    $setname = sql_safe($_POST["setname"]);
    if (!validate_value($setname, "name", $error, 1, 31, "REASONSET")) $user_msg = $error;
    if (!$user_msg) {
        $query = $mysql->query("INSERT INTO `".$config->db_prefix."_reasons_set` (`setname`) VALUES ('".$setname."')") or die ($mysql->error);
        $user_msg = '_REASONSETADDED';
        log_to_db("Reasons config", "Added Set: ".sql_safe($setname));
    }
}

// new reason
if (isset($_POST["newreason"]) && $_SESSION["loggedin"]) {
    $reason = sql_safe($_POST["reason"]);
    if (!validate_value($reason, "name", $error, 1, 99, "REASON")) $user_msg = $error;
    $time = (int)$_POST["static_bantime"];
    if (!$user_msg) {
        $max_pos_query = $mysql->query("SELECT IFNULL(MAX(pos), 0) + 1 AS new_pos FROM `".$config->db_prefix."_reasons`") or die ($mysql->error);
        $max_pos_result = $max_pos_query->fetch_object();
        $new_pos = $max_pos_result->new_pos;

        $query = $mysql->query("INSERT INTO `".$config->db_prefix."_reasons` (`reason`, `static_bantime`, `pos`) VALUES ('".$reason."', ".$time.", ".$new_pos.")") or die ($mysql->error);
        $user_msg = '_REASONADDED';
        log_to_db("Reasons config", "Added Reason: ".sql_safe($reason)." (".$time." min)");

        // Reindex positions
        reindex_reasons();
    }
}

//delete set
if (isset($_POST["delset"]) && $_SESSION["loggedin"]) {
    $rsid = isset($_POST["rsid"]) ? (int)$_POST["rsid"] : 0;
    
    if ($rsid > 0) {
        // Check if this is the default set
        $default_set_query = $mysql->query("SELECT `default_set` FROM `".$config->db_prefix."_reasons_set` WHERE `id`=".$rsid." LIMIT 1") or die ($mysql->error);
        $default_set_result = $default_set_query->fetch_object();
        $is_default_set = $default_set_result->default_set;

        // delete the set
        $query = $mysql->query("DELETE FROM `".$config->db_prefix."_reasons_set` WHERE `id`=".$rsid." LIMIT 1") or die ($mysql->error);
        
        // delete all reasons for set
        $query = $mysql->query("DELETE FROM `".$config->db_prefix."_reasons_to_set` WHERE `setid`=".$rsid) or die ($mysql->error);
        
        $user_msg = '_REASONSETDELETED';
        log_to_db("Reasons config", "Deleted set with ID: ".sql_safe($rsid));

        // If it was the default set, set a new default set
        if ($is_default_set) {
            $new_default_set_query = $mysql->query("SELECT `id`, `setname` FROM `".$config->db_prefix."_reasons_set` LIMIT 1") or die ($mysql->error);
            if ($new_default_set_query->num_rows > 0) {
                $new_default_set_result = $new_default_set_query->fetch_object();
                $new_default_set_id = $new_default_set_result->id;
                $new_default_set_name = $new_default_set_result->setname;

                $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons_set` SET `default_set`=1 WHERE `id`=".$new_default_set_id." LIMIT 1") or die ($mysql->error);
                $user_msg .= " " . sprintf('_NEWDEFAULTSET', $new_default_set_name);
                log_to_db("Reasons config", "New default set: ".sql_safe($new_default_set_name));
            }
        }
    } else {
        $user_msg = '_REASONSETNOTFOUND';
    }
}

//save set
if (isset($_POST["saveset"]) && $_SESSION["loggedin"]) {
    $rsid = isset($_POST["rsid"]) ? (int)$_POST["rsid"] : 0;
    $setname = sql_safe($_POST["setname"]);
    $default_set = isset($_POST["default_set"]) ? 1 : 0;

    if ($rsid > 0) {
        if (!validate_value($setname, "name", $error, 1, 31, "REASONSET")) {
            $user_msg = $error;
        }

        if (!$user_msg) {
            if ($default_set == 1) {
                // Unset previous default set
                $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons_set` SET `default_set`=0 WHERE `default_set`=1") or die ($mysql->error);
            }

            $query = $mysql->query("DELETE FROM `".$config->db_prefix."_reasons_to_set` WHERE `setid`=".$rsid) or die ($mysql->error);

            if (isset($_POST["aktiv"])) {
                foreach ($_POST["aktiv"] as $k => $v) {
                    $query = $mysql->query("INSERT INTO `".$config->db_prefix."_reasons_to_set` (`setid`, `reasonid`) VALUES (".$rsid.", ".$v.")") or die ($mysql->error);
                }
            }

            $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons_set` SET `setname`='".$setname."', `default_set`=".$default_set." WHERE `id`=".$rsid." LIMIT 1") or die ($mysql->error);
            $user_msg = '_REASONSSETSAVED';
            log_to_db("Reasons config", "Edited set: ".sql_safe($setname));
        }
    } else {
        $user_msg = '_REASONSETNOTFOUND';
    }
}

// del reason
if (isset($_POST["reasondel"]) && $_SESSION["loggedin"]) {
    $reason = html_safe($_POST["reason"]);
    $query = $mysql->query("DELETE FROM `".$config->db_prefix."_reasons` WHERE `id`=".$rid." LIMIT 1") or die ($mysql->error);
    $query = $mysql->query("DELETE FROM `".$config->db_prefix."_reasons_to_set` WHERE `reasonid`=".$rid) or die ($mysql->error);
    $user_msg = '_REASONDELETED';
    log_to_db("Reasons config", "Deleted reason: ".sql_safe($reason));

    // Reindex positions
    reindex_reasons();
}

//save reason
if (isset($_POST["reasonsave"]) && $_SESSION["loggedin"]) {
    $reason = sql_safe($_POST["reason"]);
    $time = (int)$_POST["static_bantime"];
    if (!is_numeric($time)) $user_msg = $error;
    if (!validate_value($reason, "name", $error, 1, 99, "REASON")) $user_msg = $error;
    if (!$user_msg) {
        $query = $mysql->query("UPDATE `".$config->db_prefix."_reasons` SET `reason`='".$reason."', `static_bantime`=".$time." WHERE `id`=".$rid." LIMIT 1") or die ($mysql->error);
        $user_msg = '_REASONSAVED';
        log_to_db("Reasons config", "Edited reason: ".sql_safe($reason)." (".$time." min)");
    }
}

//reason sets holen
$reasons_set = sql_get_reasons_set();
$smarty->assign("reasons_set", $reasons_set);

//reason holen
$reasons = sql_get_reasons($set_id);

$check_values = array("1", "0");
$check_output = array("Ja", "Nein");
$smarty->assign("check_values", $check_values);
$smarty->assign("check_output", $check_output);
$smarty->assign("reasons", $reasons);
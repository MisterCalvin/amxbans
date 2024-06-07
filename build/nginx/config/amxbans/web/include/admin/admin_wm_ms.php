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

$admin_site = "ms";
$title2 = "_TITLESITE";

//Designs suchen
$designs[""] = "default"; //name from default design
$d = opendir($config->templatedir);
while ($f = readdir($d)) {
    if ($f == "." || $f == "..") continue;
    if (is_dir($config->templatedir . "/" . $f)) {
        $prefix = explode("_", $f);
        if ($prefix[0] == "design") $designs[$f] = $f;
    }
}
closedir($d);

//Banner suchen
$banners[""] = "---";
$d = opendir($config->path_root . "/images/banner/");
while ($f = readdir($d)) {
    if ($f == "." || $f == ".." || is_dir($config->path_root . "/images/banner/" . $f)) continue;
    if (is_file($config->path_root . "/images/banner/" . $f) && $f != "index.php") {
        $banners[$f] = $f;
    }
}
closedir($d);

//Startseiten suchen
//$start_pages[""]="---";
$vorbidden = array("index.php", "login.php", "logout.php", "admin.php", "search.php", "setup.php", "motd.php");
$d = opendir($config->path_root . "/");
while ($f = readdir($d)) {
    if ($f == "." || $f == ".." || is_dir($config->path_root . "/" . $f)) continue;
    if (is_file($f) && !in_array($f, $vorbidden) && substr($f, -3, 3) == "php") {
        $start_pages[$f] = $f;
    }
}
closedir($d);

//Settings speichern
if (isset($_POST["save"]) && $_SESSION["loggedin"]) {
    $update_query = "`cookie`='" . sql_safe($_POST["cookie"]) . "'";
    $update_query .= ",`design`='" . (sql_safe($_POST["design"]) == "---" ? "" : sql_safe($_POST["design"])) . "'";
    $update_query .= ",`bans_per_page`=" . ((isset($_POST["bans_per_page"]) && is_numeric($_POST["bans_per_page"]) && $_POST["bans_per_page"] > 1) ? (int)$_POST["bans_per_page"] : 10);
    $update_query .= ",`banner`='" . (sql_safe($_POST["banner"]) == "---" ? "" : sql_safe($_POST["banner"])) . "'";
    $update_query .= ",`banner_url`='" . sql_safe(trim($_POST["banner_url"])) . "'";
    $update_query .= ",`default_lang`='" . sql_safe($_POST["language"]) . "'";
    $update_query .= ",`start_page`='" . sql_safe($_POST["start_page"]) . "'";
    $update_query .= ",`show_comment_count`=" . (int)$_POST["show_comment_count"];
    $update_query .= ",`show_demo_count`=" . (int)$_POST["show_demo_count"];
    $update_query .= ",`show_kick_count`=" . (int)$_POST["show_kick_count"];
    $update_query .= ",`use_demo`=" . (int)$_POST["use_demo"];
    $update_query .= ",`use_comment`=" . (int)$_POST["use_comment"];
    $update_query .= ",`demo_all`=" . (int)$_POST["demo_all"];
    $update_query .= ",`comment_all`=" . (int)$_POST["comment_all"];
    $update_query .= ",`use_capture`=" . (int)$_POST["use_capture"];
    $update_query .= ",`auto_prune`=" . (int)$_POST["auto_prune"];
    $update_query .= ",`max_offences`=" . ((isset($_POST["max_offences"]) && is_numeric($_POST["max_offences"]) && $_POST["max_offences"] > 1) ? (int)$_POST["max_offences"] : 10);
    $update_query .= ",`max_offences_reason`='" . (isset($_POST["max_offences_reason"]) ? sql_safe($_POST["max_offences_reason"]) : "max offences reached") . "'";
    $update_query .= ",`max_file_size`=" . (int)$_POST["max_file_size"];
    $update_query .= ",`file_type`='" . (sql_safe($_POST["file_type"])) . "'";
    $update_query .= ",`email_host`='" . sql_safe($_POST["email_host"]) . "'";
    $update_query .= ",`email_host_port`=" . (int)$_POST["email_host_port"];
    $update_query .= ",`email_username`='" . sql_safe($_POST["email_username"]) . "'";
    $update_query .= ",`email_password`='" . sql_safe($_POST["email_password"]) . "'";
    $update_query .= ",`email_from`='" . sql_safe($_POST["email_from"]) . "'";
    $update_query .= ",`email_from_name`='" . sql_safe($_POST["email_from_name"]) . "'";
    $update_query .= ",`email_security`='" . sql_safe($_POST["email_security"]) . "'";

    //save it to db
    $query = $mysql->query("UPDATE `" . $config->db_prefix . "_webconfig` SET " . $update_query . " WHERE `id`=1 LIMIT 1") or die($mysql->error);
    $user_msg = "_CONFIGSAVED";
    log_to_db("Websetting config", "Changed");

    //set language
    $_SESSION["lang"] = sql_safe($_POST["language"]);
}

// Test email functionality
if (isset($_POST["test_email"]) && $_SESSION["loggedin"]) {
    $test_email_recipient = sql_safe($_POST["test_email_recipient"]);

    // Fetch email settings
    $query = $mysql->query("SELECT `email_host`, `email_host_port`, `email_username`, `email_password`, `email_from`, `email_from_name`, `email_security` FROM `".$config->db_prefix."_webconfig` WHERE `id`=1") or die ($mysql->error);
    $email_settings = $query->fetch_assoc();

    // Load PHPMailer
    require 'include/PHPMailer/PHPMailer.php';
    require 'include/PHPMailer/SMTP.php';
    require 'include/PHPMailer/Exception.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer;
    $mail->isSMTP();
    $mail->Host = $email_settings['email_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $email_settings['email_username'];
    $mail->Password = $email_settings['email_password'];
    $mail->SMTPSecure = $email_settings['email_security'] == 'None' ? '' : strtolower($email_settings['email_security']);
    $mail->Port = $email_settings['email_host_port'];

    $mail->setFrom($email_settings['email_from'], $email_settings['email_from_name']);
    $mail->addAddress($test_email_recipient);

    $mail->Subject = 'Test Email from AMXBans';
    $mail->Body = 'This is a test email to verify the email configuration settings.';

    if (!$mail->send()) {
        $user_msg = 'Test email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    } else {
        $user_msg = 'Test email has been sent successfully.';
    }
}

//get and set websettings
$vars = sql_set_websettings();

// Fetch email settings from the database
$email_settings_query = $mysql->query("SELECT email_host, email_host_port, email_username, email_password, email_from, email_from_name, email_security FROM `" . $config->db_prefix . "_webconfig` WHERE `id`=1 LIMIT 1");
$email_settings = $email_settings_query->fetch_assoc();

$smarty->assign("yesno_select", array("_YES", "_NO"));
$smarty->assign("yesno_values", array(1, 0));
$smarty->assign("vars", $vars);
$smarty->assign("designs", $designs);
$smarty->assign("banners", $banners);
$smarty->assign("start_pages", $start_pages);
$smarty->assign("email_settings", $email_settings);
$smarty->assign("email_security_options", array("STARTTLS" => "_EMAILSTARTTLS", "SMTPS" => "_EMAILSMTPS", "None" => "_EMAILINSECURE"));
?>

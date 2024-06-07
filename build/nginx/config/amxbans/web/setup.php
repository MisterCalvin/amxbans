<?php

/*

    AMXBans v6.0
    
    Copyright 2009, 2010 by AMXBans.de

    This file is part of AMXBans.

    AMXBans is free software, but it's licensed under the
    Creative Commons - Attribution-NonCommercial-ShareAlike 2.0

    AMXBans is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

    You should have received a copy of the cc-nC-SA along with AMXBans.  
    If not, see <http://creativecommons.org/licenses/by-nc-sa/2.0/>.

*/

session_start();

// Check for existing config file
// if(file_exists("include/db.config.inc.php")) {
//     header("Location: index.php");
// }

require_once("install/functions.inc");
require_once("include/functions.inc.php");

$config = (object)array();
$config->v_web = "6.14.5";

// Installation are 6 sites
$sitenrall = 6;
$sitenr = isset($_POST["site"]) ? (int)$_POST["site"] : 1;
$msg = "";

if (isset($_POST['newlang']) && !empty($_POST['newlang'])) {
    $_SESSION['lang'] = $_POST['newlang'];
}

if (empty($_SESSION["lang"])) {
    $_SESSION["lang"] = "english";
}
$config->default_lang = $_SESSION["lang"];

// Use environment variables if set
if (isset($_POST['URL_PATH']) && !empty($_POST['URL_PATH'])) {
    $_SESSION['document_root'] = $_POST['URL_PATH'];
} elseif (!isset($_SESSION['document_root'])) {
    $_SESSION['document_root'] = getenv('URL_PATH') ? getenv('URL_PATH') : str_replace("/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]);
}

$config->document_root = $_SESSION['document_root'];

if (isset($_POST['path_root']) && !empty($_POST['path_root'])) {
    $_SESSION['path_root'] = $_POST['path_root'];
} elseif (!isset($_SESSION['path_root'])) {
    $_SESSION['path_root'] = getenv('PATH_ROOT') ? getenv('PATH_ROOT') : str_replace(DIRECTORY_SEPARATOR . basename(__FILE__), '', __FILE__);
}

$config->path_root = $_SESSION['path_root'];


$config->path_root = $_SESSION['path_root'];

if (isset($_POST['DB_HOST']) && !empty($_POST['DB_HOST'])) {
    $_SESSION['dbhost'] = $_POST['DB_HOST'];
} elseif (!isset($_SESSION['dbhost']) && getenv('DB_HOST')) {
    $_SESSION['dbhost'] = getenv('DB_HOST');
}

if (isset($_POST['DB_USER']) && !empty($_POST['DB_USER'])) {
    $_SESSION['dbuser'] = $_POST['DB_USER'];
} elseif (!isset($_SESSION['dbuser']) && getenv('DB_USER')) {
    $_SESSION['dbuser'] = getenv('DB_USER');
}

if (isset($_POST['DB_PASS']) && !empty($_POST['DB_PASS'])) {
    $_SESSION['dbpass'] = $_POST['DB_PASS'];
} elseif (!isset($_SESSION['dbpass']) && getenv('DB_PASS')) {
    $_SESSION['dbpass'] = getenv('DB_PASS');
}

if (isset($_POST['DB']) && !empty($_POST['DB'])) {
    $_SESSION['dbdb'] = $_POST['DB'];
} elseif (!isset($_SESSION['dbdb']) && getenv('DB')) {
    $_SESSION['dbdb'] = getenv('DB');
}

if (isset($_POST['DB_PREFIX']) && !empty($_POST['DB_PREFIX'])) {
    $_SESSION['dbprefix'] = $_POST['DB_PREFIX'];
} elseif (!isset($_SESSION['dbprefix']) && getenv('DB_PREFIX')) {
    $_SESSION['dbprefix'] = getenv('DB_PREFIX');
}

if (isset($_POST['ADMIN_USER']) && !empty($_POST['ADMIN_USER'])) {
    $_SESSION['adminuser'] = $_POST['ADMIN_USER'];
} elseif (!isset($_SESSION['adminuser']) && getenv('ADMIN_USER')) {
    $_SESSION['adminuser'] = getenv('ADMIN_USER');
}

if (isset($_POST['ADMIN_PASS']) && !empty($_POST['ADMIN_PASS'])) {
    $_SESSION['adminpass'] = $_POST['ADMIN_PASS'];
    $_SESSION['adminpass2'] = $_POST['ADMIN_PASS'];
} elseif (!isset($_SESSION['adminpass']) && getenv('ADMIN_PASS')) {
    $_SESSION['adminpass'] = getenv('ADMIN_PASS');
    $_SESSION['adminpass2'] = getenv('ADMIN_PASS');
}

if (isset($_POST['ADMIN_EMAIL']) && !empty($_POST['ADMIN_EMAIL'])) {
    $_SESSION['adminemail'] = $_POST['ADMIN_EMAIL'];
} elseif (!isset($_SESSION['adminemail']) && getenv('ADMIN_EMAIL')) {
    $_SESSION['adminemail'] = getenv('ADMIN_EMAIL');
}

if ($sitenr == 7 && isset($_POST["check7"])) {
    $sitenrall = 7;
}

// If all setup data is ok, unlock and open site 7
if (isset($_POST["check6"])) {
    $sitenrall = 7;
    $sitenr++;
}
if (isset($_POST["back"])) {
    $sitenr--;
}
if (isset($_POST["next"])) {
    $sitenr++;
}

if ($sitenr < 1 || $sitenr > $sitenrall) {
    $sitenr = 1;
}

/////////////// basic functions /////////////////

// Use session data for path_root and document_root to avoid overwriting
//$config->path_root = isset($_SESSION['path_root']) ? $_SESSION['path_root'] : str_replace(DIRECTORY_SEPARATOR . basename(__FILE__), '', __FILE__);
//$config->document_root = isset($_SESSION['document_root']) ? $_SESSION['document_root'] : str_replace("/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]);

$config->templatedir = $config->path_root . "/install";
$config->langfilesdir = $config->path_root . "/install/language/";
$config->default_lang = "english";
if (empty($_SESSION["lang"])) {
    $_SESSION["lang"] = "english";
}
// debug
if (!is__writable($config->path_root . "/include/smarty/templates_c/")) {
    echo '<div style="text-align: center; margin-top: 10%; color: #c04040;font-width=bold;font-size=18px;"><img src="images/warning.gif" /> <u>Directory include/smarty/templates_c is not writable or doesn\'t exist!  Create and/or set the permissions appropriately.</u></div>';
    exit;
}

$lang = array();
if ($handle = opendir($config->langfilesdir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && substr($file, 0, 4) == "lang") {
            $show_lang = explode(".", $file);
            if (!in_array($show_lang[1], $lang)) {
                $lang[] = $show_lang[1];
            }
        }
    }
    closedir($handle);
}
sort($lang);

/* Smarty settings */
define("SMARTY_DIR", $config->path_root . "/include/smarty/");

require_once(SMARTY_DIR . "Smarty.class.php");

class dynamicPage extends Smarty
{
    function __construct()
    {
        global $config;

        parent::__construct();

        $this->template_dir = $config->templatedir;
        $this->compile_dir  = SMARTY_DIR . "templates_c/";
        $this->config_dir   = SMARTY_DIR . "configs/";
        $this->cache_dir    = SMARTY_DIR . "cache/";
        $this->caching      = false;

        // For changing templates it's better "true", but slow down site load
        $this->force_compile = true;
        $this->caching = false;

        $this->assign("app_name", "dynamicPage");
    }
}
$smarty = new dynamicPage;

$smarty->assign("next", false);

if ($sitenr == 1) {
    $smarty->assign("next", true);
}

/////////////// site 2 server settings /////////////////
if ($sitenr == 2) {
    $php_settings = array(
        "display_errors"     => (ini_get('display_errors') == "") ? "off" : ini_get('display_errors'),
        "register_globals"   => (ini_get('register_globals') == 1 || ini_get('register_globals') == "on") ? "_ON" : "_OFF",
        "safe_mode"          => (ini_get('safe_mode') == 1 || ini_get('safe_mode') == "on") ? "_ON" : "_OFF",
        "post_max_size"      => ini_get('post_max_size') . " (" . return_bytes(ini_get('post_max_size')) . " bytes)",
        "upload_max_filesize" => ini_get('upload_max_filesize') . " (" . return_bytes(ini_get('upload_max_filesize')) . " bytes)",
        "max_execution_time" => ini_get('max_execution_time'),
        "version_php"        => phpversion(),
        "version_amxbans_web" => $config->v_web,
        "server_software"    => $_SERVER["SERVER_SOFTWARE"],
        "mysql_version"      => mysqli_get_client_info(),
        "bcmath"             => (extension_loaded('bcmath') == "1") ? "_YES" : "_NO",
        "gmp"                => (extension_loaded('gmp') == "1") ? "_YES" : "_NO"
    );
    $smarty->assign("next", true);
    $smarty->assign("checkvalue", "_REFRESH");
    $smarty->assign("php_settings", $php_settings);
}

/////////////// site 3 dirs /////////////////
if ($sitenr == 3) {

    $include_dir = is__writable($config->path_root . "/include/");
    $backup_dir = is__writable($config->path_root . "/include/backup/");
    $files_dir = is__writable($config->path_root . "/include/files/");
    $temp_dir = is__writable($config->path_root . "/temp/");
    $templates_c_dir = is__writable($config->path_root . "/include/smarty/templates_c/");
    $setupphp = is__writable($config->path_root . "/");

    $dirs = array(
        "document_root" => $config->document_root,
        "path_root"     => $config->path_root,
        "include"       => $include_dir,
        "files"         => $files_dir,
        "backup"        => $backup_dir,
        "temp"          => $temp_dir,
        "templates_c"   => $templates_c_dir,
        "setupphp"      => $setupphp
    );
    if ($include_dir && $files_dir && $temp_dir && $templates_c_dir && $backup_dir) $smarty->assign("next", true);
    $smarty->assign("checkvalue", "_RECHECK");
    $smarty->assign("dirs", $dirs);
}

/////////////// site 4 db /////////////////
$prefix_exists = false;

if ($sitenr == 4 && isset($_POST["check4"])) {
    $_SESSION["dbcheck"] = false;
    $dbhost = trim($_POST["dbhost"]);
    $dbuser = trim($_POST["dbuser"]);
    $dbpass = trim($_POST["dbpass"]);
    $dbdb = trim($_POST["dbdb"]);
    $dbprefix = trim($_POST["dbprefix"]);

    $_SESSION["dbhost"] = $dbhost;
    $_SESSION["dbuser"] = $dbuser;
    $_SESSION["dbpass"] = $dbpass;
    $_SESSION["dbdb"] = $dbdb;
    $_SESSION["dbprefix"] = $dbprefix;

    $smarty->assign("db", array($dbhost, $dbuser, $dbpass, $dbdb, $dbprefix));

    if ($dbhost == "" || $dbuser == "" || $dbdb == "" || $dbprefix == "") {
        $msg = "_NOREQUIREDFIELDS";
    }

    try {
        $mysql = new mysqli($dbhost, $dbuser, $dbpass, $dbdb);
        if ($mysql->connect_error) {
            throw new Exception($mysql->connect_error);
        }
    } catch (Exception $e) {
        $msg = "_CANTCONNECT";
        $mysql_error = $e->getMessage();
        $smarty->assign("mysql_error", $mysql_error);
    }

    if (!$msg) {
        $enc = @$mysql->set_charset('utf8');
    }

    // get user privileges
    if (!$msg) {
        $privileges = sql_get_privilege($mysql);
        $prev = [];
        $prev[] = array("name" => "SELECT", "value" => in_array("SELECT", $privileges));
        $prev[] = array("name" => "INSERT", "value" => in_array("INSERT", $privileges));
        $prev[] = array("name" => "UPDATE", "value" => in_array("UPDATE", $privileges));
        $prev[] = array("name" => "DELETE", "value" => in_array("DELETE", $privileges));
        $prev[] = array("name" => "CREATE", "value" => in_array("CREATE", $privileges));
        // search for all needed privileges
        foreach ($prev as $v) {
            if (!$v["value"]) {
                $msg = "_NOTALLPREVILEGES";
                break;
            }
        }
    }

    // check for existing tables
    if (!$msg) {
        // search for existing dbprefix
        if ($mysql->query("SHOW TABLES FROM `" . $dbdb . "` LIKE '" . $dbprefix . "\_%'")->num_rows) {
            $prefix_exists = true;
            // search for field "imported" in bans table, added since 6.0
            if (@$mysql->query("SHOW COLUMNS FROM `" . $dbprefix . "_bans` WHERE Field LIKE 'imported'")->num_rows) {
                $prefix_isnew = true;
            }
        }
    }

    $smarty->assign("prevs", $prev);

    if (!$msg) {
        if ($prefix_exists) {
            if ($prefix_isnew) {
                $msg = "_PREFIXEXISTSV6";
                $_SESSION["dbcheck"] = true;
                $smarty->assign("next", true);
            } else {
                $msg = "_PREFIXEXISTSV5";
            }
        } else {
            $msg = "_DBOK";
            $_SESSION["dbcheck"] = true;
            $smarty->assign("next", true);
        }
    }
}

if ($sitenr == 4) $smarty->assign("checkvalue", "_DBCHECK");

/////////////// site 5 admin /////////////////
if ($sitenr == 5 && isset($_POST["check5"])) {
    $_SESSION["admincheck"] = false;
    $adminuser = trim($_POST["adminuser"]);
    $adminpass = trim($_POST["adminpass"]);
    $adminpass2 = trim($_POST["adminpass2"]);
    $adminemail = trim($_POST["adminemail"]);

    $_SESSION["adminuser"] = $adminuser;
    $_SESSION["adminemail"] = $adminemail;
    $_SESSION["adminpass"] = $adminpass;
    $_SESSION["adminpass2"] = $adminpass2;

    $admin = array($adminuser, $adminemail);
    $smarty->assign("admin", $admin);
    $smarty->assign("adminpass", $adminpass);

    $validate = [];
    if (strlen($adminuser) < 4) $validate[] = "_USERTOSHORT";
    if (strlen($adminpass) < 4) $validate[] = "_PWTOSHORT";
    if ($adminpass != $adminpass2) $validate[] = "_PWNOCONFIRM";
    if (!preg_match("/^[a-zA-Z0-9-_.]{2,}@[a-zA-Z0-9-_.]{2,}.[a-zA-Z]{2,6}$/", $adminemail)) $validate[] = "_NOVALIDEMAIL";

    if (!$adminuser || !$adminpass || !$adminpass2 || !$adminemail) {
        $validate[] = "_NOREQUIREDFIELDS";
    }

    if (!$validate) {
        $_SESSION["admincheck"] = true;
        $msg = "_ADMINOK";
        $smarty->assign("next", true);
    } else {
        $_SESSION['validate'] = $validate;
        $smarty->assign("validate", $validate);
    }

    $smarty->assign("msg", $msg);
}

if ($sitenr == 5) {
    $smarty->assign("admin", isset($_SESSION['adminuser']) ? array($_SESSION['adminuser'], $_SESSION['adminemail']) : array('admin', 'admin@domain.com'));
    $smarty->assign("adminpass", isset($_SESSION['adminpass']) ? $_SESSION['adminpass'] : '');
    $smarty->assign("validate", isset($_SESSION['validate']) ? $_SESSION['validate'] : array());
    $smarty->assign("checkvalue", "_ADMINCHECK");
    $smarty->assign("next", isset($_SESSION["admincheck"]) && $_SESSION["admincheck"]);
}


/////////////// site 6 show data /////////////////
if ($sitenr == 6) $smarty->assign("checkvalue", "_STEP7");

/////////////// site 7 end /////////////////
if ($sitenr == 7 && $_SESSION["dbcheck"] == true && $_SESSION["admincheck"] == true && !isset($_POST["check7"])) {
    // Open connection to database again
    $mysql = new mysqli($_SESSION["dbhost"], $_SESSION["dbuser"], $_SESSION["dbpass"], $_SESSION["dbdb"]);
    if (mysqli_connect_errno()) $msg = "_CANTCONNECT";
    include("install/tables.inc");
    // create db structure
    foreach ($table_create as $k => $v) {
        $table = array("table" => $k, "success" => ($mysql->query("CREATE TABLE " . $k . " (" . $v . ") DEFAULT CHARSET=utf8") ? "_CREATED" : "_ALREADYEXISTS"));
        $tables[] = $table;
    }
    // get default data
    include("install/datas.inc");
    // create default data
    foreach ($data_create as $k => $v) {
        $data = array("data" => $k, "success" => ($mysql->query("INSERT INTO " . $k . " " . $v) ? "_INSERTED" : "_FAILED"));
        $datas[] = $data;
    }
    // create default websettings
    $websettings_create = array("data" => "_CREATEWEBSETTINGS", "success" => ($mysql->query($websettings_query) ? "_INSERTED" : "_FAILED"));
    // create default usermenu
    $usermenu_create = array("data" => "_CREATEUSERMENU", "success" => ($mysql->query($usermenu_query) ? "_INSERTED" : "_FAILED"));
    // create webadmin userlevel
    $webadmin_create[] = array("data" => "_CREATEUSERLEVEL", "success" => ($mysql->query($userlevel_query) ? "_INSERTED" : "_FAILED"));
    // create webadmin
    $webadmin_create[] = array("data" => "_CREATEWEBADMIN", "success" => ($mysql->query($webadmin_query) ? "_INSERTED" : "_FAILED"));
    // install default modules
    foreach ($modules_install as $k => $v) {
        $modul = array("name" => $k, "success" => ($mysql->query($v) ? "_INSERTED" : "_FAILED"));
        $modules[] = $modul;
    }

    // write db.config.inc.php
    $content = "<?php

    \$config->document_root = \"" . addslashes($_SESSION["document_root"]) . "\";
    \$config->path_root = \"" . addslashes($_SESSION["path_root"]) . "\";

    \$config->db_host = \"" . addslashes($_SESSION["dbhost"]) . "\";
    \$config->db_user = \"" . addslashes($_SESSION["dbuser"]) . "\";
    \$config->db_pass = \"" . addslashes($_SESSION["dbpass"]) . "\";
    \$config->db_db = \"" . addslashes($_SESSION["dbdb"]) . "\";
    \$config->db_prefix = \"" . addslashes($_SESSION["dbprefix"]) . "\";
    
?>";
    $msg = write_cfg_file($config->path_root . "/include/db.config.inc.php", $content);
    $smarty->assign("content", $content);
    // create first log ;-)
    $mysql->query($log_query);

    $smarty->assign("tables", $tables);
    $smarty->assign("datas", $datas);
    $smarty->assign("modules", $modules);
    $smarty->assign("usermenu_create", $usermenu_create);
    $smarty->assign("websettings_create", $websettings_create);
    $smarty->assign("webadmin_create", $webadmin_create);
    $smarty->assign("checkvalue", "_SETUPEND");
}
if ($sitenr == 7 && isset($_POST["check7"])) {
    // clear smarty cache
    //$smarty->clear_compiled_tpl(); // ?; ShadesBot
    $smarty->clearCompiledTemplate();
    // delete setup info
    //@unlink("setup.php");
    //@rmdir("install");
    header("Location: index.php");
    exit;
}

$config->path_root = $_SESSION['path_root'];
$config->document_root = $_SESSION['document_root'];
$config->db_host = $_SESSION['dbhost'];
$config->db_user = $_SESSION['dbuser'];
$config->db_pass = $_SESSION['dbpass'];
$config->db_db = $_SESSION['dbdb'];
$config->db_prefix = $_SESSION['dbprefix'];
$config->admin_user = $_SESSION['adminuser'];
$config->admin_pass = $_SESSION['adminpass'];
$config->admin_email = $_SESSION['adminemail'];

// Generate template
$smarty->assign("msg", $msg);
$smarty->assign("sitenr", $sitenr);
$smarty->assign("sitenrall", $sitenrall);
$smarty->assign("current_lang", $_SESSION["lang"]);
$smarty->assign("v_web", $config->v_web);

$smarty->assign("lang", $lang);
$smarty->assign("default_lang", $config->default_lang);
$smarty->assign("document_root", $config->document_root);

$smarty->display('setup.tpl');

?>

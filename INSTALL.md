> [!IMPORTANT]  
> If you intend to use an existing AMXBans database, you will need to import the new columns from [build/nginx/config/amxbans/amxbans_update.sql](build/nginx/config/amxbans/amxbans_update.sql) (be sure to create a backup of your database beforehand). Failure to do so will result in application errors when using certain pages. It is recommended you do a fresh install of AMXBans if you intend to use this version of the application.

# Installation
- [Web](#web)
- [Plugin](#plugin)

## Web
- [Docker](#docker) (recommended)
- [Manual](#manual)

## Docker
[https://docs.docker.com/engine/install/](https://docs.docker.com/engine/install/)

Once installed, execute the following command to get up and running:

`git https://github.com/MisterCalvin/amxbans.git && cd ./amxbans && docker compose pull && docker compose up -d`

> [!NOTE]
> You are not required to use the `amxbans-mariadb` container image in this repo if you have an existing MySQL database. This image is provided for convenience

Your web and database container should both be online within a few seconds, you can check the status of your containers with `docker ps`:

```
user@amxbans-docker:~/amxbans$ docker ps
CONTAINER ID   IMAGE                                COMMAND                  CREATED             STATUS                         PORTS                                         NAMES
dae78330ae47   amxbans-nginx:latest             "/bin/bash /usr/bin/…"   About a minute ago   Up About a minute (healthy)   0.0.0.0:80->80/tcp, :::80->80/tcp, 8080/tcp   amxbans
4308f93efde5   amxbans-mariadb:latest           "/run.sh"                About a minute ago   Up About a minute (healthy)     0.0.0.0:3306->3306/tcp, :::3306->3306/tcp     amxbans-db
```

### Useful Docker commands

- Check your container logs: `docker logs -f <your_container_name>`
- Copy files in or out of a container (e.g. adding new mod images, or replacing the `plugins/` with pre-built files): `docker cp ./desert_crisis.gif amxbans-nginx:/var/www/html/images/mods/dcrisis.gif`
- Exec into container: `docker exec -it --user nobody <your_container_name> bash`

## Manual Install
AMXBans requires a web server, PHP (the required PHP modules will be listed below), and a MySQL database. At the time of writing the application has been tested with: `NGINX 1.24.0`, `PHP 8.3.7`, and `MariaDB 8.3.7`. Copy [build/nginx/config/amxbans/web/](build/nginx/config/amxbans/web/) to your web server. Create a directory named `templates_c/` in your AMXBans web root, and ensure it has adequate write permissions for the user you are running your web server as. Next, navigate to `<Your-WebServer-IP>:<Your-WebServer-Port>` and complete the install process. After completing the install process, ensure the `install/` directory and `setup.php` have been deleted from the root directory (the application should do this for you automatically, but double-check). If you are having any issues with certain parts of the application (specifically, exporting bans or SQL backups) ensure the `include/files/`, and `include/backup/` directories has proper write permissions for your web server user.

### Required PHP modules:
| PHP Module  |
| ----------- |
| ctype       |	
| curl        |
| dom         |
| fileinfo    |
| fpm         |
| gd          |
| intl        |
| mbstring    |
| bcmath      |
| gmp         |
| mysqli      |
| opcache     |
| openssl     |
| phar        |
| zip         |
| session     |
| tokenizer   |
| pdo         |
| pdo_mysql   |

### AMXBans Example Permissions
```
sudo chown www-data:www-data /var/www/html/amxbans/include/{backup,files}
sudo chmod 755 /var/www/html/amxbans/include/{backup,files}
```

## Plugin

You will need [Metamod](http://metamod.org/) and [AMX Mod X](https://www.amxmodx.org/) installed on your server, see [here](https://wiki.alliedmods.net/Installing_AMX_Mod_X_Manually) for details on how to do so. This plugin was tested with [Metamod-P](https://metamod-p.sourceforge.net/) `v1.21p38`, and the latest major versions of AMX Mod X: `1.8.2`, `1.9 Build 5294`, `1.1.10 Build 5467`. You can find the server plugins in [build/nginx/config/amxbans/web/plugins/addons/amxmodx/](build/nginx/config/amxbans/web/plugins/addons/amxmodx/), or you can download them from the web interface via `Admin area -> System Information`. The `.sma` files inside the `scripting` directory will need to be compiled into `.amxx` files before they can be loaded onto the server. You can find the documentation on compiling AMX Mod X plugins [here](https://www.amxmodx.org/doc/index.html?page=source/plugins/plugins.htm#compiling), or you may choose to use the web compiler linked below.

> [!NOTE]
> The author of this repo is not affiliated with this web compiler

[https://amx.icegame.ro/amxx/webcompiler.php](https://amx.icegame.ro/amxx/webcompiler.php)

Once you have your plugins compiled, copy the `configs/` and `data/` directories into `<your_game>/addons/amxmodx/`, and copy the `.amxx` files to `<your_game>/addons/amxmodx/plugins/`. The `scripting/` directory is for plugin development and does not need to be installed. Once these files are installed, navigate to `<your_game>/addons/amxmodx/configs/`, and open `amxbans.cfg`. Make sure the following lines are set correctly:

```
// Your SQL database prefix
// NOTE: The Prefix defined in your sql.cfg is NOT used for AMXBans anymore!!!
amx_sql_prefix "amx"

// How AMXBans should handle the admins
// 0 = SQL, 1 = users.ini, 2 = load no admins
amxbans_use_admins_file 0

// AMXBans tries to get the address from the server automatically.
// You can set a different one here - ex. "<ip>:<port>"
amxbans_server_address "my-halflife-server-ip:server-port"
```

Once you're finished editing this file, open up `sql.cfg` and setup your database connection:

```
// SQL configuration file
// File location: $moddir/addons/amxmodx/configs/sql.cfg

// *NOTE* Linux users may encounter problems if they specify "localhost" instead of "127.0.0.1"
// We recommend using your server IP address instead of its name

// *NOTE* amx_sql_type specifies the DEFAULT database type which admin.sma will use.

amx_sql_host	"my-sql-database:database-port"
amx_sql_user	"myuser"
amx_sql_pass	"mypassword"
amx_sql_db		"amxbans"
amx_sql_table	"amxadmins"
amx_sql_type	"mysql"
amx_sql_timeout "60"
```

Next, open `modules.ini` and uncomment `mysql`:

```

;;;
; To enable a module, remove the semi-colon (;) in front of its name.
; If it's not here, simply add it its name, one per line.
; You don't need to write the _amxx part or the file extension.
;;;

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;; SQL Modules usually need to be enabled manually ;;
;; You can have any number on at a time.  Use      ;;
;;  amx_sql_type in sql.cfg to specify the default ;;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

mysql
;sqlite

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;; Put third party modules below here.              ;;
;; You can just list their names, without the _amxx ;;
;;  or file extension.                              ;;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;



;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;; These modules will be auto-detected and loaded   ;;
;;  as needed.  You do not need to enable them here ;;
;;  unless you have problems.                       ;;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

;fun
;engine
;fakemeta
;geoip
;sockets
;regex
;nvault
;hamsandwich
```

Next, open `plugins.ini` and add your AMXBans plugins:

> [!NOTE]
AMXBans `_core` and `_main` must always be the **FIRST** plugins loaded in your `plugins.ini`. You must also disable the built-in AMX Mod X Admin Base plugins

```
; AMX Mod X plugins

; AMXBans - Always load first
amxbans_core.amxx
amxbans_main.amxx

; Admin Base - Always one has to be activated
;admin.amxx		; admin base (required for any admin-related)
;admin_sql.amxx		; admin base - SQL version (comment admin.amxx)

; Basic
admincmd.amxx		; basic admin console commands
adminhelp.amxx		; help command for admin console commands
adminslots.amxx		; slot reservation
multilingual.amxx	; Multi-Lingual management

; Menus
menufront.amxx		; front-end for admin menus
cmdmenu.amxx		; command menu (speech, settings)
plmenu.amxx		; players menu (kick, ban, client cmds.)
;telemenu.amxx		; teleport menu (Fun Module required!)
mapsmenu.amxx		; maps menu (vote, changelevel)
pluginmenu.amxx		; Menus for commands/cvars organized by plugin

; Chat / Messages
adminchat.amxx		; console chat commands
antiflood.amxx		; prevent clients from chat-flooding the server
scrollmsg.amxx		; displays a scrolling message
imessage.amxx		; displays information messages
adminvote.amxx		; vote commands

; Map related
nextmap.amxx		; displays next map in mapcycle
mapchooser.amxx		; allows to vote for next map
timeleft.amxx		; displays time left on map

; Configuration
pausecfg.amxx		; allows to pause and unpause some plugins
statscfg.amxx		; allows to manage stats plugins via menu and commands
```

Finally, open `plugins-amxbans.ini` and uncomment any AMXBans specific plugins you wish to enable:

```
// In this file you can specify additional plugins for AMXBans
// Make sure you place plugins for AMXBans here and not in the global plugins.ini
// 

// Freezes the player after ban (between motd and kick)
// (restricts player from moving, jump, say / say_team and strips his weapons)
amxbans_freeze.amxx

// Displays a message when a flagged player joins
// Reason, Time left, etc
amxbans_flagged.amxx

// Used for troubleshooting various things within AMXBans
// Recommend not enabling unless you really need to and know what you are doing
;amxbans_assist.amxx
```

You can now start your server, if everything has been setup correctly your server console should say the following:

```
[AMXBans] is starting to execute amxbans.cfg 
[AMXBans] amxbans.cfg is fully executed 
L 05/31/2024 - 18:08:30: Started map "crossfire" (CRC "1997150937")
[AMXBans] Loaded 0 admins from database
L 05/31/2024 - 18:08:30: [amxbans_main.amxx] [AMXBans] AMXBans 6.13 is online
[AMXBans] No Reasons found
[AMXBans] No Reasons found in Database. Static reasons were loaded instead.
L 05/31/2024 - 18:08:30: [amxbans_main.amxx] [AMXBans] No Reasons found in Database. Static reasons were loaded instead.
amxx plugins
Currently loaded plugins:
       name                    version     author            file             status   
 [  1] AMXBans Core            6.13        YamiKaitou        amxbans_core.amxx  running  
 [  2] AMXBans Main            6.13        YamiKaitou        amxbans_main.amxx  running  
 [  3] Admin Commands          1.9.0.5294  AMXX Dev Team     admincmd.amxx    running  
 [  4] Admin Help              1.9.0.5294  AMXX Dev Team     adminhelp.amxx   running  
 [  5] Slots Reservation       1.9.0.5294  AMXX Dev Team     adminslots.amxx  running  
 [  6] Multi-Lingual System    1.9.0.5294  AMXX Dev Team     multilingual.amxx  running  
 [  7] Menus Front-End         1.9.0.5294  AMXX Dev Team     menufront.amxx   running  
 [  8] Commands Menu           1.9.0.5294  AMXX Dev Team     cmdmenu.amxx     running  
 [  9] Players Menu            1.9.0.5294  AMXX Dev Team     plmenu.amxx      running  
 [ 10] Teleport Menu           1.9.0.5294  AMXX Dev Team     telemenu.amxx    running  
 [ 11] Maps Menu               1.9.0.5294  AMXX Dev Team     mapsmenu.amxx    running  
 [ 12] Plugin Menu             1.9.0.5294  AMXX Dev Team     pluginmenu.amxx  running  
 [ 13] Admin Chat              1.9.0.5294  AMXX Dev Team     adminchat.amxx   running  
 [ 14] Anti Flood              1.9.0.5294  AMXX Dev Team     antiflood.amxx   running  
 [ 15] Scrolling Message       1.9.0.5294  AMXX Dev Team     scrollmsg.amxx   running  
 [ 16] Info. Messages          1.9.0.5294  AMXX Dev Team     imessage.amxx    running  
 [ 17] Admin Votes             1.9.0.5294  AMXX Dev Team     adminvote.amxx   running  
 [ 18] Pause Plugins           1.9.0.5294  AMXX Dev Team     pausecfg.amxx    running  
 [ 19] Stats Configuration     1.9.0.5294  AMXX Dev Team     statscfg.amxx    running    
 [ 21] AMXBans Flagged         6.13        YamiKaitou        amxbans_freeze.amxx  running  
 [ 22] AMXBans Flagged         6.13        YamiKaitou        amxbans_flagged.amxx  running  
 22 plugins, 22 running
 ```

 Your server-side setup is done! If you're having any issues you can set `amxbans_debug` to 1, 2, or 3 (with 1 being the least verbose and 3 being the most verbose) inside of `addons/amxmodx/configs/amxbans.cfg`. On the Web UI, be sure to set your servers RCON password on `Admin area -> Server -> Server -> <Your GoldSrc Server> -> RCON password`, and then assign admins via `Admin area -> Server -> Assign Admins -> <Your GoldSrc Server> -> Edit admins`.
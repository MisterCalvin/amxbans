## Changelog
### 2024/05/30

- Replaced `rcon_hl_net.inc` with [xPaw/SourceQuery](https://github.com/xPaw/PHP-Source-Query) for much faster and more robust queries / RCON commands
- Fixed crash when sorting User Menu on `Admin area -> Website -> User Menu`
- Remove individual Ban / Kick buttons beside each player on Add online ban page, replace with checkboxes and single Ban Selected or Kick Selected Players button at bottom of page
- Added Silent Ban checkbox to Add online ban page. When checked ban is only added to database and player will not be kicked if they are currently in server; ban will take effect when server changes levels / restarts / the player disconnects and reconnects
- Last viewed server is saved in PHP session and automatically selected when loading Add online ban page
- Server being viewed is highlighted in green on Add online ban page (same color as expired bans on `ban_list.php`)
- New columns added to Add online ban page: SteamID and IP Address (SteamID is converted to SteamID64 and hyperlinked to Steam community profile)
- Offline servers will no longer hang the application on `view.php` or Add online ban page. If a server is offline the View button will be disabled, and the background is highlighted red. Additionally, all server information columns are replaced with `N/A `(only on Add online ban page, server will be removed from list on `view.php` until it is back online)
- Add Server Query Timeout variable, customizeable under `Admin area -> Server -> <Your Goldsrc Server>` (this is the max time SourceQuery will wait before giving up when issuing queries or RCON commands to the server)
- Fixed miscellaenous errors on comment pages for `ban_list.php`
- `amx_reloadadmins` will be fired automatically when adding or editing Admins on `Admin area -> Server -> Assign Admins`
- Fixed "Latest Ban" sidebar on `view.php` to display latest ban from `ban_list.php` (was displaying lastest ban from database, but if we pruned expired bans at any point this ban would not be displayed on `ban_list.php`, even if it still exists in our database or can be searched)
- Added sorting buttons for `Admin area -> Server -> Ban Reasons`, similar to `Admin area -> Website -> User Menu`
- Fixed logic where saving changes to `Admin area -> Website -> User Levels` would log you out without warning if the level you are editing matched (it will now log out other users, but a display a message for the active user that they must log out and log back in for changes to take effect)
- Replace Sendmail with PHPMailer for sending user password emails. Email settings can be found on `Admin area -> Website -> Settings` at the bottom of the page (note: password resets via `Admin area -> Website -> Admins -> Change Password` will be mailed to the user in plain-text)
- Dynamically update Ban Length based on value of Static Ban Time for currently selected Ban Reason on Add ban and Add online ban pages. If Static Ban Time is 0 the "Permanent" checkbox will be checked automatically
- Fixed "Time Remaining" for bans with an expiration not displaying seconds on `ban_list.php`
- Fixed errors preventing SQL database from being backed up on `Admin area -> Modules -> Import/Export`
- Trackback link on User Ban Details will now automatically detect HTTP/HTTPS and update the link
- Added `Download Server Plugins` feature to `Admin area-> System Information` (underneath `Prune DB`). Will zip up `plugins/` in the root directory and serve it up for downloading (only for authenticated users)
- New language keys added, currently only `lang.english.php` has these keys, you will need to add them to your desired language .php file. The keys are:

```
// updated keys; ShadesBot
// ban list details
define("_CHANGEDBANTIME","Changed ban time from"); // Probably not using these
define("_CHANGEDBANREASON","Changed ban reason from"); // Probably not using these

// server
define("_TIMEOUT", "Server Query Timeout");

// live ban
define("_RCONPASSWORDNULL","RCON Password is NULL or empty!");
define("_SILENTBAN","Silent Ban (only adds ban to database)");
define("_BANSELECTED","Ban Selected Players");
define("_KICKSELECTED","Kick Selected Players");
define("_BANSELECTEDCONFIRM","Do you really want to ban these players?");
define("_KICKSELECTEDCONFIRM","Do you really want to kick these players?");
define("_NOSELECTEDPLAYERS","Please select players!");
define("_CANNOTBANBOTS","You cannot ban bots!");

// user level
define("_LEVELSAVEDLOGOUTREQUIRED", "Level saved. Please log out and log back in for the changes to take effect.");

//new design related
define("_DOWNLOADPLUGINS", "Download Server Plugins");
define("_SOURCEDOESNOTEXIST", "Plugins directory does not exist");
define("_DESTINATIONNOTWRITABLE", "Destination directory is not writable");
define("_ZIPCREATIONFAILED", "Failed to create zip file");

// reasons
define("_DEFAULTBANSET", "Default Reasons Set");

// settings
define("_EMAILSETTINGS", "Email Settings");
define("_EMAILHOST", "Host");
define("_EMAILHOSTPORT", "Port");
define("_EMAILSECURITY", "Security");
define("_EMAILSTARTTLS", "STARTTLS");
define("_EMAILSMTPS", "SMTPS");
define("_EMAILINSECURE", "None");
define("_EMAILUSERNAME", "Username");
define("_EMAILPASSWORD", "Password");
define("_EMAILFROM", "From Email");
define("_EMAILFROMNAME", "From Name");
define("_TESTEMAIL", "Test Email Settings");
define("_TESTEMAILRECIPIENT", "Test Email Recipient");
```

## Database Updates

If you are updating an old installation to this version of AMXBans, you will need to add the following columns to your database:

```
ALTER TABLE `amx_serverinfo` ADD COLUMN timeout INT NOT NULL DEFAULT 3;
ALTER TABLE `amx_reasons` ADD COLUMN `pos` INT NOT NULL DEFAULT 0;

ALTER TABLE `amx_webconfig`
ADD `email_host` VARCHAR(255) NOT NULL,
ADD `email_host_port` INT NOT NULL,
ADD `email_security` VARCHAR(10) DEFAULT 'STARTTLS',
ADD `email_username` VARCHAR(255) NOT NULL,
ADD `email_password` VARCHAR(255) NOT NULL,
ADD `email_from` VARCHAR(255) NOT NULL,
ADD `email_from_name` VARCHAR(255) NOT NULL;

ALTER TABLE `amx_reasons_set` ADD COLUMN `default_set` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `amx_reasons_to_set`
ADD COLUMN `active` TINYINT(1) DEFAULT 1;
```

Alternatively, you can download and import the new columns via [build/nginx/config/amxbans/amxbans_update.sql](build/nginx/config/amxbans/amxbans_update.sql) (be sure to create a backup of your database beforehand)

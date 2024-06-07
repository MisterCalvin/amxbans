NOTICE: PHP message: PHP Warning:  Undefined array key "ban_length" in /var/www/html/include/admin/admin_ban_add.php on line 50
2024/05/31 16:02:19 [error] 13#13: *4 FastCGI sent in stderr: "PHP message: PHP Warning:  Undefined array key "ban_length" in /var/www/html/include/admin/admin_ban_add.php on line 50" while reading response header from upstream, client: 10.10.0.233, server: localhost, request: "POST /admin.php?site=ban_add HTTP/1.1", upstream: "fastcgi://unix:/run/php-fpm.sock:", host: "files.sbotnas.home", referrer: "http://files.sbotnas.home/admin.php?site=ban_add"
10.10.0.233 - - [31/May/2024:16:02:19 +0000] "POST /admin.php?site=ban_add HTTP/1.1" 200 3561 "http://files.sbotnas.home/admin.php?site=ban_add" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36" "-" 0.013 0.013 . -


_SERVERINVALID on add_online_ban for fresh installs

NOTICE: PHP message: PHP Warning:  Undefined variable $ban_details_activ in /var/www/html/include/user/user_bd.php on line 368
NOTICE: PHP message: PHP Warning:  Undefined variable $ban_details_exp in /var/www/html/include/user/user_bd.php on line 369
NOTICE: PHP message: PHP Warning:  Undefined variable $ban_details_edits in /var/www/html/include/user/user_bd.php on line 370
NOTICE: PHP message: PHP Warning:  Undefined variable $edit_count in /var/www/html/include/user/user_bd.php on line 371
NOTICE: PHP message: PHP Warning:  Undefined variable $activ_count in /var/www/html/include/user/user_bd.php on line 372
NOTICE: PHP message: PHP Warning:  Undefined variable $exp_count in /var/www/html/include/user/user_bd.php on line 373
NOTICE: PHP message: PHP Warning:  Undefined array key "player_comid" in /var/www/html/include/smarty/templates_c/73d7b8c55098d0162772ee6f79b474532af2f628_0.file.user_bd.tpl.php on line 83
2024/05/31 16:03:57 [error] 13#13: *4 FastCGI sent in stderr: "PHP message: PHP Warning:  Undefined variable $ban_details_activ in /var/www/html/include/user/user_bd.php on line 368; PHP message: PHP Warning:  Undefined variable $ban_details_exp in /var/www/html/include/user/user_bd.php on line 369; PHP message: PHP Warning:  Undefined variable $ban_details_edits in /var/www/html/include/user/user_bd.php on line 370; PHP message: PHP Warning:  Undefined variable $edit_count in /var/www/html/include/user/user_bd.php on line 371; PHP message: PHP Warning:  Undefined variable $activ_count in /var/www/html/include/user/user_bd.php on line 372; PHP message: PHP Warning:  Undefined variable $exp_count in /var/www/html/include/user/user_bd.php on line 373; PHP message: PHP Warning:  Undefined array key "player_comid" in /var/www/html/include/smarty/templates_c/73d7b8c55098d0162772ee6f79b474532af2f628_0.file.user_bd.tpl.php on line 83" while reading response header from upstream, client: 10.10.0.233, server: localhost, request: "POST /ban_list.php HTTP/1.1", upstream: "fastcgi://unix:/run/php-fpm.sock:", host: "files.sbotnas.home", referrer: "http://files.sbotnas.home/ban_list.php"

Steam Community ID also says N/A so we broke that at some point (only inside of user_bd.php, it works on our ban_list.php)

Our edit messages are not longer being shown? Looks like they are still added to the database correctly.

Timezone not working in container (well, it is, but shows as UTC despite having $TZ set)

File uploading for comments flat out does not work

Modifying a ban removes it from ban_list.php (still in database, for some reason we're setting expired to 1 in our db - possibly due to missing variables in user_bd)

setup.php -> Don't think we fix our issue with saving changes to URL PATH (document_root), path_root seems to save correctly but if we enter an invalid path we get a 500 error on the next screen (our setup wizard tries to find all of our install files in the document_root)

What is the difference between document_root and URL PATH? This is a tad confusing.

2024/05/31 14:58:35 [warn] 13#13: *219 a client request body is buffered to a temporary file /tmp/client_temp/0000000001, client: 10.10.0.233, server: localhost, request: "POST /ban_list.php?bid=1 HTTP/1.1", host: "files.sbotnas.home", referrer: "http://files.sbotnas.home/ban_list.php?bid=1" (when unauth user uploads a file)
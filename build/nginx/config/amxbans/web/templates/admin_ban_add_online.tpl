<div id="navigation">
    <div id="main-nav">
        <ul class="tabbed">
            <li id="header_1" onclick="ToggleMenu_open('1');"><a href="#">{"_ADMINAREA"|lang}</a></li>
            <li id="header_2" onclick="ToggleMenu_open('2');"><a href="#">{"_SERVER"|lang}</a></li>
            <li id="header_3" onclick="ToggleMenu_open('3');"><a href="#">{"_WEB"|lang}</a></li>
            <li id="header_4" onclick="ToggleMenu_open('4');"><a href="#">{"_OTHER"|lang}</a></li>
            <li id="header_5" onclick="ToggleMenu_open('5');"><a href="#">{"_MODULES"|lang}</a></li>
        </ul>
        <div class="clearer">&nbsp;"></div>
    </div>

    <div id="sub-nav">
        <div id="menu_1" style="display: block;">
            <ul class="tabbed">
                <li><a href="admin.php">{"_MENUINFO"|lang}</a></li>
                <li><a href="admin.php?site=ban_add">{"_ADDBAN"|lang}</a></li>
                <li><a href="admin.php?site=ban_add_online">{"_ADDBANONLINE"|lang}</a></li>
            </ul>
        </div>
        <div id="menu_2" style="display: none;">
            <ul class="tabbed">
                <li><a href="admin.php?site=sm_sv">{"_SERVER"|lang}</a></li>
                <li><a href="admin.php?site=sm_bg">{"_MENUREASONS"|lang}</a></li>
                <li><a href="admin.php?site=sm_av">{"_ADMINS"|lang}</a></li>
                <li><a href="admin.php?site=sm_sa">{"_TITLEADMIN"|lang}</a></li>
            </ul>
        </div>
        <div id="menu_3" style="display: none;">
            <ul class="tabbed">
                <li><a href="admin.php?site=wm_wa">{"_ADMINS"|lang}</a></li>
                <li><a href="admin.php?site=wm_ul">{"_PERM"|lang}</a></li>
                <li><a href="admin.php?site=wm_um">{"_MENUUSERMENU"|lang}</a></li>
                <li><a href="admin.php?site=wm_ms">{"_SETTINGS"|lang}</a></li>
            </ul>
        </div>
        <div id="menu_4" style="display: none;">
            <ul class="tabbed">
                <li><a href="admin.php?site=so_mo">{"_MODULES"|lang}</a></li>
                <li><a href="admin.php?site=so_up">{"_MENUUPDATE"|lang}</a></li>
                <li><a href="admin.php?site=so_lg">{"_MENULOGS"|lang}</a></li>
            </ul>
        </div>
        <div id="menu_5" style="display: none;">
            <ul class="tabbed">
                <li><a href="admin.php?modul=iexport">{"_MENUIMPORTEXPORT"|lang}</a></li>
            </ul>
        </div>
        <div class="clearer">&nbsp;"></div>
    </div>
</div>

<div class="main">
    <div class="post">
        <table frame="box" rules="groups" summary="">
            <thead>
                <tr>
                    <th style="width:30px;">&nbsp;</th>
                    <th style="width:20px;">{"_MOD"|lang}</th>
                    <th style="width:20px;">{"_OS"|lang}</th>
                    <th style="width:20px;">{"_VAC"|lang}</th>
                    <th>{"_HOSTNAME"|lang}</th>
                    <th style="width:30px;">{"_PLAYER"|lang}</th>
                    <th style="width:130px;">{"_MAP"|lang}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$servers item=server}
                <form method="post">
                    <input type="hidden" name="server" value="{$server.id}" />
                    {if $server.is_online}
                    <tr {if $server.id == $server_select}class="selected-server"{/if}>
                        <td>
                            <form method="post">
                                <input type="hidden" name="server" value="{$server.id}" />
                                <input class="button" type="submit" value="{"_VIEWSETTINGS"|lang}" />
                            </form>
                        </td>
                        <td class="_center"><img alt="{$server.mod}" title="{$server.mod}" src="templates/{$design}_gfx/games/{$server.mod}.gif" /></td>
                        <td class="_center"><img alt="{$server.os}" title="{$server.os}" src="templates/{$design}_gfx/os/{$server.os}.png" /></td>
                        <td class="_center"><img alt="{"_VAC_ALT"|lang}" title="{"_VAC_ALT"|lang}" src="templates/{$design}_gfx/acheat/vac.png" /></td>
                        <td>{$server.hostname}</td>
                        <td class="_center">
                            {if $server.bot_players}
                            {$server.cur_players-$server.bot_players} ({$server.cur_players})
                            {else}
                            {$server.cur_players}
                            {/if}
                            / {$server.max_players}
                        </td>
                        <td>{$server.map}</td>
                    </tr>
                    {else}
                    <tr class="offline-server">
                        <td>
                            <form method="post">
                                <input type="hidden" name="server" value="0" />
                                <input class="button disabled" type="submit" value="{"_VIEWSETTINGS"|lang}" disabled />
                            </form>
                        </td>
                        <td class="_center">{"_NA"|lang}</td>
                        <td class="_center">{"_NA"|lang}</td>
                        <td class="_center">{"_NA"|lang}</td>
                        <td>{$server.hostname}</td>
                        <td class="_center">{"_NA"|lang}</td>
                        <td>{"_NA"|lang}</td>
                    </tr>
                    {/if}
                </form>
                {/foreach}
            </tbody>
        </table>
        <div class="clearer">&nbsp;"></div>
    </div>

    <form name="frm" method="post" onsubmit="return confirmAction();">
        {if $playerscount}
        <div class="post">
            <table frame="box" rules="groups" summary="">
                <thead>
                    <tr>
                        <th style="width:250px;">{"_ADDBAN"|lang}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fat">{"_BANTYPE"|lang}</td>
                        <td>
                            <select name="ban_type">
                                <option label="Steamid" value="S">{"_STEAMID"|lang}</option>
                                <option label="Steamid &amp; IP" value="SI">{"_STEAMID"|lang} &amp; {"_IP"|lang}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="fat">{"_REASON"|lang}</td>
                        <td>
                            <select name="ban_reason" onchange="setBanLength()">
                                {html_options output=$reasons values=$reasons}
                            </select>
                            {"_OR"|lang} {"_NEWREASON"|lang}: <input type="text" size="30" name="user_reason" onkeyup="document.frm.ban_reason.disabled=(this.value!='');" />
                        </td>
                    </tr>
                    <tr>
                        <td class="fat">{"_BANLENGHT"|lang}</td>
                        <td>
                            <input type="text" size="8" name="ban_length" id="ban_length" /> {"_MIN_OR"|lang}
                            <input type="checkbox" name="perm" id="perm" onclick="handlePermanentCheckbox();" /> {"_PERMANENT"|lang}
                        </td>
                    </tr>
                    <tr>
                        <td class="fat">{"_SILENTBAN"|lang}</td>
                        <td><input type="checkbox" name="silent" /></td>
                    </tr>
                </tbody>
            </table>
            <div class="clearer">&nbsp;"></div>
        </div>
        {/if}

        <div class="post">
            <table frame="box" rules="groups" summary="">
                <thead>
                    <tr>
                        <th style="width:5px;">{"_NUMBER"|lang}</th>
                        <th style="width:20px;">&nbsp;</th>
                        <th>{"_NAME"|lang}</th>
                        <th style="width:150px;">{"_STEAMID"|lang}</th>
                        <th style="width:150px;">{"_IP"|lang}</th>
                        <th style="width:50px;">{"_USERID"|lang}</th>
                        <th style="width:50px;">{"_STATUSNAME"|lang}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Users Online -->
                    {if $playerscount}
                    {foreach from=$players item=player}
                    <tr {if $player.statusname == "_BOT"}class="offline"{/if}>
                        <td class="_center">{counter}.</td>
                        <td class="_center">
                            <input type="checkbox" name="selected_players[]" value="{$player.name}|{$player.userid}|{$player.steamid}|{$player.ip}">
                        </td>
                        <td>{$player.name}</td>
                        {if $player.statusname !="_BOT"}
                        <td><a href="http://steamcommunity.com/profiles/{$player.steamcomid}" target="_blank">{$player.steamid}</a></td>
                        <td>{$player.ip}</td>
                        {else}
                        <td>{$player.steamid}</a></td>
                        <td></td>
                        {/if}
                        <td class="_center">#{$player.userid}</td>
                        <td class="_center">{$player.statusname|lang}</td>
                    </tr>
                    {/foreach}
                    {else}
                    <tr>
                        <td class="error _center" colspan="8">{if $smsg!=""}<b class="error">{$smsg}{else}<b>{"_NOPLAYERS"|lang}{/if}</b></td>
                    </tr>
                    {/if}
                    <!-- Users Online -->
                </tbody>
            </table>
            <div class="clearer">&nbsp;"></div>
        </div>

        <input type="hidden" name="server" value="{$server_select}" />
        <input type="hidden" name="player_name" id="player_name" value="" />
        <input type="hidden" name="player_uid" id="player_uid" value="" />
        <input type="hidden" name="player_steamid" id="player_steamid" value="" />
        <input type="hidden" name="player_ip" id="player_ip" value="" />

        <div class="actions">
            <input type="submit" class="button" name="ban_selected" value="{"_BANSELECTED"|lang}" />
            <input type="submit" class="button" name="kick_selected" value="{"_KICKSELECTED"|lang}" />
        </div>
    </form>
    <div class="clearer">&nbsp;"></div>
</div>

{if $smsg}
<div class="notice">
    <div class="rcon_box">
        <pre>{$smsg}</pre>
        <div class="clearer">&nbsp;"></div>
    </div>
    <div class="clearer">&nbsp;"></div>
</div>
{/if}

<style>
.selected-server {
    background-color: #DBF4D7; /* Light green, same as ban_list expired */
}

.offline-server {
    background-color: #ffcccc; /* Light red */
}

input[type="submit"][disabled] {
    background-color: #d3d3d3; /* Grey */
    cursor: not-allowed;
}

.offline {
    background-color: #ffcccc; /* Light red for bots */
}
</style>

<script src="templates/_js/livebans.js"></script>
<script>
    // Pass the reasons_with_times data to the external JavaScript file
    var reasonsWithTimes = JSON.parse('{$reasons_with_times|escape:"javascript"}');

        function setBanLength() {
        var reasonSelect = document.querySelector('select[name="ban_reason"]');
        var selectedReasonText = reasonSelect.options[reasonSelect.selectedIndex].text;
        var banLengthInput = document.getElementById('ban_length');
        var permCheckbox = document.getElementById('perm');

        var selectedReasonObj = reasonsWithTimes.find(function(reason) {
            return reason.reason === selectedReasonText;
        });

        if (selectedReasonObj) {
            var staticBanTime = selectedReasonObj.static_bantime;
            banLengthInput.value = staticBanTime;
            if (staticBanTime == 0) {
                permCheckbox.checked = true;
                toggleBanLength(true);
            } else {
                permCheckbox.checked = false;
                toggleBanLength(false);
            }
        } else {
            console.log("Reason not found in reasonsWithTimes");
            banLengthInput.value = '';
            permCheckbox.checked = false;
            toggleBanLength(false);
        }
    }

    function toggleBanLength(isPermanent) {
        var banLengthInput = document.getElementById('ban_length');
        banLengthInput.disabled = isPermanent;
    }

    function handlePermanentCheckbox() {
        var permCheckbox = document.getElementById('perm');
        toggleBanLength(permCheckbox.checked);
    }

        function confirmAction() {
        var selectedPlayers = document.querySelectorAll('input[name="selected_players[]"]:checked');
        if (selectedPlayers.length === 0) {
            alert('No players selected');
            return false;
        }

        var banSelected = document.querySelector('input[name="ban_selected"]');
        var kickSelected = document.querySelector('input[name="kick_selected"]');

        if (banSelected && banSelected.clicked) {
            return confirm('Are you sure you want to ban the selected players?');
        }

        if (kickSelected && kickSelected.clicked) {
            return confirm('Are you sure you want to kick the selected players?');
        }

        return true;
    }
</script>
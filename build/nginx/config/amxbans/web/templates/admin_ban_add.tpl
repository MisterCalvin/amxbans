<div id="navigation">
    <div id="main-nav">
        <ul class="tabbed">
            <li id="header_1" onclick="ToggleMenu_open('1');"><a href="#">{"_ADMINAREA"|lang}</a></li>
            <li id="header_2" onclick="ToggleMenu_open('2');"><a href="#">{"_SERVER"|lang}</a></li>
            <li id="header_3" onclick="ToggleMenu_open('3');"><a href="#">{"_WEB"|lang}</a></li>
            <li id="header_4" onclick="ToggleMenu_open('4');"><a href="#">{"_OTHER"|lang}</a></li>
            <li id="header_5" onclick="ToggleMenu_open('5');"><a href="#">{"_MODULES"|lang}</a></li>
        </ul>
        <div class="clearer">&nbsp;</div>
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
{if $msg<>""}
<div class="notice">{"$msg"|lang}</div>
{/if}
<div class="main">
    <div class="post">
        {if $smarty.session.bans_add=="yes"}
        <form method="post" id="banForm">
            <table frame="box" rules="groups" summary="">
                <thead>
                    <tr>
                        <th style="width:200px;">{"_ADDBAN"|lang}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="settings_line">
                        <td class="fat">{"_NICKNAME"|lang}</td>
                        <td><input type="text" size="40" name="name" {if $inputs.name != ""}value="{$inputs.name}"{/if}/></td>
                    </tr>
                    <tr class="settings_line">
                        <td class="fat">{"_STEAMID"|lang}</td>
                        <td><input type="text" size="40" name="steamid" {if $inputs.steamid != ""}value="{$inputs.steamid}"{else}value="STEAM_0:"{/if}/></td>
                    </tr>
                    <tr class="settings_line">
                        <td class="fat">{"_IP"|lang}</td>
                        <td><input type="text" size="40" name="ip" {if $inputs.ip != ""}value="{$inputs.ip}"{/if}/></td>
                    </tr>
                    <tr class="settings_line">
                        <td class="fat">{"_BANTYPE"|lang}</td>
                        <td>
                            <select name="ban_type">{html_options output=$banby_output values=$banby_values selected=$inputs.type}</select>
                        </td>
                    </tr>
                    <tr class="settings_line">
                        <td class="fat">{"_REASON"|lang}</td>
                        <td>
                            <select name="ban_reason" onchange="setBanLength()">
                                {html_options output=$reasons values=$reasons}
                            </select>
                            {"_OR"|lang} {"_NEWREASON"|lang}: <input type="text" size="30" name="user_reason" onkeyup="document.querySelector('select[name=ban_reason]').disabled=(this.value!='');" />
                        </td>
                    </tr>
                    <tr class="settings_line">
                        <td class="fat">{"_BANLENGHT"|lang}</td>
                        <td>
                            <input type="text" size="8" name="ban_length" id="ban_length" /> {"_MIN_OR"|lang}
                            <input type="checkbox" name="perm" id="perm" onclick="handlePermanentCheckbox();" /> {"_PERMANENT"|lang}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="_right"><input type="submit" class="button" name="save" value="{"_ADD"|lang}" /></div>
        </form>
        {else}
        <center><div class="admin_msg">{"_NOACCESS"|lang}</div></center>
        {/if}
        <div class="clearer">&nbsp;"></div>
    </div>

    <div class="clearer">&nbsp;"></div>
</div>

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

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('select[name="ban_reason"]').addEventListener('change', setBanLength);
        document.getElementById('perm').addEventListener('change', handlePermanentCheckbox);

        setBanLength(); // Set initial value on page load

        document.querySelector('input[name="user_reason"]').addEventListener('keyup', function () {
            var reasonInput = this;
            var reasonSelect = document.querySelector('select[name="ban_reason"]');
            reasonSelect.disabled = reasonInput.value.trim() !== '';
        });
    });
</script>
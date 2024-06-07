<td class="table_list" colspan="10">
    <div style="display:none;">
        <table width="90%">
            <tr class="htable">
                <td><nobr><b>{"_EDITBAN"|lang}</b></nobr></td>
            </tr>
            <tr>
                <td colspan="2">
                    <table width="100%" class="table_details" border="1">
                        <form method="post" action="ban_list.php" id="banForm">
                            <input type="hidden" name="site" value="{$site}" />
                            <input type="hidden" name="bid" value="{$ban_detail.bid}" />
                            <input type="hidden" name="details_x" value="1" />
                            <input type="hidden" name="ban_length_old" value="{$ban_detail.ban_length}" />
                            <input type="hidden" name="ban_reason_old" value="{$ban_detail.ban_reason}" />
                            <tr class="settings_line">
                                <td width="30%"><b>{"_NICKNAME"|lang}:</b></td>
                                <td><input type="text" size="40" id="id0" name="player_nick" value="{$ban_detail.player_nick}" /></td>
                            </tr>
                            <tr class="settings_line">
                                <td><b>{"_STEAMID"|lang}:</b></td>
                                <td><input type="text" size="40" id="id1" name="player_id" value="{$ban_detail.player_id}" /></td>
                            </tr>
                            <tr class="settings_line">
                                <td><b>{"_IP"|lang}:</b></td>
                                <td>{if $smarty.session.ip_view=="yes"}
                                    <input type="text" size="40" id="id2" name="player_ip" value="{$ban_detail.player_ip}" />{else}<i>{"_HIDDEN"|lang}</i>
                                    {/if}
                                </td>
                            </tr>
                            <tr class="settings_line">
                                <td><b>{"_BANTYPE"|lang}:</b></td>
                                <td><select id="id3" name="ban_type" width="200">{html_options output=$type_output values=$type_values selected=$ban_detail.ban_type}</select></td>
                            </tr>
                            <tr class="settings_line">
                                <td><b>{"_REASON"|lang}:</b></td>
                                <td>
                                    <select id="id4" name="ban_reason" onchange="setBanLength()" {if $ban_detail.custom_reason != ''}disabled{/if}>
                                        {foreach from=$reasons item=reason}
                                            <option value="{$reason}" {if $reason == $ban_detail.ban_reason}selected{/if}>{$reason}</option>
                                        {/foreach}
                                    </select>
                                    {"_OR"|lang} {"_NEWREASON"|lang}: <input type="text" size="30" name="user_reason" value="{$ban_detail.custom_reason}" onkeyup="document.querySelector('select[name=ban_reason]').disabled=(this.value!='');" />
                                </td>
                            </tr>
                            <tr class="settings_line">
                                <td><b>{"_BANLENGHT"|lang}:</b></td>
                                <td>
                                    <input type="text" size="10" id="ban_length" name="ban_length" value="{$ban_detail.ban_length}" /> {"_MINS"|lang}
                                    <b><input type="checkbox" name="perm" id="perm" onclick="handlePermanentCheckbox();" {if $ban_detail.ban_length == 0}checked{/if} /> {"_PERMANENT"|lang}</b>
                                    <b><input type="checkbox" name="unban" id="unban" onclick="handleUnbanCheckbox();" {if $ban_detail.ban_length == -1}checked{/if} /> {"_UNBANPLAYER"|lang}</b>
                                </td>
                            </tr>
                            <tr class="settings_line">
                                <td><b>{"_EDITREASON"|lang}:</b></td>
                                <td>
                                    <textarea name="edit_reason" id="edit_reason" cols="50" rows="3" wrap="soft"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="_right">
                                    <input type="submit" class="button" name="edit_ban" onclick="return confirm('{"_SAVEEDIT"|lang}');" value="{"_SAVE"|lang}" />
                                </td>
                            </tr>
                        </form>
                    </table>
                    {if $ban_details_edits}
                    <br />
                    <table width="100%" cellspacing="0" border="1">
                        <tr class="htable">
                            <td colspan="3"><b>{"_BANDETAILSEDITS"|lang}</b></td>
                        </tr>
                        <tr class="settings_line">
                            <td width="1%"><b>{"_DATE"|lang}</b></td>
                            <td width="1%"><b>{"_ADMIN"|lang}</b></td>
                            <td><b>{"_EDITREASON"|lang}</b></td>
                        </tr>
                        {foreach from=$ban_details_edits item=ban_detail_edit}
                        <tr class="settings_line">
                            <td nowrap>{$ban_detail_edit.edit_time|date_format:"%d. %b %Y - %T"}</td>
                            <td nowrap>{$ban_detail_edit.admin_nick}</td>
                            <td>{$ban_detail_edit.edit_reason|bbcode2html}</td>
                        </tr>
                        {/foreach}
                    </table>
                    {/if}
                </td>
            </tr>
        </table>
    </div>
</td>

<script src="templates/_js/livebans.js"></script>
<script>
    var reasonsWithTimes = JSON.parse('{$reasons_with_times|escape:"javascript"}');

    function setBanLength() {
        var reasonSelect = document.querySelector('select[name="ban_reason"]');
        var banLengthInput = document.getElementById('ban_length');
        var permCheckbox = document.getElementById('perm');
        var customReasonInput = document.querySelector('input[name="user_reason"]');

        if (!reasonSelect || reasonSelect.selectedIndex === -1) {
            console.log("Reason select element or selected index not found");
            return;
        }

        var selectedReasonText = reasonSelect.options[reasonSelect.selectedIndex].text;
        var selectedReasonObj = reasonsWithTimes.find(function(reason) {
            return reason.reason === selectedReasonText;
        });

        if (customReasonInput.value.trim() !== '') {
            // If custom reason is entered, do not change the ban length
            return;
        }

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

    function handleUnbanCheckbox() {
        var unbanCheckbox = document.getElementById('unban');
        var formFields = document.querySelectorAll('#banForm input[type="text"], #banForm select');

        formFields.forEach(function(field) {
            if (unbanCheckbox.checked) {
                field.disabled = true;
            } else {
                field.disabled = false;
            }
        });

        // Ensure the "Unban" checkbox itself is not disabled
        unbanCheckbox.disabled = false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var reasonSelect = document.querySelector('select[name="ban_reason"]');
        var permCheckbox = document.getElementById('perm');
        var unbanCheckbox = document.getElementById('unban');
        var userReasonInput = document.querySelector('input[name="user_reason"]');

        if (reasonSelect) {
            reasonSelect.addEventListener('change', setBanLength);
        }
        if (permCheckbox) {
            permCheckbox.addEventListener('change', handlePermanentCheckbox);
        }
        if (unbanCheckbox) {
            unbanCheckbox.addEventListener('change', handleUnbanCheckbox);
        }
        if (userReasonInput) {
            userReasonInput.addEventListener('keyup', function () {
                var reasonSelect = document.querySelector('select[name="ban_reason"]');
                reasonSelect.disabled = this.value.trim() !== '';
            });
        }

        setBanLength(); // Set initial value on page load

        handleUnbanCheckbox(); // Set initial state based on checkbox
    });
</script>

<div>
    <div align="center" class="notice">{"_STEP5DESC"|lang}</div>
    <br />
    <fieldset>
        <legend>{"_ADMINSETTINGS"|lang}</legend>
        <table width="100%" cellpadding="5">
            <tr class="settings_line">
                <td>{"_USER"|lang}:</td>
                <td width="1%"><input type="text" name="adminuser" value="{$admin.0|default:'admin'}" size="20" /></td>
            </tr>
            <tr class="settings_line">
                <td>{"_PASSWORD"|lang}:</td>
                <td width="1%"><input type="password" name="adminpass" value="{$adminpass|default:''}" size="20" /></td>
            </tr>
            <tr class="settings_line">
                <td>{"_PASSWORD2"|lang}:</td>
                <td width="1%"><input type="password" name="adminpass2" value="{$adminpass|default:''}" size="20" /></td>
            </tr>
            <tr class="settings_line">
                <td>{"_EMAILADR"|lang}:</td>
                <td width="1%"><input type="text" name="adminemail" value="{$admin.1|default:'admin@domain.com'}" size="20" /></td>
            </tr>
        </table>
    <br />
    {if $validate}
        {foreach from=$validate item=validation}
            <div class="error">{$validation|lang}</div>
        {/foreach}
    {/if}
    {if $msg}
        <div class="{if $msg=="_ADMINOK"}success{else}error{/if}">{$msg|lang}</div>
    {/if}
</div>

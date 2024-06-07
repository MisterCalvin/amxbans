<div>
	<div align="center" class="notice">{"_STEP2DESC"|lang}</div>
	<fieldset>
	<legend>{"_SERVERSETUP"|lang}</legend>
	<table width="80%" align="center">
		<tr class="settings_line">
			<td>PHP {"_VERSION"|lang}</td>
			<td><img src="images/{if $php_settings.version_php >=5.0}success{else}warning{/if}.gif" /> {$php_settings.version_php}</td>
		</tr>
		<tr class="settings_line">
			<td>MySQL {"_VERSION"|lang}</td>
			<td><img src="images/{if $php_settings.mysql_version >=4.1}success{else}warning{/if}.gif" /> {$php_settings.mysql_version}</td>
		</tr>

		<tr class="settings_line">
			<td>register_globals</td>
			<td><img src="images/{if $php_settings.register_globals=="_OFF"}success{else}warning{/if}.gif" /> {$php_settings.register_globals|lang}</td>
		</tr>
		<tr class="settings_line">
			<td>upload_max_filesize</td>
			<td>{$php_settings.upload_max_filesize}</td>
		</tr>
		<tr class="settings_line">
			<td>max_execution_time</td>
			<td>{$php_settings.max_execution_time} {"_SEC"|lang}</td>
		</tr>
	</table>
	</fieldset>
	<br />
	<div class="notice">
		<img src="images/success.gif" /> - {"_SETRECOMMENDED"|lang}<br />
		<img src="images/warning.gif" /> - {"_SETNOTRECOMMENDED"|lang}
	</div>
</div>
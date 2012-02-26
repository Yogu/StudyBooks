{extends "../_layout.tpl"}
{block "title"}{l SETUP}{/block}

{block "body"}
<p>{l SETUP_MESSAGE}</p>

{if $errors}
	<div class="errors">
		{$errors}
	</div>
{/if}

<form action="{$request->internalURL}" method="post">
	<fieldset class="input">
		<legend>{l SETUP_DATABASE}</legend>
		<dl>
			<dt><label for="dbHost">{l SETUP_DB_HOST}:</label></dt>
			<dd><input id="dbHost" name="dbHost" type="text" value="{html $request->post('dbHost')}" /></dd>
		</dl>
		<dl>
			<dt><label for="dbUser">{l SETUP_DB_USER}:</label></dt>
			<dd><input id="dbUser" name="dbUser" type="text" value="{html $request->post('dbUser')}" /></dd>
		</dl>
		<dl>
			<dt><label for="dbPassword">{l SETUP_DB_PASSWORD}:</label></dt>
			<dd><input id="dbPassword" name="dbPassword" type="password" value="{html $request->post('dbPassword')}" /></dd>
		</dl>
		<dl>
			<dt><label for="dbDataBase">{l SETUP_DB_DATABASE}:</label></dt>
			<dd><input id="dbDataBase" name="dbDataBase" type="text" value="{html $request->post('dbDataBase')}" /></dd>
		</dl>
		<dl>
			<dt><label for="dbPrefix">{l SETUP_DB_PREFIX}:</label></dt>
			<dd><input id="dbPrefix" name="dbPrefix" type="text" value="{html $request->post('dbPrefix')}" /></dd>
		</dl>
	</fieldset>
	<fieldset class="input">
		<legend>{l SETUP_ADMIN}</legend>
		<dl>
			<dt><label for="adminName">{l SETUP_ADMIN_NAME}:</label></dt>
			<dd><input id="adminName" name="adminName" type="string" value="{html $request->post('adminName')}" /></dd>
		</dl>
		<dl>
			<dt><label for="adminPassword">{l SETUP_ADMIN_PASSWORD}:</label></dt>
			<dd><input id="adminPassword" name="adminPassword" type="password" value="{html $request->post('adminPassword')}" /></dd>
		</dl>
		<dl>
			<dt><label for="adminPasswordConfirmation">{l SETUP_ADMIN_PASSWORD_CONFIRMATION}:</label></dt>
			<dd><input id="adminPasswordConfirmation" name="adminPasswordConfirmation" type="password" value="{html $request->post('adminPasswordConfirmation')}" /></dd>
		</dl>
	</fieldset>
	<fieldset class="buttons">
		<input type="submit" name="submit" value="{l SETUP_SUBMIT}" />
	</fieldset>
</form>
{/block}

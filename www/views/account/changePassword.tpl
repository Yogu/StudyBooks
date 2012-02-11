{extends "../_layout.tpl"}
{block "title"}{l CHANGE_PASSWORD}{/block}

{block "body"}
<p>{l CHANGE_PASSWORD_MESSAGE}</p>

{if $errors}
	<div class="errors">
		{$errors}
	</div>
{/if}

<form action="{url}" method="post">
	<fieldset class="input">
		<dl>
			<dt><label for="oldPassword">{l CHANGE_PASSWORD_CURRENT}:</label></dt>
			<dd><input id="oldPassword" name="oldPassword" type="password" /></dd>
		</dl>
		<dl>
			<dt><label for="newPassword">{l CHANGE_PASSWORD_NEW}:</label></dt>
			<dd><input id="newPassword" name="newPassword" type="password" /></dd>
		</dl>
		<dl>
			<dt><label for="passwordConfirmation">{l CHANGE_PASSWORD_CONFIRMATION}:</label></dt>
			<dd><input id="passwordConfirmation" name="passwordConfirmation" type="password" /></dd>
		</dl>
	</fieldset>
	<fieldset class="buttons">
		<input type="submit" name="submit" value="{l CHANGE_PASSWORD_SUBMIT}" />
	</fieldset>
</form>
{/block}

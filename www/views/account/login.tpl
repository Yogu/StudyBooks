{extends "../_layout.tpl"}
{block "title"}{l LOGIN}{/block}

{block "body"}
{if $request->session}
	<p>{l LOGIN_LOGGED_IN_AS} <b>{html $request->user->name}</b>.</p>
	<p><a href="./">» {l BACK_TO_HOME}</a></p>
	<form action="{url login account}" method="post">
		<input type="submit" name="logout" value="{l LOG_OUT}" />
	</form>
{else}
	{if $isFailed}
		<div class="errors">
			<p>{l LOGIN_FAILED}</p>
		</div>
	{elseif $isBanned}
		<div class="errors">
			<p>{l LOGIN_FAILED_BANNED}</p>
		</div>
	{else}
		<p>{l LOGIN_MESSAGE}</p>
	{/if}
	
	<form action="{url login account}" method="post">
		<input type="hidden" name="referer" value="{html $request->internalURL}" />
		<fieldset class="input">
			<dl>
				<dt><label for="user">{l LOGIN_USER}:</label></dt>
				<dd><input id="user" name="user" type="text" /></dd>
			</dl>
			<dl>
				<dt><label for="password">{l LOGIN_PASSWORD}:</label></dt>
				<dd><input id="password" name="password" type="password" /></dd>
			</dl>
		</fieldset>
		<fieldset class="buttons">
			<input type="submit" name="login" value="{l LOG_IN}" />
		</fieldset>
	</form>
{/if}
{/block}

{extends "../_layout.tpl"}
{block "title"}{l SETUP}{/block}

{block "body"}
<p>{l SETUP_CONFIRM_MESSAGE}</p>

{if $willDrop}
	<div class="warning">
		<p>{l SETUP_WILL_DROP_MESSAGE}</p>
	</div>
{/if}

<form action="{$request->internalURL}" method="post">
	<input type="hidden" name="isStep2" value="true" />
	
	<input type="hidden" name="dbHost" value="{html $request->post('dbHost')}" />
	<input type="hidden" name="dbUser" value="{html $request->post('dbUser')}" />
	<input type="hidden" name="dbPassword" value="{html $request->post('dbPassword')}" />
	<input type="hidden" name="dbDataBase" value="{html $request->post('dbDataBase')}" />
	<input type="hidden" name="dbPrefix" value="{html $request->post('dbPrefix')}" />
	<input type="hidden" name="adminName" value="{html $request->post('adminName')}" />
	<input type="hidden" name="adminPassword" value="{html $request->post('adminPassword')}" />
	<input type="hidden" name="adminPasswordConfirmation" value="{html $request->post('adminPasswordConfirmation')}" />
	
	{if $willDrop}
		<input type="hidden" name="confirmDrop" value="true" />
	{/if}
	<fieldset class="buttons">
		<input type="submit" name="submit" value="{if $willDrop}{l SETUP_CONFIRM_DROP}{else}{l SETUP_CONFIRM}{/if}" {if !$willDrop}class="default"{/if} />
		<input type="submit" name="cancel" value="{l FORM_CANCEL}" {if $willDrop}class="default"{/if} />
	</fieldset>
</form>
{/block}


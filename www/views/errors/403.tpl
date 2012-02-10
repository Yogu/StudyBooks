{extends "../_layout.tpl"}
{block "title"}{l ACCESS_DENIED}{/block}

{block "body"}
<p>{l ACCESS_DENIED_MESSAGE</p>
<p><a href="./">» {l BACK_TO_HOME}</a></p>
{if $details}
	<p>{html $details}</p>
{/if}
{/block}

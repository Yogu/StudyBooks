{extends "../_layout.tpl"}
{block "title"}{l PAGE_NOT_FOUND}{/block}

{block "body"}
<p>{l PAGE_NOT_FOUND_MESSAGE}</p>
<p><a href="./">» {l BACK_TO_HOME}</a></p>
{if $details}
	<p>{html $details}</p>
{/if}
{/block}

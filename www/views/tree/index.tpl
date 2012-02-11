{extends "../_layout.tpl"}
{block "title"}{l TREE_TITLE}{/block}

{block "body"}
	{include file='_node.tpl' nodes=$nodes]}
{/block}

{extends "../_layout.tpl"}
{block "title"}{html $book->title}{/block}
{block "head"}
	<script type="text/javascript" src="./scripts/tree.js"></script>
{/block}

{block "body"}
	<div class="book">
		{include file='_details_node.tpl' nodes=$nodes book=$book edit=$edit}
	</div>
{/block}

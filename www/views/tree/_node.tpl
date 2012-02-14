{if arrcount($nodes)}
	<ul>
		{foreach $nodes node}
			<li>
				{if $node[0]->type != 'folder'}<a href="{url details Tree array(id=$node[0]->id)}">{/if}
					{html $node[0]->title}
				{if $node[0]->type != 'folder'}</a>{/if}
				{if arrcount($node[1])}
					{include file='_node.tpl' nodes=$node[1]}
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}

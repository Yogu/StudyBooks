{if count($nodes)}
	<ul>
		{foreach $nodes node}
			<li>
				<a href="{url details Tree array(id=$node[0]->id)}">{html $node[0]->title}</a>
				{if count($node[1])}
					{include file='_node.tpl' nodes=$node[1]}
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}

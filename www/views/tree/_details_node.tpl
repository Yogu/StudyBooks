{$depth = min(max($depth, 3), 8)}

{if arrcount($nodes)}
	{foreach $nodes node}
		<div class="book-row book-row-{$node[0]->type}{if $node[0]->id == $edit->id} book-row-with-form{/if}">
			<div class="book-content">
				{if $node[0]->id == $edit->id}
					{include file='_form.tpl' node=$edit}
				{else}
					{if $node[0]->isLeaf}
						{$content = $node[0]->getContent()}
						{if $content}
							<div class="leaf">
								{$content->html()}
							</div>
						{/if}
					{else}
						<h{$depth} id="n{$node[0]->id}">{* <a href="{url details Tree array(id=$book->id)}#n{$node[0]->id}">*}{html $node[0]->title}{*</a>*}</h{$depth}>
					{/if}
				{/if}
			</div>
			<div class="book-toolbar">
				<div>
					<a class="book-edit" href="{url edit Tree array(id=$book->id node=$node[0]->id)}#form" title="{l NODE_EDIT_TITLE}">{l NODE_EDIT}</a>
					<a class="book-add-after" href="{url add Tree array(id=$book->id node=$node[0]->id pos=after)}#form" title="{l NODE_ADD_AFTER_TITLE}">{l NODE_ADD_AFTER}</a>
					<a class="book-add-before" href="{url add Tree array(id=$book->id node=$node[0]->id pos=before)}#form" title="{l NODE_ADD_BEFORE_TITLE}">{l NODE_ADD_BEFORE}</a>
					{if !$node[0]->isLeaf}
						<a class="book-add-inside" href="{url add Tree array(id=$book->id node=$node[0]->id pos=inside)}#form" title="{l NODE_ADD_INSIDE_TITLE}">{l NODE_ADD_INSIDE}</a>
					{/if}
				</div>
			</div>
			<div class="book-meta">
				{if $node[0]->isLeaf}
					<p>{html $node[0]->title}</p>
				{/if}
			</div>
			<div></div>
		</div>
		
		{if arrcount($node[1])}
			{include file='_details_node.tpl' book=$book nodes=$node[1] depth=$depth+1}
		{/if}
	{/foreach}
{/if}

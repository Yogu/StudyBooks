{$depth = min(max($depth, 3), 8)}

{if arrcount($nodes)}
	{foreach $nodes node}
		<div class="book-row book-row-{$node[0]->type}{if $node[0]->id == $edit->id} book-row-with-form{/if}" id="n{$node[0]->id}">
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
						<h{$depth}>{* <a href="{url details Tree array(id=$book->id)}#n{$node[0]->id}">*}{html $node[0]->title}{*</a>*}</h{$depth}>
					{/if}
				{/if}
			</div>
			<div class="book-toolbar">
				<div>
					<a class="book-edit" href="{url edit Tree array(id=$book->id node=$node[0]->id)}#form" title="{l NODE_EDIT_TITLE}">{l NODE_EDIT}</a>
					{if $node[0]->isLeaf}
						<a class="book-delete-item" href="{url delete Tree array(id=$book->id item=$node[0]->id)}#form" title="{l NODE_DELETE_LEAF_TITLE}">{l NODE_DELETE_LEAF}</a>
					{else}
						<a class="book-delete-item" href="{url delete Tree array(id=$book->id item=$node[0]->id)}#form" title="{l NODE_DELETE_ITEM_TITLE}">{l NODE_DELETE_ITEM}</a>
						<a class="book-delete-recursive" href="{url delete Tree array(id=$book->id node=$node[0]->id recursive='')}#form" title="{l NODE_DELETE_RECURSIVE_TITLE}">{l NODE_DELETE_RECURSIVE}</a>
					{/if}
					<a class="book-add-after" href="{url add Tree array(id=$book->id after=$node[0]->id)}#form" title="{l NODE_ADD_AFTER_TITLE}">{l NODE_ADD_AFTER}</a>
					{*
					<a class="book-add-before" href="{url add Tree array(id=$book->id node=$node[0]->id pos=before)}#form" title="{l NODE_ADD_BEFORE_TITLE}">{l NODE_ADD_BEFORE}</a>
					{if !$node[0]->isLeaf}
						<a class="book-add-inside" href="{url add Tree array(id=$book->id node=$node[0]->id pos=inside)}#form" title="{l NODE_ADD_INSIDE_TITLE}">{l NODE_ADD_INSIDE}</a>
					{/if}
					*}
				</div>
			</div>
			<div class="book-meta">
				{if $node[0]->isLeaf}
					<p>{html $node[0]->title}</p>
				{/if}
			</div>
			<div></div>
		</div>
		
		{* If to add a node after the current node, create a new row with the form *}
		{if $adding && $reference->id == $node[0]->id}
			<div class="book-row book-row-with-form{if !$newNode->type} book-row-choose-type{/if}">
				<div class="book-content">
					{include file='_form.tpl' node=$newNode book=$book reference=$reference}
				</div>
				<div></div>
			</div>
		{/if}
		
		{if arrcount($node[1])}
			{include file='_details_node.tpl' book=$book nodes=$node[1] depth=$depth+1}
		{/if}
	{/foreach}
{/if}

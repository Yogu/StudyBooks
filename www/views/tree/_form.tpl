{if !$node->type}
	<div class="book-form-choice" id="form">
		<span>{l CHOOSE_NODE_TYPE}</span>
		<a href="{url array(_addCurrent type=heading1)}#form">{l NODE_TYPE_HEADING1}</a>
		<a href="{url array(_addCurrent type=heading2)}#form">{l NODE_TYPE_HEADING2}</a>
		<a href="{url array(_addCurrent type=heading3)}#form">{l NODE_TYPE_HEADING3}</a>
		<a href="{url array(_addCurrent type=heading4)}#form">{l NODE_TYPE_HEADING4}</a>
		<a href="{url array(_addCurrent type=text)}#form">{l NODE_TYPE_TEXT}</a>
		<a href="{url array(_addCurrent type=file)}#form">{l NODE_TYPE_FILE}</a>
		<a href="{url details Tree array(id=$book->id)}#{$reference->id}">{l FORM_CANCEL}</a>
	</div>
{else}
	<form action="{url array(_addCurrent)}" method="post" class="book-form" id="form">
		{if $node->type == 'heading'}
			<input type="text" name="title" value="{html $node->title}" />
		{elseif $node->type == 'text'}
			{$content = $node->getContent()}
			<textarea name="text">{html $content->text}</textarea>
		{/if}
		<div class="buttons">
			<input type="submit" name="submit" class="default" value="{l FORM_SAVE}" />
			<input type="submit" name="cancel" value="{l FORM_CANCEL}" />
		</div>
	</form>
{/if}

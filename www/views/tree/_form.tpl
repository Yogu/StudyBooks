{if !$node->type}
	<div class="book-form-choice" id="form">
		<a href="{url array(_addCurrent type=heading)}#form">{l NODE_TYPE_HEADING}</a>
		<a href="{url array(_addCurrent type=text)}#form">{l NODE_TYPE_TEXT}</a>
		<a href="{url array(_addCurrent type=file)}#form">{l NODE_TYPE_FILE}</a>
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

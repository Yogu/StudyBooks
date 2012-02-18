{if !$node->type}
	<div class="book-form-choice" id="form">
		<span>{l CHOOSE_NODE_TYPE}</span>
		<a class="choice-heading1" href="{url array(_addCurrent type=heading1)}#form" title="{l NODE_TYPE_HEADING1_TITLE}">{l NODE_TYPE_HEADING1}</a>
		<a class="choice-heading2" href="{url array(_addCurrent type=heading2)}#form" title="{l NODE_TYPE_HEADING2_TITLE}">{l NODE_TYPE_HEADING2}</a>
		<a class="choice-heading3" href="{url array(_addCurrent type=heading3)}#form" title="{l NODE_TYPE_HEADING3_TITLE}">{l NODE_TYPE_HEADING3}</a>
		<a class="choice-heading4" href="{url array(_addCurrent type=heading4)}#form" title="{l NODE_TYPE_HEADING4_TITLE}">{l NODE_TYPE_HEADING4}</a>
		<a class="choice-text" href="{url array(_addCurrent type=text)}#form" title="{l NODE_TYPE_TEXT_TITLE}">{l NODE_TYPE_TEXT}</a>
		<a class="choice-file" href="{url array(_addCurrent type=file)}#form" title="{l NODE_TYPE_FILE_TITLE}">{l NODE_TYPE_FILE}</a>
		<a class="choice-cancel" href="{url details Tree array(id=$book->id)}#{$reference->id}">{l FORM_CANCEL}</a>
	</div>
{else}
	<form action="{url array(_addCurrent)}" method="post" class="book-form" id="form">
		{if $node->type == 'heading'}
			<input type="text" name="title" value="{html $node->title}" autofocus="autofocus" />
		{elseif $node->type == 'text'}
			{$content = $node->getContent()}
			<textarea name="text" autofocus="autofocus">{html $content->text}</textarea>
		{/if}
		<div class="buttons">
			<input type="submit" name="submit" class="default" value="{l FORM_SAVE}" />
			<input type="submit" name="cancel" value="{l FORM_CANCEL}" />
		</div>
	</form>
{/if}

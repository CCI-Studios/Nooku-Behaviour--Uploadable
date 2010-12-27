<?php defined('KOOWA') or die; ?>

<form enctype="multipart/form-data" 
	action="<?=@route('id='.$item->id)?>" method="post" 
	class="adminform" name="adminForm">
	<input type="hidden" name="" value="" />
	
	<div style="width:29%; float: left;" id="mainform">
		<fieldset>
			<legend><?=@text('Gallery Details')?></legend>
			<label for="name_field" class="mainlabel"><?=@text('Name')?></label>
			<input id="name_field" name="name" type="text" value="<?=$item->name?>" /><br/>
			
			<label for="filename_field" class="mainlabel"><?=@text('Filename')?></label>
			<input id="filename_field" name="filename" type="text" value="<?=$item->filename?>" /><br/>
			
			<label for="delete_file_field" class="mainlabel"><?=@text('Delete File')?></label>
			<input type="checkbox" id="delete_file_field" name="filename_delete" /><br/>
			
			<label for="file_field" class="mainlabel"><?=@text('File')?></label>
			<input type="file" id="file_field" name="filename_upload"  />
			
		</fieldset>
	</div>
</form>

<script src="media://lib_koowa/js/koowa.js" />
<style src="media://com_default/css/form.css" />
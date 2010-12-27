<?php defined('KOOWA') or die; ?>

<table class="adminlist">
	<thead>
		<tr>
			<th width="15" align="center">&nbsp;</th>
			<th><?=@text('Name')?></th>
			<th width="50" align="center"><?=@text('Images')?></th>
			<th width="50" align="center"><?=@text('ID')?></th>
		</tr>
	</thead>
	
	<tfoot>
		<tr>
			<td colspan="20">
				<?=@helper('paginator.pagination', array('total'=>$total))?>
			</td>
		</tr>
	</tfoot>
	
	<tbody>
		<? foreach ($items as $item): ?>
		<tr>
			<td><input type="checkbox" /></td>
			<td>
				<a href="<?=@route('view=item&id='.$item->id);?>">
					<?=$item->name?>
				</a>
			</td>
			<td align="center">
				<? if ($item->filename !== ''): ?>
				 <img src="<?=$item->filename?>" />
				<? else: ?>
				 &nbsp;
				<? endif; ?>
			</td>
			<td align="center"><?=$item->id?></td>
		</tr>
		<? endforeach;?>
		
		<? if ($total === 0): ?>
		<tr>
			<td colspan="20" align="center">
					<?=@text('No Items Found.')?>
			</td>
		</tr>
		<? endif; ?>
	</tbody>
</table>

<style src="media://com_default/css/admin.css" />
<script src="media://lib_koowa/js/koowa.js" />
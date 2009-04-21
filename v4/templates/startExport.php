<?php $exportData = $this->getExportData(); ?>
<table>
	<tr>
		<td></td>
		<td><?= $exportData->getTitle(); ?></td>
	</tr>
	<tr>
		<td></td>
		<td><?= $exportData->getTablelist(); ?></td>
	</tr>
	<tr>
		<td></td>
		<td><?= $exportData->getTotalNumberOfPages(); ?></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
	</tr>
</table>
<div id="export_progress" style="width: 500px;">
	<?php echo $this->progressView->render(); ?>
</div>
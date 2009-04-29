<div style="padding: 20px">

	<h2>Creating Export Data</h2>

	<hr>

	<?php $exportData = $this->getExportData(); ?>

	<table>
		<tr>
			<td></td>
			<td><?= $exportData->getTitle(); ?></td>
		</tr>
		<tr>
			<td></td>
			<td><?= $exportData->getExportTotalNumberOfPages(); ?></td>
		</tr>
	</table>

	<div id="export_progress" style="width: 500px;">
		<?php echo $this->progressView->render(); ?>
	</div>

</div>
<div style="padding: 20px">

	<h2>Creating Export Data</h2>
	<hr style="margin-bottom: 20px" />

	<table>
		<tr>
			<td>Export:</td>
			<td><?php echo $this->getExportData()->getTitle(); ?></td>
		</tr>
		<tr>
			<td>Total number of pages:</td>
			<td><?php echo $this->getExportData()->getExportTotalNumberOfPages(); ?></td>
		</tr>
	</table>

	<div id="export_progress" style="width: 500px;">
		<?php echo $this->progressView->render(); ?>
	</div>

</div>
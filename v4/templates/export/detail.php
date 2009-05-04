<h2>Creating Export Data</h2>
<hr style="margin-bottom: 20px" />
<table>
	<tr>
		<td>Export:</td>
		<td><?= $this->getExportData()->getTitle(); ?></td>
	</tr>
	<tr>
		<td>Total number of pages:</td>
		<td><?= $this->getExportData()->getExportTotalNumberOfPages(); ?></td>
	</tr>
</table>
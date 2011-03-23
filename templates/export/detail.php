<div style="padding: 20px">

	<h2>Export</h2>
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
		<?php if($this->getShowFiles()){ ?>
			<tr>
				<td>Export Archive:</td>
				<td><a href="<?php echo $this->getExportData()->getDownloadUrl(); ?>"><?php echo $this->getExportData()->getFilename(); ?></a></td>
			</tr>

			<tr>
				<td valign="top">Export Chunkfiles:</td>
				<td>
					<?php $exportFiles = $this->getExportData()->getExportFiles(); ?>
					<?php if($exportFiles instanceof ArrayObject){ ?>

						<?php foreach($exportFiles as $exportFile){ ?>
							<p><a href="<?php echo $exportFile->getDownloadUrl(); ?>"> <?php echo $exportFile->getFilename(); ?></a></p>
						<?php }?>
					<?php } ?>
				</td>
			</tr>
	<?php } ?>
	</table>

	<?php if($this->getShowListLink()){ ?>
		<hr style="margin-top: 20px" />
		<a href="<?php echo $this->getListLink(); ?>">Show List</a>
	<?php } ?>

</div>
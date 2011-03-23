<div style="padding: 20px">

	<table>
		<thead>
			<tr class="bgColor5 tableheader">
				<th><?php echo $this->labels->get('export.overview.date.label');?></th>
				<th><?php echo $this->labels->get('export.overview.configuration.label'); ?></th>
				<th><?php echo $this->labels->get('export.overview.type.label'); ?></th>
				<th><?php echo $this->labels->get('export.overview.targetlanguage.label'); ?></th>
				<th><?php echo $this->labels->get('export.overview.filename.label'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $exportDataCollection = $this->getExportDataCollection(); ?>
		<?php foreach( $exportDataCollection AS $exportData ) { ?>
			<tr class="bgColor3">
				<td><?php echo t3lib_BEfunc::datetime($exportData['crdate']); ?> </td>
				<td><?php echo $exportData['l10ncfg_id']; ?></td>
				<td><?php echo $exportData['export_type']; ?></td>
				<td><?php echo $exportData->getTranslationLanguageObject()->getTitle(); ?></td>
				<td>files</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>

</div>

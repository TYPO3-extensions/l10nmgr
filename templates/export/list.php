<table>
	<thead>
		<tr class="bgColor5 tableheader">
			<th><?=$this->labels->get('export.overview.date.label');?></th>
			<th><?=$this->labels->get('export.overview.configuration.label'); ?></th>
			<th><?=$this->labels->get('export.overview.type.label'); ?></th>
			<th><?=$this->labels->get('export.overview.targetlanguage.label'); ?></th>
			<th><?=$this->labels->get('export.overview.filename.label'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php $exportDataCollection = $this->getExportDataCollection(); ?>
	<?php foreach( $exportDataCollection AS $exportData ) { ?>
		<tr class="bgColor3">
			<td><?= t3lib_BEfunc::datetime($exportData['crdate']); ?> </td>
			<td><?= $exportData['l10ncfg_id']; ?></td>
			<td><?= $exportData['exportType']; ?></td>
			<td><?= $exportData->getTranslationLanguageObject()->getTitle(); ?></td>
			<td>files</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

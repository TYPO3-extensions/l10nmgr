<?php
	if ( !is_array($this->getRegistryData()) ) {
		exit('no data');
	}

//var_dump($this->getRegistrydata());
global $BACK_PATH, $LANG;
?>


<?= $this->getDocument()->startPage($LANG->getLL('general.title')); ?>
<?= $this->getDocument()->header($LANG->getLL('general.title')); ?>
<?= $this->getDocument()->section('', nl2br($LANG->getLL('general.description.message'))); ?>
<?= $this->getDocument()->section($LANG->getLL('general.list.configuration.title'),''); ?>
<?= $this->getDocument()->spacer(5); ?>

<table id="translationObjectList" class="scrollable" border="1">
	<thead>
		<tr class="bgColor5 tableheader">
			<th><?php echo $LANG->getLL('general.list.headline.info.title'); ?></th>
			<th><?php echo $LANG->getLL('general.list.headline.title.title'); ?></th>
			<th><?php echo $LANG->getLL('general.list.headline.path.title'); ?></th>
			<th><?php echo $LANG->getLL('general.list.headline.action.title'); ?></th>
		</tr>
	</thead>

	  <tbody>
		<?php $pagePermissionClause = $GLOBALS['BE_USER']->getPagePermsClause(1); ?>
		<?php $allConfigurationElementsStruct = $this->getRegistryData(); ?>
		<?php for( reset($allConfigurationElementsStruct); list(,$configurationElementArray) = each($allConfigurationElementsStruct); ) { ?>

			<?php if (!is_array(t3lib_BEfunc::readPageAccess($configurationElementArray['pid'],$pagePermissionClause))) {
				continue;
			} ?>

		<tr class="bgColor3">
			<td align="center">
				<a class="tooltip" href="#<?php echo 'tooltip_' . $configurationElementArray['uid']; ?>">
					<img src="<?php echo t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr'));?>gfx/cog.png" />
				</a>

				<?php $parentPageArray = t3lib_BEfunc::getRecord('pages',$configurationElementArray['pid']); ?>
				<?php $staticInfoTablesArray = t3lib_BEfunc::getRecord('static_languages',t3lib_div::intval_positive($configurationElementArray['sourceLangStaticId'])); ?>

				<div style="display:none;" id="<?php echo 'tooltip_' . $configurationElementArray['uid'] ;?>" class="infotip">
					<table class="infodetail" cellspacing="0" cellpadding="0">
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.pid.title'); ?></td>
							<td><?php echo $parentPageArray['title']; echo ' (' . $parentPageArray['uid'] . ')'?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.title.title'); ?></td>
							<td><?php echo $configurationElementArray['title']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.filenameprefix.title'); ?></td>
							<td><?php echo $configurationElementArray['filenameprefix']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.depth.title'); ?></td>
							<td><?php echo $configurationElementArray['depth']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.sourceLangStaticId.title'); ?></td>
							<td><?php echo $staticInfoTablesArray['lg_name_en']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.tablelist.title'); ?></td>
							<td><?php echo $configurationElementArray['tablelist']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.exclude.title'); ?></td>
							<td><?php echo $configurationElementArray['exclude']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.include.title'); ?></td>
							<td><?php echo $configurationElementArray['include']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.displaymode.title'); ?></td>
							<td><?php echo $configurationElementArray['displaymode']; ?></td>
						</tr>
						<tr>
							<td><?php echo $LANG->getLL('general.list.infodetail.incfcewithdefaultlanguage.title'); ?></td>
							<td><?php echo $configurationElementArray['incfcewithdefaultlanguage']; ?></td>
						</tr>
					</table>
				</div>
			</td>
			<td><?php echo $configurationElementArray['title']; ?></td>
			<td class="l10ncfgPath"><?php echo current(t3lib_BEfunc::getRecordPath($configurationElementArray['pid'], '1', 200, 50)); ?></td>
			<td>
				<?php echo '<a title="Export XML" href="' . t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr')) . 'export/index.php?l10nmgr[configurationId]=' . $configurationElementArray['uid'] . '&l10nmgr[selectedExportFormat]=xml">';?><img src="<?php echo t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr'));?>xml_export.png" alt="Export XML" /></a>

				<?php
					$editOnClickParams  = '&edit[tx_l10nmgr_importdata][' . $configurationElementArray['pid'] . ']=new';
					$editOnClickParams .= '&columnsOnly=configuration_id,importfiles';
					$editOnClickParams .= '&returnEditConf=1';
					$editOnClickParams .= '&defVals[tx_l10nmgr_importdata][configuration_id]='.$configurationElementArray['uid'];
					$editOnClickParams .= '&overrideVals[tx_l10nmgr_importdata][configuration_id]='.$configurationElementArray['uid'];
					$importUrl = t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr')) . 'import/index.php?l10nmgr[selectedExportFormat]=xml';
				?>
				<a title="Import XML" href="#" onclick="<?=htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $BACK_PATH, $importUrl)) ?>">
					<img src="<?= t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr')) ?>xml_import.png" alt="Import XML" />
				</a>


				<?php echo '<a title="Export XLS" href="' . t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr')) . 'export/index.php?l10nmgr[configurationId]=' . $configurationElementArray['uid'] . '&l10nmgr[selectedExportFormat]=xls">';?><img src="<?php echo t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr'));?>xls_export.png" alt="Export XLS" /></a>
				<?php echo '<a title="Import XLS" href="' . t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr')) . 'import/index.php?l10nmgr[configurationId]=' . $configurationElementArray['uid'] . '&l10nmgr[selectedExportFormat]=xls">';?><img src="<?php echo t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr'));?>xls_import.png" alt="Import XLS" /></a> |
				<?php echo '<a title="Translate online" href="' . t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr')) . 'translate/index.php?l10nmgr[configurationId]=' . $configurationElementArray['uid'] . '&l10nmgr[selectedExportFormat]=inlineEdit">';?><img src="<?php echo t3lib_div::resolveBackPath($BACK_PATH .t3lib_extMgm::extRelPath('l10nmgr'));?>pencil_go.png" alt="Translate online" /></a>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?= $this->getDocument()->spacer(10); ?>
<?= $this->getDocument()->endPage(); ?>
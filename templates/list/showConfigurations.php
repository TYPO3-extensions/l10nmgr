<?php if (!defined('TYPO3_MODE')) die ('Access denied.'); ?>

<?php $extPath = t3lib_div::resolveBackPath($GLOBALS['BACK_PATH'] .t3lib_extMgm::extRelPath('l10nmgr')); ?>

<div style="padding: 20px">

	<h2>Configurations</h2>
	<hr style="margin-bottom: 20px" />

	<table id="translationObjectList" style="border: 1px solid black;">
		<thead>
			<tr class="bgColor5 tableheader">
				<th>Info</th>
				<th>Title</th>
				<th>Path</th>
				<th>Action</th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ($this->configurations as $configuration): ?>

				<tr class="bgColor3 <?= ($i++ % 2) ? 'odd' : 'even' ?>">

					<td style="text-align: center;">
						<!-- Edit configuration -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_cfg][' . $configuration->getUid() . ']=edit';
							$editOnClickParams .= '&noView=1';
							$redirectUrl = $extPath . 'mod1/index.php';
						?>
						<a title="Edit configuration" href="#" onclick="<?=htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?= $extPath ?>gfx/cog.png" alt="Edit configuration" />
						</a>
					</td>

					<td>
						<?= $configuration->getTitle(); ?>
					</td>

					<td class="l10ncfgPath">
						<?= current(t3lib_BEfunc::getRecordPath($configuration->getPid(), '1', 200, 50)); ?>
					</td>

					<td>
						<!-- Export XML -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_exportdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][exporttype]=xml';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][title]='.$configuration->getTitle();
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$redirectUrl = $extPath . 'export/index.php?tx_l10nmgrexport[action]=generateExport';
						?>
						<a title="Export XML" href="#" onclick="<?=htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?= $extPath ?>gfx/xml_export.png" alt="Export XML" />
						</a>

						<!-- Import XML -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_importdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&columnsOnly=configuration_id,importfiles';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
							$editOnClickParams .= '&defVals[tx_l10nmgr_importdata][configuration_id]='.$configuration->getUid();
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_importdata][configuration_id]='.$configuration->getUid();
							$redirectUrl = $extPath . 'import/index.php?tx_l10nmgrexport[action]=generateImport';
						?>
						<a title="Import XML" href="#" onclick="<?=htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?= $extPath ?>gfx/xml_import.png" alt="Import XML" />
						</a> |


						<!-- Export XLS -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_exportdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][exporttype]=xls';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][title]='.$configuration->getTitle();
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$redirectUrl = $extPath . 'export/index.php?tx_l10nmgrexport[action]=generateExport';
						?>
						<a title="Export XLS" href="#" onclick="<?=htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?= $extPath ?>gfx/xls_export.png" alt="Export XLS" />
						</a>

						<!-- Import XLS -->
						<a title="Import XLS" href="<?php echo $extPath . 'import/index.php?l10nmgr[configurationId]=' . $configuration->getUid() . '&l10nmgr[selectedExportFormat]=xls';?>">
							<img src="<?php echo $extPath;?>gfx/xls_import.png" alt="Import XLS" />
						</a> |

						<!-- Translate online -->
						<a title="Translate online" href="<?php echo $extPath . 'translate/index.php?l10nmgr[configurationId]=' . $configuration->getUid() . '&l10nmgr[selectedExportFormat]=inlineEdit';?>">
							<img src="<?php echo $extPath;?>gfx/pencil_go.png" alt="Translate online" />
						</a>
					</td>

				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>

	<?= $this->pagination->render(); ?>

</div>
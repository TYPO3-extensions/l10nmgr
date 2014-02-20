<?php if (!defined('TYPO3_MODE')) die ('Access denied.'); ?>

<?php $extPath = t3lib_div::resolveBackPath($GLOBALS['BACK_PATH'] .t3lib_extMgm::extRelPath('l10nmgr')); ?>

<div style="padding: 20px">

	<h2>Configurations</h2>
	<hr style="margin-bottom: 20px" />

	<table id="translationObjectList" style="border: 1px solid black;" class="typo3-dblist">
		<thead>
			<tr class="bgColor5 tableheader">
				<td>Info</td>
				<td>Title</td>
				<td>Path</td>
				<td>Action</td>
			</tr>
		</thead>

		<tbody>

			<?php foreach ($this->configurations as $configuration): ?>

				<tr class="bgColor3 <?php echo ($i++ % 2) ? 'odd' : 'even' ?>">

					<td style="text-align: center;">
						<!-- Edit configuration -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_cfg][' . $configuration->getUid() . ']=edit';
							$editOnClickParams .= '&noView=1';
							$redirectUrl = $extPath . 'mod1/index.php';
						?>
						<a title="Edit configuration" href="#" onclick="<?php echo htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?php echo $extPath ?>gfx/cog.png" alt="Edit configuration" />
						</a>
					</td>

					<td>
						<?php echo $configuration->getTitle(); ?>
					</td>

					<td class="l10ncfgPath">
						<?php echo current(t3lib_BEfunc::getRecordPath($configuration->getPid(), '1', 200, 50)); ?>
					</td>

					<td>
						<!-- Export XML -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_exportdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
						//	$editOnClickParams .= '&columnsOnly=l10n_cfgid,title,translation_lang,source_lang,checkforexistingexports,onlychangedcontent,nohidden,noxmlcheck,checkutf8';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][export_type]=xml';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][title]='.$configuration->getTitle();
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$redirectUrl = substr($extPath, 9) . 'export/index.php?tx_l10nmgrexport[action]=generateExport';
						?>
						<a title="Export XML" href="#" onclick="<?php echo htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?php echo $extPath ?>gfx/xml_export.png" alt="Export XML" />
						</a>

						<!-- Import XML -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_importdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&columnsOnly=configuration_id,force_target_lang,import_as_default_language,importfiles,import_type';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
							$editOnClickParams .= '&defVals[tx_l10nmgr_importdata][configuration_id]='.$configuration->getUid();
							$editOnClickParams .= '&defVals[tx_l10nmgr_importdata][import_type]=xml';
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_importdata][configuration_id]='.$configuration->getUid();
							$redirectUrl = substr($extPath, 9) . 'import/index.php?tx_l10nmgrexport[action]=generateImport';
						?>
						<a title="Import XML" href="#" onclick="<?php echo htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?php echo $extPath ?>gfx/xml_import.png" alt="Import XML" />
						</a> |


						<!-- Export XLS -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_exportdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][export_type]=xls';
							$editOnClickParams .= '&defVals[tx_l10nmgr_exportdata][title]='.$configuration->getTitle();
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_exportdata][l10ncfg_id]='.$configuration->getUid();
							$redirectUrl = substr($extPath, 9) . 'export/index.php?tx_l10nmgrexport[action]=generateExport';
						?>
						<a title="Export XLS" href="#" onclick="<?php echo htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?php echo $extPath ?>gfx/xls_export.png" alt="Export XLS" />
						</a>

						<!-- Import XLS -->
						<?php
							$editOnClickParams  = '&edit[tx_l10nmgr_importdata][' . $configuration->getPid() . ']=new';
							$editOnClickParams .= '&columnsOnly=configuration_id,force_target_lang,import_as_default_language,importfiles,import_type';
							$editOnClickParams .= '&returnEditConf=1';
							$editOnClickParams .= '&noView=1';
							$editOnClickParams .= '&defVals[tx_l10nmgr_importdata][configuration_id]='.$configuration->getUid();
							$editOnClickParams .= '&defVals[tx_l10nmgr_importdata][import_type]=xls';
							$editOnClickParams .= '&overrideVals[tx_l10nmgr_importdata][configuration_id]='.$configuration->getUid();
							$redirectUrl = substr($extPath, 9) . 'import/index.php?tx_l10nmgrexport[action]=generateImport';
						?>
						<a title="Import XLS" href="#" onclick="<?php echo htmlspecialchars(t3lib_BEfunc::editOnClick($editOnClickParams, $GLOBALS['BACK_PATH'], $redirectUrl)) ?>">
							<img src="<?php echo $extPath;?>gfx/xls_import.png" alt="Import XLS" />
						</a>  |

						<!-- Translate online -->
						<a title="Translate online" href="<?php echo $extPath . 'translate/index.php?tx_l10nmgrtranslate[configurationId]=' . $configuration->getUid() . '&tx_l10nmgrtranslate[selectedExportFormat]=inlineEdit';?>">
							<img src="<?php echo $extPath;?>gfx/pencil_go.png" alt="Translate online" />
						</a>
					</td>

				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>

	<?php echo $this->pagination->render(); ?>

</div>

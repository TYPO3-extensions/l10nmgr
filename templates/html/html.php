<form action="<?php echo $this->linkCreator->getActionLink('','saveTranslation')->setScriptPath('index.php')->useOverruledParameters()->makeUrl(); ?>" method="post">
<?php 
	/* @var $this  tx_l10nmgr_view_export_exporttypes_l10nHTMLList */
	$analyseMode = True;
	if ($this->l10ncfgObj->getDisplaymode() == 2)	{ 
		$analyseMode = False;
	}
				
	$translateableInformation = $this->getTranslateableInformation();
?>
<?php if($translateableInformation instanceof tx_l10nmgr_domain_translateable_translateableInformation):?>
	<?php foreach($translateableInformation->getPageGroups() as $pageGroup): ?>
		<?php $pageId = $pageGroup->getUid(); ?>
		<h3><?php echo htmlspecialchars($pageGroup->getPageTitle()); ?> [<?php echo $pageId; ?>]</h3>
	
		<table border="1" cellpadding="1" cellspacing="1" class="bgColor2" style="border: 1px solid #999999;">
			<?php foreach($pageGroup->getTranslateableElements() as $translateableElement): ?>
				<?php /* @var $translateableElement  tx_l10nmgr_domain_translateable_translateableElement  */ ?>
				<?php $table 		= $translateableElement->getTableName(); ?>
				<?php $elementUid 	= $translateableElement->getUid(); ?>
				<?php $editLink 	= $this->getEditLink($translateableElement); ?>
				<?php $flags 		= array();?>
				<?php $flags 		= $this->getFlagsForElement($translateableElement); ?>

				<?php 	//if mode only changed is active there need to be updated or unknow changes
						if(	
							(	$this->modeOnlyChanged && 
								(count($flags['new']) > 0) || (count($flags['update']) > 0 )) 
							|| 
							(!$this->modeOnlyChanged)
						): 		
				?>	
					<tr class="bgColor3">
						<td colspan="2" style="width:300px;">
							<?php  echo $this->linkCreator->getActionLink(htmlspecialchars($table.':'.$elementUid),'inlineTranslate')->setScriptPath('index.php')->setParameter('selectedTable',$table)->setParameter('selectedUid',$elementUid)->useOverruledParameters().' '.$editLink ?>
						</td>
						<td colspan="3" style="width:200px;"><?php if(is_array($flags) && $analyseMode){ echo htmlspecialchars(t3lib_div::arrayToLogString($flags)); } ?></td>
					</tr>
					
					<?php if($this->isSelectedItem($table,$elementUid)): ?>
						<tr class="bgColor5 tableheader">
							<td>Fieldname:</td>
							<td width="25%">Default:</td>
							<td width="25%">Translation:</td>
							<td width="25%">Diff:</td>
							<td width="25%">PrevLang:</td>
						</tr>
						<?php $fieldCount = 0; ?>
						
						<?php foreach($translateableElement->getTranslateableFields() as $translateableField): ?>
							<?php /* @var $translateableField tx_l10nmgr_domain_translateable_translateableField */ ?>
							<?php $key 	= $translateableField->getIdentityKey(); ?>	
							<tr>
								<td><b><?php echo htmlspecialchars($translateableField->getFieldName()); ?></b><em><?php echo htmlspecialchars($translateableField->getMessage()); ?></em></td>
								<td><?php echo nl2br(htmlspecialchars($translateableField->getDefaultValue())); ?></td>	
								<td><?php echo $this->modeWithInlineEdit ? ($translateableField->getFieldType()==='text' ? '<textarea name="'.htmlspecialchars('tx_l10nmgrtranslate[translation]['.$table.']['.$elementUid.']['.$key.']').'" cols="60" rows="5">'.t3lib_div::formatForTextarea($translateableField->getTranslationValue()).'</textarea>' : '<input name="'.htmlspecialchars('tx_l10nmgrtranslate[translation]['.$table.']['.$elementUid.']['.$key.']').'" value="'.htmlspecialchars($translateableField->getTranslationValue()).'" size="60" />') : nl2br(htmlspecialchars($translateableField->getTranslationValue())); ?></td>				
								<td><?php echo $this->getDiffString($translateableField); ?></td>
			
								<?php if(is_array($translateableField->getPreviewLanguageValues())): ?>
									<td><?php echo nl2br(htmlspecialchars(implode('\n',$translateableField->getPreviewLanguageValues()))); ?></td>
								<?php endif; ?>
							</tr>
							<?php $fieldCount++; ?>
						<?php endforeach; ?>
						<?php if($fieldCount > 0): ?>
							<tr> 
								<td colspan="5">
									<input type="hidden" name="tx_l10nmgrtranslate[pageid]" value="<?php echo intval($pageId); ?>" />
									<input type="submit" value="<?php echo $this->labels->get('general.action.save.button.title'); ?>" name="saveInline" onclick="return confirm(\'<?php echo $this->labels->get('inlineedit.save.alert.title'); ?>\');" />
									<input type="submit" value="<?php echo $this->labels->get('general.action.cancel.button.title'); ?>" name="_" onclick="return confirm(\'<?php echo $this->labels->get('inlineedit.cancel.alert.title'); ?> \');" />
								</td>
							</tr>	
						<?php endif; ?>			
					<?php endif; ?>		

				<?php endif; ?>
			<?php endforeach;?>
		</table>
	<?php endforeach; ?>
<?php endif; ?>
</form>
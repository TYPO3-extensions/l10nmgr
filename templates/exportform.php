<form action="<?= $this->linkCreator->getLink()->setAction($this->getRenderAction())->makeUrl(true); ?>" method="POST">
	<table>
		<tr>
			<td><strong><?=$this->labels->get('general.action.select.format.title'); ?></strong></td>
			<td><strong><?=$this->labels->get('general.action.language.select.title'); ?></strong></td>
			<td><strong><?=$this->labels->get('general.action.options.title'); ?></strong></td>
		</tr>
		<tr>
			<td>
				<select name="l10nmgr[selectedExportFormat]">
				<?php $availableFormats = $this->getAvailableExportFormats(); ?>
				<?php foreach ($availableFormats as $formatKey => $formatName){ ?>
					<?php if($formatKey == $this->getSelectedExportFormat()) { $optionAppend = ' selected="selected"'; }else{ $optionAppend = '';} ?>
						<option value="<?= $formatKey ?>"<?= $optionAppend ?>><?=$this->labels->get($formatName); ?></option>
					<?php } ?>
				</select>
			<td>
				<select name="l10nmgr[targetLanguageId]">
				<?php $languages = $this->getAvailableTargetLanguages(); ?>
				<?php foreach($languages as $languageKey => $languageName){ ?>	
					<option value="<?= htmlspecialchars($languageKey) ?>"><?= t3lib_div::deHSCentities(htmlspecialchars($languageName)); ?></option>
				<?php } ?>
				</select>
			</td>
			<td>
				<input type="checkbox" value="1" name="l10nmgr[onlyChangedContent]" /><?=$this->labels->get('export.xml.new.title');?>
				<input type="checkbox" value="1" name="l10nmgr[noHidden]" /><?=$this->labels->get('export.xml.noHidden.title');?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="l10nmgr[configurationId]"  value="<?= intval($this->getConfigurationId()) ?>"/>
	
	
	<br />
	<h4><?=$this->labels->get('export.xml.options.title');?></h4>

	<input type="checkbox" value="1" name="l10nmgr[checkForExistingExports]" /><?=$this->labels->get('export.xml.check_exports.title');?><br />
	<input type="checkbox" value="1" name="l10nmgr[noXMLCheck]" /><?=$this->labels->get('export.xml.no_check_xml.title');?><br />
	<input type="checkbox" value="1" name="l10nmgr[checkUTF8]" /><?=$this->labels->get('export.xml.checkUtf8.title');?><br />
	
	<?=$this->labels->get('export.xml.source-language.title');?>
	
	<select name="l10nmgr[sourceLanguageId]">
	<?php $languages = $this->getAvailableSourceLanguages(); ?>
	<?php foreach($languages as $key => $language){ ?>	
		<option value="<?= htmlspecialchars($key) ?>"><?= t3lib_div::deHSCentities(htmlspecialchars($language)); ?></option>
	<?php } ?>
	</select>
	<br />
	<br/>
	<input type="submit" value="Export" name="export_xml" />
	<br />
	<br />
	<br/>
</form>

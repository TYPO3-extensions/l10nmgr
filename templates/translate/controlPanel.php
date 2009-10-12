
<?php 
	/* @var $this tx_l10nmgr_view_translate_controlPanel */ 
	$form = $this->getForm();
?>	

<?php echo $this->formElementRenderer->getOpeningFormTagForForm($form, $this->linkCreator->getActionLink('','inlineTranslate')->setScriptPath('index.php') ); ?>

<table>
	<tr>
		<td><strong><?php echo $this->labels->get('general.action.language.select.title'); ?></strong></td>
		<td><strong><?php echo $this->labels->get('general.action.options.title'); ?></strong></td>
	</tr>
	<tr>
		<td><?php echo $this->formElementRenderer->renderElement($form->getElementByName('target_language')); ?></td>
		<td>
			<?php echo $this->labels->get('export.xml.new.title');?>
			<?php echo $this->formElementRenderer->renderElement($form->getElementByName('new_changed_only'));?>
				
			<?php echo $this->labels->get('export.xml.noHidden.title');?>
			<?php echo $this->formElementRenderer->renderElement($form->getElementByName('no_hidden'));?>
			
			<?php echo $this->formElementRenderer->renderElement($form->getElementByName('configurationId'));?>
			
			<?php echo $this->formElementRenderer->renderMVCHiddenFields(); ?>
			<?php echo $this->formElementRenderer->getSubmitButton(); ?>
		</td>
	</tr>
</table>
<?php echo $this->formElementRenderer->getClosingForm(); ?>

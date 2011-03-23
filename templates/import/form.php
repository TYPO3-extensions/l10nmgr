<form name="editForm">
<?php echo $this->labels->get ( 'general.import.xml.options.title' ); ?>
<input type="checkbox" value="1" name="make_preview_link" /><?php echo $this->labels->get ( 'import.xml.make_preview_link.title' );?><br />
<input type="checkbox" value="1" name="import_delL10N" /><?php echo $this->labels->get ( 'import.xml.delL10N.title' );?><br />
<br />
<strong><?php echo $this->labels->get ( 'general.action.import.xml.fileselect.title' );?></strong>
<br />
<?php echo $this->getUploadField(); ?>
<br />
<br />
<input type="submit" value="Import" name="import_xml" />
<input type="submit"
	value="<?php echo $this->labels->get ( 'general.action.refresh.button.title' );?>"
	name="_" />
<br />
<br />
</form>
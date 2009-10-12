<form name="editForm">
<? $this->labels->get ( 'general.import.xml.options.title' ); ?>
<input type="checkbox" value="1" name="make_preview_link" /><?=$this->labels->get ( 'import.xml.make_preview_link.title' );?><br />
<input type="checkbox" value="1" name="import_delL10N" /><?=$this->labels->get ( 'import.xml.delL10N.title' );?><br />
<br />
<strong><?=$this->labels->get ( 'general.action.import.xml.fileselect.title' );?></strong>
<br />
<?= $this->getUploadField(); ?>
<br />
<br />
<input type="submit" value="Import" name="import_xml" />
<input type="submit"
	value="<?=$this->labels->get ( 'general.action.refresh.button.title' );?>"
	name="_" />
<br />
<br />
</form>
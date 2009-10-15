<?php echo'<?xml version="1.0"?>'; ?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:html="http://www.w3.org/TR/REC-html40"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>ConfigurationUid:<?= $this->l10ncfgObj->getUid(); ?>|ExportDataUid:<?= $this->getTranslateableInformation()->getExportData()->getUid(); ?>|TargetLanguageUid:<?= $this->getTranslateableInformation()->getTargetLanguage()->getUid(); ?>|FormatVersion:<? echo L10NMGR_FILEVERSION; ?></Author>
  <LastAuthor>Office 2004 Test Drive User</LastAuthor>
  <Created>2006-12-01T02:10:16Z</Created>
  <Version>11.512</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <AllowPNG/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>14200</WindowHeight>
  <WindowWidth>28700</WindowWidth>
  <WindowTopX>-20</WindowTopX>
  <WindowTopY>-20</WindowTopY>
  <Date1904/>
  <AcceptLabelsInFormulas/>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Verdana"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s25">
   <Alignment ss:Vertical="Top"/>
  </Style>
  <Style ss:ID="s26">
   <Alignment ss:Vertical="Top"/>
   <Font ss:FontName="Verdana" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s27">
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="s35">
   <Font ss:FontName="Verdana" ss:Size="18.0" ss:Bold="1"/>
   <Interior ss:Color="#969696" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s37">
   <Font ss:FontName="Verdana" ss:Size="14.0" ss:Bold="1"/>
   <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s38">
   <Font ss:FontName="Verdana" ss:Bold="1"/>
   <Interior ss:Color="#969696" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s39">
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Interior ss:Color="#FFFF99" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Translation">

  <Table ss:ExpandedColumnCount="6" ss:ExpandedRowCount="<?= $this->getRowCount(); ?>" x:FullColumns="1" x:FullRows="1">

   <Column ss:Hidden="1" ss:AutoFitWidth="0"/>
   <Column ss:AutoFitWidth="0" ss:Width="85.0"/>
   <Column ss:AutoFitWidth="0" ss:Width="233.0"/>
   <Column ss:AutoFitWidth="0" ss:Width="233.0"/>
   <Column ss:AutoFitWidth="0" ss:Width="151.0"/>
   <Column ss:AutoFitWidth="0" ss:Width="233.0"/>
		<?= $this->getRenderedPageGroups(); ?>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
   </PageSetup>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
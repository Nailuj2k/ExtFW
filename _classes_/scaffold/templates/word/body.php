<?
//  echo 'Body...';
 /********
  require_once '../../_classes_/PHPOffice/PHPWord.php';
  define('EOL', '<br />');
  // New Word Document
  echo date('H:i:s') , " Create new PHPWord object" , EOL;
  $PHPWord = new PHPWord();
 
 
  // New portrait section
  $section = $PHPWord->createSection();
  
  // Add header
  $header = $section->createHeader();
  $table = $header->addTable();
  $table->addRow();
 
  $table->addCell(4500)->addImage('/home/asteamur/domains/asteamur.org/public_html/media/fotos/images/.big_logo_w.png', array('width'=>250, 'height'=>45, 'align'=>'left'));
  $table->addCell(4500)->addText('This is the header.', array(/ *'width'=>50, 'height'=>50, * /'align'=>'right'));
   // Add footer
  $footer = $section->createFooter();
  $footer->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'center'));










  // Write some text
  $section->addTextBreak();
  $section->addText( $this->format_item['begin']  ); 

  $section->addTextBreak();
  $section->addText(  str_replace($_item_tags, $_item_values, $this->format_item['body'])   );	

  $section->addTextBreak();
  $section->addText( $this->format_item['end']  ); 
********/	
?>
<?php

    ob_start();
    include(SCRIPT_DIR_MODULE.'/index.php');
    $html = ob_get_clean();

    $html = str_replace(['\n','\"'],['','"'], $html);


    /****

    //PDF::$pdf_watermark = './media/page/files/6/1658349746_hotel_arcos_viejo_02.jpg'; //
    //PDF::$pdf_watermark = 'https://example.com/media/works/images/2/.big_a4.jpg';
    //PDF::$pdf_watermark = 'https://example.com/media/slider/images/11.jpg';

    PDF::$pdf_filename = $pdf_filename?$pdf_filename:($_ARGS[3]?$_ARGS[3].'.pdf':time().'.pdf');
    PDF::$pdf_savedir = $pdf_savedir;
    PDF::$pdf_savefilename = $pdf_savefilename;

    */
    //  public static $html_pdf_page_num = true;
    //  public static $html_pdf_num_pages = 0;
    //  public static $html_pdf_footer = '</html>';
    //  public static $html_pdf_detail = '<p>line</p>';
    //  public static $pdf_paper_format = 'a4';
    //  public static $pdf_orientation  = 'portrait';

    // tests:
    //PDF::$pdf_orientation  = 'landscape';
    //PDF::$pdf_paper_format  = 'a5';

    PDF::html2pdf($html);

    exit(0);
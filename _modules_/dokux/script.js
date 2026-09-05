$(function() {     

});

var pdfjsLib = window['pdfjs-dist/build/pdf'];
var pdf_text = '';
//pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';
//pdfjsLib.GlobalWorkerOptions.workerSrc = '//intranet.hulamm.sms.carm.es/_lib_/pdf.js/pdf.worker.js';
pdfjsLib.GlobalWorkerOptions.workerSrc = '/_lib_/pdf.js/pdf.worker.js';

var pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 1.2,
    pdf_canvas = document.getElementById('the-canvas'),
    pdf_ctx = pdf_canvas.getContext('2d');
var pdf_filename =   '';

function renderPage(num) {
  pageRendering = true;
  // Using promise to fetch the page
  pdfDoc.getPage(num).then(function(page) {
    var viewport = page.getViewport({scale: scale});
    pdf_canvas.height = viewport.height;
    pdf_canvas.width = viewport.width;

    // Render PDF page into canvas context
    var renderContext = {
      canvasContext: pdf_ctx,
      viewport: viewport
    };
    var renderTask = page.render(renderContext);

    // Wait for rendering to finish
    renderTask.promise.then(function() {
      pageRendering = false;
      if (pageNumPending !== null) {
        // New page rendering is pending
        renderPage(pageNumPending);
        pageNumPending = null;
      }
    });
    
    // https://ourcodeworld.com/articles/read/405/how-to-convert-pdf-to-text-extract-text-from-pdf-with-javascript
    // The main trick to obtain the text of the PDF page, use the getTextContent method
    page.getTextContent().then(function (textContent) {
        var textItems = textContent.items;
        var finalString = "";
        // Concatenate the string of the item to the final string
        for (var i = 0; i < textItems.length; i++) {
            var item = textItems[i];
            finalString += item.str + " ";
        }
        // Solve promise with the text retrieven from the page
        //resolve(finalString);
        //console.log('::::::::::::::::: Página ',num);
        //console.log('['+finalString.trim()+']');
        if (finalString.trim()!='') {
            pdf_text = pdf_text + '\n\n::::::::: Página '+num+'\n\n'+ finalString.replaceAll('.    ','<br />')+' :::::::::';
        }else{
            pdf_text = false; //'<h3>No se ha encontrado texto en este PDF, quizá sea un documento escaneado como imagen, así que le vamos ha pasar el OCR. Espere un momento por favor.</h3>';
        }
        //console.log(pdf_text);

        if(pdf_text){
            $('#pdf-text').html(pdf_text);

            //FIX
            // Enviar por ajax:
            // - filename
            // - ext
            // - nº pagina [PAGENUM]
            // - texto
            // ¿ metadatos y palabras clave (dni, nombres, etc)  
            // ---> en el server: Guadar INBOX/ocr/filename-PAGENUM.txt


            /*******
            $.each($('#pdf-text').text().split(' '), function(i, text) {
                var nifRegex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
                var nieRegex = /^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
                //if (/\d{8}[a-z A-Z]/.test(text))  console.log('DNI: ',text);
                if (nifRegex.test(text))  console.log('NIF: ',text);
                if (nieRegex.test(text))  console.log('NIE: ',text);
            });
            ****/
        }else{
            if(pdf_filename){
            let ext = pdf_filename.substring(pdf_filename.lastIndexOf('.')+1); 
            let basename = pdf_filename.replace('.'+ext,''); 
            $('#pdf-text').html('<p>No se ha encontrado texto en este PDF, quizá sea un documento escaneado como imagen.<br />Pulse en <span class="file_ocr_help">Aa</span> para pasarle el OCR e intentar extraerle el texto.</p>');
            //console.log('pdf_filename', basename);
            //console.log('ulr_ocr', '/ajax/op=ocr/file='+basename);
            }
        }

    });  


  });

  // Update page counters
  document.getElementById('page_num').textContent = num;
}

/**
 * If another page rendering in progress, waits until the rendering is
 * finised. Otherwise, executes rendering immediately.
 */
function queueRenderPage(num) {
  if (pageRendering) {
    pageNumPending = num;
  } else {
    renderPage(num);
  }
}

/**
 * Displays previous page.
 */
function onPrevPage() {
  if (pageNum <= 1) {
    return;
  }
  pageNum--;
  queueRenderPage(pageNum);
}
document.getElementById('prev').addEventListener('click', onPrevPage);

/**
 * Displays next page.
 */
function onNextPage() {
  if (pageNum >= pdfDoc.numPages) {
    return;
  }
  pageNum++;
  queueRenderPage(pageNum);
}
document.getElementById('next').addEventListener('click', onNextPage);

/**
 * Asynchronously downloads PDF.
 */

function loadpdf(url){
    //console.log('loadpdf',url);
    pageNum = 1;
    pdf_text = '';
    var pdfData = atob(url);
    pdfjsLib.getDocument({data: pdfData}).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page_count').textContent = pdfDoc.numPages;
        // Initial/first page rendering
        renderPage(pageNum);
    });
}
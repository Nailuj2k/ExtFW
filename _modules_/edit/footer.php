<script type="text/javascript">

    $(function() {     

        var VS_THEME = '<?=VS_THEME?>';
        var files = new Object();
        var active_id = '';
        var files_visible = true;
        var search_visible = false;
        var sidebar_ai_visible = false;
        var split_screen = false;
        var split_active = 'file-editor';
        var currentSuggestion = "";
        var currentProviderDisposable = null; // Disposable del provider actual
        var aiService = localStorage.getItem('ai_service') || 'dummy'; // Servicio de IA seleccionado
        var aiStatusElement = null; // Elemento para mostrar estado
        var autoSuggestEnabled = false; // Sugerencias automáticas al escribir
        var debounceTimer = null; // Timer para debounce
        var chatHistory = JSON.parse(localStorage.getItem('ai_chat_history') || '{}'); // Historial persistente

        var liveshare_status = 'disconnected';

        const editor_window  = document.querySelector('#editor');
    
        <?php if(defined('CDN_URL') && CDN_URL) { ?>
        require.config({ paths: { 'vs': '<?=CDN_URL?>/_lib_/monaco-editor/min/vs' }});       
        <?php }else if(USE_CDN==true) { ?>
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        <?php } else {?>
        require.config({ paths: { 'vs': '<?=SCRIPT_DIR_LIB?>/monaco-editor/min/vs' }});
        <?php } ?>        

        require(['vs/editor/editor.main'], () => {

            if (VS_THEME !== 'vs' && VS_THEME !== 'vs-dark' && VS_THEME !== 'hc-black')
                monaco.editor.defineTheme(VS_THEME, <?php echo file_get_contents(SCRIPT_DIR_MODULE.'/themes/'.VS_THEME.'.json'); ?> );

        });

        const MonacoDefaultOptions = {
            theme: VS_THEME,
            automaticLayout: true,
            //minimap: { enabled: true },
            wordWrap: 'on',
            //suggestOnTriggerCharacters: true,
            //snippetSuggestions: 'top',
            fontLigatures: true,
            //wordWrap: "wordWrapColumn",
            //wordWrapColumn: 40,
            //wrappingIndent: "indent", try "same", "indent" or "none"
            folding:true,
            scrollbar: {verticalScrollbarSize: 7},
            stickyScroll: {
                enabled: true,
                maxLineCount: 5 // Puedes ajustar este número según tus necesidades
            }            
        };
 
        toggleFolder = (target,folder,childFolder) => {
            const visible = getComputedStyle(childFolder).display !== 'none';            
            folder.classList.toggle('folder-closed', visible);
            folder.classList.toggle('folder-open', !visible);
            if (visible) slideUp(childFolder); else slideDown(childFolder);        
            loadDir(target);
        }

        document.addEventListener('click', (e) => {
            let t = e.target
            if      (t.matches('.link-rootdir')) toggleFolder(t, t.closest('.root-folder'), document.querySelector('#file-tree>ul')  )
            else if (t.matches('.folder.link-dir'))   loadDir(e.target);
            else if (t.matches('.link-dir'))     toggleFolder(t, t.closest('.folder')     , t.closest('.folder').querySelector('ul') )
        });

        $('#editor').on('click', '#btn-liveshare',function() {           

            if( liveshare_status == 'disconnected' ){

                liveshare_status = 'sconnected';
                $(this).attr('src','/_images_/icons/connect.svg');

            }else{

                liveshare_status = 'disconnected';
                $(this).attr('src','/_images_/icons/disconnect.svg');

            }
        });
        
        
        
        $('#editor').on('click', '#btn-search',function() {   
            
            if(files_visible){

                $('#file-search').show();
                $('#file-tree').hide();
                $('#file-search').css('width','310px')
                //$('#file-editor-wrapper').css('left','350px')

                search_visible = true;
                files_visible = false;
            }else{
                if(search_visible){
                    $('#file-search').hide();
                    $('#file-search').css('width','0px')
                    $('#file-editor-wrapper').css('left','35px')
                    search_visible = false;
                }else{
                    $('#file-search').show();
                    $('#file-tree').css('width','0px')
                    $('#file-search').css('width','310px')
                    $('#file-editor-wrapper').css('left','350px') 
                    search_visible = true;
                }
            }

        });

        // Función de búsqueda en archivos
        function doSearch() {
            const query = document.querySelector('#search-input').value.trim();
            if (query.length < 2) {
                show_info('top-right', 'Enter at least 2 characters', 3000);
                return;
            }

            const resultsDiv = document.querySelector('#file-search .search-results');
            const infoDiv = document.querySelector('#file-search .search-info');
            resultsDiv.innerHTML = '<div class="search-loading">Searching...</div>';
            infoDiv.innerHTML = '';

            const formData = new URLSearchParams();
            formData.append('op', 'search');
            formData.append('query', query);
            formData.append('token', '<?=$_SESSION['token']?>');
            formData.append('rootdir', '<?=$root_dir?>');

            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = '';

                if (data.error == 0) {
                    // Mostrar info de resultados
                    infoDiv.innerHTML = `<span class="match-count">${data.matches} results</span> in <span class="file-count">${countUniqueFiles(data.files)} files</span>`;

                    // Agrupar resultados por archivo
                    const grouped = groupByFile(data.files);

                    for (const [filePath, matches] of Object.entries(grouped)) {
                        const fileGroup = document.createElement('div');
                        fileGroup.className = 'search-file-group';

                        // Header del archivo (colapsable)
                        const firstMatch = matches[0];
                        const relativePath = firstMatch.file.replace('<?=$root_dir?>/', '');
                        const replacedPath = firstMatch.path.replace('<?=$root_dir?>/', '');
                        fileGroup.innerHTML = `
                            <div class="search-file-header" data-id="${firstMatch.id}" data-file="${firstMatch.file}">
                                <span class="collapse-icon">▼</span>
                                <span class="file-icon file-icon-${firstMatch.ext}"></span>
                                <span class="file-name">${firstMatch.basename}</span>
                                <span class="file-path">${replacedPath}</span>
                                <span class="match-badge">${matches.length}</span>
                            </div>
                            <div class="search-file-matches">
                                ${matches.map(m => `
                                    <div class="search-match" data-id="${m.id}" data-file="${m.file}" data-line="${m.line}">
                                        <span class="line-number">${m.line}</span>
                                        <span class="line-content">${highlightMatch(escapeHtml(m.content), query)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                        resultsDiv.appendChild(fileGroup);
                    }

                    if (data.matches === 0) {
                        resultsDiv.innerHTML = '<div class="search-no-results">No results found</div>';
                    }
                } else {
                    show_error(editor_window, data.msg, 5000);
                }
            })
            .catch(error => { show_error(editor_window, error, 5000); });
        }

        // Helpers para búsqueda
        function groupByFile(files) {
            return files.reduce((acc, file) => {
                const key = file.file;
                if (!acc[key]) acc[key] = [];
                acc[key].push(file);
                return acc;
            }, {});
        }

        function countUniqueFiles(files) {
            return new Set(files.map(f => f.file)).size;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function highlightMatch(text, query) {
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }

        // Enter para buscar
        $('#editor').on('keydown', '#search-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch();
            }
        });

        // Click en botón buscar
        $('#editor').on('click', '#btn-do-search', doSearch);

        // Click en header de archivo (colapsar/expandir)
        $('#editor').on('click', '.search-file-header', function(e) {
            const group = $(this).closest('.search-file-group');
            const matches = group.find('.search-file-matches');
            const icon = $(this).find('.collapse-icon');
            matches.slideToggle(150);
            icon.text(matches.is(':visible') ? '▶' : '▼');
        });

        // Click en resultado para abrir archivo en la línea
        $('#editor').on('click', '.search-match', function() {
            const id = $(this).data('id');
            const file = $(this).data('file');
            const line = $(this).data('line');
            openFileAtLine(file, id, line);
        });

        // Función para abrir archivo en línea específica
        function openFileAtLine(filePath, id, lineNumber) {
            // Función para saltar a la línea
            function goToLine() {
                if (files[id] && files[id].editor) {
                    files[id].editor.revealLineInCenter(lineNumber);
                    files[id].editor.setPosition({ lineNumber: lineNumber, column: 1 });
                    files[id].editor.focus();
                    return true;
                }
                return false;
            }

            // Si el archivo ya está abierto, solo ir a la línea
            if (files[id] && files[id].editor) {
                $('#li-' + id).trigger('click');
                goToLine();
                return;
            }

            // Si no está abierto, simular click en el file-tree
            const fileTreeElement = document.querySelector('#li-' + id);
            if (fileTreeElement) {
                fileTreeElement.click();

                // Esperar a que el archivo cargue y luego ir a la línea
                let attempts = 0;
                const checkInterval = setInterval(() => {
                    attempts++;
                    if (goToLine() || attempts > 50) { // máx 5 segundos
                        clearInterval(checkInterval);
                    }
                }, 100);
            } else {
                show_error(editor_window, 'File not found in tree: ' + filePath, 5000);
            }
        }

        $('#editor').on('click', '#btn-files',() => {     
            console.log('FILES CLICKED',files_visible,search_visible)
            if(search_visible===true){
                $('#file-search').hide();
                $('#file-tree').show();
                $('#file-tree').css('width','310px')
                $('#file-editor-wrapper').css('left','350px')
                search_visible = false;
                files_visible = true;
            }else {
                files_visible = !files_visible;
                if(files_visible){
                    $('#file-tree').css('width','310px')
                    $('#file-editor-wrapper').css('left','350px')
                }else{
                    $('#file-tree').css('width','0px')
                    $('#file-editor-wrapper').css('left','35px')
                }
            }
        });

        $('#editor').on('click', '#btn-ai',() => {     
            if(!active_id) {

                $("body").dialog({
                    title:  'AI Assistant',
                    type: 'html',
                    width: '400px',
                    //height: '350px',
                    content: '<p style="margin:20px;">Please open a file to use the AI assistant.</p>',
                    buttons: [$.dialog.closeButton]
                });

            }else{

                sidebar_ai_visible = !sidebar_ai_visible;
                if(sidebar_ai_visible){
                    //let suggestion = files[active_id].value; 
                    //showSuggestion(suggestion) 
                    $('#editor-sidebar-right').css('width','300px')
                    $('#file-editor-wrapper').css('right','301px')
                }else{
                    $('#editor-sidebar-right').css('width','0px')
                    $('#file-editor-wrapper').css('right','0px')
                }

            }
        });

        document.getElementById('btn-new-file').addEventListener('click', () => {
            show_modal('file')
            DLG_title.innerHTML = 'Nuevo archivo'
            DLG_window.querySelector('#new-filetype').style.display = 'block'
            DLG_window.querySelector('#new-filename').setAttribute('placeholder','Nombre del archivo')
            DLG_window.querySelector('#new-filename').focus()
            DLG_create.onclick     = function(e) { create_file(e) }
        });

        document.getElementById('btn-new-folder').addEventListener('click', () => {
            show_modal('folder')
            DLG_title.innerHTML = 'Nueva carpeta'
            DLG_window.querySelector('#new-filetype').style.display = 'none'
            DLG_window.querySelector('#new-filename').setAttribute('placeholder','Nombre de la carpeta')
            DLG_window.querySelector('#new-filename').focus()
            DLG_create.onclick  = function(e) { create_folder(e) }
        });     

        add_file = (file,parent) => {

            let node = document.createElement('li');
            let li_class = file.ext=='DIR' ? 'link-dir' :  `link-file file-${file.ext}`;
            let li_type = file.ext=='DIR' ? 'folder' : file.type;            
            if(file.type=='image' || file.type=='audio' || file.type=='font') file.file = file.file.replace('<?=$_SERVER['DOCUMENT_ROOT']?>','')
            let li =    `<a class = "${li_class} file-context-menu" 
                               id = "li-${file.id}"
                          data-id = "${file.id}" 
                        data-size = "${file.size}" 
                    data-basename = "${file.basename}" 
                      data-parent = "${parent.id}" 
                        data-path = "${file.path}" 
                      data-nfiles = "${file.nfiles}"               
                         data-ext = "${file.ext}"                  
                        data-file = "${file.file}"  
                    data-filename = "${file.basename}"  
                         data-dir = "${file.path}/${file.basename}"
                        data-type = "${li_type}">${file.basename}</a>
                      <span class = "file-size">${formatBytesColorized(file.size)}</span>`        

            if(file.ext=='DIR') node.classList.add( 'folder','folder-closed'  )                                   
                           else node.classList.add( `icon-${file.ext}`      )                                   
            node.innerHTML = li;
            parent.appendChild(node);          

            if (file.files){
                var ul = document.createElement('ul');
                ul.classList.add('ul-child');
                ul.dataset.parent = file.id;
                ul.id = 'child-of-'+file.id        
                ul.style.display = 'none';
                for (var key in file.files){
                    add_file(file.files[key],ul)
                }
                node.appendChild(ul);
            }
        }

        document.getElementById('btn-refresh').addEventListener('click', () => {
            const formData = new URLSearchParams();
            let node = document.getElementById('child-of-<?=md5($root_dir)?>') 
            node.innerHTML = '<div class="loader"></div>'
            formData.append('op', 'dir');
            formData.append('token', '<?=$_SESSION['token']?>');       
            formData.append('rootdir','<?=$root_dir?>')
            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                node.innerHTML = ''
                if (data.error==0) {  
                    for (var key in data.files) add_file(data.files[key],node)   
                    show_info('top-right',data.msg,5000);
                }else{
                    show_error(editor_window,data.msg,5000);
                }                
            }).then(data => {
                loadDir( document.querySelector('#li-<?=md5($root_dir)?>')) 
            })
            .catch(error => {  show_error(editor_window,error,5000);  });     
        });

        rename_dir = (e) => {

            let filename = '<?=$root_dir?>/'+e.dataset.basename
            let newname = '<?=$root_dir?>/'+e.textContent                                
            let message_error = ''

            if (!filename)         message_error = 'Falta el nombre para el archivo origen'
            if (!newname)          message_error = 'Falta el nombre para el archivo destino'
            if (filename==newname) message_error = 'Los nombres de origen y destino son iguales'

            if(message_error!=''){
                show_error(editor_window,message_error,5000);
                return;
            }

            const formData = new URLSearchParams();
            formData.append('op', 'renamedir');
            formData.append('filename', filename); 
            formData.append('newname', newname);       
            formData.append('token', '<?=$_SESSION['token']?>');       
            formData.append('rootdir','<?=$root_dir?>')
            //rmData.append('text', crypt2str(text,'<?=$_SESSION['token']?>' ) );
            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error==0) {  
                    e.dataset.id = data.id
                    e.dataset.basename = e.textContent   
                    e.dataset.file = e.dataset.path+'/'+e.dataset.basename
                    e.id = 'li-'+data.id
                    show_info(editor_window,data.msg,5000);
                }else{
                    e.textContent = e.dataset.basename
                    show_error(editor_window,data.msg,5000);
                }
            })
            .then(data => {
                document.getElementById('new-filename').value = ''; 
                close_modal(e);            
            })
            .catch(error => {
                show_info(editor_window,error,5000);
            });     
        }  

        create_file = (e) => { 
            const filename = document.getElementById('new-filename').value;
            const filetype = document.getElementById('new-filetype').value;
            
            if (!filename) {
                show_error(editor_window,'Falta el nombre para el archivo',5000);
                return;
            }

            let finalFilename = filename;
            if (!finalFilename.includes('.')) finalFilename += '.' + filetype;
            
            const formData = new URLSearchParams();
            formData.append('op', 'newfile');
            formData.append('filename', '<?=$root_dir?>/'+finalFilename);   
            formData.append('content', '');       
            
            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {

                let li =`<a id = "li-${data.id}" 
                      data-id = "${data.id}" 
                  data-parent = "${data.parent}" 
                    data-path = "${data.path}" 
                    data-file = "${data.file}" 
                data-basename = "${data.basename}" 
                data-filename = "${data.filename}" 
                     data-ext = "${data.ext}" 
                    data-type = "code" 
                        class = "link-file file-${data.ext}">${data.basename}</a>
                   <span class="file-size">${formatBytesColorized(data.size)}</span>`
                
                var nodo = document.createElement('li');
                nodo.innerHTML = li;
                nodo.classList.add(`icon-${data.ext}`)
                document.getElementById('child-of-'+data.parent).appendChild(nodo);
                if (data.error==0) show_info (editor_window,data.msg,5000);
                else               show_error(editor_window,data.msg,5000);

            })
            .then(data => {
                document.getElementById('new-filename').value = ''; 
                close_modal(e);            
            })
            .catch(error => {
                show_error(editor_window,error,5000);
            });     
        }  
 
        create_folder = (e) => { 
            const foldername = document.getElementById('new-filename').value;           
            if (!foldername) {
                show_error(editor_window,'Falta el nombre para la carpeta',5000);
                return;
            }            
            const formData = new URLSearchParams();
            formData.append('op', 'newfolder');
            formData.append('foldername', '<?=$root_dir?>/'+foldername);              
            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                $('#btn-refresh').click()
                if (data.error==0) show_info (editor_window,data.msg,5000);
                else               show_error(editor_window,data.msg,5000);
            })
            .then(data => {
                document.getElementById('new-filename').value = ''; 
                close_modal(e);            
            })
            .catch(error => {
                show_error(editor_window,error,5000);
            });     
        }  

        update_tabs = (id) => { //writes in <div> with id=output
            if (id in files ) {
                files[id].modified = true;
                $('#li-'+id+', #btn-file-'+id).removeClass('saved').addClass('modified'); 
            }
        }

        rename_file = (e) => {

            let filename = '<?=$root_dir?>/'+e.dataset.basename
            let newname = '<?=$root_dir?>/'+e.textContent                                
            let message_error = ''
            
            if (!filename)         message_error = 'Falta el nombre para el archivo origen'
            if (!newname)          message_error = 'Falta el nombre para el archivo destino'
            if (filename==newname) message_error = 'Los nombres de origen y destino son iguales'
            if (e.dataset.id in files ) message_error = 'El archivo está siendo editado'

            if(message_error!=''){
                show_error(editor_window,message_error,5000);
                return;
            }
            
            const formData = new URLSearchParams();
            formData.append('op', 'renamefile');
            formData.append('filename', filename); 
            formData.append('newname', newname);       
            formData.append('token', '<?=$_SESSION['token']?>');       
            formData.append('rootdir','<?=$root_dir?>')
            //rmData.append('text', crypt2str(text,'<?=$_SESSION['token']?>' ) );
         
            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error==0) {  
                                     
                    let icon = document.querySelector('#icon_'+e.dataset.id);
                    //let tab  = document.querySelector('#btn-file-'+e.dataset.id);
                    if(icon){
                        console.log('ICON.FIND','#icon_'+e.dataset.id)
                        icon.querySelector('.folder-name').textContent = e.textContent
                        icon.id = 'icon_'+data.id
                    }
                    const f = splitNameAndExtension(e.textContent);
                    
                    if (e.dataset.id in files )  {
                        if(e.dataset.id==active_id) active_id=data.id
                        files[data.id] = files[e.dataset.id]
                        files[data.id].file = e.dataset.path+'/'+e.textContent
                        files[data.id].filename = f.file
                        files[data.id].ext = f.ext
                        delete files[e.dataset.id]
                    } 
                    /*
                    if(tab){    // exists only if renamed file is open
                        tab.innerHTML = tab.innerHTML.replace( e.dataset.basename, e.textContent )                        
                        if (document.querySelector('#top-filename').innerHTML == e.dataset.file)
                            document.querySelector('#top-filename').innerHTML = e.dataset.path+'/'+e.textContent;
                    }
                    */
                    e.dataset.basename = e.textContent
                    e.dataset.filename = f.name;    
                    e.dataset.file = e.dataset.path+'/'+e.dataset.basename
                    e.dataset.id = data.id
                    e.id = 'li-'+data.id
                    if(icon){                   
                        let icon_file = icon.querySelector('.file');
                        icon_file.dataset.basename = e.textContent
                        icon_file.dataset.filename = f.name;    
                        icon_file.dataset.file = e.dataset.path+'/'+e.dataset.basename
                        icon_file.dataset.id = data.id
                    }
                    /*
                    if(tab){
                        tab.id = 'btn-file-'+data.id
                        tab.dataset.id = data.id
                        tab.dataset.basename = e.textContent
                        tab.dataset.filename = f.name;    
                        tab.dataset.file = e.dataset.path+'/'+e.dataset.basename
                    }
                    */
                    if(e.dataset.ext!==f.ext){
                        e.classList.removeClass('f-'+e.dataset.ext).addClass('f-'+f.ext);  ///////FIX
                        e.dataset.ext = f.ext;
                        if(icon){ 
                            icon_file.textContent  =  e.ext    
                            icon_file.classList.removeClass('f-'+icon.dataset.ext).addClass('f-'+f.ext);
                            icon_file.dataset.ext = f.ext;
                        }
                        //if(tab) tab.dataset.ext = f.ext;
                    }

                    show_info(editor_window,data.msg,5000);

                }else{

                    e.textContent = e.dataset.basename
                    show_error(editor_window,data.msg,5000);

                }
                
            })
            .then(data => {
                document.getElementById('new-filename').value = ''; 
                close_modal(e);            
            })
            .catch(error => {
                show_error(editor_window,error,5000);
            });     
        }  
         
        var cut_file, cut_from,cut_li,cut_to,paste_ul = false;

        move_file = (e) => {

            let message_error = ''

            if(cut_from===cut_to || cut_file===false){

                if (e.dataset.id in files ) message_error = 'El archivo está siendo editado'

                if(message_error!=''){
                    show_error(editor_window,message_error,5000);
                    return;
                }

                cut_li = e.closest('li')
                cut_from = e.dataset.path
                cut_file = e.dataset.basename
                cut_to = false
                //console.log('CUT', cut_from, cut_to, cut_file )

                fadeOut(cut_li, 1000)

            }else{                       

                paste_ul =  e.dataset.ext === 'DIR' ? e.closest('li').querySelector('ul') : e.closest('ul')
                cut_to = e.dataset.ext === 'DIR' ? e.dataset.file : e.dataset.path;

                if(cut_from===cut_to) {
                    //console.log('SAME',  cut_from, cut_to, cut_file )
                } else {

                    let message_error = ''
                    if (!cut_from)   message_error = 'Falta el origen para el archivo'
                    if (!cut_to)     message_error = 'Falta el destino para el archivo'
                    if (!cut_file)   message_error = 'Falta el archivo'

                    // console.log('PASTE', cut_from, cut_to, cut_file )

                    if(message_error!=''){
                        show_error(editor_window,message_error,5000);
                        return;
                    }

                    const formData = new URLSearchParams();
                    formData.append('op', 'movefile');
                    formData.append('from_dir', cut_from);  
                    formData.append('to_dir', cut_to);  
                    formData.append('filename', cut_file);  
                    formData.append('token', '<?=$_SESSION['token']?>');       
                    formData.append('rootdir','<?=$root_dir?>')                
                    fetch('<?=MODULE?>/ajax', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error==0) {  
                            cut_li.querySelector('.file-context-menu').dataset.path = cut_to
                            cut_li.querySelector('.file-context-menu').dataset.file = cut_to + '/' + e.dataset.basename
                            paste_ul.appendChild(cut_li)    
                            fadeIn(cut_li, 1000) 
                            show_info(editor_window,data.msg,5000);
                        }else{
                            show_error(editor_window,data.msg,5000);
                        }                      
                    })
                    .catch(error => {
                        show_error(editor_window,error,5000);
                    });     

                }
                cut_from,cut_to,cut_file = false;
            }  

        }

        delete_file = (e) => {

            let filename = e.dataset.file;  // '<?=$root_dir?>/'+e.dataset.basename       
            let ext      = e.dataset.ext;                                                   
            let message_error = ''
            if (!filename)              message_error = 'Falta el nombre para el archivo'
            if (e.dataset.id in files ) message_error = 'El archivo está siendo editado'

            if(message_error!=''){
                show_error(editor_window,message_error,5000);
                return;
            }
            
            if (e.dataset.id in files )   delete files[e.dataset.id]

            const formData = new URLSearchParams();
            formData.append('op', ext=='DIR'?'deletedir':'deletefile');
            formData.append('filename', filename);  
            formData.append('token', '<?=$_SESSION['token']?>');       
            formData.append('rootdir','<?=$root_dir?>')
            //rmData.append('text', crypt2str(text,'<?=$_SESSION['token']?>' ) );
         
            fetch('<?=MODULE?>/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error==0) {  
                           
                    if (e.dataset.id in files )   delete files[e.dataset.id]
                    e.parentNode.classList.add('deleted','animated','hinge');

                    let icon = document.querySelector('#icon_'+e.dataset.id);
                    //let tab  = document.querySelector('#btn-file-'+e.dataset.id);

                    if(icon) icon.classList.add('deleted','animated','hinge');
                    //if(tab) tab.classList.add('deleted');

                    show_info(editor_window,data.msg,5000);

                    setTimeout(function(){
                        e.parentNode.remove()
                        icon.remove()
                    },1800)
                    
                }else{

                    e.textContent = e.dataset.basename
                    show_error(editor_window,data.msg,5000);

                }
                
            })
            .then(data => {
                document.getElementById('new-filename').value = ''; 
                close_modal(e);            
            })
            .catch(error => {
                show_error(editor_window,error,5000);
            });     

        }

        $('.inner').on('click', '.file-code',function(event) {      //FIX
            let type  = $(this).data('type');       
            let obj = event.target
            handleClick (type,false,obj);            
        });       

        // CLICK file is open split_NOactive
        $('.inner').on('click', '.link-file',function(event) {               
            let type  = $(this).data('type');       
            let closing =   $(event.target).hasClass('fa-close')
            let obj =  closing || type !='code' ? $(this) : document.querySelector('#'+event.target.id); 

            //console.log('CLICK', split_active,  $('#monaco-container-'+event.target.dataset.id).closest('.file-editor').attr('id') )
            
            let obj_split_editor = $('#monaco-container-'+event.target.dataset.id).closest('.file-editor').attr('id') 

            if(obj_split_editor=='file-editor' ||obj_split_editor=='file-editor-right' )
                if( split_active != obj_split_editor  )
                    split_active = obj_split_editor

            handleClick (type,closing,obj);            
        });
        
        update_position = (position) => { //writes in <div> with id=output
            document.querySelector('#editor-status-bar #position').innerHTML = `Line <span>${position.lineNumber}</span>, Col <span>${position.column}</span>`
        }

        handleClick = (type,closing,obj) => {     
            if(!closing) {
                $('#'+split_active+' .editor-center,#'+split_active+' .audio-viewer,#'+split_active+' .dir-viewer,#'+split_active+' .font-viewer,#'+split_active+' .image-viewer,#'+split_active+' .text-viewer').hide();
                $('#file-tree .link-file,#'+split_active+' .btn-tabs .link-file').removeClass('active');            //BUTTON   //SPLIT btn-tabs child of active split
            }
            if       (type=='code' && closing ) closeCode(obj);
            else if  (type=='code' )            loadCode (obj);             
            else if  (type=='image')            loadImage(obj);
            else if  (type=='audio')            loadAudio(obj);
            else if  (type=='font' )            loadFont (obj);            
            else if  (type=='zip'  )            loadTextFile  (obj);            
            else if  (type=='csv'  )            loadTextFile  (obj);    
            else                                loadUnknownFile(obj);        
        }

        closeCodeFromId = (id) => {
            $('#li-'+id+', #btn-file-'+id).removeClass('active').removeClass('open').removeClass('modified').removeClass('saved');    //BUTTON
            $('#'+id+', #btn-file-'+id).remove();
            $('#monaco-container-'+id).remove();
            delete files[id];
            if(id==active_id) $('#'+split_active+' .btn-tabs .link-file:first-child').trigger('click')         //SPLIT btn-tabs child of active split               
        }

        closeCode = (obj) => {
            let id    = obj.data('id');
            if(files[id].modified==true) 
                $.modalform({ 'html':'Este archivo ha sido modificado. Si lo cierras se perderán los cambios. Luego no te quejes.', 'buttons':'ok cancel'}, function(accept) { if(accept) closeCodeFromId(id) });
            else
                closeCodeFromId(id)
        }

        loadDir = (obj) => {  
   
            let id      = obj.dataset.id;   
            let dir     = obj.dataset.dir      
            let path    = obj.dataset.path      
          //let id_path = obj.data('id_path');      
            let parent  = obj.dataset.parent

            //$('#file-editor>div').hide();
            $('#'+split_active+' .editor-center,#'+split_active+' .audio-viewer,#'+split_active+' .dir-viewer,#'+split_active+' .font-viewer,#'+split_active+' .image-viewer,#'+split_active+' .text-viewer').hide();
            
            $('#top-filename').html(id+' :: '+dir);
            if (id in files )  {
                $('#'+id).show();
            }else{
                
                files[id] = {};//document.getElementById(id);
                files[id].id = id;
                files[id].path = path;
              //files[id].id_path = id_path;
                files[id].parent = parent;
                files[id].dir = dir;
                
                //SPLIT
                $('#'+split_active).append('<div id="'+id+'" class="dir-viewer" style="color:#fdfdfd;"></div>').show();

                let parent_id = $('#li-'+id).closest('.link-dir').data('parent');

                //if (dir!='<?=$root_dir?>'){
                //    console.log(dir,'==','<?=$root_dir?>');
                //    //KO $('#file-editor #'+id).append('<span class="li-folder"><span class="icon folder link-dir" data-id="'+parent_id+'" data-XXdir="'+dir+'" data-dir="<?=$root_dir?>" title="'+dir+'"><span class="folder-name">..</span></span></span>' );
                //    $('#file-editor #'+id).append('<span class="li-folder" id="icon_'+id_path+'"><span class="icon folder link-dir" data-id="'+id_path+'"  data-dir="'+path+'" title="'+path+'"><span class="folder-name">..</span></span></span>' );
                //}
                
                $('#child-of-'+id+'>li>a.link-dir').each(function( index, element ) {
                    let n    = $(element).data('nfiles');
                    let fid  = $(element).data('id');
                    let fdir = $(element).data('dir');
                    let fsize= $(element).data('size');
                    let bname= $(element).data('basename');
                    let text = $(element).text
                    //SPLIT
                    $('#'+split_active+' #'+id).append('<span class="li-folder" id="icon_'+fid+'"><span class="icon folder '+(n?'full':'')+' link-dir" data-id="'+fid+'" data-dir="'+fdir+'" title="'+n+' items. '+fsize+'"><span class="folder-name">'+bname+'</span></span></span>' );
                });

                $('#child-of-'+id+'>li>a.link-file').each(function( index, element ) {
                    let n    = $(element).data('nfiles');
                    let fid  = $(element).data('id');
                    let file = $(element).data('file');
                    let ext  = $(element).data('ext');
                    let fsize= $(element).data('size');
                    let bname= $(element).data('basename');
                    if(ext=='jpg'||ext=='gif'||ext=='png'||ext=='webp'||ext=='svg')  //SPLIT
                        $('#'+split_active+' #'+id).append('<span id="icon_'+fid+'" class="li-folder-img checkerboard_no"><div class="img-thumb"><img src="'+file+'"></div><span class="folder-name">'+bname+'</span></span>' );
                    else if(ext=='mp3'||ext=='wav') //SPLIT
                        $('#'+split_active+' #'+id).append('<span id="icon_'+fid+'" class="li-folder-audio checkerboard_no"><audio><source src="'+file+'" type="audio/mp3"></audio><i style="cursor:pointer;" class="fa fa-play"></i><span class="folder-name">'+bname+'</span></span>' );
                    else if (ext=='php'||ext=='html'||ext=='css'||ext=='js'||ext=='sql'||ext=='txt'||ext=='py'||ext=='json'||ext=='htaccess'||ext=='log')
                        $('#'+split_active+' #'+id).append('<span id="icon_'+fid+'" class="li-folder"><span class="icon file file-code f-'+ext+'" '
                                                         +'data-id="'+fid+'" '
                                                       +'data-path="'+$(element).data('path')+'" '
                                                       +'data-file="'+file+'" '
                                                   +'data-basename="'+bname+'" '
                                                   +'data-filename="'+$(element).data('filename')+'" '
                                                        +'data-ext="'+ext+'" '
                                                       +'data-type="code" '
                                                           +'title="'+file+'">.'+ext+'</span>'
                                                     +'<span class="folder-name">'+bname+'</span></span>' );
                    else //SPLIT
                        $('#'+split_active+' #'+id).append('<span class="li-folder"><span class="icon file f-'+ext+'" data-id="'+fid+'" title="'+file+'" data-file="'+file+'" >.'+ext+'</span>'
                                                     +'<span class="folder-name">'+bname+'</span></span>' );
                });
            }
        }

        loadAudio = (obj) => {  
            // FIX count image-viewer class and apply jQuery remove() if too much  OR limit a maximun
            let id       = obj.data('id');   
            let file     = obj.data('file');      
            $('#top-filename').html(file);
            if (id in files ) $('#'+id).show();     //SPLIT
                         else $('#'+split_active).append(`<div id="${id}" class="audio-viewer checkerboard"><audio><source src="${file}" type="audio/mp3"></audio><i style="cursor:pointer;" class="fa fa-play"></i><div class="id3"></div><img class="cover"></div>`).show();                   
            jsmediatags.read('https://tienda.extralab.net'+file, {  // console.log('TAG FROM','https://tienda.extralab.net'+file)
                onSuccess: function(tag) {                          // https://github.com/aadsm/jsmediatags?tab=readme-ov-file
                    var tags = tag.tags;                            // console.log(tag);
                    let img ='';
                    $('#'+id+' .id3').html(tags.artist + "<br>" + tags.title + "<br>" + tags.album);
                    if(tags.picture){         
                        const { data, format } = tags.picture;
                        let base64String = "";
                        for (let i = 0; i < data.length; i++) { base64String += String.fromCharCode(data[i]);}
                        $('#'+id+' .cover').attr('src', `data:${data.format};base64,${window.btoa(base64String)}`)
                    }else{
                        show_info(editor_window,'NO PICTURE',5000);
                    }
                },
                onError: function(error) {
                    show_error(editor_window,error,5000);
                }
            });
        }

        loadTextFile = (obj) => {
            let id       = obj.data('id');   
            let file     = obj.data('file');      

            $('#top-filename').html(file);

            $('#'+split_active).append(`<div id="${id}" class="text-viewer">loading file ${file}</div>`).show();   //SPLIT
            
            $.ajax({
                method: "POST",
                url: "<?=MODULE.'/ajax'?>",
                data: { 'op': 'getfile', 'file':file },
                dataType: "json",
                beforeSend: function( xhr, settings ) { }
            }).done(function( data ) {
                let text = crypt2str(data.text,'<?=$_SESSION['token']?>' );
                $('.text-viewer').hide();
                $('#'+split_active).append(`<div class="text-viewer" id="${id}">${text}</div>`).show();     //SPLIT
            }).fail(function(data) {
                show_error(editor_window,data.msg,5000);
            }).always(function(data) {
            });
            
        }

        loadImage = (obj) => {  
            // FIX count image-viewee class and apply jQuery remove() if too much  OR limit a maximun
            let id       = obj.data('id');   
            let file     = obj.data('file');      
            let ext      = obj.data('ext');      

            $('#top-filename').html(file);
            if (id in files ) $('#'+id).show();   //SPLIT
                         else $('#'+split_active).append('<div id="'+id+'" class="image-viewer checkerboard"><img class="image-'+ext+'" src="'+file+'"></div>').show();           
        }

        loadFont = (obj) => {  
            let id       = obj.data('id');   
            let ext      = obj.data('ext');      
            let file     = obj.data('file');      
            let filename = obj.data('filename');      

            $('#top-filename').html(file);
            if (id in files )  {
                $('#'+id).show();
            }else{
                //$('#file-editor .font-viewer').hide();
                $('#'+split_active).append(               //SPLIT
                    '<div id="'+id+'" class="font-viewer card gridify tiny" style="width:100%;height:100%;text-align:left;"><div style="padding:10px 20px;;">'+
                // '<style>@font-face { font-family: \''+filename+'\'; src: url(\''+filename+'.ttf\')  format(\'truetype\'); src: url(\''+filename+'.eot\');  }</style>'+
                // '<style>@font-face { font-family: \''+filename+'\'; src: url(\''+file+'\')  format(\'truetype\'); src: url(\''+file+'\');  }</style>'+
                    '<style>@font-face { font-family: \''+filename+'\';  src: url(\''+file+'\');  }</style>'+
                    '<h1 style="font-family:\''+filename+'\';font-size:3em;">Font: '+filename+'</h1>'+
                    '<h1 style="font-family:\''+filename+'\';">QWERTYUIOPASDFGHJKLÑZXCVBNM €@#&%¿?=""</h1>'+
                    '<h1 style="font-family:\''+filename+'\';">qwertyuiopasdfghjklñzxcvbnm</h1>'+
                // '<h1 style="font-family:\''+filename+'\';">No por mucho tempranar amanece mas mendrugo</h1>'+
                    '<p style="font-family:\''+filename+'\';">Once there were brook trout in the streams in the mountains. You could see them standing in the amber current where the white edges of their fins wimpled softly in the flow. They smelled of moss in your hand. Polished and muscular and torsional. On their backs were vermiculate patterns that were maps of the world in its becoming. Maps and mazes. Of a thing which could not be put back. Not be made right again. In the deep glens where they lived all things were older than man and they hummed of mystery.</p>'+
                    '</div></div>').show();
            }
        }

        loadUnknownFile = (obj) => {
            let id       = obj.data('id');   
            let file     = obj.data('file');      
            let ext      = obj.data('ext');      
            $('#top-filename').html(file);               //SPLIT
            $('#'+split_active).append(`<div id="${id}" class="text-viewer"><div style="margin:30px auto;text-align:center;color:var(--red);">No viewer for ${ext} files</div></div>`).show();           
        }

        /**
         * Muestra el estado de la IA en el status bar
         */
        function setAIStatus(status, type = 'info') {
            if (!aiStatusElement) aiStatusElement = document.getElementById('ai-status');
            if (!aiStatusElement) return;

            const icons = {
                thinking: '⚡',
                success: '✓',
                error: '✗',
                info: 'ℹ'
            };

            const colors = {
                thinking: '#ffaa00',
                success: '#00ff00',
                error: '#ff0000',
                info: '#00aaff'
            };

            aiStatusElement.innerHTML = icons[type] + ' ' + status;
            aiStatusElement.style.color = colors[type];

            if (type === 'success' || type === 'error') {
                setTimeout(() => {
                    aiStatusElement.innerHTML = '';
                }, 3000);
            }
        }

        /**
         * Obtiene sugerencia de IA desde el backend
         * @param {string} code - Código seleccionado o línea actual
         * @param {string} customPrompt - Prompt personalizado del usuario
         * @param {string} fullFileContent - Contenido completo del archivo (contexto)
         * @param {boolean} hasSelection - Si hay texto seleccionado en el editor
         */
        async function getOpenAISuggestion(code, customPrompt = '', fullFileContent = '', hasSelection = false) {

             if(!sidebar_ai_visible) {
                 setAIStatus('idle...', 'info');
                 return;
             }

             if(code.length<2 && !customPrompt) return;

             setAIStatus('Thinking...', 'thinking');

             // Auto-detect: si hay selección, usar solo la selección; si no, usar archivo completo
             let question = '';
             if (customPrompt) {
                 if (hasSelection) {
                     // HAY SELECCIÓN: enviar solo el código seleccionado (más eficiente)
                     question = `Selected code:\n${code}\n\nQuestion: ${customPrompt}\n\nProvide ONLY the code solution, no explanations.`;
                 } else {
                     // NO HAY SELECCIÓN: enviar archivo completo (para "traduce todo", etc.)
                     question = `Full file content:\n${fullFileContent}\n\nQuestion: ${customPrompt}\n\nProvide ONLY the code solution, no explanations.`;
                 }
             } else {
                 // Prompt por defecto para autocompletar
                 if (hasSelection) {
                     question = `Complete the following selected code. Provide ONLY the code completion:\n\n${code}`;
                 } else {
                     question = `Full file content:\n${fullFileContent}\n\n---\nComplete the code at this position:\n${code}\n\nProvide ONLY the code completion without explanations.`;
                 }
             }

             const formData = new URLSearchParams();
             // Encode question in base64 to avoid WAF blocking PHP code
             formData.append('question', btoa(unescape(encodeURIComponent(question))));
             formData.append('encoded', '1');
             formData.append('service', aiService);
             formData.append('token', '<?=$_SESSION['token']?>');

             console.log('openAISuggestion','<?=MODULE?>/ajax/op=ai/service='+aiService+'/token='+'<?=$_SESSION['token']?>',formData);

             try {
                 const response = await fetch('<?=MODULE?>/ajax/op=ai', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                      body: formData
                 });
                 const data = await response.json();

                 if (data.error) {
                     setAIStatus('Error: ' + data.msg, 'error');
                     return null;
                 }

                 setAIStatus('Done', 'success');
                 return data.answer;
             } catch (error) {
                 setAIStatus('Network error', 'error');
                 console.error('AI Suggestion Error:', error);
                 return null;
             }
        }

        /**
         * Registra un provider de inline completions para el editor activo
         * Desregistra el provider anterior si existe
         */
        function registerAIProvider(language) {
            // Desregistrar provider anterior si existe
            if (currentProviderDisposable) {
                currentProviderDisposable.dispose();
                currentProviderDisposable = null;
            }

            // Registrar nuevo provider para el lenguaje del editor activo
            currentProviderDisposable = monaco.languages.registerInlineCompletionsProvider(language, {
                provideInlineCompletions(model, position, context, token) {
                    if (!currentSuggestion) {
                        return { items: [], dispose: () => {} };
                    }

                    const currentLine = model.getLineContent(position.lineNumber);
                    const typedPrefix = currentLine.substring(0, position.column - 1);
                    let suggestionText = currentSuggestion;

                    if (suggestionText.startsWith(typedPrefix)) {
                        suggestionText = suggestionText.substring(typedPrefix.length);
                    }

                    const range = new monaco.Range(
                        position.lineNumber,
                        position.column,
                        position.lineNumber,
                        position.column
                    );

                    return {
                        items: [{
                            insertText: suggestionText,
                            range: range
                        }],
                        dispose: () => {}
                    };
                },
                freeInlineCompletions(completions) {
                    // Called when completions are no longer needed
                },
                disposeInlineCompletions(completions) {
                    // Alternative disposal method required by some Monaco versions
                }
            });
        }

        var fetchSuggestion = async function(editor, customPrompt = '') {
           if(!files[active_id]) return;
            const code = getCode(editor);

            // Detectar si hay selección (no solo cursor)
            const selection = editor.getSelection();
            const hasSelection = selection && !(
                selection.startLineNumber === selection.endLineNumber &&
                selection.startColumn === selection.endColumn
            );

            // Obtener el contenido completo del archivo para enviar como contexto
            const fullFileContent = editor.getModel() ? editor.getModel().getValue() : '';

            // Log para debug
            console.log('AI Context:', hasSelection ? 'SELECTION ONLY' : 'FULL FILE', '| Code length:', code.length, '| File length:', fullFileContent.length);

            const suggestion = await getOpenAISuggestion(code, customPrompt, fullFileContent, hasSelection);
            if (suggestion) {
                currentSuggestion = suggestion;
                // showSuggestion(suggestion); // Ya no necesario, el chat muestra código coloreado

                // Guardar el rango de selección para usar en Apply
                const selectionRange = hasSelection ? {
                    startLineNumber: selection.startLineNumber,
                    startColumn: selection.startColumn,
                    endLineNumber: selection.endLineNumber,
                    endColumn: selection.endColumn
                } : null;

                // Agregar al historial de chat (con info de selección)
                if (customPrompt) {
                    addToChatHistory('user', customPrompt);
                    addToChatHistory('ai', suggestion, selectionRange);
                } else {
                    addToChatHistory('ai', suggestion, selectionRange);
                }

                // Forzar actualización del inline completions (ghost text)
                const inlineCompletionsController = editor.getContribution('editor.contrib.inlineCompletionsController');
                if (inlineCompletionsController && inlineCompletionsController.trigger) {
                    inlineCompletionsController.trigger();
                }
            }
        };

        /**
         * Agrega mensaje al historial de chat
         * @param {string} role - 'user' o 'ai'
         * @param {string} message - El mensaje
         * @param {object|null} selectionRange - Rango de selección original (para Apply)
         */
        function addToChatHistory(role, message, selectionRange = null) {
            if (!active_id) return;
            if (!chatHistory[active_id]) chatHistory[active_id] = [];

            chatHistory[active_id].push({
                role,
                message,
                timestamp: Date.now(),
                selectionRange  // Guardar el rango para usar en Apply
            });
            localStorage.setItem('ai_chat_history', JSON.stringify(chatHistory));
            updateChatDisplay();
        }

        /**
         * Aplica el código de la IA al editor activo
         * @param {string} code - Código a aplicar
         * @param {object|null} selectionRange - Rango de selección original (si existía)
         */
        function applyToEditor(code, selectionRange = null) {
            if (!active_id || !files[active_id] || !files[active_id].editor) {
                setAIStatus('No active editor', 'error');
                return;
            }

            const editor = files[active_id].editor;
            const model = editor.getModel();

            // Limpiar el código: quitar marcadores de código markdown si existen
            let cleanCode = code.trim();
            // Quitar ```php, ```javascript, etc al inicio y ``` al final
            cleanCode = cleanCode.replace(/^```[\w]*\n?/, '').replace(/\n?```$/, '');

            if (selectionRange) {
                // HAY SELECCIÓN: reemplazar solo el rango seleccionado
                editor.executeEdits("applyAICode", [{
                    range: new monaco.Range(
                        selectionRange.startLineNumber,
                        selectionRange.startColumn,
                        selectionRange.endLineNumber,
                        selectionRange.endColumn
                    ),
                    text: cleanCode,
                    forceMoveMarkers: true
                }]);
                setAIStatus('Selection replaced', 'success');
                show_info(editor_window, 'AI code applied to selection', 3000);
            } else {
                // NO HAY SELECCIÓN: reemplazar todo el contenido del editor
                model.setValue(cleanCode);
                setAIStatus('File replaced', 'success');
                show_info(editor_window, 'AI code applied to full file', 3000);
            }

            // Marcar como modificado
            update_tabs(active_id);
            files[active_id].value = model.getValue();
        }

        /**
         * Detecta el lenguaje del código basándose en el contenido o extensión del archivo activo
         */
        function detectLanguage(code) {
            // Si el archivo activo tiene extensión, usarla
            if (active_id && files[active_id] && files[active_id].ext) {
                const ext = files[active_id].ext;
                const langMap = {
                    'js': 'javascript', 'ts': 'typescript', 'php': 'php',
                    'py': 'python', 'css': 'css', 'html': 'html',
                    'json': 'json', 'sql': 'sql', 'md': 'markdown'
                };
                return langMap[ext] || 'plaintext';
            }
            // Detectar por contenido
            if (code.includes('<'+'?php') || code.includes('<'+'?=')) return 'php';
            if (code.includes('function') && code.includes('=>')) return 'javascript';
            if (code.includes('def ') || code.includes('import ')) return 'python';
            return 'plaintext';
        }

        /**
         * Actualiza la visualización del chat
         */
        function updateChatDisplay() {
            const chatHistoryElement = document.getElementById('ai-chat-history');
            if (!chatHistoryElement || !active_id) return;

            const history = chatHistory[active_id] || [];
            chatHistoryElement.innerHTML = '';

            history.forEach((item, index) => {
                const div = document.createElement('div');
                div.style.marginBottom = '8px';
                div.style.padding = '4px';
                div.style.borderRadius = '3px';
                div.style.backgroundColor = item.role === 'user' ? '#1a4d2e' : '#1d2951';

                let content = '<strong style="color:' + (item.role === 'user' ? '#4ade80' : '#60a5fa') + '">' + (item.role === 'user' ? 'You' : 'AI') + ':</strong> ';

                if (item.role === 'user') {
                    content += '<span style="color:#d4d4d4;">' + escapeHtml(item.message) + '</span>';
                } else {
                    // Indicador de contexto
                    const contextType = item.selectionRange ? 'selection' : 'full file';
                    content += '<span style="font-size:9px;color:#888;float:right;">' + contextType + '</span>';

                    let codeContent = item.message.trim().replace(/^```[\w]*\n?/, '').replace(/\n?```$/, '');
                    content += '<pre style="margin-top:4px;background:#1e1e1e;padding:8px;border-radius:4px;overflow-x:auto;color:#d4d4d4;font-family:monospace;font-size:11px;white-space:pre-wrap;max-height:300px;overflow-y:auto;margin-bottom:0;">' + escapeHtml(codeContent) + '</pre>';
                    content += '<div style="margin-top:5px;display:flex;justify-content:space-between;align-items:center;">';
                    content += '<button class="btn-copy-code" data-index="' + index + '" style="background:#555;color:#fff;border:none;padding:2px 8px;cursor:pointer;font-size:10px;border-radius:3px;" title="Copy to clipboard">Copy</button>';
                    content += '<div>';
                    content += '<button class="btn-apply-code" data-index="' + index + '" style="background:#007acc;color:#fff;border:none;padding:2px 8px;cursor:pointer;font-size:10px;border-radius:3px;margin-right:3px;" title="Replace ' + contextType + '">Apply</button>';
                    content += '<button class="btn-insert-code" data-index="' + index + '" style="background:#28a745;color:#fff;border:none;padding:2px 8px;cursor:pointer;font-size:10px;border-radius:3px;" title="Insert at cursor">Insert</button>';
                    content += '</div></div>';
                }

                div.innerHTML = content;
                chatHistoryElement.appendChild(div);
            });

            // Event listeners
            chatHistoryElement.querySelectorAll('.btn-copy-code').forEach(btn => {
                btn.onclick = (e) => {
                    const idx = parseInt(e.target.dataset.index);
                    const item = chatHistory[active_id][idx];
                    if (item) {
                        let code = item.message.trim().replace(/^```[\w]*\n?/, '').replace(/\n?```$/, '');
                        navigator.clipboard.writeText(code).then(() => {
                            e.target.textContent = 'Copied!';
                            setTimeout(() => e.target.textContent = 'Copy', 1500);
                        });
                    }
                };
            });

            chatHistoryElement.querySelectorAll('.btn-apply-code').forEach(btn => {
                btn.onclick = (e) => {
                    const idx = parseInt(e.target.dataset.index);
                    const item = chatHistory[active_id][idx];
                    if (item) applyToEditor(item.message, item.selectionRange);
                };
            });

            chatHistoryElement.querySelectorAll('.btn-insert-code').forEach(btn => {
                btn.onclick = (e) => {
                    const idx = parseInt(e.target.dataset.index);
                    const item = chatHistory[active_id][idx];
                    if (item) insertAtCursor(item.message);
                };
            });

            chatHistoryElement.scrollTop = chatHistoryElement.scrollHeight;
        }

        /**
         * Inserta código en la posición del cursor
         */
        function insertAtCursor(code) {
            if (!active_id || !files[active_id] || !files[active_id].editor) {
                setAIStatus('No active editor', 'error');
                return;
            }

            const editor = files[active_id].editor;
            const position = editor.getPosition();

            // Limpiar el código
            let cleanCode = code.trim();
            cleanCode = cleanCode.replace(/^```[\w]*\n?/, '').replace(/\n?```$/, '');

            // Insertar en la posición del cursor
            editor.executeEdits("insertAICode", [{
                range: new monaco.Range(
                    position.lineNumber,
                    position.column,
                    position.lineNumber,
                    position.column
                ),
                text: cleanCode,
                forceMoveMarkers: true
            }]);

            update_tabs(active_id);
            setAIStatus('Code inserted', 'success');
        }

        /**
         * Escapa HTML para mostrar en chat
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Envía mensaje desde el input de chat
         */
        async function sendChatMessage() {
            if (!active_id || !files[active_id]) return;

            const input = document.getElementById('ai-chat-input');
            const message = input.value.trim();

            if (!message) return;

            input.value = '';
            input.disabled = true;

            // Obtener respuesta de IA
            await fetchSuggestion(files[active_id].editor, message);

            input.disabled = false;
            input.focus();
        }

        /**
         * Limpia el historial de chat del editor actual
         */
        function clearChatHistory() {
            if (!active_id) return;
            chatHistory[active_id] = [];
            localStorage.setItem('ai_chat_history', JSON.stringify(chatHistory));
            updateChatDisplay();
            setAIStatus('Chat cleared', 'info');
        }

        function getCode(editor) {
            // Obtiene el rango de selección actual
            const selection = editor.getSelection();
            const model = editor.getModel();

            // Si no hay selección (o falla al obtenerla), devuelve cadena vacía
            if (!selection) {
                return '';
            }

            // Comprueba si no hay selección (solo está el cursor)
            if (selection.startLineNumber === selection.endLineNumber &&
                selection.startColumn === selection.endColumn) {
                // Devuelve el contenido completo de la línea actual
                return model.getLineContent(selection.startLineNumber);
            }

            // Si hay selección, devuelve el contenido dentro del rango seleccionado
            return model.getValueInRange(selection);
        }        

        /**
         * Acepta la sugerencia de IA actual en el editor
         */
        function acceptAISuggestion(editor) {
            if (!currentSuggestion) return false;

            const position = editor.getPosition();
            const model = editor.getModel();
            const currentLine = model.getLineContent(position.lineNumber);
            const typedPrefix = currentLine.substring(0, position.column - 1);

            let suggestionText = currentSuggestion;
            if (suggestionText.startsWith(typedPrefix)) {
                suggestionText = suggestionText.substring(typedPrefix.length);
            }

            editor.executeEdits("acceptAISuggestion", [{
                range: new monaco.Range(
                    position.lineNumber,
                    position.column,
                    position.lineNumber,
                    position.column
                ),
                text: suggestionText,
                forceMoveMarkers: true
            }]);

            currentSuggestion = "";
            return true;
        }

        /**
         * Función debounce para evitar llamar a la API en cada pulsación
         */
        function debounce(func, wait, immediate) {
            let timeout;
            return function () {
            const context = this,
                  args = arguments;
            const later = function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
            };
        }

        /**
         * Función que extrae un contexto del editor:
         * - 3 líneas anteriores (si existen)
         * - la línea actual
         * - 1 línea posterior (si existe)
         * Esto ayuda a reducir la cantidad de tokens enviados al API.
         */

        getEditorContext = (editor) => {
            const model = editor.getModel();
            const position = editor.getPosition();
            const totalLines = model.getLineCount();
            const startLine = Math.max(1, position.lineNumber - 3);
            const endLine = Math.min(totalLines, position.lineNumber + 1);
            let contextText = "";
            for (let i = startLine; i <= endLine; i++) { contextText += model.getLineContent(i) + "\n";  }
            return contextText;
        }

        let suggestionEditor = null;
        const suggestionBox = document.querySelector('#suggestion-box');

        function showSuggestion(suggestion) {
            const seditor = document.getElementById('suggestion-editor');

            if (!suggestionEditor) {
                let options = {
                    value: suggestion,
                    language: 'javascript',
                    readOnly: true,
                    minimap: { enabled: false },
                    lineNumbers: 'on',
                    scrollBeyondLastLine: false
                };

                var suggestionMonacoContainer = document.createElement('div');
                suggestionMonacoContainer.id = 'suggestion-monaco-container'
                seditor.appendChild(suggestionMonacoContainer); // Agregar al seditor, no al suggestionBox
                const suggestionMonacoEditor = monaco.editor.create(suggestionMonacoContainer, {
                   ...MonacoDefaultOptions,
                   ...options
                });
                suggestionEditor = suggestionMonacoEditor
                suggestionBox.style.display = 'block';

            } else {
                suggestionEditor.setValue(suggestion);
            }
        }

        loadCode = (obj) => {

            let id       = obj.dataset.id;
            let path     = obj.dataset.path;
            let file     = obj.dataset.file;
            let basename = obj.dataset.basename;
            let filename = obj.dataset.filename;
            let ext      = obj.dataset.ext;

            active_id = id;
            document.querySelector('#active_id').innerHTML = active_id;

            $('#top-filename').html(file);
            $('#li-'+id+', #btn-file-'+id).addClass('active').addClass('open');

            if (id in files )  {

                $('#monaco-container-'+id).show();
                document.querySelector('#status-wordwrap').setClass('on',files[id].wordwrap=='on')

                // Registrar provider para el editor que se está mostrando
                let lang = files[id].type;
                if (ext=='js') lang = 'javascript';
                if (ext=='py') lang = 'python';
                if (ext=='md') lang = 'markdown';
                if (ext=='pas') lang = 'pascal';
                registerAIProvider(lang);

                // Actualizar chat history
                updateChatDisplay();

            }else{
                $('#'+split_active+' .btn-tabs').append('<a id="btn-file-'+id+'" class="link-file active open tab-context-menu" data-type="code" data-id="'+id+'" data-path="'+path+'" data-file="'+file+'" data-basename="'+basename+'" data-filename="'+filename+'" data-ext="'+ext+'"> '+basename+' <i style="color:#ff0033;" class="fa fa-close"></i> </a>');

                $.ajax({
                    method: "POST",
                    url: "<?=MODULE.'/ajax'?>",
                    data: { 'op': 'getfile', 'file':file },
                    dataType: "json",
                    beforeSend: function( xhr, settings ) { }
                }).done(function( data ) {

                    files[id] = {};
                    files[id].id = id;
                    files[id].modified = false;
                    files[id].ext = ext;
                    files[id].file = file;
                    files[id].filename = filename;
                    files[id].wordwrap = 'on'
                    document.querySelector('#status-wordwrap').setClass('on',files[id].wordwrap=='on')

                    var MonacoContainer = document.createElement('div');
                    MonacoContainer.classList.add('editor-wrapper','editor-center','code-textarea');
                    MonacoContainer.dataset.filetype = ext;
                    MonacoContainer.id = 'monaco-container-'+id     

                    var editor = document.querySelector('#'+split_active)    //SPLIT
                    editor.appendChild(MonacoContainer);

                    files[id].value = crypt2str(data.text,'<?=$_SESSION['token']?>' );
                    files[id].type = MonacoContainer.dataset.filetype;
                    
                    let lang_option = ext;
                    if (ext=='js') lang_option = 'javascript';
                    if (ext=='py') lang_option = 'python';
                    if (ext=='md') lang_option = 'markdown';
                    if (ext=='pas') lang_option = 'pascal';
                    
                    let options = {
                        value:  crypt2str(data.text,'<?=$_SESSION['token']?>' ),     //;MonacoTextarea.value,
                        language: lang_option,
                        fontSize:12,
                        theme: VS_THEME
                    };

                    const monacoEditor = monaco.editor.create(MonacoContainer, {
                        ...MonacoDefaultOptions,
                        ...options
                    });
                    
                    MonacoContainer.style.display = 'block';

                    // Registrar provider de IA para este lenguaje
                    registerAIProvider(lang_option);

                    // F9: Toggle word wrap
                    monacoEditor.addCommand(monaco.KeyCode.F9, function () {
                        files[id].wordwrap = monacoEditor.getOption(monaco.editor.EditorOption.wordWrap) === 'on' ? 'off' : 'on';
                        monacoEditor.updateOptions({ wordWrap: files[id].wordwrap });
                        document.querySelector('#status-wordwrap').setClass('on',files[id].wordwrap=='on')
                    });

                    // F1: Forzar sugerencia de IA
                    monacoEditor.addCommand(monaco.KeyCode.F1, () => {
                        if(sidebar_ai_visible) {
                            fetchSuggestion(monacoEditor);
                        }
                    });

                    // TAB: Aceptar sugerencia IA si existe, sino comportamiento normal
                    monacoEditor.addCommand(monaco.KeyCode.Tab, () => {
                        if (currentSuggestion && sidebar_ai_visible) {
                            acceptAISuggestion(monacoEditor);
                        } else {
                            // Ejecutar comportamiento por defecto de TAB
                            monacoEditor.trigger('keyboard', 'tab', null);
                        }
                    });

                    // Escuchar los cambios en el editor
                    monacoEditor.onDidChangeModelContent((event) => {
                        files[id].value = monacoEditor.getValue();
                        update_tabs(id);

                        // Sugerencias automáticas con debounce (solo si está habilitado)
                        if (autoSuggestEnabled && sidebar_ai_visible) {
                            if (debounceTimer) clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                fetchSuggestion(monacoEditor);
                            }, 2000); // 2 segundos de inactividad
                        }
                    });

                    // Actualizar posición del cursor
                    monacoEditor.onDidChangeCursorPosition((event) => {
                            active_id = id;
                            document.querySelector('#active_id').innerHTML = active_id;
                            update_position(event.position);
                    });

                    files[id].editor = monacoEditor

                }).fail(function(data) {
                    console.error( data.msg );
                }).always(function(data) {
                    console.info('DATA', data.msg )
                });
            }
        }
        
        document.querySelectorAll('.file-editor').forEach(split => 
            split.addEventListener('click', () => {
                if(!files[active_id]) return;
                split_active = split.id
                document.querySelector('#status-wordwrap').setClass('on',files[active_id].wordwrap=='on')
            })
        );
        
        document.querySelectorAll('.file-editor').forEach(split => 
            split.addEventListener('keyup', (event) => {
                document.querySelector('#status-capslock'  ).setClass('on',event.getModifierState('CapsLock')  )
            })
        );
 
        document.querySelector('#status-wordwrap').addEventListener('click', ()=> {
            if(!files[active_id]) return;
            files[active_id].wordwrap = files[active_id].editor.getOption(monaco.editor.EditorOption.wordWrap) === 'on' ? 'off' : 'on';
            files[active_id].editor.updateOptions({ wordWrap: files[active_id].wordwrap });
            document.querySelector('#status-wordwrap').setClass('on',files[active_id].wordwrap=='on')
        });

        $(document).keydown(function(event) {

            if (!(event.metaKey || event.ctrlKey)) { return true; }                       

            if (String.fromCharCode(event.which)=='W'){

                event.preventDefault();
                closeCode( $('#btn-file-'+active_id) )

            }else if (String.fromCharCode(event.which)=='S'){

                event.preventDefault();
                //save_file()
                save_file().then(() => console.log('Guardado completo')).catch(err => console.error(err));
            }

        });

        async function async_str2crypt(value, key){
            return str2crypt( value, key );
        }

        /**
         * 
         *  dentro de otra función async: await save_file();
         */
        save_file = async () => {
            if (files[active_id].modified==true){

                $('#li-'+active_id+', #btn-file-'+active_id).removeClass('modified').addClass('saved').highlight();  //BUTTON

                //var str = str2crypt( files[active_id].value, '< ?=$_SESSION['token']? >' );
                try{
                    const str = await  str2crypt( files[active_id].value, '<?=$_SESSION['token']?>' );

                    console.log('STR.LENGTH',str.length);

                    const formData = new FormData();
                    formData.append('op'  , 'settext');
                    formData.append('id'  , active_id);
                    formData.append('file', files[active_id].file);
                    //formData.append('text', str);
                    formData.append('text', new Blob([str], { type: 'text/plain' }));

                    //formData.append('token', '<?=$_SESSION['token']?>'); 
                    //formData.append('rootdir','<?=$root_dir?>')

                    const response = await fetch('<?=MODULE?>/ajax', {
                        method: 'POST',
                       //headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`Error HTTP: ${response.status} - ${errorText}`);
                    }
       
                    const data = await response.json();
                    console.log('Respuesta del servidor:', data);
                    if (data.error==0){
                        files[active_id].modified = false;
                        show_info(editor_window,data.msg,5000);                            
                    }else{
                        show_error(editor_window,data.msg,5000);
                    }


                } catch (error) {

                    console.error('Error:', error);
                    show_error(editor_window,'No va fino esto: '+error,5000);

                }

            }else{
                show_warning(editor_window,'Documento no modificado',5000);            
            }
        }
        
        $('#btn-fullscreen').click(function(){
            $('#editor').toggleClass('fullscreen');
            $(this).toggleClass('fa-window-maximize').toggleClass('fa-window-restore'); 
            $('nav,footer,#breadcrumb,#top').toggleClass('blur')            
        });

        document.getElementById('btn-editor-save').addEventListener('click', () => {        
            //console.log('SAVE',files[active_id].filename);
            save_file()
        });

        document.getElementById('btn-split').addEventListener('click', () => {      
            split_screen = !split_screen;  
            //console.log('LEFT',files[active_id].filename)

            if(!split_screen){

                const source_tabs = document.querySelector('#file-editor-right #btn-tabs-right');
                const target_tabs = document.querySelector('#file-editor #btn-tabs');
                if (source_tabs && target_tabs) target_tabs.append(...source_tabs.children); 
                const source_editors = document.querySelector('#file-editor-right');
                const target_editors = document.querySelector('#file-editor');
                if (source_editors && target_editors) {   // Seleccionar solo los div que son hijos directos (>) y excluir #btn-tabs-right
                    const divs = source_editors.querySelectorAll(':scope > div:not(#btn-tabs-right)');
                    divs.forEach(div => target_editors.appendChild(div));
                }              

            }else{
                split_active = 'file-editor-right'
            }

            document.querySelector('#file-editor').style.right = split_screen ? '50%' : '0%';                          //SPLIT
            document.querySelector('#file-editor-right').style.display = split_screen ? 'block' : 'none';

           // document.querySelector('#monaco-container-'+active_id).removeClass('editor-center').addClass('editor-left')
           // document.querySelector('.editor-center').removeClass('editor-center').addClass('editor-right')
        });

        $('#file-tree>ul').show('fast');

        $('.file-editor').on('click','audio + .fa',function(e){     //SPLIT
            e.preventDefault();
            var song = $(this).prev('audio').get(0);
            if (song.paused) {
                song.play();
                $(this).addClass("fa-pause");
                $(this).removeClass("fa-play");
            } else {
                song.pause();
                $(this).addClass("fa-play");
                $(this).removeClass("fa-pause");
            }
        });        

        $('#file-tree').on('blur','a',function(event){
            event.target.contentEditable = false;
            event.target.classList.remove('editing')
            //FIX restore prevoius name. trigger ESC  or prompt for save changes
        })

        splitNameAndExtension = (filename) => {
            const lastDotIndex = filename.lastIndexOf('.');
            if (lastDotIndex <= 0)  return { name: filename, ext: '' };           
            return { name: filename.slice(0, lastDotIndex), ext: filename.slice(lastDotIndex + 1) };
        }

        onShowMenu = (element) => {
            document.querySelector('#menu-cut').textContent = cut_from===cut_to || cut_file===false ? 'Cortar' : 'Pegar';
        }
            
        const fileContextMenuItems = [ 
            {
                text: 'Renombrar',
                id: 'menu-rename',
                handler: (element, item) => {
                    if (element.dataset.id in files ) {
                        show_error(editor_window,'El archivo está siendo editado',5000);
                    }else{
                        element.contentEditable = true
                        element.classList.add('editing')
                        element.focus()                   
                    }
                }
            },{
                text: 'Eliminar',
                id: 'menu-delete',
                handler: (element, item) => {                
                    $.modalform({ 'html':'Va usted a eliminar el archivo '+element.dataset.basename+'<br>¿Está seguro?', 'buttons':'ok cancel'}, function(accept) { if(accept)  delete_file(element); });
                }
            },{
                text: 'Cortar',
                id: 'menu-cut',
                handler: (element, item) => { move_file(element); }
            }
        ];

        const tabContextMenuItems = [ 
            {
                text: 'Close',
                id: 'menu-close',
                handler: (element, item) => { closeCode( $(element) )  }
            },{
                text: 'Close Others',
                id: 'menu-close-others',
                handler: (element, item) => { document.querySelectorAll('.tab-context-menu').forEach(tab =>  { if(tab.dataset.id!=element.dataset.id) closeCode($(tab)) }) }
            }
        ];

        const fileContextMenu = new ContextMenu('file-context-menu', fileContextMenuItems, onShowMenu);
        const tabContextMenu = new ContextMenu('tab-context-menu', tabContextMenuItems, onShowMenu);

        $('#editor').on('keydown','.editing',function(event){

            var element=event.target
            if(event.key === 'Enter') {
                
                event.preventDefault();
                element.contentEditable = false;
                element.classList.remove('editing')
                if(event.target.dataset.type=='folder') rename_dir(element) 
                                                  else  rename_file(element);                

            }else if(event.key === 'ArrowLeft') {
            }else if(event.key === 'ArroRight') {
            }else if(event.key === 'Home') {
            }else if(event.key === 'End') {
            }else if(event.key === 'Delete') {
            }else if(event.key === 'BackSpace') {
            }else if(event.key === 'Escape') {
                event.preventDefault();
                element.contentEditable = false;
                element.classList.remove('editing')
            }else if ( event.key.match(/[-._a-zA-Z0-9]/) ) {   
            }else{
                event.preventDefault()
            }            
        });

        var selectTheme = document.querySelector('#status-theme select');
        var loadedThemes = null;
        var loadedThemesData = {};

        loadTheme = (theme) => {
            var path = '/_modules_/edit/themes/' + theme + '.json';
            return fetch(path)
                .then(r => r.json())
                .then(data => {
                loadedThemesData[theme] = data;
                if (window.monaco) monaco.editor.defineTheme(theme, data);
                return data;
            });
        }

        selectTheme.addEventListener('change', function(ev) {
            var val = ev.target.value;
            console.log('THEME',val)
            setCookie('vs_theme', val, 365);
            if (val === 'vs' || val === 'vs-dark' || val === 'hc-black' || loadedThemesData[val]) monaco.editor.setTheme(val);
            else   loadTheme(val).then((data) => { monaco.editor.setTheme(val);  });     

        });
        
        loadThemeList = () => {        // https://editor.bitwiser.in/
            let data = {
                "vs":"VS",
                "vs-dark":"VS Dark",
                "hc-black":"HC Black",
                "one-dark-pro": "One Dark Pro",
                "tomorrow-night": "Tomorrow-Night",
                "twilight": "Twilight"
            }

            // If load themes from json file
            //return fetch('<?=SCRIPT_DIR_MODULE?>/themes/themelist.json') 
            //    .then(r => r.json())
            //    .then(data => {
                    loadedThemes = data;
                    let themes = Object.keys(data);
                    themes.forEach(theme => {
                        let opt = document.createElement('option');
                        opt.value = theme;
                        opt.text = data[theme]
                        if (opt.value==VS_THEME) opt.setAttribute('selected',true)
                        selectTheme.add(opt);
                    });
            //    });

        }

        loadThemeList();
        $('#btn-refresh').click()

        // Inicializar selector de servicio IA
        const aiServiceSelector = document.getElementById('ai-service-selector');
        if (aiServiceSelector) {
            aiServiceSelector.value = aiService;
            aiServiceSelector.addEventListener('change', (e) => {
                aiService = e.target.value;
                localStorage.setItem('ai_service', aiService);
                setAIStatus('Service: ' + aiService, 'info');
            });
        }

        // Toggle de sugerencias automáticas con doble clic en el icono de IA
        $('#btn-ai').on('dblclick', () => {
            autoSuggestEnabled = !autoSuggestEnabled;
            const msg = autoSuggestEnabled ? 'Auto-suggest ON' : 'Auto-suggest OFF';
            setAIStatus(msg, 'info');
            show_info(editor_window, msg, 3000);
        });

        // Inicializar chat
        const aiChatInput = document.getElementById('ai-chat-input');
        const aiChatSend = document.getElementById('ai-chat-send');
        const aiChatClear = document.getElementById('ai-chat-clear');

        if (aiChatSend) {
            aiChatSend.addEventListener('click', sendChatMessage);
        }

        if (aiChatClear) {
            aiChatClear.addEventListener('click', clearChatHistory);
        }

        if (aiChatInput) {
            aiChatInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendChatMessage();
                }
            });
        }

        onbeforeunload = (e) => {
            //for (var key in files)
                //if(files[key].modified)
                    return "You have attempted to leave this page.  If you have made any changes to the fields without clicking the Save button, your changes will be lost.  Are you sure you want to exit this page?";
        }
        
    });
</script>
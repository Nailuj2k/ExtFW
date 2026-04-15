class MonacoEditor{
        
    constructor() {
        this.editors = {};
        this.currentFile = null;
        this.init();
    }
    
    async init() {
     // require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        require.config({ paths: { 'vs': MONACO_VS_PATH }});
        

      //  require(['vs/editor/editor.main'], () => {
        require(['vs/editor/editor.main'], () => {
            this.initializeEditors();
            //this.setupEventListeners();
            
            if(console_log) console.log('MONACO OK');
            /*
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                this.activateTab(firstTab.dataset.key);
            }
            */
        });
    }
    
    initializeEditors() {
        document.querySelectorAll('.editor-wrapper').forEach(wrapper => {
            const key = wrapper.id; //.replace('monaco_', '');
            const textarea = wrapper.querySelector('textarea');
            const content = textarea.value;
            const type = wrapper.dataset.filetype;
            
            // textarea atpc
            wrapper.innerHTML = '';
            //console.log('EDITOR: ',key)
            
            this.editors[key] = monaco.editor.create(wrapper, {
                value: content,
                language: type,
                theme: 'vs-dark',
                fontSize: 12,
                automaticLayout: true,
                minimap: { enabled: true },
                scrollBeyondLastLine: false,
                wordWrap: 'on',
                suggestOnTriggerCharacters: true,
                snippetSuggestions: 'top',
                fontLigatures: true
            });
        });
    }

    getValue(key){
        //return this.editors[key].textContent;
        return this.editors[key].getValue()
    }
    setValue(key,value){
        //return this.editors[key].textContent;
        return this.editors[key].setValue(value)
    }

    insertHtml(key, html) {
        const editor = this.editors[key];
        const selection = editor.getSelection();
        const range = selection.isEmpty() 
            ? new monaco.Range(
                selection.startLineNumber,
                selection.startColumn,
                selection.startLineNumber,
                selection.startColumn
            )
            : new monaco.Range(
                selection.startLineNumber,
                selection.startColumn,
                selection.endLineNumber,
                selection.endColumn
            );
        
        editor.executeEdits("", [{ range, text: html, forceMoveMarkers: true }]);
        
        // Mueve el cursor al final del texto insertado
        const newPosition = editor.getPosition();
        editor.setPosition(newPosition); // Opcional: fuerza el enfoque en el editor

    }
    
}

//}

const monaco_editor = new MonacoEditor();
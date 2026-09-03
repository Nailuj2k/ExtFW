export function editableImagesPlugin() {

    // ImageEditor es una clase js que hace editable une lemento <img> permitiendo 
    // recortr la imagen, cambiarla por captura d ela webcam, desde archivo, incluso 
    // con drag&drop, ponerle filtros, etc. 
    // el primere parámetro indica la class a cuyas imáagenes se aplicará el editor
    // els egundo es el ajax entrypoint al que se enviará la imagen editada.
    ImageEditor.editable_images('.editable-image','/test/ajax/op=image-crop');  

    return {
        updateState: ()=>{ return false}
    };

}
/*


cada mnodulo será resposable de su busqueda, de manera que cada modulo admita 
una url por ejemplo, para un modulo blog:  {HOST_URL]/blog/search/<searchstring>

asi search será un 'output'
y en cada modulo, en su index, podra haber un if(OUTPUT=='search) include( SCRIPT_DIR_MODULE/search.php)

*/
<?php


// include(SCRIPT_DIR_MODULES.'/test/xsshtml.class.php');

/**
 * PHP 富文本XSS过滤类  (Clase de filtrado XSS de texto enriquecido)
 *
 * @package XssHtml
 * @version 1.0.0 
 * @link http://phith0n.github.io/XssHtml
 * @since 20140621
 * @copyright (c) Phithon All Rights Reserved
 *
 */

#
# Written by Phithon <root@leavesongs.com> in 2014 and placed in
# the public domain.
#
# phithon <root@leavesongs.com> 编写于20140621
# From: XDSEC <www.xdsec.org> & 离别歌 <www.leavesongs.com>
# Usage: 
# <?php
# require('xsshtml.class.php');
# $html = '<html code>';
# $xss = new XssHtml($html);
# $html = $xss->getHtml();
# ?\>
# 
# 需求： (Dependencias:)
# PHP Version > 5.0
# 浏览器版本：IE7+ 或其他浏览器，无法防御IE6及以下版本浏览器中的XSS (Versión del navegador: IE7+ u otros, no puede defenderse contra XSS en navegadores de IE6 e inferiores)
# 更多使用选项见 (Más información:) http://phith0n.github.io/XssHtml

class XssHtml {
	private $m_dom;
	private $m_xss;
	private $m_ok;
	private $m_AllowAttr = array('title', 'src', 'href', 'id', 'class',/* *'style',**/ 'width', 'height', 'alt', 'target', 'align',/* 'onclick',*/ 'placeholder', 'value');
	private $m_AllowTag = array('a', 'img', 'br', 'strong', 'b', 'code', 'pre', 'p', 'div', 'em', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'table', 'colgroup', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'hr', 'li', 'u', 'input');

	/**
     * 构造函数 (Constructor) 
     *
     * @param string $html 待过滤的文本 (Texto a filtrar)
     * @param string $charset 文本编码，默认utf-8 (Codificación de texto, utf-8 predeterminado)
     * @param array $AllowTag 允许的标签，如果不清楚请保持默认，默认已涵盖大部分功能，不要增加危险标签
     *                       (Etiquetas permitidas, si no está claro, mantenga el valor predeterminado, el valor predeterminado ha cubierto la mayoría de las funciones, no agregue etiquetas peligrosas)
     */
	public function __construct($html, $charset = 'utf-8', $AllowTag = array(), $AllowAttr = array()){
		$this->m_AllowTag = empty($AllowTag) ? $this->m_AllowTag : $AllowTag;
		$this->m_AllowAttr = empty($AllowAttr) ? $this->m_AllowAttr : $AllowAttr;
		$this->m_xss = strip_tags($html, '<' . implode('><', $this->m_AllowTag) . '>');
		if (empty($this->m_xss)) {
			$this->m_ok = FALSE;
			return ;
		}
		$this->m_xss = "<meta http-equiv=\"Content-Type\" content=\"text/html;charset={$charset}\"><nouse>" . $this->m_xss . "</nouse>";
		$this->m_dom = new DOMDocument();
		$this->m_dom->strictErrorChecking = FALSE;
		$this->m_ok = @$this->m_dom->loadHTML($this->m_xss);
	}

	/**
     * 获得过滤后的内容 (Obtener contenido filtrado)
     */
	public function getHtml()
	{
		if (!$this->m_ok) {
			return '';
		}
		$nodeList = $this->m_dom->getElementsByTagName('*');
		for ($i = 0; $i < $nodeList->length; $i++){
			$node = $nodeList->item($i);
			if (in_array($node->nodeName, $this->m_AllowTag)) {
				if (method_exists($this, "__node_{$node->nodeName}")) {
					call_user_func(array($this, "__node_{$node->nodeName}"), $node);
				}else{
					call_user_func(array($this, '__node_default'), $node);
				}
			}
		}
		$html = strip_tags($this->m_dom->saveHTML(), '<' . implode('><', $this->m_AllowTag) . '>');
		$html = preg_replace('/^\n(.*)\n$/s', '$1', $html);
		return $html;
	}

	private function __true_url($url){
		if (preg_match('#^https?://.+#is', $url)) {
			return $url;
		}else{
			return /*'http://' .*********/ $url;
		}
	}

	private function __get_style($node){
		if ($node->attributes->getNamedItem('style')) {
			$style = $node->attributes->getNamedItem('style')->nodeValue;
			$style = str_replace('\\', ' ', $style);
			$style = str_replace(array('&#', '/*', '*/'), ' ', $style);
			$style = preg_replace('#e.*x.*p.*r.*e.*s.*s.*i.*o.*n#Uis', ' ', $style);
			return $style;
		}else{
			return '';
		}
	}

	private function __get_link($node, $att){
		$link = $node->attributes->getNamedItem($att);
		if ($link) {
			return $this->__true_url($link->nodeValue);
		}else{
			return '';
		}
	}

	private function __setAttr($dom, $attr, $val){
		if (!empty($val)) {
			$dom->setAttribute($attr, $val);
		}
	}

	private function __set_default_attr($node, $attr, $default = '')
	{
		$o = $node->attributes->getNamedItem($attr);
		if ($o) {
			$this->__setAttr($node, $attr, $o->nodeValue);
		}else{
			$this->__setAttr($node, $attr, $default);
		}
	}

	private function __common_attr($node)
	{
		$list = array();
		foreach ($node->attributes as $attr) {
			if (!in_array($attr->nodeName, 
				$this->m_AllowAttr)) {
				$list[] = $attr->nodeName;
			}
		}
		foreach ($list as $attr) {
			$node->removeAttribute($attr);
		}
		$style = $this->__get_style($node);
		$this->__setAttr($node, 'style', $style);
		$this->__set_default_attr($node, 'title');
		$this->__set_default_attr($node, 'id');
		$this->__set_default_attr($node, 'class');
	}

	private function __node_img($node){
		$this->__common_attr($node);

		$this->__set_default_attr($node, 'src');
		$this->__set_default_attr($node, 'width');
		$this->__set_default_attr($node, 'height');
		$this->__set_default_attr($node, 'alt');
		$this->__set_default_attr($node, 'align');

	}

	private function __node_a($node){
		$this->__common_attr($node);
		$href = $this->__get_link($node, 'href');

		$this->__setAttr($node, 'href', $href);
		///////////////////////$this->__set_default_attr($node, 'target', '_blank');
	}

	private function __node_embed($node){
		$this->__common_attr($node);
		$link = $this->__get_link($node, 'src');

		$this->__setAttr($node, 'src', $link);
		$this->__setAttr($node, 'allowscriptaccess', 'never');
		$this->__set_default_attr($node, 'width');
		$this->__set_default_attr($node, 'height');
	}

	private function __node_default($node){
		$this->__common_attr($node);
	}
}

// if(php_sapi_name() == "cli"){
// 	$html = $argv[1];
// 	$xss = new XssHtml($html);
// 	$html = $xss->getHtml();
// 	echo "'$html'";
// }



class XSS{


    /*
    <h3>Programa</h3>
    <p>Bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla</p>
    <img width="40px" style="border:3px solid blue;" class="una-clase" src="media/page/files/21/.tn_1704477824_revolucion_francesa_thiers.jpg">
    */
   
    // To test
    // https://sourceforge.net/projects/simplehtmldom/

    public static function strip_trip($textl, $charset = 'utf-8', $AllowTag = array(), $AllowAttr = array()){

        $xss = new XssHtml($textl, $charset, $AllowTag, $AllowAttr );
        $r = $xss->getHtml();
		$r = str_replace(['%5B','%5D'],['[',']'],$r);

        // $r = str_replace(['%22','%5C','<p></p>','<b></b>','<span>&nbsp;</span>','<p><br></p>',"<p>\n\r</p>"],'', $r  );
        // $r = str_replace('/p><',"/p>\n<", $r  );
        return $r;

	}
    
    public static function strip_trip_0($text){
        // https://stackoverflow.com/questions/3026096/remove-all-attributes-from-html-tags
        $r = $text; //str_replace("\n",'', $text  );

        // $r = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $r)  ;
        // $r = preg_replace('/<([A-Z][A-Z0-9]*)(\b[^>src]*)(src\=[\'|"|\s]?[^\'][^"][^\s]*[\'|"|\s]?)?(\b[^>]*)>/i','<$1$2$3>', $r);

        // "/<([a-z][a-z0-9-]*)[^>]*?(\/?)>/si"  // custom tags

        $dom = new DOMDocument;
        $dom->loadHTML(mb_convert_encoding($r, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//@*[not(name()="src")]') as $attr) {
            $attr->parentNode->removeAttribute($attr->nodeName);
        }

        //foreach ($xpath->query('//@*') as $attr) {
        //    if($attr->nodeName!='title' && $attr->nodeName!='src' )
        //    $attr->parentNode->removeAttribute($attr->nodeName);
        //}

        
        //$nodes = $xpath->query('//@*');
        //foreach ($nodes as $node) {
        //    if($node->nodeName!='title' && $node->nodeName!='src' )
        //    $node->parentNode->removeAttribute($node->nodeName);
        //}

        $r =  FixUtf8::fromHtmlEntities($dom->saveHTML());//$dom->saveHTML();
        //$r =  $dom->saveHTML();

 //       $r = str_replace(['%22','%5C','<p></p>','<b></b>','<span>&nbsp;</span>','<p><br></p>',"<p>\n\r</p>"],'', $r  );
        //$r = str_replace('><',">\n<", $r  );
        return $r;

    }

}
<?php

// Code from https://github.com/RobQuistNL/SimpleHtmlToText

//namespace SimpleHtmlToText;

class HtmlToText {
    /** @var  string */
    private $html;

    /** @var  string */
    private $text;

    // Code from https://donatstudios.com/Damn-Simple-PHP-ASCII-Art-Generator
    private function img2ascii($file){

        //example
        //$file = SCRIPT_DIR_MEDIA.'/page/files/99/72374ac8465aff1399c77a3410b171a9b3934234.jpg';
        
        $return = '';

        $img = imagecreatefromstring(file_get_contents($file));
        list($width, $height) = getimagesize($file);

        $scale = 10;

        $chars = array(
            ' ', '\'', '.', ':',
            '|', 'T',  'X', '0',
            '#',
        );

        $chars = array_reverse($chars);

        $c_count = count($chars);

        for($y = 0; $y <= $height - $scale - 1; $y += $scale) {
            for($x = 0; $x <= $width - ($scale / 2) - 1; $x += ($scale / 2)) {
                $rgb = imagecolorat($img, $x, $y);
                $r = (($rgb >> 16) & 0xFF);
                $g = (($rgb >> 8) & 0xFF);
                $b = ($rgb & 0xFF);
                $sat = ($r + $g + $b) / (255 * 3);
                $return .= $chars[ (int)( $sat * ($c_count - 1) ) ];
            }
             $return .= PHP_EOL;
        }

        return $return;

    }

    private $parseRules = [
       
        '/<(img)\b[^>]*alt=\"([^>"]+)\"[^>]*>/Uis' => '[IMG]$2[/IMG]', //Parse image tags with alt
        '/<(img)\b[^>][^>]*>/Uis' => '', // Remove image tags without alt
        '/<a(.*)href=[\'"](.*)[\'"]>(.*)<\/a>/Uis' => '$3 ($2)', //Parse links
        '/<hr(.*)>/Uis' => "\n==================================\n", //Parse lines
        '/<br(.*)>/Uis' => "\n", //Parse breaklines
        '/<(.*)br>/Uis' => "\n", //Parse broken breaklines
        '/<p(.*)>(.*)<\/p>/Uis' => "\n$2\n", //Parse alineas
        '/<div(.*)>(.*)<\/div>/Uis' => "\n$2\n", //Parse divs (added by Nailuj)
        '/<pre(.*)>(.*)<\/pre>/Uis' => "\n$2\n", //Parse pre (added by Nailuj). // line breaks not working

        //Lists
        '/(<ul\b[^>]*>|<\/ul>)/i' => "\n\n",
        '/(<ol\b[^>]*>|<\/ol>)/i' => "\n\n",
        '/(<dl\b[^>]*>|<\/dl>)/i' => "\n\n",

        '/<li\b[^>]*>(.*?)<\/li>/i' => "\t* $1\n",
        '/<dd\b[^>]*>(.*?)<\/dd>/i' => "$1\n",
        '/<dt\b[^>]*>(.*?)<\/dt>/i' => "\t* $1",
        '/<li\b[^>]*>/i' => "\n\t* ",

        //Parse table columns
        '/<tr>(.*)<\/tr>/Uis' => "\n$1",
        '/<td>(.*)<\/td>/Uis' => "$1\t",
        '/<th>(.*)<\/th>/Uis' => "$1\t",
        //Parse markedup text
        '/<em\b[^>]*>(.*?)<\/em>/i' => '$1',
        '/<b>(.*)<\/b>/Uis' => '**$1**',
        '/<strong(.*)>(.*)<\/strong>/Uis' => '**$2**',
        '/<i>(.*)<\/i>/Uis' => '*$1*',
        '/<u>(.*)<\/u>/Uis' => '_$1_',
        //Headers
        '/<h1(.*)>(.*)<\/h1>/Uis' => "\n### $2 ###\n",
        '/<h2(.*)>(.*)<\/h2>/Uis' => "\n## $2 ##\n",
        '/<h3(.*)>(.*)<\/h3>/Uis' => "\n## $2 ##\n",
        '/<h4(.*)>(.*)<\/h4>/Uis' => "\n## $2 ##\n",
        '/<h5(.*)>(.*)<\/h5>/Uis' => "\n# $2 #\n",
        '/<h6(.*)>(.*)<\/h6>/Uis' => "\n# $2 #\n",
        //Surround tables with newlines
        '/<table(.*)>(.*)<\/table>/Uis' => "\n$2\n",
    ];

    /**
     * @param $rule
     * @param $value
     */
    public function setParseRule($rule, $value) {
        $this->parseRules[$rule] = $value;
    }

    /**
     * @param $rule
     */
    public function removeParseRule($rule) {
        if (array_key_exists($rule, $this->parseRules)) {
            unset($this->parseRules[$rule]);
        }
    }

    /**
     * @param $string
     * @return string
     */
    public function parseString($string,$images_to_ascii=false) {
        $this->setHtml($string);
        $this->parse();
        $text = $this->getText();

        if($images_to_ascii){
            //Convert images to ascii
            $text =  preg_replace_callback('/\[IMG\](.*?)\[\/IMG\]/is', function($matches){
                return $this->img2ascii('.'.$matches[1])."\n".$matches[1];
                //TEST OK: return $this->img2ascii('./media/page/files/99/72374ac8465aff1399c77a3410b171a9b3934234.jpg');
            }, $text);
        }
        return $text;
    }

    /**
     * Parse the HTML and put it into the text variable
     */
    private function parse() {
        $string = $this->getHtml();

        foreach ($this->parseRules as $rule => $output) {
            $string = preg_replace($rule, $output, $string);
        }

        //Strip remaining tags before decoding entities (prevents '<' from entities becoming tags)
        $string = strip_tags($string);

        //Decode HTML entities once tags are gone
        $string = html_entity_decode($string);

        //Collapse internal spaces per line but preserve leading indentation
        $lines = preg_split("/(\r\n|\r|\n)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $rebuilt = '';
        for ($i = 0; $i < count($lines); $i += 2) {
            $line = $lines[$i];
            $eol = isset($lines[$i+1]) ? $lines[$i+1] : '';
            if ($line !== '') {
                if (preg_match('/^([ \t]+)/', $line, $m)) {
                    $indent = $m[1];
                    $rest = substr($line, strlen($indent));
                    $rest = preg_replace('/[ \t]{2,}/', ' ', $rest);
                    $line = $indent . $rest;
                } else {
                    $line = preg_replace('/[ \t]{2,}/', ' ', $line);
                }
            }
            $rebuilt .= $line . $eol;
        }
        $string = $rebuilt;

        //Newlines with a space behind it - don't need that. (except in some cases, in which you'll miss 1 whitespace.
        // Well, too bad for you. File a PR <3
        $string = preg_replace('/\n /', "\n", $string);
        $string = preg_replace('/ \n/', "\n", $string);

        //Remove tabs before newlines
        $string = preg_replace('/\t /', "\t", $string);
        $string = preg_replace('/\t \n/', "\n", $string);
        $string = preg_replace('/\t\n/', "\n", $string);

        //Limit consecutive newlines to at most two
        $string = preg_replace('/\n{3,}/', "\n\n", $string);

        //Replace all \n with \r\n because some clients prefer that
        $string = preg_replace('/\n/', "\r\n", $string);

        $this->setText($string);
    }

    /**
     * @return string
     */
    private function getHtml()
    {
        return $this->html;
    }

    /**
     * @param $string
     * @return $this
     */
    private function setHtml($string)
    {
        $this->html = $string;
        return $this;
    }

    /**
     * @return string
     */
    private function getText()
    {
        return $this->text;
    }

    /**
     * @param $string
     * @return $this
     */
    private function setText($string)
    {
        $this->text = $string;
        return $this;
    }
}

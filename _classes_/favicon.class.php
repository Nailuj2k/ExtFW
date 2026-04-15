<?php
/**
 * FaviconImageGenerator  — genera todos los PNG de iconos con PHP GD (sin Composer)
 * FaviconHtmlGenerator   — genera icons.php y manifest.json con campos PWA completos
 *
 * Reemplaza las clases favicon/favicon-generator de Composer.
 * Requiere la extensión GD de PHP (estándar en PHP 7.2+).
 */


class FaviconImageGenerator
{
    private $dir;
    private $androidSrc;
    private $appleSrc;
    private $proportion; // % del lado menor del canvas que ocupa el logo en los splashes

    public function __construct($name, $dir, $androidSrc, $appleSrc, $proportion = 80.0)
    {
        $this->dir        = rtrim($dir, '/');
        $this->androidSrc = $androidSrc;
        $this->appleSrc   = $appleSrc;
        $this->proportion = (float)$proportion;
    }

    /* ── utilidades GD ──────────────────────────────────────────────── */

    private function load($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'jpg': case 'jpeg': return imagecreatefromjpeg($path);
            case 'gif':              return imagecreatefromgif($path);
            default:                 return imagecreatefrompng($path);
        }
    }

    /** Redimensiona a WxH manteniendo canal alfa */
    private function resize($src, $w, $h)
    {
        $dst = imagecreatetruecolor($w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefilledrectangle($dst, 0, 0, $w, $h,
            imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
        return $dst;
    }

    /** Crea imagen de arranque (splash): logo centrado sobre fondo de color sólido */
    private function splash($src, $canvasW, $canvasH, $bgHex = '#ffffff')
    {
        $dst = imagecreatetruecolor($canvasW, $canvasH);

        // fondo
        list($r, $g, $b) = sscanf(ltrim($bgHex, '#'), '%02x%02x%02x');
        imagefilledrectangle($dst, 0, 0, $canvasW, $canvasH,
            imagecolorallocate($dst, $r ?: 0, $g ?: 0, $b ?: 0));

        // tamaño del logo: proporción del lado menor del canvas
        $shorter = min($canvasW, $canvasH);
        $logoMax = (int)($shorter * $this->proportion / 100.0);
        $sw = imagesx($src);
        $sh = imagesy($src);
        if ($sw >= $sh) { $lw = $logoMax; $lh = (int)round($logoMax * $sh / $sw); }
        else            { $lh = $logoMax; $lw = (int)round($logoMax * $sw / $sh); }

        // centrado con alfa
        $ox = (int)(($canvasW - $lw) / 2);
        $oy = (int)(($canvasH - $lh) / 2);
        imagealphablending($dst, true);
        imagecopyresampled($dst, $src, $ox, $oy, 0, 0, $lw, $lh, $sw, $sh);

        return $dst;
    }

    private function save($img, $path)
    {
        imagepng($img, $path, 6);
        imagedestroy($img);
    }

    /* ── método público ─────────────────────────────────────────────── */

    public function generate()
    {
        $android = $this->load($this->androidSrc);
        $apple   = $this->load($this->appleSrc);
        $d       = $this->dir;

        // Android
        foreach ([36, 48, 72, 96, 144, 192] as $s)
            $this->save($this->resize($android, $s, $s), "$d/android-icon-{$s}x{$s}.png");

        // Apple touch icons
        foreach ([57, 60, 72, 76, 114, 120, 152, 180] as $s)
            $this->save($this->resize($apple, $s, $s), "$d/apple-icon-{$s}x{$s}.png");

        // Favicons estándar
        foreach ([16, 32, 96] as $s)
            $this->save($this->resize($android, $s, $s), "$d/favicon-{$s}x{$s}.png");

        // MS iconos cuadrados
        foreach ([70, 144, 150, 310] as $s)
            $this->save($this->resize($android, $s, $s), "$d/ms-icon-{$s}x{$s}.png");

        // MS wide 310x150 (logo centrado sobre fondo blanco)
        $this->save($this->splash($android, 310, 150, '#ffffff'), "$d/ms-icon-310x150.png");

        // Apple startup / splash screens
        $startups = [
            ['320x460',    320,  460],
            ['640x920',    640,  920],
            ['640x1096',   640, 1096],
            ['748x1024',   748, 1024],
            ['750x1024',   750, 1024],
            ['750x1294',   750, 1294],
            ['768x1004',   768, 1004],
            ['1182x2208', 1182, 2208],
            ['1242x2148', 1242, 2148],
            ['1496x2048', 1496, 2048],
            ['1536x2008', 1536, 2008],
        ];
        foreach ($startups as [$name, $w, $h])
            $this->save($this->splash($apple, $w, $h, '#ffffff'), "$d/apple-startup-{$name}.png");

        imagedestroy($android);
        imagedestroy($apple);
    }
}


class FaviconHtmlGenerator
{
    private $name;      // nombre en bruto (para JSON)
    private $nameHtml;  // nombre con htmlspecialchars (para HTML)
    private $dir;
    private $tileColor;
    private $themeColor;
    private $barColor;

    public function __construct($name, $dir, $tileColor, $themeColor, $barColor)
    {
        $this->name      = $name;
        $this->nameHtml  = htmlspecialchars($name, ENT_QUOTES);
        $this->dir       = rtrim($dir, '/');
        $this->tileColor = $tileColor;
        $this->themeColor= $themeColor;
        $this->barColor  = $barColor;
    }

    /**
     * Genera el HTML que se escribe en media/icons/icons.php.
     * $v = cadena de versión (sin '?v='), p.ej. date('dmyHis')
     */
    public function generate($v = '')
    {
        $d  = $this->dir;
        $n  = $this->nameHtml;
        $tc = $this->tileColor;
        $th = $this->themeColor;
        $bar= $this->barColor;
        $q  = $v ? '?v='.$v : '';

        $h  = '<meta name="application-name" content="'.$n.'" />';
        $h .= '<meta name="mobile-web-app-capable" content="yes" />';
        $h .= '<meta name="apple-mobile-web-app-title" content="'.$n.'" />';
        $h .= '<meta name="msapplication-TileColor" content="'.$tc.'" />';
        $h .= '<meta name="theme-color" content="'.$th.'" />';
        $h .= '<meta name="apple-mobile-web-app-status-bar-style" content="'.$bar.'" />';

        foreach ([57, 60, 72, 76, 114, 120, 152, 180] as $s)
            $h .= '<link rel="apple-touch-icon" sizes="'.$s.'x'.$s.'" href="'.$d.'/apple-icon-'.$s.'x'.$s.'.png'.$q.'" />';

        $h .= '<link rel="icon" type="image/png" href="'.$d.'/favicon-32x32.png'.$q.'" sizes="32x32" />';

        foreach ([36, 48, 72, 96, 144, 192] as $s)
            $h .= '<link rel="icon" type="image/png" href="'.$d.'/android-icon-'.$s.'x'.$s.'.png'.$q.'" sizes="'.$s.'x'.$s.'" />';

        $h .= '<link rel="icon" type="image/png" href="'.$d.'/favicon-96x96.png'.$q.'" sizes="96x96" />';
        $h .= '<link rel="icon" type="image/png" href="'.$d.'/favicon-16x16.png'.$q.'" sizes="16x16" />';

        $h .= '<meta name="msapplication-TileImage" content="'.$d.'/ms-icon-144x144.png'.$q.'" />';
        $h .= '<meta name="msapplication-square70x70logo" content="'.$d.'/ms-icon-70x70.png'.$q.'" />';
        $h .= '<meta name="msapplication-square150x150logo" content="'.$d.'/ms-icon-150x150.png'.$q.'" />';
        $h .= '<meta name="msapplication-wide310x150logo" content="'.$d.'/ms-icon-310x150.png'.$q.'" />';
        $h .= '<meta name="msapplication-square310x310logo" content="'.$d.'/ms-icon-310x310.png'.$q.'" />';

        $startups = [
            ['320x460',   '(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 1)'],
            ['640x920',   '(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)'],
            ['640x1096',  '(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)'],
            ['748x1024',  '(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 1) and (orientation: landscape)'],
            ['750x1024',  ''],
            ['750x1294',  '(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)'],
            ['768x1004',  '(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 1) and (orientation: portrait)'],
            ['1182x2208', '(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)'],
            ['1242x2148', '(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)'],
            ['1496x2048', '(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)'],
            ['1536x2008', '(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)'],
        ];
        foreach ($startups as [$size, $media])
            $h .= '<link href="'.$d.'/apple-startup-'.$size.'.png'.$q.'" media="'.$media.'" rel="apple-touch-startup-image" />';

        $h .= '<link rel="manifest" href="'.$d.'/manifest.json'.$q.'" />';

        return $h;
    }

    /**
     * Genera el contenido JSON del manifest.json con todos los campos PWA requeridos.
     */
    public function generateManifest()
    {
        $d = '/' . $this->dir;

        $icons = [];
        $densities = ['36'=>'0.75','48'=>'1.0','72'=>'1.5','96'=>'2.0','144'=>'3.0','192'=>'4.0'];
        foreach ($densities as $size => $density) {
            $icon = [
                'src'     => "$d/android-icon-{$size}x{$size}.png",
                'sizes'   => "{$size}x{$size}",
                'type'    => 'image/png',
                'density' => $density,
            ];
            if ($size === '192') $icon['purpose'] = 'any maskable';
            $icons[] = $icon;
        }

        $manifest = [
            'name'             => $this->name,
            'short_name'       => $this->name,
            'start_url'        => '/',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'theme_color'      => $this->themeColor,
            'background_color' => $this->themeColor,
            'icons'            => $icons,
        ];

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

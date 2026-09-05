<?php

class Mp3 {

  // https://github.com/JamesHeinrich/getID3
  // https://stackoverflow.com/questions/16746647/how-can-i-extract-the-album-art-from-an-mp3-id3-tag

  // https://www.hashbangcode.com/tag/php
  // https://www.hashbangcode.com/article/extracting-data-mp3-php

  protected $tags = [];

  protected $versions = [
    0x0 => '2.5',
    0x1 => 'x',
    0x2 => '2',
    0x3 => '1',
  ];

  protected $layers = [
    0x0 => 'x',
    0x1 => '3',
    0x2 => '2',
    0x3 => '1',
  ];

  protected $bitrates = [
    'V1L1' => [0,32,64,96,128,160,192,224,256,288,320,352,384,416,448],
    'V1L2' => [0,32,48,56, 64, 80, 96,112,128,160,192,224,256,320,384],
    'V1L3' => [0,32,40,48, 56, 64, 80, 96,112,128,160,192,224,256,320],
    'V2L1' => [0,32,48,56, 64, 80, 96,112,128,144,160,176,192,224,256],
    'V2L2' => [0, 8,16,24, 32, 40, 48, 56, 64, 80, 96,112,128,144,160],
    'V2L3' => [0, 8,16,24, 32, 40, 48, 56, 64, 80, 96,112,128,144,160],
  ];

  protected $samplerates = [
    '1'   => [44100, 48000, 32000],
    '2'   => [22050, 24000, 16000],
    '2.5' => [11025, 12000, 8000],
  ];

  protected $samples = [
    1 => [1 => 384, 2 => 1152, 3 => 1152,],
    2 => [1 => 384, 2 => 1152, 3 => 576,],
  ];

  protected $factor = 10;

  protected $filename;

  protected $data = [];

  protected $duration = 0;

  public function __construct($filename) {
    $this->filename = $filename;
  }

  public function readAudioData() {
    // Open the file.
    $fileHandle = fopen($this->filename, "rb");

    // Skip header.
    $offset = $this->headerOffset($fileHandle);
    fseek($fileHandle, $offset, SEEK_SET);

    while (!feof($fileHandle)) {
      // We nibble away at the file, 10 bytes at a time.
      $block = fread($fileHandle, 8);
      if (strlen($block) < 8) {
        break;
      }
      //looking for 1111 1111 111 (frame synchronization bits)
      else if ($block[0] == "\xff" && (ord($block[1]) & 0xe0)) {
        $fourbytes = substr($block, 0, 4);
        // The first block of bytes will always be 0xff in the framesync
        // so we ignore $fourbytes[0] but need to process $fourbytes[1] for
        // the version information.
        $b1 = ord($fourbytes[1]);
        $b2 = ord($fourbytes[2]);
        $b3 = ord($fourbytes[3]);

        // Extract the version and create a simple version for lookup.
        $version = $this->versions[($b1 & 0x18) >> 3];
        $simpleVersion = ($version == '2.5' ? 2 : $version);

        // Extract layer.
        $layer = $this->layers[($b1 & 0x06) >> 1];

        // Extract protection bit.
        $protectionBit = ($b1 & 0x01);

        // Extract bitrate.
        $bitrateKey = sprintf('V%dL%d', $simpleVersion, $layer);
        $bitrateId = ($b2 & 0xf0) >> 4;
        $bitrate = isset($this->bitrates[$bitrateKey][$bitrateId]) ? $this->bitrates[$bitrateKey][$bitrateId] : 0;

        // Extract the sample rate.
        $sampleRateId = ($b2 & 0x0c) >> 2;
        $sampleRate = isset($this->samplerates[$version][$sampleRateId]) ? $this->samplerates[$version][$sampleRateId] : 0;

        // Extract padding bit.
        $paddingBit = ($b2 & 0x02) >> 1;

        // Extract framesize.
        if ($layer == 1) {
          $framesize = intval(((12 * $bitrate * 1000 / $sampleRate) + $paddingBit) * 4);
        }
        else {
          // Later 2 and 3.
          $framesize = intval(((144 * $bitrate * 1000) / $sampleRate) + $paddingBit);
        }

        // Extract samples.
        $frameSamples = $this->samples[$simpleVersion][$layer];

        // Extract other bits.
        $channelModeBits = ($b3 & 0xc0) >> 6;
        $modeExtensionBits = ($b3 & 0x30) >> 4;
        $copyrightBit = ($b3 & 0x08) >> 3;
        $originalBit = ($b3 & 0x04) >> 2;
        $emphasis = ($b3 & 0x03);

        // Calculate the duration and add this to the running total.
        $this->duration += ($frameSamples / $sampleRate);

        // Read the frame data into memory.
        $frameData = fread($fileHandle, $framesize - 6);
        //
        // $average = 0;
        // $sampleBytes = 8;
        // for ($i = 0; $i <= $sampleBytes; $i++) {
        //   $average += ord($frameData[$i]);
        // }
        // $this->data[0][$this->duration * $this->factor] = $average / $sampleBytes;

        $this->data[0][$this->duration * $this->factor] = ord($frameData[0]);
        $this->data[1][$this->duration * $this->factor] = ord($frameData[2]);
        $this->data[2][$this->duration * $this->factor] = ord($frameData[9]);
        $this->data[3][$this->duration * $this->factor] = ord($frameData[16]);
        $this->data[4][$this->duration * $this->factor] = ord($frameData[23]);
      }
      else if (substr($block, 0, 3) == 'TAG') {
        // If this is a tag then jump over it.
        fseek($fileHandle, 128 - 10, SEEK_CUR);
      }
      else {
        fseek($fileHandle, -9, SEEK_CUR);
      }
    }
    $this->tags['BitRate'] = $bitrate;
    $s = floor($this->duration);
    $m = floor($s / 60);
    $this->tags['Duration'] = $m .':'. ($s-($m*60));
    //$this->tags['Duration'] = $s;
  }

  public function renderAsImage() {
    $height = 500;

    // Create image resource.
    $image = imagecreate($this->duration * $this->factor, $height);
    // Set background colour to black.
    imagecolorallocate($image, 0, 0, 0);

    // Assign a collection of foreground colours we can use.
    $colors[] = imagecolorallocate($image, 255, 255, 255);
    $colors[] = imagecolorallocate($image, 255, 0, 0);
    $colors[] = imagecolorallocate($image, 0, 255, 0);
    $colors[] = imagecolorallocate($image, 0, 0, 255);
    $colors[] = imagecolorallocate($image, 128, 0, 0);
    $colors[] = imagecolorallocate($image, 0, 128, 0);
    $colors[] = imagecolorallocate($image, 0, 0, 128);

    // Loop through the data and draw onto the canvas.
    foreach ($this->data as $index => $data) {
      foreach ($data as $dataDuration => $dataBit) {
        imagefilledellipse($image, $dataDuration, (($dataBit * 2) - $height) * -1, 2, 2, $colors[$index]);
      }
    }

    // Render the image out, using the original filename as part of the image name.
    imagepng($image, str_replace('.mp3','',$this->filename) . '.png');
  }

  /**
   *
   */
  public function headerOffset($fileHandle) {
    // Extract the first 10 bytes of the file and set the handle back to 0.
    fseek($fileHandle, 0);
    $block = fread($fileHandle, 10);
    fseek($fileHandle, 0);
    $offset = 0;

    if (substr($block, 0, 3) == "ID3") {
      // We can ignore bytes 3 and 4 so they aren't extracted here.
      // Extract ID3 flags.
      $id3v2Flags = ord($block[5]);
      $flagUnsynchronisation  = $id3v2Flags & 0x80 ? 1 : 0;
      $flagExtendedHeader    = $id3v2Flags & 0x40 ? 1 : 0;
      $flagExperimental   = $id3v2Flags & 0x20 ? 1 : 0;
      $flagFooterPresent     = $id3v2Flags & 0x10 ? 1 : 0;

      // Extract the length bytes.
      $length0 = ord($block[6]);
      $length1 = ord($block[7]);
      $length2 = ord($block[8]);
      $length3 = ord($block[9]);

      // Check to make sure this is a safesynch integer by looking at the starting bit.
      if ((($length0 & 0x80) == 0) && (($length1 & 0x80) == 0) && (($length2 & 0x80) == 0) && (($length3 & 0x80) == 0)) {
        // Extract the tag size.
        $tagSize = $length0 << 21 | $length1 << 14 | $length2 << 7 | $length3;
        // Find out the length of other elements based on header size and footer flag.
        $headerSize = 10;
        $footerSize = $flagFooterPresent ? 10 : 0;
        // Add this all together.
        $offset = $headerSize + $tagSize + $footerSize;
      }
    }
    return $offset;
  }

  public function readTags() {
    $fileHandle = fopen($this->filename, 'rb');
    $headerOffset = $this->headerOffset($fileHandle);
    $binary = fread($fileHandle, $headerOffset);

    if (substr($binary, 0, 3) == "ID3") {
      // ID3 tags detected.
      $this->tags['FileName'] = $this->filename;
      $this->tags['TAG'] = substr($binary, 0, 3);
      $this->tags['Version'] = hexdec(bin2hex(substr($binary, 3, 1))) . "." . hexdec(bin2hex(substr($binary, 4, 1)));
    }
    else {
      $this->tags['FileName'] = $this->filename;
      return;
    }

    if ($this->tags['Version'] == "2.0") {
      $id3v22 = ["TT2", "TAL", "TP1", "TRK", "TYE", "TLE", "ULT"];
      for ($i = 0; $i < count($id3v22); $i++) {
        // Look for each tag within the data of the file.
        if (strpos($binary, $id3v22[$i] . chr(0)) != FALSE) {

          // Extract the tag position and length of data.
          $pos = strpos($binary, $id3v22[$i] . chr(0));
          $len = hexdec(bin2hex(substr($binary, ($pos + 3), 3)));
          $data = substr($binary, ($pos + 6), $len);
          $tag = substr($binary, $pos, 3);

          // Extract data.
          $tagData = '';
          for ($a = 0; $a <= strlen($data); $a++) {
            $char = substr($data, $a, 1);
            if (ord($char) != 0 && ord($char) != 3 && ord($char) != 225 && ctype_print($char)) {
              $tagData .= $char;
            }
            elseif (ord($char) == 225 || ord($char) == 13) {
              $tagData .= "\n";
            }
          }

          if ($tag == "TT2") {
            $this->tags['Title'] = $tagData;
          }

          if ($tag == "TAL") {
            $this->tags['Album'] = $tagData;
          }

          if ($tag == "TP1") {
            $this->tags['Author'] = $tagData;
          }

          if ($tag == "TRK") {
            $this->tags['Track'] = $tagData;
          }

          if ($tag == "TYE") {
            $this->tags['Year'] = $tagData;
          }

          if ($tag == "TLE") {
            $this->tags['Length'] = $tagData;
          }

          if ($tag == "ULT") {
            $this->tags['Lyric'] = $tagData;
          }
        }
      }
    }

    if ($this->tags['Version'] == "4.0" || $this->tags['Version'] == "3.0") {
      $id3v23 = ["TIT2", "TALB", "TPE1", "TRCK", "TYER", "TLEN", "USLT"];
      // Look for each tag within the data of the file.
      for ($i = 0; $i < count($id3v23); $i++) {
        if (strpos($binary, $id3v23[$i] . chr(0)) != FALSE) {

          // Extract the tag position and length of data.
          $pos = strpos($binary, $id3v23[$i] . chr(0));
          $len = hexdec(bin2hex(substr($binary, ($pos + 5), 3)));
          $data = substr($binary, ($pos + 10), $len);
          $tag = substr($binary, $pos, 4);

          // Extract tag and data.
          $tagData = '';
          for ($a = 0; $a <= strlen($data); $a++) {
            $char = substr($data, $a, 1);
            if (ord($char) != 0 && ord($char) != 3 && ord($char) != 225 && ctype_print($char)) {
              $tagData .= $char;
            }
            elseif (ord($char) == 225 || ord($char) == 13) {
              $tagData .= "\n";
            }
          }

          if ($tag == "TIT2") {
            $this->tags['Title'] = $tagData;
          }

          if ($tag == "TALB") {
            $this->tags['Album'] = $tagData;
          }

          if ($tag == "TPE1") {
            $this->tags['Author'] = $tagData;
          }

          if ($tag == "TRCK") {
            $this->tags['Track'] = $tagData;
          }

          if ($tag == "TYER") {
            $this->tags['Year'] = $tagData;
          }

          if ($tag == "TLEN") {
            $this->tags['Length'] = $tagData;
          }

          if ($tag == "USLT") {
            $this->tags['Lyric'] = $tagData;
          }
        }
      }
    }
  }

  public function getTags() {
    return $this->tags;
  }
}
<link  href="<?=SCRIPT_DIR_MODULE?>/icono.min.css?ver=1.3.3" rel="stylesheet" type="text/css" />
<?php

include(SCRIPT_DIR_MODULE.'/mp3.class.php');

$file_mp3 = DOKUX_INBOX_DIR.'/'.'marios_frangoulis__skoteinos_erwtas__03__neraida.mp3';
/***
$filename = Str::get_file_name($file_mp3);
$ext = Str::get_file_extension($file_mp3);
$name = $filename;

   Vars::debug_var($filename);
   Vars::debug_var($ext);

$url_mp3  = MODULE.'/raw/filename='.$filename.'/name='.$name.'/ext='.$ext;
$url_img  = MODULE.'/raw/filename='.$filename.'/path=inbox/mode=inline/name='.$name.'/ext=png';
Vars::debug_var($url_mp3);

echo '<a href="/'.APP_NAME.'/raw/path=inbox/filename=marios_frangoulis__skoteinos_erwtas__03__neraida/name=maraida/ext=mp3">download</a>';
**/
if (file_exists($file_mp3)){
/*
    $mp3 = new Mp3($file_mp3);
    $mp3->readTags();
    $mp3->readAudioData();
    $mp3->renderAsImage();

    ?><img style="max-height:80px;" src="<?=$url_img?>"><?php

   Vars::debug_var($mp3->getTags());

*/

/**
$tag = id3_get_tag( $file_mp3 );
print_r($tag);

*/

include(SCRIPT_DIR_LIB.'/getID3/getid3/getid3.php');

$getID3 = new getID3;
$ThisFileInfo = $getID3->analyze($file_mp3);
$getID3->CopyTagsToComments($ThisFileInfo);
/*******
   Vars::debug_var( $ThisFileInfo['comments_html'] , 'Comments HTML');
// Vars::debug_var( $ThisFileInfo['tags']['id3v2'] , 'Tags');
   Vars::debug_var( $ThisFileInfo['audio']         , 'Audio ');
   Vars::debug_var( $ThisFileInfo['playtime_string'] , 'Playtime' );

  if(isset($ThisFileInfo['comments']['picture'][0])){
     $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
  }
  
  echo 'Thumb<br /><img id="FileImage" width="150" src="'.@$Image.'" height="150">';
*/

        foreach($ThisFileInfo['comments_html'] as $k=>$v){
            $text .= $k.': '.$v[0]."\n";
        }

        foreach($ThisFileInfo['audio']['streams'][0] as $k=>$v){
            $text .= $k.': '.$v."\n";
        }

        $text .= 'Playtime: '.$ThisFileInfo['playtime_string']."\n";
        /**
        if(isset($ThisFileInfo['comments']['picture'][0])){
             $img_src='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
             $text .= 'Thumb<br /><img id="FileImage" style="width:150px;" src="'.@$img_src.'">';
        }
        **/
        if (isset($ThisFileInfo['comments']['picture']['0']['data'])) {
            $image = $ThisFileInfo['comments']['picture']['0']['data'];
            if(file_put_contents(DOKUX_INBOX_DIR . '/imageTest.jpg', $image)) {
                echo 'Image Added';
            } else {
                echo 'Image Not Added';
            }
        }


        echo '<pre>'.$text.'</pre>';

}else{

  echo 'ko';

}
/**/






?>




<!--
<audio>
  <source src="https://ia800905.us.archive.org/19/items/FREE_background_music_dhalius/backsound.mp3"  type="audio/mp3">
</audio> 
-->
<!--<div style="width: 50px; height: 50px;"></div>-->
<div class="audio-player">
  <div class="timeline">
    <div class="progress"></div>
  </div>
  <div class="controls">
    <div class="play-container">
      <div class="toggle-play play">
    </div>
    </div>
    <div class="time">
      <div class="current">0:00</div>
      <div class="divider">/</div>
      <div class="length"></div>
    </div>
    <div class="name">Music Song</div>
<!--     credit for icon to https://saeedalipoor.github.io/icono/ -->
    <div class="volume-container">
      <div class="volume-button">
        <div class="volume icono-volumeMedium"></div>
      </div>
      
      <div class="volume-slider">
        <div class="volume-percentage"></div>
      </div>
    </div>
  </div>
</div>
<!-- https://codepen.io/EmNudge/pen/rRbLJQ -->
<style>
.audio-player {
  height: 24px;
  width: 350px;
  background: #444;
/*  box-shadow: 0 0 20px 0 #000a;*/
  font-family: arial;
  color: white;
  font-size: 0.75em;
  overflow: hidden;
  display: grid;
  grid-template-rows: 3px auto;
  border-top:1px solid #efefef;
}
.audio-player .timeline {
  background: white;
  width: 100%;
  position: relative;
  cursor: pointer;
  box-shadow: 0 2px 10px 0 #0008;
}
.audio-player .timeline .progress {
  background: coral;
  width: 0%;
  height: 100%;
  transition: 0.25s;
}
.audio-player .controls {
  display: flex;
  justify-content: space-between;
  align-items: stretch;
  padding: 0 20px;
}
.audio-player .controls > * {
  display: flex;
  justify-content: center;
  align-items: center;
}
.audio-player .controls .toggle-play.play {
  cursor: pointer;
  position: relative;
  left: 0;
  height: 0;
  width: 0;
  border: 7px solid #0000;
  border-left: 13px solid white;
}
.audio-player .controls .toggle-play.play:hover {
  transform: scale(1.1);
}
.audio-player .controls .toggle-play.pause {
  height: 15px;
  width: 20px;
  cursor: pointer;
  position: relative;
}
.audio-player .controls .toggle-play.pause:before {
  position: absolute;
  top: 0;
  left: 0px;
  background: white;
  content: "";
  height: 15px;
  width: 3px;
}
.audio-player .controls .toggle-play.pause:after {
  position: absolute;
  top: 0;
  right: 8px;
  background: white;
  content: "";
  height: 15px;
  width: 3px;
}
.audio-player .controls .toggle-play.pause:hover {
  transform: scale(1.1);
}
.audio-player .controls .time {
  display: flex;
}
.audio-player .controls .time > * {
  padding: 2px;
}
.audio-player .controls .volume-container {
  cursor: pointer;
  position: relative;
  z-index: 2;
}
.audio-player .controls .volume-container .volume-button {
  height: 20px;
  display: flex;
  align-items: center;
}
.audio-player .controls .volume-container .volume-button .volume {
  transform: scale(0.7);
}
.audio-player .controls .volume-container .volume-slider {
  position: absolute;
  left: -3px;
  top: 3px;
  z-index: -1;
  width: 0;
  height: 16px;
  background: white;
  box-shadow: 0 0 20px #000a;
  transition: 0.25s;
}
.audio-player .controls .volume-container .volume-slider .volume-percentage {
  background: coral;
  height: 100%;
  width: 75%;
}
.audio-player .controls .volume-container:hover .volume-slider {
  left: -123px;
  width: 120px;
}
</style>
<script>
// Possible improvements:
// - Change timeline and volume slider into input sliders, reskinned
// - Change into Vue or React component
// - Be able to grab a custom title instead of "Music Song"
// - Hover over sliders to see preview of timestamp/volume change

const audioPlayer = document.querySelector(".audio-player");
const audio = new Audio(
  "https://ia800905.us.archive.org/19/items/FREE_background_music_dhalius/backsound.mp3"
);
//credit for song: Adrian kreativaweb@gmail.com

console.dir(audio);

audio.addEventListener(
  "loadeddata",
  () => {
    audioPlayer.querySelector(".time .length").textContent = getTimeCodeFromNum(
      audio.duration
    );
    audio.volume = .75;
  },
  false
);

//click on timeline to skip around
const timeline = audioPlayer.querySelector(".timeline");
timeline.addEventListener("click", e => {
  const timelineWidth = window.getComputedStyle(timeline).width;
  const timeToSeek = e.offsetX / parseInt(timelineWidth) * audio.duration;
  audio.currentTime = timeToSeek;
}, false);

//click volume slider to change volume
const volumeSlider = audioPlayer.querySelector(".controls .volume-slider");
volumeSlider.addEventListener('click', e => {
  const sliderWidth = window.getComputedStyle(volumeSlider).width;
  const newVolume = e.offsetX / parseInt(sliderWidth);
  audio.volume = newVolume;
  audioPlayer.querySelector(".controls .volume-percentage").style.width = newVolume * 100 + '%';
}, false)

//check audio percentage and update time accordingly
setInterval(() => {
  const progressBar = audioPlayer.querySelector(".progress");
  progressBar.style.width = audio.currentTime / audio.duration * 100 + "%";
  audioPlayer.querySelector(".time .current").textContent = getTimeCodeFromNum(
    audio.currentTime
  );
}, 500);

//toggle between playing and pausing on button click
const playBtn = audioPlayer.querySelector(".controls .toggle-play");
playBtn.addEventListener(
  "click",
  () => {
    if (audio.paused) {
      playBtn.classList.remove("play");
      playBtn.classList.add("pause");
      audio.play();
    } else {
      playBtn.classList.remove("pause");
      playBtn.classList.add("play");
      audio.pause();
    }
  },
  false
);

audioPlayer.querySelector(".volume-button").addEventListener("click", () => {
  const volumeEl = audioPlayer.querySelector(".volume-container .volume");
  audio.muted = !audio.muted;
  if (audio.muted) {
    volumeEl.classList.remove("icono-volumeMedium");
    volumeEl.classList.add("icono-volumeMute");
  } else {
    volumeEl.classList.add("icono-volumeMedium");
    volumeEl.classList.remove("icono-volumeMute");
  }
});

//turn 128 seconds into 2:08
function getTimeCodeFromNum(num) {
  let seconds = parseInt(num);
  let minutes = parseInt(seconds / 60);
  seconds -= minutes * 60;
  const hours = parseInt(minutes / 60);
  minutes -= hours * 60;

  if (hours === 0) return `${minutes}:${String(seconds % 60).padStart(2, 0)}`;
  return `${String(hours).padStart(2, 0)}:${minutes}:${String(
    seconds % 60
  ).padStart(2, 0)}`;
}

</script>

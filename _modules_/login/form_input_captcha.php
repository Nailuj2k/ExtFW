<?php
 
        if(CFG::$vars['captcha']['google_v3']['enabled']) {
        }else{ 
            $_captcha = Captcha::create();
            ?>
            <div class="control-group row">
                <label class="control-label" for="captcha" title="<?=$_captcha['help']?>">Resolver (Anti-spam)</label>
                <div class="controls controls-input">
                    <input type="text" id="captcha" name="captcha"  autocomplete="off" placeholder="<?=$_captcha['label']?>"  title="<?=$_captcha['help']?>">
                </div>
            </div>
            <?php
        }

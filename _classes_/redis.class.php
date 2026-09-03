<?php

require_once SCRIPT_DIR_LIB.'/redisent/Redis.php';

    class Redis extends \redisent\Redis {

    public function delete($pattern){

        $keys = $this->keys($pattern);   // all keys will match this.

        foreach ($keys as $key) {
            $this->del($key);
        }

    }

}


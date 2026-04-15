<?php

    // Nostr relay hosts for CSP connect-src
    // Appended AFTER DB config load (crud/init.php) so DB values are preserved
    $cfg['options']['csp_headers']['connect_src'] .= ' wss: *';// relay.damus.io nos.lol relay.nostr.band relay.primal.net relay.snort.social relay.nos.social lang.relays.land/es purplepag.es';
    $cfg['options']['csp_headers']['media_src']   .= ' video.nostr.build blossom.primal.net files.catbox.moe r2a.primal.net videos.pexels.com void.cat video.twimg.com';
    $cfg['options']['csp_headers']['frame_src']   .= ' www.youtube-nocookie.com';

    
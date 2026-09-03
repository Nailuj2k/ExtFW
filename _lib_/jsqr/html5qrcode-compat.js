/**
 * Html5Qrcode compatibility wrapper — uses jsQR internally
 * Drop-in replacement for html5-qrcode.min.js (~375 KB → ~127 KB)
 *
 * Implements the subset of the Html5Qrcode API used in this project:
 *   new Html5Qrcode(elementId)
 *   .start(camIdOrConstraint, { fps, qrbox }, onSuccess, onError) → Promise
 *   .stop() → Promise
 *   Html5Qrcode.getCameras() → Promise<[{id, label}]>
 */
var Html5Qrcode = (function () {

    function Html5Qrcode(elementId) {
        this._id     = elementId;
        this._stream = null;
        this._raf    = null;
        this._video  = null;
    }

    /* Static ----------------------------------------------------------- */

    Html5Qrcode.getCameras = async function () {
        // Trigger permission dialog so labels are populated
        try {
            var tmp = await navigator.mediaDevices.getUserMedia({ video: true });
            tmp.getTracks().forEach(function (t) { t.stop(); });
        } catch (e) {}

        var devices = await navigator.mediaDevices.enumerateDevices();
        var idx = 0;
        return devices
            .filter(function (d) { return d.kind === 'videoinput'; })
            .map(function (d) { idx++; return { id: d.deviceId, label: d.label || ('Camera ' + idx) }; });
    };

    /* Instance --------------------------------------------------------- */

    Html5Qrcode.prototype.start = function (camIdOrConstraint, cfg, onSuccess, onError) {
        var self        = this;
        var fps         = (cfg && cfg.fps) || 10;
        var msPerFrame  = 1000 / fps;
        var lastAt      = 0;

        var videoConstraint = typeof camIdOrConstraint === 'string'
            ? { deviceId: { exact: camIdOrConstraint } }
            : camIdOrConstraint;   // e.g. { facingMode: 'environment' }

        return navigator.mediaDevices.getUserMedia({ video: videoConstraint })
            .then(function (stream) {
                self._stream = stream;

                var el = document.getElementById(self._id);
                if (!el) {
                    stream.getTracks().forEach(function (t) { t.stop(); });
                    throw new Error('Html5Qrcode: element not found — ' + self._id);
                }

                var video = document.createElement('video');
                video.setAttribute('playsinline', 'true');
                video.setAttribute('muted', 'true');
                video.style.cssText = 'width:100%;display:block;border-radius:4px;';
                video.srcObject = stream;
                el.innerHTML = '';
                el.appendChild(video);
                self._video = video;

                var canvas = document.createElement('canvas');
                var ctx    = canvas.getContext('2d', { willReadFrequently: true });

                return new Promise(function (resolve, reject) {
                    video.onerror = reject;
                    video.onloadedmetadata = function () {
                        video.play().then(function () {
                            resolve();

                            function tick(now) {
                                if (!self._stream) return;               // stopped
                                self._raf = requestAnimationFrame(tick);
                                if (now - lastAt < msPerFrame) return;   // fps throttle
                                lastAt = now;
                                if (video.readyState < 2) return;        // not enough data yet

                                if (canvas.width  !== video.videoWidth)  canvas.width  = video.videoWidth;
                                if (canvas.height !== video.videoHeight) canvas.height = video.videoHeight;
                                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                                var img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
                                var code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });

                                if (code) {
                                    if (typeof onSuccess === 'function') onSuccess(code.data, code);
                                } else {
                                    if (typeof onError === 'function') onError('QR not found');
                                }
                            }

                            self._raf = requestAnimationFrame(tick);
                        }).catch(reject);
                    };
                });
            });
    };

    Html5Qrcode.prototype.stop = function () {
        var self = this;
        return new Promise(function (resolve) {
            if (self._raf)    { cancelAnimationFrame(self._raf); self._raf = null; }
            if (self._stream) { self._stream.getTracks().forEach(function (t) { t.stop(); }); self._stream = null; }
            if (self._video)  { self._video.srcObject = null; self._video = null; }
            var el = document.getElementById(self._id);
            if (el) el.innerHTML = '';
            resolve();
        });
    };

    return Html5Qrcode;
}());

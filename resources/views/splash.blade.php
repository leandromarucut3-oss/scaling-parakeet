<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Welcome</title>
    <style>
        html,body{height:100%;margin:0}
        .splash{position:fixed;inset:0;background:#000;display:flex;align-items:center;justify-content:center}
        video{width:100%;height:100%;object-fit:cover}
        .overlay-play{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:20}
        .play-btn{width:84px;height:84px;border-radius:999px;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;cursor:pointer}
    </style>
</head>
<body>
    <div class="splash" id="splash">
        <video id="splashVideo" autoplay muted playsinline preload="auto">
            <source src="/assets/download.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>


        
    </div>

    <script>
        const video = document.getElementById('splashVideo');
        let clickedToUnmute = false;

        function goToLogin(){ window.location = '/login'; }

        // Try to play muted immediately (most browsers allow muted autoplay)
        document.addEventListener('DOMContentLoaded', () => {
            video.play().catch(()=>{});
        });

        // If the user clicks anywhere, unmute and play with sound (one gesture)
        document.addEventListener('click', function handleClick(){
            if (clickedToUnmute) return;
            clickedToUnmute = true;
            try { video.muted = false; video.play(); } catch(e) {}
            document.removeEventListener('click', handleClick);
        }, { once: true });

        let endTimeout = null;

        // Fallback: some browsers may not reliably fire ended; use duration to schedule redirect
        video.addEventListener('loadedmetadata', () => {
            try {
                if (video.duration && isFinite(video.duration) && video.duration > 0) {
                    endTimeout = setTimeout(goToLogin, Math.ceil(video.duration * 1000) + 500);
                }
            } catch (e) {}
        });

        function redirectToLogin() {
            if (endTimeout) { clearTimeout(endTimeout); endTimeout = null; }
            goToLogin();
        }

        video.addEventListener('ended', redirectToLogin);
    </script>
</body>
</html>

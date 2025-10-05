// Load YouTube Iframe API
let tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
let firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// Create player in video-container
let player;
function onYouTubeIframeAPIReady() {
    player = new YT.Player('video-container', {
        height: '360',
        width: '640',
        videoId: 'ZpblP61AYAc', // Replace with Qamarshan’s YouTube video ID
        playerVars: {
            autoplay: 0,
            controls: 1,
            rel: 0,
            modestbranding: 1
        }
    });
}

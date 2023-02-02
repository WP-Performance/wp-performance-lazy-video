document.addEventListener("DOMContentLoaded", function () {
  var lazyVideos = [].slice.call(
    document.querySelectorAll("video.wp-video-lazy")
  );

  if ("IntersectionObserver" in window) {
    var lazyVideoObserver = new IntersectionObserver(function (
      entries,
      observer
    ) {
      entries.forEach(function (video) {
        if (video.isIntersecting) {
          if (video.target.dataset.src !== null) {
            video.target.src = video.target.dataset.src;
          } else {
            for (var source in video.target.children) {
              var videoSource = video.target.children[source];
              if (
                typeof videoSource.tagName === "string" &&
                videoSource.tagName === "SOURCE"
              ) {
                videoSource.src = videoSource.dataset.src;
              }
            }
          }

          video.target.load();
          video.target.classList.remove("wp-video-lazy");
          lazyVideoObserver.unobserve(video.target);
        }
      });
    });

    lazyVideos.forEach(function (lazyVideo) {
      lazyVideoObserver.observe(lazyVideo);
    });
  }
});

(() => {
  const IFRAME_SRC = "https://example.com";
  const FRAME_ID = "external-app";

  const getFrameHeight = () => {
    const body = document.body;
    const html = document.documentElement;
    return Math.max(
      body.scrollHeight,
      body.offsetHeight,
      html.clientHeight,
      html.scrollHeight,
      html.offsetHeight,
    );
  };

  const resizeApp = () => {
    if (window.BX24 && typeof window.BX24.resizeWindow === "function") {
      const height = getFrameHeight();
      window.BX24.resizeWindow(window.innerWidth, height);
    }
  };

  const init = () => {
    const iframe = document.getElementById(FRAME_ID);
    if (!iframe) {
      return;
    }

    iframe.src = IFRAME_SRC;
    iframe.addEventListener("load", () => {
      resizeApp();
    });

    window.addEventListener("resize", () => {
      resizeApp();
    });

    resizeApp();
  };

  if (window.BX24 && typeof window.BX24.init === "function") {
    window.BX24.init(() => {
      init();
    });
  } else {
    document.addEventListener("DOMContentLoaded", init);
  }
})();

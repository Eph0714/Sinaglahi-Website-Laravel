// Universal "Back" button used at the top of every page's content
// (public site, Admin area, Artist area). Prefers real browser history so the
// user lands exactly where they came from; falls back to the given area's
// home page when there's nothing to go back to (e.g. the page was opened
// directly in a new tab, or the previous page was on a different site).
window.sinaglahiGoBack = function (fallbackUrl) {
  var cameFromThisSite = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
  if (cameFromThisSite && window.history.length > 1) {
    window.history.back();
  } else {
    window.location.href = fallbackUrl;
  }
};

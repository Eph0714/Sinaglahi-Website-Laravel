/*
 * Minimal dependency-free lightbox (Section 62: large image view, next/previous,
 * close). Any element with class "lightbox-open" and the data attributes below
 * becomes part of the lightbox group on the page it appears on:
 *   data-full   - full-size image URL (required)
 *   data-title  - artwork/artist title (optional)
 *   data-meta   - secondary line, e.g. "Artist · Medium · Year" (optional)
 *   data-detail - URL to the full detail page, shown as a link (optional)
 *   data-desc   - longer description shown below the meta line (optional)
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var triggers = Array.prototype.slice.call(document.querySelectorAll(".lightbox-open"));
        if (triggers.length === 0) return;

        var overlay = document.createElement("div");
        overlay.className = "lightbox-overlay";
        overlay.setAttribute("role", "dialog");
        overlay.setAttribute("aria-modal", "true");
        overlay.hidden = true;
        overlay.innerHTML =
            '<button type="button" class="lightbox-close" aria-label="Close">&times;</button>' +
            '<button type="button" class="lightbox-nav lightbox-prev" aria-label="Previous artwork">&#8249;</button>' +
            '<div class="lightbox-content">' +
            '  <img class="lightbox-img" alt="" />' +
            '  <div class="lightbox-caption">' +
            '    <div class="lightbox-title"></div>' +
            '    <div class="lightbox-meta"></div>' +
            '    <div class="lightbox-desc"></div>' +
            '    <a class="lightbox-detail-link" href="#">View Full Details &rarr;</a>' +
            '  </div>' +
            '</div>' +
            '<button type="button" class="lightbox-nav lightbox-next" aria-label="Next artwork">&#8250;</button>';
        document.body.appendChild(overlay);

        var img = overlay.querySelector(".lightbox-img");
        var titleEl = overlay.querySelector(".lightbox-title");
        var metaEl = overlay.querySelector(".lightbox-meta");
        var descEl = overlay.querySelector(".lightbox-desc");
        var detailLink = overlay.querySelector(".lightbox-detail-link");
        var current = 0;

        function show(index) {
            current = (index + triggers.length) % triggers.length;
            var t = triggers[current];
            img.src = t.getAttribute("data-full");
            img.alt = t.getAttribute("data-title") || "";
            titleEl.textContent = t.getAttribute("data-title") || "";
            metaEl.textContent = t.getAttribute("data-meta") || "";
            var desc = t.getAttribute("data-desc") || "";
            descEl.textContent = desc;
            descEl.hidden = desc.length === 0;
            var detailUrl = t.getAttribute("data-detail");
            if (detailUrl) {
                detailLink.href = detailUrl;
                detailLink.hidden = false;
            } else {
                detailLink.hidden = true;
            }
        }

        function open(index) {
            show(index);
            overlay.hidden = false;
            document.body.style.overflow = "hidden";
        }

        function close() {
            overlay.hidden = true;
            document.body.style.overflow = "";
        }

        triggers.forEach(function (t, i) {
            t.addEventListener("click", function (e) {
                e.preventDefault();
                open(i);
            });
        });

        overlay.querySelector(".lightbox-close").addEventListener("click", close);
        overlay.querySelector(".lightbox-prev").addEventListener("click", function () { show(current - 1); });
        overlay.querySelector(".lightbox-next").addEventListener("click", function () { show(current + 1); });
        overlay.addEventListener("click", function (e) {
            if (e.target === overlay) close();
        });
        document.addEventListener("keydown", function (e) {
            if (overlay.hidden) return;
            if (e.key === "Escape") close();
            if (e.key === "ArrowLeft") show(current - 1);
            if (e.key === "ArrowRight") show(current + 1);
        });
    });
})();

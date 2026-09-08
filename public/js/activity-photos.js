/*
 * Drives the admin "Manage Photos" page: drag-and-drop bulk upload with a
 * progress bar, drag-to-reorder, inline caption editing, set-cover, single
 * and bulk delete. Plain fetch/XHR - no framework - matching the rest of
 * the admin area.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var activityId = window.__activityId;
        var token = window.__antiforgeryToken;
        var baseUrl = "/admin/activities";

        function toast(message, isError) {
            // Minimal one-off toast, styled like the layout's Bootstrap toasts,
            // for feedback on actions that don't cause a full page reload.
            var container = document.querySelector(".toast-container") || (function () {
                var c = document.createElement("div");
                c.className = "toast-container position-fixed bottom-0 end-0 p-3";
                c.style.zIndex = 4000;
                document.body.appendChild(c);
                return c;
            })();
            var el = document.createElement("div");
            el.className = "toast align-items-center text-white bg-" + (isError ? "danger" : "success") + " border-0";
            el.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
            container.appendChild(el);
            new bootstrap.Toast(el, { delay: 4000 }).show();
        }

        // ---------------- Upload ----------------
        var dropzone = document.getElementById("photoDropzone");
        var fileInput = document.getElementById("photoFileInput");
        var selectedCount = document.getElementById("selectedCount");
        var startUploadBtn = document.getElementById("startUploadBtn");
        var progressWrap = document.getElementById("uploadProgressWrap");
        var progressBar = document.getElementById("uploadProgressBar");
        var pendingFiles = [];

        if (dropzone) {
            dropzone.addEventListener("click", function () { fileInput.click(); });
            ["dragenter", "dragover"].forEach(function (evt) {
                dropzone.addEventListener(evt, function (e) { e.preventDefault(); dropzone.classList.add("dragover"); });
            });
            ["dragleave", "drop"].forEach(function (evt) {
                dropzone.addEventListener(evt, function (e) { e.preventDefault(); dropzone.classList.remove("dragover"); });
            });
            dropzone.addEventListener("drop", function (e) {
                addFiles(e.dataTransfer.files);
            });
            fileInput.addEventListener("change", function () { addFiles(fileInput.files); });
        }

        function addFiles(fileList) {
            for (var i = 0; i < fileList.length; i++) pendingFiles.push(fileList[i]);
            selectedCount.textContent = pendingFiles.length + " photo(s) selected.";
            startUploadBtn.classList.toggle("d-none", pendingFiles.length === 0);
        }

        if (startUploadBtn) {
            startUploadBtn.addEventListener("click", function () {
                if (pendingFiles.length === 0) return;

                var formData = new FormData();
                formData.append("_token", token);
                formData.append("id", activityId);
                pendingFiles.forEach(function (f) { formData.append("files[]", f); });

                var xhr = new XMLHttpRequest();
                xhr.open("POST", baseUrl + "/UploadPhotos", true);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

                progressWrap.classList.remove("d-none");
                startUploadBtn.disabled = true;

                xhr.upload.addEventListener("progress", function (e) {
                    if (e.lengthComputable) {
                        var pct = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = pct + "%";
                        progressBar.textContent = pct + "%";
                    }
                });

                xhr.onload = function () {
                    startUploadBtn.disabled = false;
                    if (xhr.status === 200) {
                        toast("Photos successfully uploaded.");
                        setTimeout(function () { window.location.reload(); }, 700);
                    } else {
                        toast("Upload failed. Please try again.", true);
                    }
                };
                xhr.onerror = function () {
                    startUploadBtn.disabled = false;
                    toast("Upload failed. Please check your connection.", true);
                };

                xhr.send(formData);
            });
        }

        // ---------------- Reorder (native HTML5 drag-and-drop) ----------------
        var grid = document.getElementById("photoGrid");
        var saveOrderBtn = document.getElementById("saveOrderBtn");
        var dragSrc = null;

        if (grid) {
            grid.querySelectorAll(".photo-manage-tile").forEach(function (tile) {
                tile.addEventListener("dragstart", function () {
                    dragSrc = tile;
                    tile.classList.add("dragging");
                });
                tile.addEventListener("dragend", function () {
                    tile.classList.remove("dragging");
                    saveOrderBtn.classList.remove("d-none");
                });
                tile.addEventListener("dragover", function (e) {
                    e.preventDefault();
                    var after = getDragAfterElement(grid, e.clientY, e.clientX);
                    if (after == null) {
                        grid.appendChild(dragSrc);
                    } else {
                        grid.insertBefore(dragSrc, after);
                    }
                });
            });
        }

        function getDragAfterElement(container, y, x) {
            var tiles = Array.prototype.slice.call(container.querySelectorAll(".photo-manage-tile:not(.dragging)"));
            return tiles.reduce(function (closest, tile) {
                var box = tile.getBoundingClientRect();
                var offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: tile };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        if (saveOrderBtn) {
            saveOrderBtn.addEventListener("click", function () {
                var ids = Array.prototype.slice.call(grid.querySelectorAll(".photo-manage-tile"))
                    .map(function (t) { return t.getAttribute("data-photo-id"); });

                var formData = new FormData();
                formData.append("_token", token);
                formData.append("id", activityId);
                ids.forEach(function (id) { formData.append("orderedPhotoIds", id); });

                fetch(baseUrl + "/ReorderPhotos", { method: "POST", body: formData })
                    .then(function (r) { return r.json(); })
                    .then(function () {
                        toast("Photo order saved.");
                        saveOrderBtn.classList.add("d-none");
                    })
                    .catch(function () { toast("Could not save the new order.", true); });
            });
        }

        // ---------------- Caption + description editing ----------------
        function saveCaptionAndDescription(photoId) {
            var captionInput = document.querySelector('.photo-caption-input[data-photo-id="' + photoId + '"]');
            var descInput = document.querySelector('.photo-description-input[data-photo-id="' + photoId + '"]');

            var formData = new FormData();
            formData.append("_token", token);
            formData.append("id", activityId);
            formData.append("photoId", photoId);
            formData.append("caption", captionInput ? captionInput.value : "");
            formData.append("description", descInput ? descInput.value : "");

            fetch(baseUrl + "/UpdateCaption", {
                method: "POST",
                body: formData,
                headers: { "X-Requested-With": "XMLHttpRequest" }
            }).then(function () { toast("Saved."); });
        }

        document.querySelectorAll(".photo-caption-input, .photo-description-input").forEach(function (input) {
            input.addEventListener("input", debounce(function () {
                saveCaptionAndDescription(input.getAttribute("data-photo-id"));
            }, 800));
        });

        function debounce(fn, wait) {
            var t;
            return function () {
                var args = arguments;
                clearTimeout(t);
                t = setTimeout(function () { fn.apply(null, args); }, wait);
            };
        }

        // ---------------- Set cover / Featured Photo ----------------
        document.querySelectorAll(".photo-manage-cover-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var formData = new FormData();
                formData.append("_token", token);
                formData.append("id", activityId);
                formData.append("photoId", btn.getAttribute("data-photo-id"));

                fetch(baseUrl + "/SetCoverPhoto", { method: "POST", body: formData })
                    .then(function () {
                        document.querySelectorAll(".photo-manage-cover-btn").forEach(function (b) { b.classList.remove("is-cover"); });
                        document.querySelectorAll(".photo-manage-featured-badge").forEach(function (badge) { badge.remove(); });
                        btn.classList.add("is-cover");
                        var badge = document.createElement("span");
                        badge.className = "badge photo-manage-featured-badge";
                        badge.textContent = "Featured";
                        btn.insertAdjacentElement("afterend", badge);
                        toast("Featured photo updated.");
                    });
            });
        });

        // ---------------- Replace photo ----------------
        document.querySelectorAll(".photo-replace-input").forEach(function (input) {
            input.addEventListener("change", function () {
                if (!input.files || input.files.length === 0) return;
                var photoId = input.getAttribute("data-photo-id");

                var formData = new FormData();
                formData.append("_token", token);
                formData.append("id", activityId);
                formData.append("photoId", photoId);
                formData.append("file", input.files[0]);

                fetch(baseUrl + "/ReplacePhoto", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                }).then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.success) {
                            var img = document.querySelector('.photo-manage-img[data-photo-id="' + photoId + '"]');
                            if (img) img.src = (result.thumbnailPath || result.filePath) + "?v=" + Date.now();
                            toast("Photo replaced successfully.");
                        } else {
                            toast(result.error || "Could not replace this photo.", true);
                        }
                        input.value = "";
                    })
                    .catch(function () {
                        toast("Could not replace this photo.", true);
                        input.value = "";
                    });
            });
        });

        // ---------------- Delete (single) ----------------
        document.querySelectorAll(".photo-delete-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                if (!confirm("Delete this photo? This cannot be undone.")) return;

                var formData = new FormData();
                formData.append("_token", token);
                formData.append("id", activityId);
                formData.append("photoId", btn.getAttribute("data-photo-id"));

                fetch(baseUrl + "/DeletePhoto", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                }).then(function () {
                    // Full reload (rather than just removing the tile) so that if this was
                    // the Featured Photo, the server's auto-reassigned replacement shows
                    // its star/badge correctly without a stale client-side guess.
                    toast("Photo deleted successfully.");
                    setTimeout(function () { window.location.reload(); }, 500);
                });
            });
        });

        // ---------------- Bulk delete ----------------
        var bulkDeleteBtn = document.getElementById("bulkDeleteBtn");
        function updateBulkButtonState() {
            var anyChecked = document.querySelectorAll(".photo-select-checkbox:checked").length > 0;
            bulkDeleteBtn.disabled = !anyChecked;
        }
        document.querySelectorAll(".photo-select-checkbox").forEach(function (cb) {
            cb.addEventListener("change", updateBulkButtonState);
        });
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener("click", function () {
                var ids = Array.prototype.slice.call(document.querySelectorAll(".photo-select-checkbox:checked"))
                    .map(function (cb) { return cb.value; });
                if (ids.length === 0) return;
                if (!confirm("Delete " + ids.length + " selected photo(s)? This cannot be undone.")) return;

                var formData = new FormData();
                formData.append("_token", token);
                formData.append("id", activityId);
                ids.forEach(function (id) { formData.append("photoIds", id); });

                fetch(baseUrl + "/BulkDeletePhotos", { method: "POST", body: formData })
                    .then(function () { window.location.reload(); });
            });
        }
    });
})();

/*
 * Wires up Quill rich-text editors (Section 6: headings, bold/italic, lists,
 * links, images, quotes) for any element matching .rich-editor-container.
 * Each container must have a data-target attribute naming a sibling
 * <textarea name="{target}" class="d-none"> that holds the real form value.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        if (typeof Quill === "undefined") return;

        document.querySelectorAll(".rich-editor-container").forEach(function (container) {
            var targetName = container.getAttribute("data-target");
            var textarea = document.querySelector('textarea[name="' + targetName + '"]');
            if (!textarea) return;

            var editorDiv = document.createElement("div");
            editorDiv.style.minHeight = "220px";
            editorDiv.style.background = "#fff";
            container.appendChild(editorDiv);

            var quill = new Quill(editorDiv, {
                theme: "snow",
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ["bold", "italic", "underline"],
                        [{ list: "ordered" }, { list: "bullet" }],
                        ["blockquote", "link", "image"],
                        ["clean"]
                    ]
                }
            });

            quill.root.innerHTML = textarea.value || "";
            textarea.classList.add("d-none");

            quill.on("text-change", function () {
                textarea.value = quill.root.innerHTML;
            });
        });
    });
})();

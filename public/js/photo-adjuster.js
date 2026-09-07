// Generic reusable "adjust before upload" control for profile/cover photo
// file inputs. Opt in per-input with:
//   <input type="file" data-adjust-ratio="1" data-preview-target="somePreviewImgId">
// data-adjust-ratio is the crop's width/height ratio (e.g. "1" for a square
// avatar, "3" for a wide 3:1 cover banner). Any input WITHOUT data-adjust-ratio
// keeps the plain preview-only behavior other pages already use, so this
// script is safe to include everywhere.
//
// On "Apply" the picked image is re-drawn, cropped/zoomed/panned exactly as
// shown in the preview frame, onto a canvas and swapped back into the same
// <input> as a File - the server-side controller and its validation see a
// normal single-file upload and need no changes.
(function () {
  'use strict';

  var modal = null; // lazily-built, reused across every adjustable input on the page
  var state = null; // { input, preview, img, naturalW, naturalH, ratio, baseScale, scale, x, y, dragging, startX, startY }

  function buildModal() {
    var overlay = document.createElement('div');
    overlay.className = 'photo-adjust-overlay';
    overlay.innerHTML =
      '<div class="photo-adjust-box" role="dialog" aria-modal="true" aria-label="Adjust photo">' +
      '  <div class="photo-adjust-header">' +
      '    <span>Adjust Photo</span>' +
      '    <button type="button" class="photo-adjust-close" aria-label="Cancel">&times;</button>' +
      '  </div>' +
      '  <div class="photo-adjust-viewport"><img class="photo-adjust-img" alt="" draggable="false" /></div>' +
      '  <p class="photo-adjust-hint">Drag to reposition. Use the slider to zoom.</p>' +
      '  <input type="range" class="photo-adjust-zoom" min="1" max="3" step="0.01" value="1" />' +
      '  <div class="photo-adjust-actions">' +
      '    <button type="button" class="btn btn-cancel photo-adjust-cancel">Cancel</button>' +
      '    <button type="button" class="btn btn-save photo-adjust-apply">Apply</button>' +
      '  </div>' +
      '</div>';
    document.body.appendChild(overlay);

    var els = {
      overlay: overlay,
      viewport: overlay.querySelector('.photo-adjust-viewport'),
      img: overlay.querySelector('.photo-adjust-img'),
      zoom: overlay.querySelector('.photo-adjust-zoom'),
      apply: overlay.querySelector('.photo-adjust-apply'),
      cancel: overlay.querySelector('.photo-adjust-cancel'),
      close: overlay.querySelector('.photo-adjust-close')
    };

    els.cancel.addEventListener('click', closeAndRevert);
    els.close.addEventListener('click', closeAndRevert);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeAndRevert(); });
    els.apply.addEventListener('click', applyAdjustment);
    els.zoom.addEventListener('input', function () { setZoom(parseFloat(els.zoom.value)); });

    els.viewport.addEventListener('pointerdown', onDragStart);
    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);

    return els;
  }

  function clampPan() {
    var vw = state.viewportW, vh = state.viewportH;
    var scaledW = state.naturalW * state.scale;
    var scaledH = state.naturalH * state.scale;
    state.x = Math.min(0, Math.max(vw - scaledW, state.x));
    state.y = Math.min(0, Math.max(vh - scaledH, state.y));
  }

  function render() {
    modal.img.style.width = (state.naturalW * state.scale) + 'px';
    modal.img.style.height = (state.naturalH * state.scale) + 'px';
    modal.img.style.transform = 'translate(' + state.x + 'px, ' + state.y + 'px)';
  }

  function setZoom(multiplier) {
    state.scale = state.baseScale * multiplier;
    clampPan();
    render();
  }

  function onDragStart(e) {
    if (!state) return;
    state.dragging = true;
    state.startX = e.clientX - state.x;
    state.startY = e.clientY - state.y;
    modal.viewport.setPointerCapture && modal.viewport.setPointerCapture(e.pointerId);
  }

  function onDragMove(e) {
    if (!state || !state.dragging) return;
    state.x = e.clientX - state.startX;
    state.y = e.clientY - state.startY;
    clampPan();
    render();
  }

  function onDragEnd() {
    if (state) state.dragging = false;
  }

  function openAdjuster(input, preview, file) {
    var reader = new FileReader();
    reader.onload = function () {
      var img = new Image();
      img.onload = function () {
        if (!modal) modal = buildModal();

        var ratio = parseFloat(input.getAttribute('data-adjust-ratio')) || 1;
        var viewportW = ratio >= 2 ? 420 : 300;
        var viewportH = viewportW / ratio;
        modal.viewport.style.width = viewportW + 'px';
        modal.viewport.style.height = viewportH + 'px';
        modal.viewport.style.borderRadius = ratio === 1 ? '50%' : 'var(--dash-radius, 6px)';

        var baseScale = Math.max(viewportW / img.naturalWidth, viewportH / img.naturalHeight);

        state = {
          input: input, preview: preview, img: img, file: file,
          naturalW: img.naturalWidth, naturalH: img.naturalHeight,
          ratio: ratio, viewportW: viewportW, viewportH: viewportH,
          baseScale: baseScale, scale: baseScale, x: 0, y: 0, dragging: false
        };
        // Center the image inside the viewport initially.
        state.x = (viewportW - img.naturalWidth * baseScale) / 2;
        state.y = (viewportH - img.naturalHeight * baseScale) / 2;
        clampPan();

        modal.img.src = reader.result;
        modal.zoom.value = '1';
        render();
        modal.overlay.classList.add('is-open');
      };
      img.onerror = function () {
        // Not a decodable image - let the plain file input / server-side
        // validation surface the problem instead of blocking submission.
      };
      img.src = reader.result;
    };
    reader.readAsDataURL(file);
  }

  function closeAndRevert() {
    if (!modal) return;
    modal.overlay.classList.remove('is-open');
    if (state) {
      // The user backed out - don't submit the picked file at all.
      state.input.value = '';
      if (state.preview) {
        var originalSrc = state.preview.dataset.originalSrc || '';
        var originalDisplay = state.preview.dataset.originalDisplay || '';
        state.preview.src = originalSrc;
        state.preview.style.display = originalSrc ? (originalDisplay || 'block') : 'none';
      }
    }
    state = null;
  }

  function applyAdjustment() {
    if (!state) return;
    var targetW = state.ratio >= 2 ? 1200 : 640;
    var targetH = Math.round(targetW / state.ratio);

    var canvas = document.createElement('canvas');
    canvas.width = targetW;
    canvas.height = targetH;
    var ctx = canvas.getContext('2d');

    var sourceX = -state.x / state.scale;
    var sourceY = -state.y / state.scale;
    var sourceW = state.viewportW / state.scale;
    var sourceH = state.viewportH / state.scale;

    try {
      ctx.drawImage(state.img, sourceX, sourceY, sourceW, sourceH, 0, 0, targetW, targetH);
    } catch (err) {
      closeAndRevert();
      return;
    }

    var input = state.input, preview = state.preview;
    canvas.toBlob(function (blob) {
      if (!blob) { closeAndRevert(); return; }

      var baseName = (state.file.name || 'photo').replace(/\.[^.]+$/, '');
      var adjustedFile = new File([blob], baseName + '-adjusted.jpg', { type: 'image/jpeg' });

      try {
        var dt = new DataTransfer();
        dt.items.add(adjustedFile);
        input._suppressAdjust = true;
        input.files = dt.files;
      } catch (err) {
        // DataTransfer construction isn't supported in this browser - keep
        // the originally picked file instead of blocking the upload.
      }

      if (preview) {
        preview.src = URL.createObjectURL(blob);
        preview.style.display = 'block';
      }

      modal.overlay.classList.remove('is-open');
      state = null;
    }, 'image/jpeg', 0.92);
  }

  function init() {
    var inputs = document.querySelectorAll('input[type="file"][data-adjust-ratio]');
    if (!inputs.length) return;

    inputs.forEach(function (input) {
      var preview = input.getAttribute('data-preview-target')
        ? document.getElementById(input.getAttribute('data-preview-target'))
        : null;
      if (preview && preview.dataset.originalSrc === undefined) {
        preview.dataset.originalSrc = preview.getAttribute('src') || '';
        preview.dataset.originalDisplay = preview.style.display || '';
      }

      input.addEventListener('change', function () {
        if (input._suppressAdjust) { input._suppressAdjust = false; return; }
        var file = input.files && input.files[0];
        if (!file || file.type.indexOf('image/') !== 0) return;
        if (typeof FileReader === 'undefined' || typeof HTMLCanvasElement === 'undefined') return;
        openAdjuster(input, preview, file);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

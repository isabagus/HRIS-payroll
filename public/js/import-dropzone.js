/**
 * Universal Reusable Excel Import Dropzone Handler
 * Prevents file picker re-triggering bug when clicking 'X' remove button
 */
document.addEventListener('DOMContentLoaded', function () {
    window.initImportDropzones();
});

window.initImportDropzones = function () {
    const dropzones = document.querySelectorAll('.import-dropzone');

    dropzones.forEach(function (dropzone) {
        const inputId = dropzone.getAttribute('data-input-id');
        if (!inputId) return;

        const fileInput  = document.getElementById(inputId);
        const previewBox = document.getElementById('preview-' + inputId);
        const contentBox = document.getElementById('content-' + inputId);
        const filenameP  = document.getElementById('filename-' + inputId);
        const filesizeP  = document.getElementById('filesize-' + inputId);
        const removeBtn  = document.getElementById('btn-remove-' + inputId);

        if (!fileInput || !previewBox || !contentBox || !removeBtn) return;

        // Reset state helper
        const resetDropzone = function () {
            fileInput.value = '';
            previewBox.style.display = 'none';
            contentBox.style.display = 'block';
            dropzone.classList.remove('dropzone-selected');
        };

        // 1. Remove Button Event Listener with STRICT STOP PROPAGATION
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            resetDropzone();
            return false;
        });

        // 2. Dropzone Container Click Listener
        dropzone.addEventListener('click', function (e) {
            // Guard: If clicking the remove button or inside it, NEVER trigger file input
            if (e.target === removeBtn || removeBtn.contains(e.target) || e.target.closest('.btn-remove-selected-file')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Only trigger file picker if dropzone is in empty/default state
            if (previewBox.style.display === 'none' || previewBox.style.display === '') {
                fileInput.click();
            }
        });

        // 3. File Input Change Listener
        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                if (filenameP) filenameP.textContent = file.name;
                if (filesizeP) filesizeP.textContent = (file.size / 1024).toFixed(1) + ' KB';
                
                contentBox.style.display = 'none';
                previewBox.style.display = 'block';
                dropzone.classList.add('dropzone-selected');
            } else {
                resetDropzone();
            }
        });

        // 4. Drag and Drop Support
        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('dropzone-drag');
        });

        dropzone.addEventListener('dragleave', function () {
            this.classList.remove('dropzone-drag');
        });

        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('dropzone-drag');
            if (e.dataTransfer && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    });
};

// Global modal open/close helpers
window.openImportModal = function (modalId = 'modal-import') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
};

window.closeImportModal = function (modalId = 'modal-import') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};

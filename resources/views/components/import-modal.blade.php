@props([
    'modalId' => 'modal-import',
    'title' => 'Import File Excel',
    'subtitle' => 'Upload file Excel untuk mengimpor data',
    'action' => '',
    'inputId' => 'file_excel_import',
    'templateInfo' => '',
    'submitText' => 'Import Sekarang'
])

<div class="modal-overlay" id="{{ $modalId }}" role="dialog" aria-modal="true">
    <div class="modal-box modal-box-import">
        <div class="modal-import-header">
            <div class="modal-import-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
            </div>
            <div>
                <h3 class="modal-title" style="text-align:left; margin:0;">{{ $title }}</h3>
                <p class="modal-subtitle">{{ $subtitle }}</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeImportModal('{{ $modalId }}')" title="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="form-{{ $inputId }}">
            @csrf
            
            <div class="import-dropzone" id="dropzone-{{ $inputId }}" data-input-id="{{ $inputId }}">
                <input type="file" name="file_excel" id="{{ $inputId }}" accept=".xlsx,.xls,.csv" hidden>
                
                {{-- Preview State --}}
                <div class="dropzone-preview-box" id="preview-{{ $inputId }}" style="display: none; padding: 12px; width: 100%;">
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1.5px dashed #4ade80; border-radius: 14px; padding: 12px 14px; gap: 12px; width: 100%; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #dcfce7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" width="22" height="22">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                            <div style="min-width: 0; flex: 1; text-align: left;">
                                <p id="filename-{{ $inputId }}" style="color: #15803d; font-weight: 700; font-size: 13px; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;"></p>
                                <p id="filesize-{{ $inputId }}" style="color: #16a34a; font-size: 11px; margin: 2px 0 0 0; font-weight: 500;"></p>
                            </div>
                        </div>
                        <button type="button" class="btn-remove-selected-file" id="btn-remove-{{ $inputId }}" title="Batal / Hapus file" style="background: #fee2e2; border: 1px solid #fca5a5; color: #ef4444; border-radius: 50%; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.2s ease;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Default State --}}
                <div class="dropzone-content" id="content-{{ $inputId }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="11" x2="12" y2="17"/>
                        <polyline points="9 14 12 11 15 14"/>
                    </svg>
                    <p class="dropzone-text">Klik atau seret file di sini</p>
                    <p class="dropzone-hint">Format: .xlsx, .xls, .csv — Maks 5MB</p>
                </div>
            </div>

            @if(!empty($templateInfo))
            <div class="import-template-info">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div class="template-info-text">
                    {!! $templateInfo !!}
                </div>
            </div>
            @endif

            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeImportModal('{{ $modalId }}')">Batal</button>
                <button type="submit" class="btn-modal-submit-import" id="btn-submit-{{ $inputId }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    {{ $submitText }}
                </button>
            </div>
        </form>
    </div>
</div>

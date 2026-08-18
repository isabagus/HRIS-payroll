@props([
    'id' => 'table-' . uniqid(),
    'class' => ''
])

<div class="responsive-table-wrapper flex flex-col w-full relative my-2" id="{{ $id }}-wrapper">
    {{-- Top Horizontal Scrollbar --}}
    <div class="top-scrollbar-container overflow-x-auto w-full bg-slate-100/80 border-b border-slate-200 rounded-t-xl"
         style="height: 12px; scrollbar-width: thin; scrollbar-color: #94a3b8 #f1f5f9; display: none;"
         id="{{ $id }}-top-scroll">
        <div class="top-scrollbar-track" id="{{ $id }}-top-track" style="height: 1px; width: 100%;"></div>
    </div>

    {{-- Main Table Scroll Container --}}
    <div class="table-responsive main-table-container overflow-x-auto w-full {{ $class }}"
         id="{{ $id }}-main-table"
         style="scrollbar-width: thin; scrollbar-color: #94a3b8 #f1f5f9;">
        {{ $slot }}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    (function initTableSync() {
        const wrapperId = '{{ $id }}-wrapper';
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        const topScroll = document.getElementById('{{ $id }}-top-scroll');
        const topTrack  = document.getElementById('{{ $id }}-top-track');
        const mainTable = document.getElementById('{{ $id }}-main-table');

        if (!topScroll || !topTrack || !mainTable) return;

        function syncTrackWidth() {
            const tableElem = mainTable.querySelector('table');
            const scrollW   = tableElem ? tableElem.offsetWidth : mainTable.scrollWidth;
            const clientW   = mainTable.clientWidth;

            topTrack.style.width = scrollW + 'px';

            if (scrollW > clientW + 5) {
                topScroll.style.display = 'block';
            } else {
                topScroll.style.display = 'none';
            }
        }

        syncTrackWidth();

        let isSyncingTop = false;
        let isSyncingMain = false;

        topScroll.addEventListener('scroll', function() {
            if (!isSyncingMain) {
                isSyncingTop = true;
                mainTable.scrollLeft = topScroll.scrollLeft;
            }
            isSyncingMain = false;
        }, { passive: true });

        mainTable.addEventListener('scroll', function() {
            if (!isSyncingTop) {
                isSyncingMain = true;
                topScroll.scrollLeft = mainTable.scrollLeft;
            }
            isSyncingTop = false;
        }, { passive: true });

        window.addEventListener('resize', syncTrackWidth);
        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(syncTrackWidth);
            observer.observe(mainTable);
            const tableElem = mainTable.querySelector('table');
            if (tableElem) observer.observe(tableElem);
        }
    })();
});
</script>
@endpush

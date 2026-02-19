{{-- Custom Filament scripts --}}
@if (request()->is('admin/*'))
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
    <script src="/js/inputmask-money.js"></script>
@endif

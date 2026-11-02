@if (session('success') || session('verified') || session('status') === 'verification-link-sent' || session('error'))
<div class="max-w-5xl mx-auto px-4 sm:px-6 mt-5 space-y-3">
    @if (session('success'))
        <div class="flex gap-3 items-start border border-emerald-300 border-l-4 border-l-emerald-600 bg-emerald-50 text-emerald-950 rounded-xl px-4 py-3.5 shadow-sm" role="status" aria-live="polite">
            <i class="fas fa-circle-check text-emerald-700 mt-0.5 text-lg" aria-hidden="true"></i><span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('verified'))
        <div class="flex gap-3 items-start border border-emerald-300 border-l-4 border-l-emerald-600 bg-emerald-50 text-emerald-950 rounded-xl px-4 py-3.5 shadow-sm" role="status" aria-live="polite">
            <i class="fas fa-circle-check text-emerald-700 mt-0.5 text-lg" aria-hidden="true"></i><span class="font-bold">Correo verificado correctamente.</span>
        </div>
    @endif
    @if (session('status') === 'verification-link-sent')
        <div class="flex gap-3 items-start border border-sky-300 border-l-4 border-l-sky-700 bg-sky-50 text-sky-950 rounded-xl px-4 py-3.5 shadow-sm" role="status" aria-live="polite">
            <i class="fas fa-paper-plane text-sky-700 mt-0.5 text-lg" aria-hidden="true"></i><span class="font-bold">Te enviamos un nuevo enlace de verificación.</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex gap-3 items-start border border-red-300 border-l-4 border-l-red-700 bg-red-50 text-red-950 rounded-xl px-4 py-3.5 shadow-sm" role="alert" aria-live="assertive">
            <i class="fas fa-circle-exclamation text-red-700 mt-0.5 text-lg" aria-hidden="true"></i><span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif
</div>
@endif

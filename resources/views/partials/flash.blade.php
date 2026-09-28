@if (session('success') || session('verified') || session('status') === 'verification-link-sent' || session('error'))
<div class="max-w-6xl mx-auto px-4 sm:px-6 mt-4 space-y-3">
    @if (session('success'))
        <div class="flex gap-3 items-start bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl px-4 py-3 font-semibold">
            <i class="fas fa-circle-check text-emerald-500 mt-0.5"></i><span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('verified'))
        <div class="flex gap-3 items-start bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl px-4 py-3 font-semibold">
            <i class="fas fa-circle-check text-emerald-500 mt-0.5"></i><span>Correo verificado correctamente.</span>
        </div>
    @endif
    @if (session('status') === 'verification-link-sent')
        <div class="flex gap-3 items-start bg-sky-50 border border-sky-200 text-sky-900 rounded-xl px-4 py-3 font-semibold">
            <i class="fas fa-paper-plane text-sky-500 mt-0.5"></i><span>Te enviamos un nuevo enlace de verificación.</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex gap-3 items-start bg-red-50 border border-red-200 text-red-900 rounded-xl px-4 py-3 font-semibold">
            <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i><span>{{ session('error') }}</span>
        </div>
    @endif
</div>
@endif

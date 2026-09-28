@extends('layouts.app')
@section('title', 'Verificar correo - carsTUmotor')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="rounded-3xl overflow-hidden bg-slate-950 text-white p-8 flex flex-col justify-between min-h-[420px] relative">
            <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-sm font-bold"><i class="fas fa-envelope-circle-check text-teal-400"></i> Un paso más</span>
                <h1 class="mt-4 text-3xl font-black">Confirmá tu correo</h1>
                <p class="mt-2 text-white/70">Por seguridad necesitamos verificar que el email sea tuyo. Revisá bandeja de entrada y spam.</p>
            </div>
            <div class="relative mt-8 space-y-2">
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-link text-amber-400"></i> Abrí el enlace que te enviamos</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-rotate text-amber-400"></i> Si no llega, podés reenviar</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-car-side text-amber-400"></i> Después explorá el catálogo</div>
            </div>
        </div>
        <div class="rounded-3xl bg-white border border-zinc-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-2xl font-black text-slate-900">Verificá tu correo</h2>
            <p class="text-sm text-zinc-500 mt-1">Te enviamos un enlace de verificación</p>
            <div class="mt-4 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-zinc-50 border border-zinc-200 font-bold text-slate-800 text-sm"><i class="fas fa-at text-teal-600"></i> {{ auth()->user()->email }}</div>
            <p class="mt-4 text-sm text-zinc-600 leading-relaxed">Antes de continuar, hacé clic en el enlace del correo. Si no ves el mensaje, esperá unos minutos o pedí reenvío.</p>
            @if (session('status') === 'verification-link-sent')
                <div class="mt-3 flex gap-2 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl p-3 font-bold text-sm"><i class="fas fa-paper-plane text-emerald-600 mt-0.5"></i> Te enviamos un nuevo enlace. Revisá tu correo.</div>
            @endif
            <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-3 rounded-xl bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black"><i class="fas fa-paper-plane"></i> Reenviar correo de verificación</button>
            </form>
            <div class="my-3 flex items-center gap-3 text-zinc-400 text-sm font-bold"><span class="h-px flex-1 bg-zinc-200"></span>o<span class="h-px flex-1 bg-zinc-200"></span></div>
            <a href="{{ route('catalogo') }}" class="w-full inline-flex justify-center items-center gap-2 py-3 rounded-xl border border-zinc-200 bg-white font-bold text-slate-800"><i class="fas fa-layer-group text-teal-600"></i> Ir al catálogo</a>
            <p class="mt-3 text-sm text-zinc-600">¿Usaste otro correo? <a class="font-black text-teal-700" href="/">Volver al inicio</a></p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-3 rounded-xl border border-zinc-200 bg-white font-bold"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button></form>
        </div>
    </div>
</div>
@endsection

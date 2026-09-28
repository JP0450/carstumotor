@extends('layouts.app')
@section('title', 'Recuperar contraseña - carsTUmotor')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="rounded-3xl overflow-hidden bg-slate-950 text-white p-8 flex flex-col justify-between min-h-[420px] relative">
            <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-sm font-bold"><i class="fas fa-key text-teal-400"></i> Recuperación</span>
                <h1 class="mt-4 text-3xl font-black">Recuperá tu acceso</h1>
                <p class="mt-2 text-white/70">Esta pantalla es solo estética por ahora. Más adelante conectamos el envío de email y la recuperación.</p>
            </div>
            <div class="relative mt-8 space-y-2">
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-envelope text-amber-400"></i> Enviaremos un link a tu correo</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-shield text-amber-400"></i> Proceso seguro</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-clock text-amber-400"></i> Rápido y simple</div>
            </div>
        </div>
        <div class="rounded-3xl bg-white border border-zinc-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-2xl font-black text-slate-900">Olvidé mi contraseña</h2>
            <p class="text-sm text-zinc-500 mt-1">Ingresá tu email y te enviaremos instrucciones</p>
            @if (session('status'))
                <div class="mb-3 flex gap-2 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl p-3 font-bold text-sm"><i class="fas fa-paper-plane text-emerald-600 mt-0.5"></i> {{ session('status') }}</div>
            @endif
            <form action="{{ route('password.email') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-bold text-slate-900">Email</span>
                    <span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5">
                        <i class="fas fa-envelope text-teal-600"></i><input type="email" name="email" value="{{ old('email') }}" placeholder="tuemail@email.com" required class="w-full bg-transparent outline-none">
                    </span>
                    @error('email')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </label>
                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-3 rounded-xl bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black"><i class="fas fa-paper-plane"></i> Enviar instrucciones</button>
                <p class="text-sm bg-amber-50 border border-amber-200 rounded-xl p-3 text-zinc-600">¿Te acordaste? Volvé a <a href="{{ route('login') }}" class="font-black text-teal-700">Iniciar sesión</a>.</p>
            </form>
        </div>
    </div>
</div>
@endsection

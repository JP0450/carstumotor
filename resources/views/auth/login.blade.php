@extends('layouts.app')
@section('title', 'Iniciar sesión - carsTUmotor')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="rounded-3xl overflow-hidden bg-slate-950 text-white p-8 flex flex-col justify-between min-h-[420px] relative">
            <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-sm font-bold"><i class="fas fa-shield text-teal-400"></i> Acceso seguro</span>
                <h1 class="mt-4 text-3xl font-black leading-tight">Ingresá a tu cuenta</h1>
                <p class="mt-2 text-white/70">Ingresá con tu email y contraseña para ver el catálogo y gestionar tu cuenta.</p>
            </div>
            <div class="relative mt-8 space-y-2">
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-check-circle text-amber-400"></i> Guardá vehículos y preferencias</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-bell text-amber-400"></i> Recibí avisos de nuevos anuncios</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-user-shield text-amber-400"></i> Acceso a panel y configuraciones</div>
            </div>
        </div>
        <div class="rounded-3xl bg-white border border-zinc-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-2xl font-black text-slate-900">Iniciar sesión</h2>
            <p class="text-sm text-zinc-500 mt-1">Completá tus datos para continuar</p>
            <form action="/login" method="POST" class="mt-6 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-bold text-slate-900">Email</span>
                    <span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5 focus-within:border-teal-400 focus-within:ring-2 focus-within:ring-teal-100">
                        <i class="fas fa-envelope text-teal-600"></i><input type="email" name="email" value="{{ old('email') }}" placeholder="tuemail@email.com" required class="w-full bg-transparent outline-none">
                    </span>
                    @error('email')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-900">Contraseña</span>
                    <span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5">
                        <i class="fas fa-lock text-teal-600"></i><input type="password" name="password" placeholder="••••••••" required class="w-full bg-transparent outline-none">
                    </span>
                    @error('password')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </label>
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <label class="inline-flex items-center gap-2 font-bold text-zinc-600"><input type="checkbox" name="remember" class="accent-teal-600"> Recordarme</label>
                    <a href="/forgot-password" class="font-black text-teal-700 hover:underline">Olvidé mi contraseña</a>
                </div>
                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-3 rounded-xl bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black shadow"><i class="fas fa-arrow-right"></i> Entrar</button>
                <div class="flex items-center gap-3 text-zinc-400 text-sm font-bold"><span class="h-px flex-1 bg-zinc-200"></span>o<span class="h-px flex-1 bg-zinc-200"></span></div>
                <p class="text-sm text-zinc-600 font-medium bg-amber-50 border border-amber-200 rounded-xl p-3">Si no tenés cuenta, creala en <a href="/register" class="font-black text-teal-700">Registrarse</a>.</p>
            </form>
        </div>
    </div>
</div>
@endsection

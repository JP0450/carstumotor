@extends('layouts.app')
@section('title', 'Registrarse - carsTUmotor')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-6">
        <div class="rounded-3xl overflow-hidden bg-slate-950 text-white p-8 flex flex-col justify-between min-h-[420px] relative">
            <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-sm font-bold"><i class="fas fa-user-plus text-teal-400"></i> Crear cuenta</span>
                <h1 class="mt-4 text-3xl font-black">Sumate a carsTUmotor</h1>
                <p class="mt-2 text-white/70">Creá tu cuenta y verificá tu correo para usar todas las funciones.</p>
            </div>
            <div class="relative mt-8 space-y-2">
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-list text-amber-400"></i> Accedé al catálogo completo</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-bell text-amber-400"></i> Recibí avisos personalizados</div>
                <div class="flex items-center gap-2 bg-white/10 border border-white/10 rounded-xl px-3 py-3 font-bold"><i class="fas fa-shield text-amber-400"></i> Perfil y datos protegidos</div>
            </div>
        </div>
        <div class="rounded-3xl bg-white border border-zinc-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-2xl font-black text-slate-900">Registrarse</h2>
            <p class="text-sm text-zinc-500 mt-1">Creá tu cuenta en pocos pasos</p>
            <form action="/register" method="POST" class="mt-6 space-y-3">
                @csrf
                <label class="block"><span class="text-sm font-bold text-slate-900">Nombre</span><span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5"><i class="fas fa-user text-teal-600"></i><input type="text" name="name" value="{{ old('name') }}" placeholder="Tu nombre" required class="w-full bg-transparent outline-none"></span>@error('name')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-900">Email</span><span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5"><i class="fas fa-envelope text-teal-600"></i><input type="email" name="email" value="{{ old('email') }}" placeholder="tuemail@email.com" required class="w-full bg-transparent outline-none"></span>@error('email')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-900">Teléfono (opcional)</span><span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5"><i class="fas fa-phone text-teal-600"></i><input type="text" name="phone" value="{{ old('phone') }}" placeholder="+57 300 123 4567" class="w-full bg-transparent outline-none"></span></label>
                <label class="block"><span class="text-sm font-bold text-slate-900">Tipo de cuenta</span><span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5"><i class="fas fa-user-tag text-teal-600"></i><select name="role" required class="w-full bg-transparent outline-none"><option value="externo" @selected(old('role')=='externo')>Usuario externo</option><option value="jefe" @selected(old('role')=='jefe')>Jefe</option><option value="negocios_internacionales" @selected(old('role')=='negocios_internacionales')>Negocios internacionales</option><option value="contador" @selected(old('role')=='contador')>Contador / Finanzas</option></select></span></label>
                <label class="block"><span class="text-sm font-bold text-slate-900">Contraseña</span><span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5"><i class="fas fa-lock text-teal-600"></i><input type="password" name="password" placeholder="••••••••" required class="w-full bg-transparent outline-none"></span>@error('password')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-900">Confirmar contraseña</span><span class="mt-1 flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5"><i class="fas fa-lock text-teal-600"></i><input type="password" name="password_confirmation" placeholder="••••••••" required class="w-full bg-transparent outline-none"></span></label>
                <button type="submit" class="w-full mt-2 inline-flex justify-center items-center gap-2 py-3 rounded-xl bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black"><i class="fas fa-arrow-right"></i> Crear cuenta</button>
                <p class="text-sm text-zinc-600 bg-amber-50 border border-amber-200 rounded-xl p-3">¿Ya tenés cuenta? <a href="{{ route('login') }}" class="font-black text-teal-700">Iniciar sesión</a>.</p>
            </form>
        </div>
    </div>
</div>
@endsection

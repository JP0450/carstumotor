@extends('layouts.app')
@section('title', 'carsTUmotor - Tu comercializadora de confianza')
@section('content')
<section class="relative overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-[#16213e] to-teal-900/40"></div>
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-teal-500/10 blur-3xl"></div>
    <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] rounded-full bg-amber-400/10 blur-3xl"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-sm font-bold text-white/90"><i class="fas fa-shield-halved text-teal-400"></i> Plataforma verificada</span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-black leading-tight tracking-tight">
                Bienvenido a <span class="bg-gradient-to-r from-teal-400 to-amber-400 bg-clip-text text-transparent">carsTUmotor</span>
            </h1>
            @auth
                <p class="mt-3 text-lg text-zinc-300">Hola, {{ strtok(auth()->user()->name, ' ') }} — tu cuenta está activa. Explorá el catálogo cuando quieras.</p>
            @else
                <p class="mt-3 text-lg text-zinc-300">La mejor plataforma para encontrar tu próximo vehículo con garantía y financiamiento.</p>
            @endauth
            <p class="mt-4 text-zinc-400 max-w-2xl mx-auto">Explorá nuestra selección de vehículos verificados, con transacciones seguras y entrega rápida. Tu auto ideal está a un clic.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="/catalogo" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black shadow-lg hover:opacity-90 transition"><i class="fas fa-search"></i> Explorar catálogo</a>
                <a href="mailto:contacto@carstumotor.com" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-white/20 text-white font-bold hover:bg-white/10 transition"><i class="fas fa-phone"></i> Contáctanos</a>
            </div>
        </div>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl font-black text-slate-900">¿Por qué elegir carsTUmotor?</h2>
            <p class="mt-2 text-zinc-500">La mejor experiencia en la compra de vehículos</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php $features = [
                ['icon'=>'fa-check-circle','title'=>'Autos verificados','desc'=>'Inspecciones rigurosas para garantizar calidad.'],
                ['icon'=>'fa-lock','title'=>'Transacciones seguras','desc'=>'Encriptación de última generación.'],
                ['icon'=>'fa-credit-card','title'=>'Pago flexible','desc'=>'Financiamiento adaptado a tu presupuesto.'],
                ['icon'=>'fa-users','title'=>'Atención personalizada','desc'=>'Equipo disponible en cada paso.'],
                ['icon'=>'fa-truck','title'=>'Entrega rápida','desc'=>'Recibí tu auto en tiempo récord.'],
                ['icon'=>'fa-star','title'=>'Garantía incluida','desc'=>'Garantía extendida para tu tranquilidad.'],
            ]; @endphp
            @foreach($features as $f)
            <div class="rounded-2xl bg-white border border-zinc-200 p-6 text-center shadow-sm hover:shadow-md hover:border-teal-200 transition">
                <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-100 grid place-items-center mx-auto text-teal-600 text-xl"><i class="fas {{ $f['icon'] }}"></i></div>
                <h3 class="mt-3 font-black text-slate-900">{{ $f['title'] }}</h3>
                <p class="mt-1 text-sm text-zinc-500 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="mx-4 sm:mx-6 lg:mx-8 rounded-3xl bg-slate-950 text-white overflow-hidden relative">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950 to-teal-900/30"></div>
    <div class="relative px-6 sm:px-10 py-10 text-center">
        <h2 class="text-2xl sm:text-3xl font-black">¿Listo para encontrar tu auto ideal?</h2>
        <p class="mt-2 text-zinc-300">Comenzá a explorar ahora mismo nuestro catálogo disponible</p>
        <a href="/catalogo" class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black">Explorar ahora <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
@endsection

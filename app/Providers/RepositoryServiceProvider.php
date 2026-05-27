<?php

namespace App\Providers;

use App\Interfaces\IArchivoPedidoRepository;
use App\Interfaces\ICitaRepository;
use App\Interfaces\IFinanzaRepository;
use App\Interfaces\IInventarioRepository;
use App\Interfaces\IPedidoRepository;
use App\Interfaces\IUserRepository;
use App\Interfaces\IUserProfileRepository;
use App\Interfaces\IVehiculoRepository;
use App\Repositories\ArchivoPedidoRepository;
use App\Repositories\CitaRepository;
use App\Repositories\FinanzaRepository;
use App\Repositories\InventarioRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserProfileRepository;
use App\Repositories\VehiculoRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(IUserProfileRepository::class, UserProfileRepository::class);
        $this->app->bind(IVehiculoRepository::class, VehiculoRepository::class);
        $this->app->bind(ICitaRepository::class, CitaRepository::class);
        $this->app->bind(IPedidoRepository::class, PedidoRepository::class);
        $this->app->bind(IArchivoPedidoRepository::class, ArchivoPedidoRepository::class);
        $this->app->bind(IInventarioRepository::class, InventarioRepository::class);
        $this->app->bind(IFinanzaRepository::class, FinanzaRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

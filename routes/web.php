<?php

use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta raíz redirige según autenticación
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

// Rutas de invitados (Solo si NO estás logueado)
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

// Rutas protegidas (Exigen iniciar sesión -> Muestran el Navbar)
Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    // Route::livewire('/solicitudes', 'pages::solicitudes.index')->name('solicitudes');
    // Route::livewire('/mis-solicitudes', 'pages::solicitudes.mis-solicitudes')->name('mis-solicitudes');
    // Route::livewire('/crear-solicitud', 'pages::solicitudes.crear-solicitud')->name('crear-solicitud');
    // Route::livewire('/detalle-solicitud/{id}', 'pages::solicitudes.detalle-solicitud')->name('detalle-solicitud');
    // Route::livewire('/editar-solicitud/{id}', 'pages::solicitudes.editar-solicitud')->name('editar-solicitud');

    Route::livewire('/distribuciones-diarias', 'pages::distribucion_maquinaria.index')->name('distribuciones-diarias');
    Route::livewire('/crear-distribucion', 'pages::distribucion_maquinaria.crear')->name('crear-distribucion');

    Route::livewire('/notificacion-detalle/{id}', 'pages::notificaciones.detalles')->name('notificacion-detalle');

    Route::livewire('/usuarios', 'pages::usuarios.index')->name('usuarios');
    Route::livewire('/crear-usuario', 'pages::usuarios.crear')->name('usuario-crear');


    Route::livewire('/maquinaria', 'pages::maquinaria.index')->name('maquinaria');
    Route::livewire('/implementos', 'pages::implementos.index')->name('implementos');
    Route::livewire('/operadores', 'pages::operadores.index')->name('operadores');
    Route::livewire('/actividades', 'pages::actividades.index')->name('actividades');
    Route::livewire('/tiempo-perdido', 'pages::tiempo_perdido.index')->name('tiempo-perdido');







    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

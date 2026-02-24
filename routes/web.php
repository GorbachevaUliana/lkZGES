<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Client\DashboardController as DashboardController;
use App\Http\Controllers\Client\TicketController as ClientTicketController;
// Подключить здесь будущий контроллер для показаний
// use App\Http\Controllers\ReadingController; 

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. Публичные роуты
Route::redirect('/', '/login');

// 2. Общие роуты для всех авторизованных (настройка профиля/аккаунта)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/welcome-step', [AccountController::class, 'index'])->name('welcome.step');
    Route::post('/account/link', [AccountController::class, 'link'])->name('account.link');
});

// 3. АДМИН-ПАНЕЛЬ (Только для сотрудников с CheckAdmin)
Route::middleware(['auth', 'verified', \App\Http\Middleware\CheckAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    
    // Главная админки
    // Route::get('/dashboard', function () {
    //     return Inertia::render('Admin/Dashboard');
    // })->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Потребители (Клиенты)
    Route::get('/clients', [AdminClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [AdminClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{id}', [AdminClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [AdminClientController::class, 'destroy'])->name('clients.destroy');
    
    // Документы
    Route::post('/documents/{id}', [AdminClientController::class, 'upload'])->name('documents.upload');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Обращения (Админ видит все тикеты)
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');

    // Сотрудники
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');

    // БЛОК ПОКАЗАНИЯ (АДМИН) - запланировано на 18.02.2026
    // Route::get('/readings', [AdminReadingController::class, 'index'])->name('readings.index');
});

// 4. ЛИЧНЫЙ КАБИНЕТ (Только для потребителей)
// Рекомендуется добавить middleware 'isClient', чтобы админ случайно не зашел сюда
Route::middleware(['auth', 'verified'])
    ->prefix('client') 
    ->name('client.')
    ->group(function() {
    
    // Главная личного кабинета
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    
    // Профиль и документы
    Route::get('/profile', [ClientDashboardController::class, 'profile'])->name('profile');
    Route::get('/documents', [ClientDashboardController::class, 'documents'])->name('documents'); 
    
    // Обращения (Клиент видит только свои)
    Route::get('/tickets', [ClientTicketController::class, 'ticketsIndex'])->name('tickets.index');
    Route::post('/tickets', [ClientTicketController::class, 'storeTicket'])->name('tickets.store');

    // БЛОК ПОКАЗАНИЯ (КЛИЕНТ) - запланировано на 18.02.2026
    // Route::get('/readings', [ClientReadingController::class, 'index'])->name('readings.index');
    // Route::post('/readings', [ClientReadingController::class, 'store'])->name('readings.store');
});

require __DIR__.'/auth.php';
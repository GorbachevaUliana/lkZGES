<?php

use App\Models\User;
use App\Models\Ticket;
use App\Models\Client;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\TicketController as ClientTicketController;
use App\Http\Controllers\ApplicationController;
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

Route::get('/new-application/{slug}', [ApplicationController::class, 'show'])->name('application.show');
Route::post('/new-application/{slug}', [ApplicationController::class, 'store'])
    ->name('application.store');

// 3. АДМИН-ПАНЕЛЬ (Только для сотрудников с CheckAdmin)
Route::middleware(['auth', 'verified', \App\Http\Middleware\CheckAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    
    // Главная админки
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $stats = [];

        if ($user->role === 'admin' || collect($user->permissions)->contains('clients')) {
            $stats['clients_count'] = Client::count();
        }

        if ($user->role === 'admin') {
            $stats['tickets_count'] = Ticket::where('status', 'open')->count();
        } else {
            $stats['tickets_count'] = Ticket::where('staff_id', $user->id)
                ->where('status', 'open')
                ->count();
        }

        return Inertia::render('Admin/Dashboard', ['stats' => $stats]);
    })->name('dashboard');

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [AdminClientController::class, 'index'])->name('index');
        Route::post('/', [AdminClientController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [AdminClientController::class, 'destroy'])->name('destroy');
        // Загрузка документов перенесена сюда для логики: admin/clients/{id}/upload
        Route::post('/{id}/upload', [AdminClientController::class, 'upload'])->name('upload');
    });
    
    // Документы (удаление конкретного файла)
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Обращения (Админ видит все тикеты)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [AdminTicketController::class, 'index'])->name('index');
        Route::put('/{id}', [AdminTicketController::class, 'update'])->name('update');
    });

    // Сотрудники
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
        Route::delete('/{staff}', [StaffController::class,'destroy'])->name('destroy');
    });

    // БЛОК ПОКАЗАНИЯ (АДМИН)
    // Route::get('/readings', [AdminReadingController::class, 'index'])->name('readings.index');
});

// 4. ЛИЧНЫЙ КАБИНЕТ (Только для потребителей)
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

    // Route::get('/readings', [ClientReadingController::class, 'index'])->name('readings.index');
    // Route::post('/readings', [ClientReadingController::class, 'store'])->name('readings.store');
});

require __DIR__.'/auth.php';
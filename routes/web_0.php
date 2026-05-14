<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMeterReadingController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\ApplicationSubmitController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\TicketController as ClientTicketController;
use App\Http\Controllers\MeterReadingController;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публичные роуты
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Общие роуты для всех авторизованных
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Страница приветствия (выбор: привязать ЛС или подать заявку)
    Route::get('/welcome-step', [AccountController::class, 'index'])->name('welcome.step');
    Route::post('/account/link', [AccountController::class, 'link'])->name('account.link');
});

/*
|--------------------------------------------------------------------------
| Заявки на заключение договора (доступны гостям)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Показ формы заявки
    Route::get('/new-application/{slug}', [ApplicationSubmitController::class, 'show'])->name('application.show');
    // Отправка заявки
    Route::post('/new-application/{slug}', [ApplicationSubmitController::class, 'submit'])->name('application.store');
});

/*
|--------------------------------------------------------------------------
| АДМИН-ПАНЕЛЬ (Только для сотрудников: admin, staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', \App\Http\Middleware\CheckAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Главная админки
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Потребители
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [AdminClientController::class, 'index'])->name('index');
            Route::post('/', [AdminClientController::class, 'store'])->name('store');
            Route::put('/{id}', [AdminClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [AdminClientController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/upload', [AdminClientController::class, 'upload'])->name('upload');
        });

        // Документы (удаление конкретного файла)
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        // Обращения
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [AdminTicketController::class, 'index'])->name('index');
            Route::put('/{id}', [AdminTicketController::class, 'update'])->name('update');
        });

        // Сотрудники
        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
            Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy');
        });

        Route::get('/readings', [AdminMeterReadingController::class, 'index'])->name('readings.index');
        // Метод для подтверждения оплаты админом
        Route::patch('/readings/{id}/verify', [AdminMeterReadingController::class, 'verifyPayment'])->name('readings.verify');

        // Показания
        //
        Route::get('/readings', [AdminMeterReadingController::class, 'index'])->name('readings.index');
        Route::patch('/readings/{id}/verify', [AdminMeterReadingController::class, 'verifyPayment'])->name('readings.verify');

        // ========================================
        // ЗАЯВКИ НА ЗАКЛЮЧЕНИЕ ДОГОВОРА (НОВОЕ)
        // ========================================
        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/', [AdminApplicationController::class, 'index'])->name('index');
            Route::get('/{application}', [AdminApplicationController::class, 'show'])->name('show');
            Route::post('/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('status');
            Route::post('/{application}/take-to-work', [AdminApplicationController::class, 'takeToWork'])->name('take-to-work');
            Route::post('/{application}/contract', [AdminApplicationController::class, 'uploadContract'])->name('contract');
            Route::post('/{application}/document', [AdminApplicationController::class, 'uploadDocument'])->name('document');
        });
    });

/*
|--------------------------------------------------------------------------
| ПОКАЗАНИЯ (meter reading)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/readings', [MeterReadingController::class, 'index'])->name('readings');
        Route::post('/readings', [MeterReadingController::class, 'storeReading'])->name('readings.store');
        Route::get('/invoice/{month}/{account}', [MeterReadingController::class, 'downloadInvoice'])->name('invoice.download');
        Route::post('/readings/{id}/pay', [MeterReadingController::class, 'pay'])->name('readings.pay');
    });

// Route::prefix('admin')->name('admin.')->group(function () {
// });

/*
|--------------------------------------------------------------------------
| ЛИЧНЫЙ КАБИНЕТ (applicant и client)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        // Главная личного кабинета
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

        // Профиль и документы
        Route::get('/profile', [ClientDashboardController::class, 'profile'])->name('profile');
        Route::get('/documents', [ClientDashboardController::class, 'documents'])->name('documents');

        // Обращения (только для полноценных клиентов!)
        Route::middleware(['can:create-tickets'])->group(function () {
            Route::get('/tickets', [ClientTicketController::class, 'ticketsIndex'])->name('tickets.index');
            Route::post('/tickets', [ClientTicketController::class, 'storeTicket'])->name('tickets.store');
        });
    });

require __DIR__.'/auth.php';

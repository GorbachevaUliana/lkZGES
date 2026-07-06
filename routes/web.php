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
| Страница приветствия (для новых пользователей)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/secure/documents/{document}', [DocumentController::class, 'serve'])
        ->name('documents.serve');
    Route::get('/secure/attachments/{attachment}', [\App\Http\Controllers\AttachmentController::class, 'serve'])
        ->name('attachments.serve');

    Route::get('/welcome-step', [AccountController::class, 'index'])->name('welcome.step');
    Route::post('/account/link', [AccountController::class, 'link'])
        ->middleware('throttle:10,1')
        ->name('account.link');
    Route::post('/account/verify', [AccountController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('account.verify');
});

/*
|--------------------------------------------------------------------------
| Заявки на заключение договора (доступны гостям)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/new-application/{slug}', [ApplicationSubmitController::class, 'show'])->name('application.show');
    // throttle:5,1 — не более 5 отправок заявки в минуту.
    // Каждая заявка порождает транзакцию, PDF и загрузку файлов — флуд дорог.
    Route::post('/new-application/{slug}', [ApplicationSubmitController::class, 'submit'])
        ->middleware('throttle:5,1')
        ->name('application.store');
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
        // can_access:dashboard — раньше пункт меню скрывался на фронте, если
        // у сотрудника не стоит галочка "Главная", но сам урл был открыт для
        // любого admin/staff. Теперь это реально проверяется и на бэкенде.
        Route::middleware('can_access:dashboard')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        });

        // Потребители
        Route::middleware('can_access:clients')->prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [AdminClientController::class, 'index'])->name('index');
            Route::post('/', [AdminClientController::class, 'store'])->name('store');
            Route::put('/{id}', [AdminClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [AdminClientController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/upload', [AdminClientController::class, 'upload'])->name('upload');
        });

        // Документы (удаление конкретного файла) — сейчас используется только
        // со страницы "Обращения", поэтому привязано к праву tickets
        Route::middleware('can_access:tickets')->group(function () {
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        });

        // Обращения
        Route::middleware('can_access:tickets')->prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [AdminTicketController::class, 'index'])->name('index');
            Route::put('/{id}', [AdminTicketController::class, 'update'])->name('update');
        });

        // Сотрудники
        Route::middleware('can_access:staff')->prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
            Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy');
        });

        // Показания (дубль роутов убран — раньше тут было два одинаковых GET и PATCH)
        Route::middleware('can_access:readings')->group(function () {
            Route::get('/readings', [AdminMeterReadingController::class, 'index'])->name('readings.index');
            // Метод для подтверждения оплаты админом
            Route::patch('/readings/{id}/verify', [AdminMeterReadingController::class, 'verifyPayment'])->name('readings.verify');
        });

        // ========================================
        // ЗАЯВКИ НА ЗАКЛЮЧЕНИЕ ДОГОВОРА (НОВОЕ)
        // ========================================
        Route::middleware('can_access:applications')->prefix('applications')->name('applications.')->group(function () {
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
| ЛИЧНЫЙ КАБИНЕТ (applicant и client)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ClientDashboardController::class, 'profile'])->name('profile');
        Route::get('/documents', [ClientDashboardController::class, 'documents'])->name('documents');

        Route::get('/readings', [MeterReadingController::class, 'index'])->name('readings');
        Route::post('/readings', [MeterReadingController::class, 'storeReading'])->name('readings.store');
        Route::post('/readings/{id}/pay', [MeterReadingController::class, 'pay'])->name('readings.pay');
        Route::get('/invoice/{month}/{account}', [MeterReadingController::class, 'downloadInvoice'])->name('invoice.download');

        Route::middleware(['can:create-tickets'])->group(function () {
            Route::get('/tickets', [ClientTicketController::class, 'ticketsIndex'])->name('tickets.index');
            Route::post('/tickets', [ClientTicketController::class, 'storeTicket'])->name('tickets.store');
        });
    });

require __DIR__.'/auth.php';
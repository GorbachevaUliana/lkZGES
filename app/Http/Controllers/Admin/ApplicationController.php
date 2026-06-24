<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Tariff;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    private ApplicationService $applicationService;

    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Список всех заявок
     */
    public function index()
    {
        $applications = Application::with(['user', 'client', 'property'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Applications/ApplicationsList', [
            'applications' => $applications,
            'statuses' => Application::getStatuses(),
            'clientTypes' => Application::getClientTypes(),
            'tariffs' => Tariff::all(),
        ]);
    }

    /**
     * Заявки, ожидающие обработки
     */
    public function pending()
    {
        $applications = Application::with(['user', 'client', 'property'])
            ->whereIn('status', ['new', 'processing'])
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Admin/Applications/ApplicationsList', [
            'applications' => $applications,
            'mode' => 'pending',
        ]);
    }

    /**
     * Просмотр одной заявки (API для модального окна)
     */
    public function show(Application $application)
    {
        $application->load([
            'user',
            'client.properties',
            'property',
            'documents',
            'client.documents',
        ]);

        return response()->json([
            'application' => $application,
            'tariffs' => Tariff::all(),
        ]);
    }

    /**
     * Обновление статуса заявки
     */
    public function updateStatus(Request $request, Application $application)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,processing,pending,approved,rejected',
            'account_number' => 'required_if:status,approved|string|nullable|unique:properties,account_number,' . ($application->property_id ?? 'NULL'),
            'admin_comment' => 'nullable|string',
            'tariff_id' => 'required_if:status,approved|integer|nullable|exists:tariffs,id',
        ]);

        $this->applicationService->updateStatus($application, $validated);

        return back()->with('success', 'Статус заявки обновлен');
    }

    /**
     * Взять заявку в работу
     */
    public function takeToWork(Application $application)
    {
        if ($application->status !== 'new' && $application->status !== 'pending') {
            return back()->with('error', 'Заявка уже в работе или обработана');
        }

        $application->update([
            'status' => 'processing',
            'processed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Заявка взята в работу');
    }

    /**
     * Загрузка договора админом
     */
    public function uploadContract(Request $request, Application $application)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('contracts', 'local');

        $application->update([
            'contract_pdf_path' => $path,
        ]);

        \App\Models\Document::create([
            'client_id' => $application->client_id,
            'application_id' => $application->id,
            'name' => 'Договор №' . $application->id,
            'file_path' => $path,
            'type' => 'contract',
            'description' => 'Договор энергоснабжения',
        ]);

        return back()->with('success', 'Договор загружен');
    }

    /**
     * Загрузка дополнительного документа админом
     */
    public function uploadDocument(Request $request, Application $application)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('client_documents', 'local');

        \App\Models\Document::create([
            'client_id' => $application->client_id,
            'application_id' => $application->id,
            'name' => $request->name ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'type' => 'other',
            'description' => 'Загружено администратором',
        ]);

        return back()->with('success', 'Документ загружен');
    }
}
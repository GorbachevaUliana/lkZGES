<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Tariff;
use App\Services\ApplicationService;
use App\Services\ContractService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UploadApplicationDocumentRequest;
use App\Http\Requests\Admin\UploadContractRequest;
use App\Http\Requests\Admin\PublishContractRequest;
use Illuminate\Support\Facades\Storage;
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
        // Черновики (draft) — незаконченные заявки, видны ТОЛЬКО клиенту,
        // который их пишет. В админке их быть не должно: заявку ещё не
        // подали. Исключаем из списка и из счётчика «Все». Остальные
        // счётчики считают по конкретным статусам, draft в них и так не
        // попадает.
        $applications = Application::with(['user', 'client', 'property', 'documents', 'contract'])
            ->where('status', '!=', ApplicationStatus::Draft->value)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return Inertia::render('Admin/Applications/ApplicationsList', [
            'applications' => $applications,
            'statuses'     => Application::getStatuses(),
            'clientTypes'  => Application::getClientTypes(),
            'tariffs'      => Tariff::all(),
            'stats' => [
                'all'        => Application::where('status', '!=', ApplicationStatus::Draft->value)->count(),
                'pending'    => Application::whereIn('status', ['new', 'pending'])->count(),
                'processing' => Application::where('status', 'processing')->count(),
                'approved'   => Application::where('status', 'approved')->count(),
            ],
        ]);
    }

    /**
     * Заявки, ожидающие обработки
     */
    public function pending()
    {
        $applications = Application::with(['user', 'client', 'property', 'contract'])
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
            'application'         => $application,
            'tariffs'             => Tariff::all(),
            // Только допустимые следующие статусы — фронт показывает
            // в Select лишь их, а не все пять статусов подряд.
            'allowedNextStatuses' => $this->applicationService->getAllowedNextStatuses($application->status),
        ]);
    }

    /**
     * Обновление статуса заявки
     */
    public function updateStatus(Request $request, Application $application)
    {
        // Разрешаем только статусы, допустимые из текущего состояния заявки.
        // Полная проверка логики переходов — в ApplicationService::updateStatus().
        $allowedNext = array_column(
            $this->applicationService->getAllowedNextStatuses($application->status),
            'value'
        );

        $validated = $request->validate([
            'status'         => ['required', 'string', 'in:' . implode(',', $allowedNext)],
            'account_number' => 'required_if:status,approved|string|nullable|unique:properties,account_number,' . ($application->property_id ?? 'NULL'),
            'admin_comment'  => 'nullable|string|max:2000',
            'tariff_id'      => 'required_if:status,approved|integer|nullable|exists:tariffs,id',
        ]);

        $this->applicationService->updateStatus($application, $validated);

        return back()->with('success', 'Статус заявки обновлён');
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
    public function uploadContract(
        UploadContractRequest $request,
        Application $application,
        ContractService $contractService
    ) {
        // Только PDF: по файлу считается хеш, и он же подписывается
        // электронной подписью. Скан в jpg для этой роли не годится.
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $contract = $contractService->createFromUpload($application, $request->file('file'));

        $application->update([
            'contract_pdf_path' => $contract->file_path,
        ]);

        return back()->with('success', 'Договор загружен. Он ещё не направлен потребителю.');
    }

    /**
     * Скачивание файла договора оператором.
     *
     * Нужен отдельно от documents.serve: пока договор в черновике,
     * записи Document не существует — она появляется только при публикации.
     */
    public function downloadContract(Application $application)
    {
        $contract = $application->contract;

        if (! $contract || ! Storage::disk('local')->exists($contract->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($contract->file_path, $contract->original_name);
    }

    public function publishContract(PublishContractRequest $request, Application $application, ContractService $contractService)
    {
        $contract = $application->contract;

        if (! $contract) {
            return back()->withErrors(['contract' => 'Договор не загружен.']);
        }

        $contractService->publish($contract);

        return back()->with('success', 'Договор направлен потребителю.');
    }

    /**
     * Загрузка дополнительного документа админом
     */
    public function uploadDocument(UploadApplicationDocumentRequest $request, Application $application)
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
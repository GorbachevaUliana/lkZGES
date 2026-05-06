<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Document;
use App\Models\Tariff;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    /**
     * Список заявок для админки
     */
    public function index()
    {
        $applications = Application::with(['user', 'client', 'processor'])->latest()->get();
        return Inertia::render('Admin/Applications/ApplicationsList', [
            'applications' => ApplicationResource::collection($applications),
            'tariffs' => Tariff::all(),
            'statuses' => [
                'pending' => 'Ожидает рассмотрения',
                'processing' => 'В работе',
                'approved' => 'Одобрена',
                'rejected' => 'Отклонена',
            ],
            'clientTypes' => [
                'individual' => 'Физическое лицо',
                'legal' => 'Юридическое лицо',
            ],
        ]);
    }

    /**
     * Данные заявки (для карточки)
     */
    public function show(Application $application)
    {
        $application->loadMissing(['user', 'client.documents', 'processor']);
        return new ApplicationResource($application);
    }

    /**
     * Изменить статус заявки
     */
    public function updateStatus(Request $request, Application $application, ApplicationService $service)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,approved,rejected',
            'admin_comment' => 'nullable|string|max:1000',
            // Если статус "approved", тариф обязателен
            'tariff_id' => 'required_if:status,approved|exists:tariffs,id',
            // Если статус "approved", ЛС обязателен и должен быть уникален в таблице properties
            'account_number' => [
                'required_if:status,approved',
                'nullable',
                'string',
                'max:50',
                // Проверка уникальности ЛС только при одобрении
                $request->status === 'approved' ? 'unique:properties,account_number' : '',
            ],
        ]);

        $service->updateStatus($application, $validated);
        
        return back()->with('success', 'Статус заявки обновлён');
    }

    public function uploadContract(Request $request, Application $application)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('contracts', 'public');
        \DB::transaction(function() use ($application, $path) {
            $application->update(['contract_pdf_path' => $path]);

            if ($application->client) {
                Document::create([
                    'client_id' => $application->client->id,
                    'application_id' => $application->id,
                    'name' => "Договор №{$application->id}",
                    'file_path' => $path,
                    'type' => Document::TYPE_CONTRACT,
                ]);
            }
        });
        

        return back()->with('success', 'Договор успешно загружен');
    }

    public function uploadDocument(Request $request, Application $application)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:10240',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('client_documents', 'public');

        if ($application->client) {
            Document::create([
                'client_id' => $application->client->id,
                'application_id' => $application->id,
                'name' => $request->name ?? $file->getClientOriginalName(),
                'file_path' => $path,
                'type' => Document::TYPE_OTHER,
            ]);
        }

        return back()->with('success', 'Документ успешно загружен');
    }

    public function takeToWork(Application $application)
    {
        $application->update([
            'status' => 'processing',
            'processor_id' => auth()->id()
            ]);

        return back()->with('success', 'Заявка взята в работу');
    }
}

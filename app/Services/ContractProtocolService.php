<?php

namespace App\Services;

use App\Enums\PdfDocumentType;
use App\Enums\SignatureMethod;
use App\Enums\SignerType;
use App\Enums\SigningReason;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\Document;
use App\Models\PdfTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContractProtocolService
{
    private const ORGANIZATION = 'ООО «Заринская горэлектросеть»';
    private const TIMEZONE     = 'Asia/Barnaul';

    /**
     * Сформировать протокол подписания договора.
     *
     * Протокол — человекочитаемая выписка из журнала подписей. Он не
     * создаёт юридической силы, а фиксирует уже произошедшее, поэтому
     * его можно пересоздавать: старая запись заменяется новой.
     */
    public function generate(Contract $contract): Document
    {
        $contract->load(['signatures.signedBy', 'client', 'application.property']);

        $html = $this->render($contract);

        $pdf      = Pdf::loadHTML($html)->setPaper('a4');
        $fileName = 'protocol_' . $contract->id . '_' . time() . '.pdf';
        $filePath = 'protocols/' . $fileName;
        
        Storage::disk('local')->put($filePath, $pdf->output());

        return DB::transaction(function () use ($contract, $filePath) {
            // Протокол у договора один: пересоздание заменяет прежний.
            $old = Document::where('application_id', $contract->application_id)
                ->where('type', PdfDocumentType::SigningProtocol->value)
                ->get();

            foreach ($old as $document) {
                Storage::disk('local')->delete($document->file_path);
                $document->delete();
            }

            return Document::create([
                'client_id'      => $contract->client_id,
                'application_id' => $contract->application_id,
                'name'           => 'Протокол подписания договора №' . $contract->application_id,
                'file_path'      => $filePath,
                'type'           => PdfDocumentType::SigningProtocol->value,
                'description'    => 'Сформирован автоматически при подписании',
            ]);
        });
    }

    private function render(Contract $contract): string
    {
        $data = $this->collectData($contract);

        $template = PdfTemplate::getTemplate(
            $contract->client_type,
            PdfDocumentType::SigningProtocol->value
        );

        return $template
            ? $template->render($data)
            : view('pdf.signing_protocol', $data)->render();
    }

    /**
     * Данные для шаблона. Отдельным методом — их же проверяют тесты,
     * не разбирая готовый PDF.
     */
    public function collectData(Contract $contract): array
    {
        $client = $contract->client;

        return [
            'generated_at'      => now(self::TIMEZONE)->format('d.m.Y H:i'),
            'contract_number'   => $contract->application_id,
            'file_name'         => $contract->original_name,
            'file_hash'         => $contract->file_hash,
            'signing_reason'    => $this->reasonLabel($contract),
            'organization_name' => self::ORGANIZATION,
            'client_name'       => $client?->display_name ?? 'Не указано',
            'client_inn'        => $client?->inn,
            'account_number'    => $contract->application?->property?->account_number,
            'signatures' => $contract->signatures
                ->sortBy(fn (ContractSignature $s) => [$s->signed_at?->getTimestamp(), $s->id])
                ->map(fn (ContractSignature $s) => $this->signatureRow($s))
                ->values()
                ->all(),
        ];
    }

    private function reasonLabel(Contract $contract): string
    {
        $reason = SigningReason::tryFrom((string) $contract->signing_reason);

        if ($reason === SigningReason::PowerThreshold && $contract->max_power_kw !== null) {
            return $reason->label() . ' (' . rtrim(rtrim(number_format($contract->max_power_kw, 2, ',', ' '), '0'), ',') . ' кВт)';
        }

        return $reason?->label() ?? 'Не указано';
    }

    private function signatureRow(ContractSignature $signature): array
    {
        $signer = SignerType::tryFrom($signature->signer);
        $method = SignatureMethod::tryFrom($signature->method);

        return [
            'signer'        => 'Подпись: ' . ($signer?->label() ?? $signature->signer),
            'method'        => $method?->label() ?? $signature->method,
            'signed_at'     => $signature->signed_at?->timezone(self::TIMEZONE)->format('d.m.Y H:i:s'),
            'document_hash' => $signature->document_hash,
            'details'       => $this->signatureDetails($signature),
        ];
    }

    /**
     * Подробности, зависящие от способа подписи. Список пар
     * «название — значение», чтобы шаблон не переписывался,
     * когда появятся данные сертификата.
     */
    private function signatureDetails(ContractSignature $signature): array
    {
        $details = [];

        if ($signature->pep_channel) {
            $details['Канал подтверждения'] = $signature->pep_channel === 'email'
                ? 'Электронная почта'
                : 'СМС';
        }

        if ($signature->pep_sent_to) {
            $details['Код направлен'] = $signature->pep_sent_to;
        }

        if ($signature->signature_file_path) {
            $details['Файл подписи'] = basename($signature->signature_file_path);
        }

        if ($signature->signature_file_hash) {
            $details['Хеш файла подписи (SHA-256)'] = $signature->signature_file_hash;
        }

        if ($signature->certificate_subject) {
            $details['Сертификат выдан'] = $signature->certificate_subject;
        }

        if ($signature->certificate_inn) {
            $details['ИНН в сертификате'] = $signature->certificate_inn;
        }

        if ($signature->ip) {
            $details['IP-адрес'] = $signature->ip;
        }

        if ($signature->signedBy) {
            $details['Загрузил в системе'] = $signature->signedBy->email;
        }

        return $details;
    }
}
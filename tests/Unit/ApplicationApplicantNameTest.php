<?php

namespace Tests\Unit;

use App\Enums\ClientType;
use App\Models\Application;
use Tests\TestCase;

class ApplicationApplicantNameTest extends TestCase
{
    /**
     * Заявку не сохраняем и клиента не привязываем: проверяем ветку,
     * которая берёт имя из данных формы, когда записи Client ещё нет.
     */
    private function makeApplication(string $clientType, array $data = []): Application
    {
        $application = new Application();
        $application->client_type = $clientType;
        $application->data        = $data;

        return $application;
    }

    public function test_individual_is_shown_by_fio_from_form_data(): void
    {
        $application = $this->makeApplication(ClientType::Individual->value, [
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'middle_name' => 'Иванович',
        ]);

        $this->assertSame('Иванов Иван Иванович', $application->applicant_name);
    }

    public function test_legal_is_shown_by_company_name_from_form_data(): void
    {
        $application = $this->makeApplication(ClientType::Legal->value, [
            'company_name' => 'ООО «Ромашка»',
        ]);

        $this->assertSame('ООО «Ромашка»', $application->applicant_name);
    }

    public function test_entrepreneur_is_shown_by_fio_with_prefix(): void
    {
        $application = $this->makeApplication(ClientType::Entrepreneur->value, [
            'last_name'  => 'Иванов',
            'first_name' => 'Иван',
        ]);

        $this->assertSame('ИП Иванов Иван', $application->applicant_name);
    }

    public function test_entrepreneur_without_fio_falls_back_to_placeholder(): void
    {
        $application = $this->makeApplication(ClientType::Entrepreneur->value);

        $this->assertSame('Не указано', $application->applicant_name);
    }
}
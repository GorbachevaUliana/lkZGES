<?php

namespace Tests\Unit;

use App\Enums\ClientType;
use App\Models\PdfTemplate;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DefaultApplicationViewTest extends TestCase
{
    public function test_each_client_type_points_to_its_own_view(): void
    {
        $this->assertSame('pdf.application_individual', ClientType::Individual->defaultApplicationView());
        $this->assertSame('pdf.application_legal', ClientType::Legal->defaultApplicationView());
        $this->assertSame('pdf.application_entrepreneur', ClientType::Entrepreneur->defaultApplicationView());
    }

    public function test_every_client_type_has_an_existing_view(): void
    {
        foreach (ClientType::cases() as $type) {
            $view = $type->defaultApplicationView();

            $this->assertTrue(
                View::exists($view),
                "Для типа лица {$type->value} нет blade-шаблона {$view}"
            );
        }
    }

    /**
     * Шаблон ИП должен не просто существовать, но и компилироваться
     * на пустых данных — именно так его вызывает getDefaultTemplate().
     */
    public function test_entrepreneur_default_template_renders(): void
    {
        $html = PdfTemplate::getDefaultTemplate(ClientType::Entrepreneur->value);

        $this->assertNotNull($html, 'Шаблон ИП не отрендерился');
        $this->assertStringContainsString('индивидуальный предприниматель', $html);
        $this->assertStringContainsString('ОГРНИП', $html);
    }

    public function test_every_default_template_renders(): void
    {
        foreach (ClientType::cases() as $type) {
            $this->assertNotNull(
                PdfTemplate::getDefaultTemplate($type->value),
                "Шаблон по умолчанию для {$type->value} не отрендерился"
            );
        }
    }

    public function test_unknown_client_type_has_no_default_template(): void
    {
        $this->assertNull(PdfTemplate::getDefaultTemplate('something_else'));
    }
}
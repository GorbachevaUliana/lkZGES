<?php

namespace Tests\Feature\Pdf;

use App\Models\PdfTemplate;
use Database\Seeders\PdfTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfTemplateRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PdfTemplateSeeder::class);
    }

    public function test_individual_template_renders_data_via_twig(): void
    {
        $template = PdfTemplate::getTemplate('individual', 'application');
        $this->assertNotNull($template, 'Шаблон физлица должен быть засеян');

        $html = $template->render([
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'middle_name' => 'Иванович',
            'passport'    => '1111 111111',
        ]);

        $this->assertStringContainsString('Иванов', $html);
        $this->assertStringContainsString('1111 111111', $html);

        $this->assertStringNotContainsString('{{', $html);
        $this->assertStringNotContainsString("\$data[", $html);
        $this->assertStringNotContainsString('@php', $html);
    }
}
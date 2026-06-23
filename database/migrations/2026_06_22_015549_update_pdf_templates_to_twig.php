<?php
 
use App\Models\PdfTemplate;
use App\Services\PdfTemplateDefinitions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
 
return new class extends Migration
{
    public function up(): void
    {
        $this->updateTemplate(
            'application_individual',
            'Заявление на заключение договора (физлицо)',
            PdfTemplateDefinitions::individualContent(),
            'individual'
        );
 
        $this->updateTemplate(
            'application_legal',
            'Заявление на заключение договора (юрлицо)',
            PdfTemplateDefinitions::legalContent(),
            'legal'
        );
    }
 
    private function updateTemplate(string $slug, string $name, string $content, string $clientType): void
    {
        $table = (new PdfTemplate())->getTable();
        $existing = DB::table($table)->where('slug', $slug)->first();
 
        $now = now();
 
        if ($existing) {
            DB::table($table)->where('slug', $slug)->update([
                'name' => $name,
                'content' => $content,
                'client_type' => $clientType,
                'document_type' => 'application',
                'updated_at' => $now,
            ]);
 
            Log::info("PDF template '{$slug}' updated to Twig sandbox syntax.");
        } else {
            DB::table($table)->insert([
                'name' => $name,
                'slug' => $slug,
                'client_type' => $clientType,
                'document_type' => 'application',
                'content' => $content,
                'variables' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
 
            Log::info("PDF template '{$slug}' created with Twig sandbox syntax.");
        }
    }

    public function down(): void
    {
    }
};
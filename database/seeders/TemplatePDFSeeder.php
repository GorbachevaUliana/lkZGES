<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PdfTemplate;

class TemplatePDFSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PdfTemplate::create([
            'name' => 'Заявление на заключение договора (физлицо)',
            'slug' => 'application_individual',
            'client_type' => 'individual',
            'document_type' => 'application',
            'is_active' => true,
            'variables' => [
                'last_name', 'first_name', 'middle_name',
                'passport', 'passport_issue', 'passport_issue_date',
                'region', 'district', 'locality', 'street', 'house', 'corpus', 'apartment',
                'actual_region', 'actual_district', 'actual_locality', 'actual_street', 'actual_house', 'actual_corpus', 'actual_apartment',
                'phone', 'email',
                'power_object', 'region_object', 'district_object', 'locality_object', 'street_object', 'house_object', 'corpus_object', 'apartment_object',
                'area', 'resident_count', 'max_power', 'voltage_level', 'act_reference',
                'consumption_purpose', 'has_meter', 'tariff_choice', 'supply_period',
                'appeal_reason', 'payment_delivery', 'notification_delivery',
            ],
            'content' => file_get_contents(resource_path('views/pdf/application_individual.blade.php')),
        ]);
    }
}

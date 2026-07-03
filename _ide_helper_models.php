<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $client_id
 * @property int $template_id
 * @property int|null $property_id
 * @property string $client_type
 * @property array<array-key, mixed> $data
 * @property string $status
 * @property string|null $generated_pdf_path
 * @property string|null $contract_pdf_path
 * @property string|null $admin_comment
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property int|null $processed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $tariff_id
 * @property string|null $tariff_category
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read string|null $account_number
 * @property-read string $applicant_name
 * @property-read string $client_type_name
 * @property-read string|null $contract_pdf_url
 * @property-read string|null $generated_pdf_url
 * @property-read string $processor_name
 * @property-read string $status_name
 * @property-read string $user_email
 * @property-read \App\Models\User|null $processor
 * @property-read \App\Models\Property|null $property
 * @property-read \App\Models\Tariff|null $tariff
 * @property-read \App\Models\ApplicationTemplate $template
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application individuals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application legal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application processing()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereAdminComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereContractPdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereGeneratedPdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTariffCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTariffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application withoutTrashed()
 */
	class Application extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property array<array-key, mixed>|null $content
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $pdf_template_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate wherePdfTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationTemplate whereUpdatedAt($value)
 */
	class ApplicationTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $client_type
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $company_name
 * @property string|null $inn
 * @property string|null $kpp
 * @property string|null $ogrn
 * @property string|null $contact_person
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $contract_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read string $client_type_name
 * @property-read string $display_name
 * @property-read string $full_name
 * @property-read string $status_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeterReading> $readings
 * @property-read int|null $readings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client individuals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client legal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereContractDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereInn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereKpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereOgrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withoutTrashed()
 */
	class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * Модель документа
 *
 * Типы документов:
 * - application: Заявка на заключение договора
 * - contract: Договор электроснабжения
 * - other: Другие документы
 *
 * @property int $id
 * @property int $client_id
 * @property int|null $application_id
 * @property string $name
 * @property string $file_path
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $original_name
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\Client|null $client
 * @property-read string $display_name
 * @property-read string $type_name
 * @property-read string $url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document applications()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document contracts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document other()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withoutTrashed()
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $property_id
 * @property int|null $tariff_id
 * @property int $previous_value
 * @property int $current_value
 * @property int $consumed
 * @property numeric|null $total_sum
 * @property \Illuminate\Support\Carbon $reading_date
 * @property bool $is_paid
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Property|null $property
 * @property-read \App\Models\Tariff|null $tariff
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereConsumed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereCurrentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading wherePreviousValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereReadingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereTariffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereTotalSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MeterReading withoutTrashed()
 */
	class MeterReading extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $client_type
 * @property string $document_type
 * @property string $content
 * @property array<array-key, mixed>|null $variables
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationTemplate> $applicationTemplates
 * @property-read int|null $application_templates_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate applications()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate contracts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate forIndividuals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate forLegal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PdfTemplate whereVariables($value)
 */
	class PdfTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $client_id
 * @property string|null $account_number
 * @property string $address
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $tariff_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\MeterReading|null $last_reading
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeterReading> $meterReadings
 * @property-read int|null $meter_readings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MeterReading> $readings
 * @property-read int|null $readings_count
 * @property-read \App\Models\Tariff|null $tariff
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property activeWithAccount()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereTariffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property withoutTrashed()
 */
	class Property extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property numeric|null $coefficient
 * @property numeric $price_1
 * @property numeric $price_2
 * @property numeric $price_3
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff wherePrice1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff wherePrice2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff wherePrice3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tariff whereUpdatedAt($value)
 */
	class Tariff extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $staff_id
 * @property int|null $replied_by
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property string|null $admin_reply
 * @property string|null $replied_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\User|null $repliedBy
 * @property-read \App\Models\User|null $staff
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereAdminReply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereRepliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereRepliedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticket withoutTrashed()
 */
	class Ticket extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property string $file_path
 * @property string $file_name
 * @property bool $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ticket|null $ticket
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAttachment whereUpdatedAt($value)
 */
	class TicketAttachment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property \App\Enums\UserRole $role
 * @property string $status
 * @property array<array-key, mixed>|null $permissions
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $link_code
 * @property string|null $link_code_expires
 * @property int|null $link_client_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\Client|null $client
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User applicants()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User clients()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User guests()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User staff()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkCodeExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}


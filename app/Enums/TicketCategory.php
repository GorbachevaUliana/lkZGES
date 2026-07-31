<?php

namespace App\Enums;

enum TicketCategory: string
{
    case ContractChange      = 'contract_change';
    case ContractTermination = 'contract_termination';
    case ContractDocuments   = 'contract_documents';
    case MeterService        = 'meter_service';
    case Complaint           = 'complaint';
    case Statement           = 'statement';
    case Other               = 'other';

    public function label():string
    {
        return match($this) {
            self::ContractChange => 'Изменение договора',
            self::ContractTermination => 'Расторжение договора',
            self::ContractDocuments => 'Получение проекта договора, доп. соглашения и иных документов',
            self::MeterService => 'Замена/установка/поверка приборов учета',
            self::Complaint => 'Жалобы и предложения',
            self::Statement => 'Заявления',
            self::Other => 'Другое',
        };
    }

    public static function labels(): array
    {
        return array_column(
            array_map(fn($c) => ['key' => $c->value, 'label' => $c->label()], self::cases()),
            'label',
            'key'
        );
    }
}
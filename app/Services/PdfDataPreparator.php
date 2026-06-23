<?php

namespace App\Services;

class PdfDataPreparator
{
    private const DASH = '___';
    private const EMPTY_ADDRESS = 'Не указан';

    public function prepare(array $rawData, array $mainInfo): array
    {
        $result = [];
        foreach ($mainInfo as $key => $value) {
            $result[$key] = $this->stringify($value);
        }

        $result['registration_address'] = $this->buildAddress($rawData, '');
        $result['actual_address'] = $this->buildAddress($rawData, 'actual_');
        $result['object_address_full'] = $this->buildAddress($rawData, null, true);
        $result['consent'] = $this->consentToString($rawData['personal_info'] ?? null);

        foreach (['voltage_level', 'consumption_purpose', 'has_meter', 'tariff_choice'] as $key){
            $result[$key] = $this->arrayToString($rawData[$key] ?? null);
        }

        foreach(['payment_delivery', 'notification_delivery'] as $key){
            $result[$key] = $this->dynamicInputToString($rawData[$key] ?? null);
        }

        foreach($rawData as $key => $value) {
            if (array_key_exists($key, $result)) {
                continue;
            }
            $result[$key] = $this->scalarOrDash($value);
        }

        $result['data'] = array_map(
            fn ($v) => is_array($v) ? $this->arrayToString($v) : $this->stringify($v),
            $rawData
        );

        return $result;
    }

    public function buildAddress(array $data, ?string $prefix = '', bool $isObject = false): string
    {
        $suffix = $isObject ? '_object' : '';
        $get = function (string $field) use ($data, $prefix, $suffix): ?string{
            $key = $prefix . $field . $suffix;
            $value = $data[$key] ?? null;
            return $this->stringify($value);
        };

        $parts = [];
        $region = $get('region') ?: 'Алтайский край';
        if ($region) {
            $parts[] = $region;
        }

        $district = $get('district');
        if ($district !== '') {
            $parts[] = $district . ' район';
        }
 
        $locality = $get('locality');
        if ($locality !== '') {
            $parts[] = $locality;
        }
 
        $street = $get('street');
        if ($street !== '') {
            $parts[] = 'ул. ' . $street;
        }
 
        $house = $get('house');
        if ($house !== '') {
            $parts[] = 'д. ' . $house;
        }
 
        $corpus = $get('corpus');
        if ($corpus !== '') {
            $parts[] = 'корп. ' . $corpus;
        }
 
        $apartment = $get('apartment');
        if ($apartment !== '') {
            $parts[] = 'кв. ' . $apartment;
        }
 
        return !empty($parts) ? implode(', ', $parts) : self::EMPTY_ADDRESS;
    }

    public function arrayToString($value): string
    {
        if (is_array($value)) {
            if (isset($value['preset']) || isset($value['custom'])) {
                $preset = (array) ($value['preset'] ?? []);
                $customs = collect($value['custom'] ?? [])->pluck('value')->filter()->all();
                $value = array_merge($preset, $customs);
            }
            $value = implode(', ', array_filter(array_map([$this, 'stringify'], $value)));
        }
        return $this->scalarOrDash($value);
    }

    public function dynamicInputToString($value): string
    {
        if (is_array($value)) {
            if (isset($value['selected']) || array_key_exists('selected', $value)) {
                $selected = $this->stringify($value['selected'] ?? '');
                $input = $this->stringify($value['inputValue'] ?? '');
 
                if ($selected !== '' && $input !== '') {
                    return $selected . ': ' . $input;
                }
                if ($selected !== '') {
                    return $selected;
                }
            }
            return $this->arrayToString($value);
        }
 
        return $this->scalarOrDash($value);
    }
 
    public function consentToString($value): string
    {
        if (is_array($value)) {
            return in_array('Да', $value, true) ? 'Да' : 'Нет';
        }
 
        return str_contains($this->stringify($value), 'Да') ? 'Да' : 'Нет';
    }
 
    public function scalarOrDash($value): string
    {
        $string = $this->stringify($value);
 
        return $string !== '' ? $string : self::DASH;
    }
 
    private function stringify($value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        if ($value instanceof \Stringable) {
            return (string) $value;
        }
        if (is_array($value)) {
            return implode(', ', array_filter(array_map('strval', $value)));
        }
 
        return '';
    }
}
?>
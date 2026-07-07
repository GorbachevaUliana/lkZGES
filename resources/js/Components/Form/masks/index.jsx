import React from 'react';
import { IMaskInput } from 'react-imask';

const createMask = (mask, placeholder, extraProps = {}) =>
    React.forwardRef(function MaskedInput(props, ref) {
        const { onChange, value, ...other } = props;
        return (
            <IMaskInput
                {...other}
                {...extraProps}
                value={value != null ? String(value) : ''}
                mask={mask}
                inputRef={ref}
                onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
                overwrite
                placeholder={placeholder}
            />
        );
    });

export const PassportMask    = createMask('0000 000000',          '____ ______');
export const PhoneMask       = createMask('+7 (000) 000-00-00',   '+7 (___) ___-__-__');
export const SnilsMask       = createMask('000-000-000 00',       '___-___-___ __');
export const RangeNumberMask = createMask('0[00000000000] - 0[0000000000]', 'от - до');
export const RangeDateMask   = createMask('00.00.0000 - 00.00.0000',        'дд.мм.гггг - дд.мм.гггг');
export const DateMask        = createMask('00.00.0000',           'дд.мм.гггг');

export const getMaskComponent = (specialFormat, fieldType) => {
    if (fieldType === 'date') return DateMask;
    switch (specialFormat) {
        case 'passport':      return PassportMask;
        case 'phone':         return PhoneMask;
        case 'snils':         return SnilsMask;
        case 'range_numbers': return RangeNumberMask;
        case 'range_date':    return RangeDateMask;
        default:              return undefined;
    }
};
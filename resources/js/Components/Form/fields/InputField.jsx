import React from 'react';
import { TextField } from '@mui/material';
import { getMaskComponent } from '../masks';

export default function InputField({ block, fieldKey, value, onChange, error }) {
    const { label, is_required, special_format, type, is_readonly, placeholder, helper_text } = block.data;
    const maskComponent = getMaskComponent(special_format, type);

    return (
        <TextField
            fullWidth
            label={<>{label}{is_required && <span style={{ color: '#EF4444' }}> *</span>}</>}
            value={value || ''}
            onChange={e => onChange(fieldKey, e.target.value)}
            type={type === 'number' ? 'number' : 'text'}
            InputProps={maskComponent ? { inputComponent: maskComponent } : {}}
            placeholder={type === 'date' ? 'дд.мм.гггг' : (placeholder || '')}
            disabled={is_readonly}
            helperText={helper_text}
            error={!!error}
        />
    );
}
import React from 'react';
import { Grid, Box, Typography, TextField } from '@mui/material';
import { PhoneMask } from '../masks';

export default function DynamicInputField({ block, fieldKey, value, onChange }) {
    const { label, is_required, options } = block.data;
    const fieldValue     = value || { selected: '', inputValue: '' };
    const selectedOption = (options || []).find(o => o.value === fieldValue.selected);
    const showInput      = selectedOption?.input_type && selectedOption.input_type !== 'none';

    return (
        <Grid item xs={12}>
            <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                {label}
                {is_required && <span style={{ color: '#EF4444', marginLeft: '4px' }}>*</span>}
            </Typography>

            <TextField
                select
                fullWidth
                value={fieldValue.selected || ''}
                onChange={e => onChange(fieldKey, { ...fieldValue, selected: e.target.value, inputValue: '' })}
                SelectProps={{ native: true }}
                sx={{ mb: showInput ? 1 : 0 }}
            >
                <option value="">Выберите вариант...</option>
                {(options || []).map((opt, i) => (
                    <option key={i} value={opt.value}>{opt.value}</option>
                ))}
            </TextField>

            {showInput && (
                <TextField
                    fullWidth
                    placeholder={selectedOption.input_label || 'Введите значение...'}
                    value={fieldValue.inputValue || ''}
                    onChange={e => onChange(fieldKey, { ...fieldValue, inputValue: e.target.value })}
                    type={selectedOption.input_type === 'email' ? 'email' : 'text'}
                    InputProps={selectedOption.input_type === 'phone' ? { inputComponent: PhoneMask } : {}}
                    sx={{ mt: 1 }}
                />
            )}
        </Grid>
    );
}
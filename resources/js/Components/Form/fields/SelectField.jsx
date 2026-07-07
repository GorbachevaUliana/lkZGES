import React from 'react';
import { Grid, TextField } from '@mui/material';

export default function SelectField({ block, fieldKey, value, onChange }) {
    const { label, is_required, options, allow_custom } = block.data;

    return (
        <Grid item xs={12}>
            <TextField
                select
                fullWidth
                label={
                    <>
                        {label}
                        {is_required && <span style={{ color: '#EF4444' }}> *</span>}
                    </>
                }
                value={value?.value || ''}
                onChange={e => onChange(fieldKey, { ...value, value: e.target.value })}
                SelectProps={{ native: true }}
            >
                <option value=""></option>
                {options?.map((opt, i) => (
                    <option key={i} value={opt.value}>{opt.value}</option>
                ))}
                {allow_custom && <option value="other">Другое (ввести свой вариант)</option>}
            </TextField>

            {allow_custom && value?.value === 'other' && (
                <TextField
                    fullWidth
                    sx={{ mt: 2 }}
                    placeholder="Введите свой вариант..."
                    value={value?.customValue || ''}
                    onChange={e => onChange(fieldKey, { ...value, customValue: e.target.value })}
                />
            )}
        </Grid>
    );
}
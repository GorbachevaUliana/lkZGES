import React from 'react';
import { Grid, Box, Typography, Checkbox, TextField, Button, IconButton } from '@mui/material';
import { Add as AddIcon, Delete as DeleteIcon } from '@mui/icons-material';

export default function CheckboxGroupField({ block, fieldKey, value, onChange }) {
    const { label, is_required, options, allow_multiple_custom } = block.data;
    const fieldValue = value || { preset: [], custom: [] };

    const togglePreset = (optValue, checked) => {
        const next = checked
            ? [...(fieldValue.preset || []), optValue]
            : fieldValue.preset.filter(v => v !== optValue);
        onChange(fieldKey, { ...fieldValue, preset: next });
    };

    const updateCustom = (i, text) => {
        const next = [...fieldValue.custom];
        next[i].value = text;
        onChange(fieldKey, { ...fieldValue, custom: next });
    };

    const removeCustom = (i) => {
        const next = fieldValue.custom.filter((_, idx) => idx !== i);
        onChange(fieldKey, { ...fieldValue, custom: next });
    };

    const addCustom = () => {
        onChange(fieldKey, {
            ...fieldValue,
            custom: [...(fieldValue.custom || []), { id: Date.now(), value: '' }],
        });
    };

    return (
        <Grid item xs={12}>
            <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                {label}
                {is_required && <span style={{ color: '#EF4444', marginLeft: '4px' }}>*</span>}
            </Typography>
            <Box sx={{ pl: 1 }}>
                {options?.map((opt, i) => (
                    <Box key={i} sx={{ display: 'flex', alignItems: 'center' }}>
                        <Checkbox
                            checked={fieldValue.preset?.includes(opt.value) || false}
                            onChange={e => togglePreset(opt.value, e.target.checked)}
                        />
                        <Typography>{opt.value}</Typography>
                    </Box>
                ))}

                {fieldValue.custom?.map((item, i) => (
                    <Box key={item.id} sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1 }}>
                        <Checkbox checked disabled />
                        <TextField
                            size="small"
                            fullWidth
                            value={item.value}
                            onChange={e => updateCustom(i, e.target.value)}
                        />
                        <IconButton size="small" onClick={() => removeCustom(i)} sx={{ color: '#FF4D4D' }}>
                            <DeleteIcon fontSize="small" />
                        </IconButton>
                    </Box>
                ))}

                {allow_multiple_custom && (
                    <Button size="small" startIcon={<AddIcon />} onClick={addCustom} sx={{ color: '#4318FF' }}>
                        Добавить свой вариант
                    </Button>
                )}
            </Box>
        </Grid>
    );
}
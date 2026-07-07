import React from 'react';
import { Box, Typography, Card, CardContent, Divider } from '@mui/material';

const renderValue = (block, value) => {
    if (!value) return '—';

    if (block.type === 'checkbox_group') {
        const selected = [
            ...(value.preset || []),
            ...(value.custom?.map(c => c.value).filter(Boolean) || []),
        ];
        return selected.length > 0 ? selected.join(', ') : '—';
    }

    if (block.type === 'select_field') {
        if (value.value === 'other') return value.customValue || '—';
        return value.value || '—';
    }

    if (block.type === 'file_upload') {
        return Array.isArray(value) && value.length > 0
            ? value.map(f => f.name).join(', ')
            : '—';
    }

    if (block.type === 'dynamic_input') {
        if (!value.selected) return '—';
        const selectedOption = block.data.options?.find(o => o.value === value.selected);
        if (selectedOption?.input_type && selectedOption.input_type !== 'none' && value.inputValue) {
            return `${value.selected}: ${value.inputValue}`;
        }
        return value.selected || '—';
    }

    return typeof value === 'string' ? value : JSON.stringify(value);
};

export default function FormReview({ visibleFields, data, clientTypeLabel }) {
    return (
        <Box>
            <Typography variant="h6" gutterBottom>
                Проверьте введённые данные
            </Typography>
            <Card variant="outlined" sx={{ borderRadius: '12px' }}>
                <CardContent>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        Тип клиента: <strong>{clientTypeLabel}</strong>
                    </Typography>
                    <Divider sx={{ my: 2 }} />
                    {visibleFields.map((block, index) => {
                        if (block.type === 'section_header') {
                            return (
                                <Typography key={index} variant="subtitle1" fontWeight="bold" sx={{ mt: 2, mb: 1 }}>
                                    {block.data.title}
                                </Typography>
                            );
                        }
                        if (block.type === 'text_block') return null;

                        const fieldKey = block.data.key || block.data.label;
                        return (
                            <Box key={index} sx={{ mb: 2 }}>
                                <Typography variant="body2" color="text.secondary">
                                    {block.data.label}
                                </Typography>
                                <Typography variant="body1">
                                    {renderValue(block, data[fieldKey])}
                                </Typography>
                            </Box>
                        );
                    })}
                </CardContent>
            </Card>
        </Box>
    );
}
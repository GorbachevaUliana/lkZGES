import React from 'react';
import { Box, Typography, Stack } from '@mui/material';
import FormField from '../FormField';

export default function FormStep({ visibleFields, data, onChange, errors, clientTypeLabel, hasExistingClient }) {
    return (
        <Box>
            <Typography variant="h6" sx={{ mb: 3 }}>
                Данные заявителя
                {hasExistingClient && (
                    <Typography component="span" color="primary" sx={{ ml: 2, fontWeight: 'normal' }}>
                        ({clientTypeLabel})
                    </Typography>
                )}
            </Typography>

            <Stack spacing={3}>
                {visibleFields.map((block, index) => (
                    <Box
                        key={index}
                        sx={{ mt: block.type === 'section_header' ? 2 : 0 }}
                    >
                        <FormField
                            block={block}
                            index={index}
                            data={data}
                            onChange={onChange}
                            errors={errors}
                        />
                    </Box>
                ))}
            </Stack>
        </Box>
    );
} 
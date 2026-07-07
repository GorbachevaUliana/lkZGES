import React from 'react';
import { Box, Typography, Grid, Card, CardContent } from '@mui/material';
import { Person as PersonIcon, Business as BusinessIcon } from '@mui/icons-material';

export default function ClientTypeStep({ clientType, onChange }) {
    const options = [
        { value: 'individual', label: 'Физическое лицо',  desc: 'Для граждан РФ',   Icon: PersonIcon },
        { value: 'legal',      label: 'Юридическое лицо', desc: 'Для организаций',  Icon: BusinessIcon },
    ];

    return (
        <Box>
            <Typography variant="h6" gutterBottom>
                Выберите тип клиента
            </Typography>
            <Grid container spacing={3}>
                {options.map(({ value, label, desc, Icon }) => {
                    const isActive = clientType === value;
                    return (
                        <Grid item xs={12} sm={6} key={value}>
                            <Card
                                onClick={() => onChange(value)}
                                sx={{
                                    cursor: 'pointer',
                                    border: isActive ? '2px solid #4318FF' : '1px solid #E0E5F2',
                                    borderRadius: '16px',
                                    transition: 'all 0.2s',
                                    '&:hover': { borderColor: '#4318FF' },
                                }}
                            >
                                <CardContent sx={{ textAlign: 'center', py: 4 }}>
                                    <Icon sx={{ fontSize: 48, color: isActive ? '#4318FF' : '#A3AED0', mb: 2 }} />
                                    <Typography variant="h6" fontWeight="bold">{label}</Typography>
                                    <Typography variant="body2" color="text.secondary">{desc}</Typography>
                                </CardContent>
                            </Card>
                        </Grid>
                    );
                })}
            </Grid>
        </Box>
    );
}
import React from 'react';
import ClientLayout from '@/Layouts/ClientLayout';
import { Grid, Paper, Typography, Box } from '@mui/material';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';

export default function Dashboard({ auth, client }) {
    return (
        <ClientLayout user={auth.user} title="Панель управления">
            <Grid container spacing={3}>
                <Grid item xs={12} md={6}>
                    <Paper sx={{ p: 3, borderRadius: '20px', display: 'flex', alignItems: 'center', gap: 3 }}>
                        <AccountCircleIcon sx={{ fontSize: 60, color: '#4318FF' }} />
                        <Box>
                            <Typography variant="h6" fontWeight="bold">Добро пожаловать!</Typography>
                            <Typography color="text.secondary">
                                {client ? `${client.first_name} ${client.middle_name || ''}` : auth.user.name}
                            </Typography>
                        </Box>
                    </Paper>
                </Grid>
                
                {/* Здесь в будущем можно вывести последние обращения или статус договора */}
                <Grid item xs={12} md={6}>
                    <Paper sx={{ p: 3, borderRadius: '20px', bgcolor: '#4318FF', color: '#fff' }}>
                        <Typography variant="h6" fontWeight="bold">Статус обслуживания</Typography>
                        <Typography sx={{ opacity: 0.8 }}>
                            {client ? 'Договор активен' : 'Ожидается привязка договора'}
                        </Typography>
                    </Paper>
                </Grid>
            </Grid>
        </ClientLayout>
    );
}
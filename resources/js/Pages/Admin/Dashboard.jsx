import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Grid, Paper, Typography, Box } from '@mui/material';

export default function Dashboard({ auth, stats }) {
    return (
        <AdminLayout user={auth.user}>
            <Typography variant="h4" sx={{ mb: 4, fontWeight: 'bold' }}>Панель управления</Typography>
            <Grid container spacing={3}>
                <Grid item xs={12} md={4}>
                    <Paper sx={{ p: 3, borderRadius: '15px' }}>
                        <Typography color="text.secondary">Всего потребителей</Typography>
                        <Typography variant="h4" fontWeight="bold">{stats.clients_count}</Typography>
                    </Paper>
                </Grid>
                <Grid item xs={12} md={4}>
                    <Paper sx={{ p: 3, borderRadius: '15px' }}>
                        <Typography color="text.secondary">Новых обращений</Typography>
                        <Typography variant="h4" fontWeight="bold" color="primary">{stats.tickets_count}</Typography>
                    </Paper>
                </Grid>
            </Grid>
        </AdminLayout>
    );
}
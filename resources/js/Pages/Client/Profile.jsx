import React from 'react';
import ClientLayout from '@/Layouts/ClientLayout';
import { Paper, Grid, Typography, Box, Button, TextField, Alert } from '@mui/material';
import AssignmentIcon from '@mui/icons-material/Assignment';

export default function Profile({ auth, client, application }) {
    return (
        <ClientLayout user={auth.user}
        title="Личная информация"
        application={application}>
            {!client ? (
                <Paper sx={{ p: 4, borderRadius: '20px', textAlign: 'center', boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' }}>
                    <AssignmentIcon sx={{ fontSize: 60, color: '#4318FF', mb: 2 }} />
                    <Typography variant="h5" fontWeight="bold" gutterBottom>У вас еще не заполнен профиль</Typography>
                    <Typography color="text.secondary" sx={{ mb: 3 }}>
                        Чтобы пользоваться всеми функциями ЛК, необходимо подать заявку на заключение договора.
                    </Typography>
                    <Button variant="contained" size="large" sx={{ bgcolor: '#4318FF', borderRadius: '12px', px: 4 }}>
                        Заполнить заявку
                    </Button>
                </Paper>
            ) : (
                <Grid container spacing={3}>
                    <Grid item xs={12} md={8}>
                        <Paper sx={{ p: 3, borderRadius: '20px', boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                            <Typography variant="h6" fontWeight="bold" mb={3}>Данные договора</Typography>
                            <Grid container spacing={2}>
                                <Grid item xs={12}><TextField fullWidth label="Адрес" variant="filled" value={client.address} inputProps={{ readOnly: true }} /></Grid>
                                <Grid item xs={6}><TextField fullWidth label="Лицевой счет" variant="filled" value={client.account_number} inputProps={{ readOnly: true }} /></Grid>
                                <Grid item xs={6}><TextField fullWidth label="Телефон" variant="filled" value={client.phone} inputProps={{ readOnly: true }} /></Grid>
                                <Grid item xs={12}><TextField fullWidth label="Email" variant="filled" value={client.email} inputProps={{ readOnly: true }} /></Grid>
                            </Grid>
                            <Alert severity="info" sx={{ mt: 3, borderRadius: '12px' }}>
                                Для изменения данных, пожалуйста, создайте обращение в <a href={ route('client.tickets.index')} style={{textDecoration:'underline', fontWeight:'bold'}}>службу поддержки</a>.
                            </Alert>
                        </Paper>
                    </Grid>
                </Grid>
            )}
        </ClientLayout>
    );
}
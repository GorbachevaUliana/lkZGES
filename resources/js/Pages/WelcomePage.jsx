import React from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Container, Box, Typography, Card, CardContent, Button, TextField, Grid, Divider } from '@mui/material';
import AssignmentIcon from '@mui/icons-material/Assignment';
import PersonAddIcon from '@mui/icons-material/PersonAdd';

export default function WelcomePage() {
    const { data, setData, post, processing, errors } = useForm({
        account_number: '',
        last_name: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('account.link'));
    };

    return (
        <Container maxWidth="lg">
            <Head title="Добро пожаловать" />
            
            <Box sx={{ mt: 8, textAlign: 'center' }}>
                <Typography variant="h4" component="h1" gutterBottom fontWeight="bold">
                    Почти готово!
                </Typography>
                <Typography variant="body1" color="text.secondary" sx={{ mb: 4 }}>
                    Чтобы начать пользоваться личным кабинетом, нам нужно идентифицировать вас.
                </Typography>

                <Grid container spacing={4} justifyContent="center">
                    {/* Сценарий А: Есть договор */}
                    <Grid item xs={12} md={5}>
                        <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column', boxShadow: 3 }}>
                            <CardContent sx={{ flexGrow: 1, p: 4 }}>
                                <AssignmentIcon sx={{ fontSize: 40, color: 'primary.main', mb: 2 }} />
                                <Typography variant="h5" gutterBottom>Я уже клиент</Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                                    У меня есть действующий договор и номер лицевого счета.
                                </Typography>
                                
                                <form onSubmit={submit}>
                                    <TextField
                                        fullWidth
                                        label="Лицевой счет"
                                        margin="normal"
                                        value={data.account_number}
                                        onChange={e => setData('account_number', e.target.value)}
                                        error={!!errors.account_number}
                                        helperText={errors.account_number}
                                    />
                                    <TextField
                                        fullWidth
                                        label="Фамилия"
                                        margin="normal"
                                        value={data.last_name}
                                        onChange={e => setData('last_name', e.target.value)}
                                        error={!!errors.last_name}
                                        helperText={errors.last_name}
                                    />
                                    <Button 
                                        fullWidth 
                                        variant="contained" 
                                        type="submit" 
                                        disabled={processing}
                                        sx={{ mt: 2 }}
                                    >
                                        Найти и привязать
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </Grid>

                    {/* Сценарий Б: Нет договора */}
                    <Grid item xs={12} md={5}>
                        <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column', boxShadow: 3 }}>
                            <CardContent sx={{ flexGrow: 1, p: 4 }}>
                                <PersonAddIcon sx={{ fontSize: 40, color: 'secondary.main', mb: 2 }} />
                                <Typography variant="h5" gutterBottom>Новый пользователь</Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                                    Я хочу заключить договор на электроснабжение впервые.
                                </Typography>
                                <Divider sx={{ my: 4 }} />
                                <Button 
                                    fullWidth 
                                    variant="outlined" 
                                    color="secondary"
                                    onClick={() => router.get('/new-application')}
                                    sx={{ mt: 'auto' }}
                                >
                                    Заполнить заявку
                                </Button>
                            </CardContent>
                        </Card>
                    </Grid>
                </Grid>
            </Box>
        </Container>
    );
}
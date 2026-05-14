import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import { 
    Container, 
    Box, 
    Typography, 
    Paper, 
    Button, 
    TextField, 
    Grid, 
    Avatar,
    InputAdornment
} from '@mui/material';
import AssignmentIcon from '@mui/icons-material/Assignment';
import PersonAddIcon from '@mui/icons-material/PersonAdd';
import ElectricBoltIcon from '@mui/icons-material/ElectricBolt';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';

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
        <Box sx={{ 
            minHeight: '100vh', 
            bgcolor: '#F4F7FE',
            display: 'flex',
            flexDirection: 'column'
        }}>
            <Head title="Добро пожаловать"/>
            
            {/* Шапка с логотипом */}
            <Box sx={{ 
                py: 3, 
                px: 4, 
                display: 'flex', 
                alignItems: 'center', 
                gap: 2,
                bgcolor: '#fff',
                boxShadow: '0px 4px 20px rgba(0, 0, 0, 0.05)'
            }}>
                <Box sx={{ 
                    width: 50, 
                    height: 50, 
                    borderRadius: '12px',
                    bgcolor: '#4318FF',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                }}>
                    <ElectricBoltIcon sx={{ color: '#fff', fontSize: 28 }} />
                </Box>
                <Box>
                    <Typography variant="h6" fontWeight="bold" sx={{ color: '#1B2559' }}>
                        Личный кабинет
                    </Typography>
                    <Typography variant="caption" sx={{ color: '#A3AED0' }}>
                        ООО "Заринская горэлектросеть"
                    </Typography>
                </Box>
            </Box>

            {/* Основной контент */}
            <Container maxWidth="lg" sx={{ py: 6, flex: 1 }}>
                {/* Приветствие */}
                <Box sx={{ textAlign: 'center', mb: 5 }}>
                    <Typography 
                        variant="h3" 
                        component="h1" 
                        fontWeight="bold" 
                        sx={{ color: '#1B2559', mb: 2 }}
                    >
                        Добро пожаловать!
                    </Typography>
                    <Typography 
                        variant="body1" 
                        sx={{ color: '#A3AED0', maxWidth: 600, mx: 'auto', fontSize: '1.1rem' }}
                    >
                        Чтобы начать пользоваться личным кабинетом, нам нужно идентифицировать вас. 
                        Выберите один из вариантов ниже.
                    </Typography>
                </Box>

                {/* Две карточки */}
                <Grid container spacing={4} justifyContent="center">
                    {/* Карточка: Я уже клиент */}
                    <Grid item xs={12} md={5}>
                        <Paper 
                            sx={{ 
                                height: '100%', 
                                borderRadius: '20px',
                                border: '1px solid #E0E5F2',
                                boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.08)',
                                overflow: 'hidden',
                                transition: 'all 0.3s ease',
                                '&:hover': {
                                    boxShadow: '0px 20px 50px rgba(112, 144, 176, 0.12)',
                                    transform: 'translateY(-4px)'
                                }
                            }}
                        >
                            <Box sx={{ p: 4 }}>
                                {/* Иконка и заголовок */}
                                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 3 }}>
                                    <Avatar sx={{ 
                                        bgcolor: '#4318FF', 
                                        width: 56, 
                                        height: 56,
                                        borderRadius: '16px'
                                    }}>
                                        <AssignmentIcon sx={{ fontSize: 28 }} />
                                    </Avatar>
                                    <Box>
                                        <Typography variant="h5" fontWeight="bold" sx={{ color: '#1B2559' }}>
                                            Я уже клиент
                                        </Typography>
                                        <Typography variant="body2" sx={{ color: '#A3AED0' }}>
                                            Есть действующий договор
                                        </Typography>
                                    </Box>
                                </Box>

                                <Typography variant="body2" sx={{ color: '#485585', mb: 3, lineHeight: 1.6 }}>
                                    Если у вас уже есть действующий договор и номер лицевого счета, 
                                    введите данные для привязки к личному кабинету.
                                </Typography>

                                <form onSubmit={submit}>
                                    <TextField
                                        fullWidth
                                        label="Лицевой счет"
                                        placeholder="Введите номер лицевого счета"
                                        margin="normal"
                                        value={data.account_number}
                                        onChange={e => setData('account_number', e.target.value)}
                                        error={!!errors.account_number}
                                        helperText={errors.account_number}
                                        InputProps={{
                                            startAdornment: (
                                                <InputAdornment position="start">
                                                    <AssignmentIcon sx={{ color: '#A3AED0' }} />
                                                </InputAdornment>
                                            ),
                                        }}
                                        sx={{
                                            '& .MuiOutlinedInput-root': {
                                                borderRadius: '12px',
                                                bgcolor: '#F4F7FE'
                                            }
                                        }}
                                    />
                                    <TextField
                                        fullWidth
                                        label="Фамилия"
                                        placeholder="Введите вашу фамилию"
                                        margin="normal"
                                        value={data.last_name}
                                        onChange={e => setData('last_name', e.target.value)}
                                        error={!!errors.last_name}
                                        helperText={errors.last_name}
                                        InputProps={{
                                            startAdornment: (
                                                <InputAdornment position="start">
                                                    <AccountCircleIcon sx={{ color: '#A3AED0' }} />
                                                </InputAdornment>
                                            ),
                                        }}
                                        sx={{
                                            '& .MuiOutlinedInput-root': {
                                                borderRadius: '12px',
                                                bgcolor: '#F4F7FE'
                                            }
                                        }}
                                    />
                                    <Button 
                                        fullWidth 
                                        variant="contained" 
                                        type="submit" 
                                        disabled={processing}
                                        sx={{ 
                                            mt: 3,
                                            py: 1.5,
                                            borderRadius: '12px',
                                            bgcolor: '#4318FF',
                                            textTransform: 'none',
                                            fontSize: '1rem',
                                            fontWeight: 600,
                                            boxShadow: '0px 10px 20px rgba(67, 24, 255, 0.15)',
                                            '&:hover': {
                                                bgcolor: '#3613CC',
                                                boxShadow: '0px 14px 28px rgba(67, 24, 255, 0.2)'
                                            },
                                            '&:disabled': {
                                                bgcolor: '#A3AED0'
                                            }
                                        }}
                                    >
                                        Найти и привязать
                                    </Button>
                                </form>
                            </Box>
                        </Paper>
                    </Grid>

                    {/* Карточка: Новый пользователь */}
                    <Grid item xs={12} md={5}>
                        <Paper 
                            sx={{ 
                                height: '100%', 
                                borderRadius: '20px',
                                border: '1px solid #E0E5F2',
                                boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.08)',
                                overflow: 'hidden',
                                transition: 'all 0.3s ease',
                                display: 'flex',
                                flexDirection: 'column',
                                '&:hover': {
                                    boxShadow: '0px 20px 50px rgba(112, 144, 176, 0.12)',
                                    transform: 'translateY(-4px)'
                                }
                            }}
                        >
                            <Box sx={{ p: 4, flex: 1, display: 'flex', flexDirection: 'column' }}>
                                {/* Иконка и заголовок */}
                                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 3 }}>
                                    <Avatar sx={{ 
                                        bgcolor: '#05CD99', 
                                        width: 56, 
                                        height: 56,
                                        borderRadius: '16px'
                                    }}>
                                        <PersonAddIcon sx={{ fontSize: 28, color: '#fff' }} />
                                    </Avatar>
                                    <Box>
                                        <Typography variant="h5" fontWeight="bold" sx={{ color: '#1B2559' }}>
                                            Новый пользователь
                                        </Typography>
                                        <Typography variant="body2" sx={{ color: '#A3AED0' }}>
                                            Нет действующего договора
                                        </Typography>
                                    </Box>
                                </Box>

                                <Typography variant="body2" sx={{ color: '#485585', mb: 3, lineHeight: 1.6, flex: 1 }}>
                                    Если вы хотите заключить договор на электроснабжение впервые, 
                                    заполните заявку и наши специалисты свяжутся с вами.
                                </Typography>

                                {/* Преимущества */}
                                <Box sx={{ mb: 3 }}>
                                    {[
                                        'Подача заявки онлайн',
                                        'Рассмотрение в течение 3 дней',
                                        'Уведомления о статусе'
                                    ].map((item, index) => (
                                        <Box 
                                            key={index}
                                            sx={{ 
                                                display: 'flex', 
                                                alignItems: 'center', 
                                                gap: 1.5,
                                                mb: 1
                                            }}
                                        >
                                            <Box sx={{ 
                                                width: 6, 
                                                height: 6, 
                                                borderRadius: '50%', 
                                                bgcolor: '#05CD99' 
                                            }} />
                                            <Typography variant="body2" sx={{ color: '#485585' }}>
                                                {item}
                                            </Typography>
                                        </Box>
                                    ))}
                                </Box>

                                <Button 
                                    fullWidth 
                                    variant="contained"
                                    component={Link}
                                    href={route('application.show', { slug: 'konstruktor' })}
                                    sx={{ 
                                        py: 1.5,
                                        borderRadius: '12px',
                                        bgcolor: '#05CD99',
                                        textTransform: 'none',
                                        fontSize: '1rem',
                                        fontWeight: 600,
                                        boxShadow: '0px 10px 20px rgba(5, 205, 153, 0.15)',
                                        '&:hover': {
                                            bgcolor: '#04B386',
                                            boxShadow: '0px 14px 28px rgba(5, 205, 153, 0.2)'
                                        }
                                    }}
                                >
                                    Заполнить заявку
                                </Button>
                            </Box>
                        </Paper>
                    </Grid>
                </Grid>

                {/* Дополнительная информация */}
                <Box sx={{ textAlign: 'center', mt: 5 }}>
                    <Typography variant="body2" sx={{ color: '#A3AED0' }}>
                        Есть вопросы? Свяжитесь с нами по телефону{' '}
                        <Typography component="span" fontWeight="bold" sx={{ color: '#4318FF' }}>
                            +7 (38595)-55-0-36
                        </Typography>
                    </Typography>
                </Box>
            </Container>

            {/* Футер */}
            <Box sx={{ 
                py: 3, 
                px: 4, 
                bgcolor: '#fff', 
                borderTop: '1px solid #E0E5F2',
                textAlign: 'center'
            }}>
            </Box>
        </Box>
    );
}

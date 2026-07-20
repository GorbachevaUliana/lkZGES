import React from 'react';
import { Head, useForm, Link, router, usePage } from '@inertiajs/react';
import {
    Container, Box, Typography, Paper, Button,
    TextField, Grid, Avatar, InputAdornment, Alert
} from '@mui/material';
import AssignmentIcon from '@mui/icons-material/Assignment';
import PersonAddIcon  from '@mui/icons-material/PersonAdd';
import ElectricBoltIcon from '@mui/icons-material/ElectricBolt';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import LockIcon from '@mui/icons-material/Lock';
import EmailIcon from '@mui/icons-material/Email';

const inputSx = {
    '& .MuiOutlinedInput-root': { borderRadius: '12px', bgcolor: '#F4F7FE' },
};

// ─── Шаг 1: форма поиска по ЛС + ФИО ────────────────────────────────────────
function StepLink() {
    const { data, setData, post, processing, errors } = useForm({
        account_number: '',
        last_name:      '',
        first_name:     '',
        middle_name:    '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('account.link'));
    };

    return (
        <form onSubmit={submit}>
            {[
                { field: 'account_number', label: 'Лицевой счёт',   placeholder: 'Введите номер лицевого счёта', icon: <AssignmentIcon sx={{ color: '#A3AED0' }} /> },
                { field: 'last_name',      label: 'Фамилия',         placeholder: 'Иванов',  icon: <AccountCircleIcon sx={{ color: '#A3AED0' }} /> },
                { field: 'first_name',     label: 'Имя',             placeholder: 'Иван',    icon: <AccountCircleIcon sx={{ color: '#A3AED0' }} /> },
                { field: 'middle_name',    label: 'Отчество',         placeholder: 'Иванович',icon: <AccountCircleIcon sx={{ color: '#A3AED0' }} /> },
            ].map(({ field, label, placeholder, icon }) => (
                <TextField
                    key={field}
                    fullWidth
                    label={label}
                    placeholder={placeholder}
                    margin="normal"
                    value={data[field]}
                    onChange={e => setData(field, e.target.value)}
                    error={!!errors[field]}
                    helperText={errors[field]}
                    InputProps={{ startAdornment: <InputAdornment position="start">{icon}</InputAdornment> }}
                    sx={inputSx}
                />
            ))}
            <Button
                fullWidth variant="contained" type="submit"
                disabled={processing}
                sx={{
                    mt: 3, py: 1.5, borderRadius: '12px',
                    bgcolor: '#4318FF', textTransform: 'none',
                    fontSize: '1rem', fontWeight: 600,
                    boxShadow: '0px 10px 20px rgba(67,24,255,0.15)',
                    '&:hover': { bgcolor: '#3613CC' },
                    '&:disabled': { bgcolor: '#A3AED0' },
                }}
            >
                Получить код подтверждения
            </Button>
        </form>
    );
}

// ─── Шаг 2: ввод кода из письма ──────────────────────────────────────────────
function StepVerify({ maskedEmail }) {
    const { errors: pageErrors } = usePage().props;
    const { data, setData, post, processing } = useForm({ code: '' });
    const errors = pageErrors || {};

    const submit = (e) => {
        e.preventDefault();
        post(route('account.verify'));
    };

    return (
        <Box>
            <Alert
                severity="info"
                icon={<EmailIcon />}
                sx={{ borderRadius: '12px', mb: 3 }}
            >
                {maskedEmail
                    ? <>Код отправлен на <strong>{maskedEmail}</strong>. Проверьте почту.</>
                    : 'Если данные верны, вы получите письмо с кодом на email, указанный в вашем договоре.'}
            </Alert>

            <form onSubmit={submit}>
                <TextField
                    fullWidth
                    label="Код из письма"
                    placeholder="123456"
                    margin="normal"
                    value={data.code}
                    onChange={e => setData('code', e.target.value.replace(/\D/g, '').slice(0, 6))}
                    error={!!errors.code}
                    helperText={errors.code}
                    inputProps={{ inputMode: 'numeric', maxLength: 6 }}
                    InputProps={{
                        startAdornment: (
                            <InputAdornment position="start">
                                <LockIcon sx={{ color: '#A3AED0' }} />
                            </InputAdornment>
                        ),
                    }}
                    sx={inputSx}
                />
                <Button
                    fullWidth variant="contained" type="submit"
                    disabled={processing || data.code.length !== 6}
                    sx={{
                        mt: 3, py: 1.5, borderRadius: '12px',
                        bgcolor: '#4318FF', textTransform: 'none',
                        fontSize: '1rem', fontWeight: 600,
                        boxShadow: '0px 10px 20px rgba(67,24,255,0.15)',
                        '&:hover': { bgcolor: '#3613CC' },
                        '&:disabled': { bgcolor: '#A3AED0' },
                    }}
                >
                    Подтвердить и войти
                </Button>
            </form>

            <Typography
                variant="body2"
                sx={{ color: '#A3AED0', mt: 2, textAlign: 'center', cursor: 'pointer',
                      '&:hover': { color: '#4318FF' } }}
                onClick={() => window.location.reload()}
            >
                Ввести данные заново
            </Typography>
        </Box>
    );
}

// ─── Основной компонент ───────────────────────────────────────────────────────
export default function WelcomePage({ step = 'link', maskedEmail }) {
    const isVerifyStep = step === 'verify';

    return (
        <Box sx={{ minHeight: '100vh', bgcolor: '#F4F7FE', display: 'flex', flexDirection: 'column' }}>
            <Head title="Добро пожаловать" />

            {/* Шапка */}
            <Box sx={{ py: 3, px: 4, display: 'flex', alignItems: 'center', gap: 2,
                        bgcolor: '#fff', boxShadow: '0px 4px 20px rgba(0,0,0,0.05)' }}>
                <Box sx={{ width: 50, height: 50, borderRadius: '12px', bgcolor: '#4318FF',
                            display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <ElectricBoltIcon sx={{ color: '#fff', fontSize: 28 }} />
                </Box>
                <Box>
                    <Typography variant="h6" fontWeight="bold" sx={{ color: '#1B2559' }}>
                        Личный кабинет
                    </Typography>
                    <Typography variant="caption" sx={{ color: '#A3AED0' }}>
                        ООО «Заринская горэлектросеть»
                    </Typography>
                </Box>
            </Box>

            {/* Контент */}
            <Container maxWidth="lg" sx={{ py: 6, flex: 1 }}>
                <Box sx={{ textAlign: 'center', mb: 5 }}>
                    <Typography variant="h3" component="h1" fontWeight="bold"
                                sx={{ color: '#1B2559', mb: 2 }}>
                        Добро пожаловать!
                    </Typography>
                    <Typography variant="body1"
                                sx={{ color: '#A3AED0', maxWidth: 600, mx: 'auto', fontSize: '1.1rem' }}>
                        Чтобы начать пользоваться личным кабинетом, нам нужно идентифицировать вас.
                        Выберите один из вариантов ниже.
                    </Typography>
                </Box>

                <Grid container spacing={4} justifyContent="center">
                    {/* Карточка: Я уже клиент */}
                    <Grid item xs={12} md={5}>
                        <Paper sx={{
                            height: '100%', borderRadius: '20px',
                            border: '1px solid #E0E5F2',
                            boxShadow: '0px 18px 40px rgba(112,144,176,0.08)',
                            overflow: 'hidden', transition: 'all 0.3s ease',
                            '&:hover': { boxShadow: '0px 20px 50px rgba(112,144,176,0.12)',
                                         transform: 'translateY(-4px)' },
                        }}>
                            <Box sx={{ p: 4 }}>
                                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 3 }}>
                                    <Avatar sx={{ bgcolor: '#4318FF', width: 56, height: 56, borderRadius: '16px' }}>
                                        <AssignmentIcon sx={{ fontSize: 28 }} />
                                    </Avatar>
                                    <Box>
                                        <Typography variant="h5" fontWeight="bold" sx={{ color: '#1B2559' }}>
                                            {isVerifyStep ? 'Подтверждение' : 'Я уже клиент'}
                                        </Typography>
                                        <Typography variant="body2" sx={{ color: '#A3AED0' }}>
                                            {isVerifyStep ? 'Введите код из письма' : 'Есть действующий договор'}
                                        </Typography>
                                    </Box>
                                </Box>

                                {!isVerifyStep && (
                                    <Typography variant="body2" sx={{ color: '#485585', mb: 3, lineHeight: 1.6 }}>
                                        Если у вас уже есть действующий договор и номер лицевого счёта,
                                        введите данные для привязки к личному кабинету.
                                    </Typography>
                                )}

                                {isVerifyStep
                                    ? <StepVerify maskedEmail={maskedEmail} />
                                    : <StepLink />}
                            </Box>
                        </Paper>
                    </Grid>

                    {/* Карточка: Новый пользователь — скрываем на шаге 2 */}
                    {!isVerifyStep && (
                        <Grid item xs={12} md={5}>
                            <Paper sx={{
                                height: '100%', borderRadius: '20px',
                                border: '1px solid #E0E5F2',
                                boxShadow: '0px 18px 40px rgba(112,144,176,0.08)',
                                overflow: 'hidden', transition: 'all 0.3s ease',
                                display: 'flex', flexDirection: 'column',
                                '&:hover': { boxShadow: '0px 20px 50px rgba(112,144,176,0.12)',
                                             transform: 'translateY(-4px)' },
                            }}>
                                <Box sx={{ p: 4, flex: 1, display: 'flex', flexDirection: 'column' }}>
                                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 3 }}>
                                        <Avatar sx={{ bgcolor: '#05CD99', width: 56, height: 56, borderRadius: '16px' }}>
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

                                    <Typography variant="body2"
                                                sx={{ color: '#485585', mb: 3, lineHeight: 1.6, flex: 1 }}>
                                        Если вы хотите заключить договор на электроснабжение впервые,
                                        заполните заявку и наши специалисты свяжутся с вами.
                                    </Typography>

                                    <Box sx={{ mb: 3 }}>
                                        {['Подача заявки онлайн', 'Рассмотрение в течение 3 дней',
                                          'Уведомления о статусе'].map((item, i) => (
                                            <Box key={i} sx={{ display: 'flex', alignItems: 'center',
                                                               gap: 1.5, mb: 1 }}>
                                                <Box sx={{ width: 6, height: 6, borderRadius: '50%',
                                                           bgcolor: '#05CD99' }} />
                                                <Typography variant="body2" sx={{ color: '#485585' }}>
                                                    {item}
                                                </Typography>
                                            </Box>
                                        ))}
                                    </Box>

                                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}>
                                        <Button
                                            fullWidth variant="contained"
                                            component={Link}
                                            href={route('application.show', { slug: 'application-individual' })}
                                            sx={{
                                                py: 1.5, borderRadius: '12px',
                                                bgcolor: '#05CD99', textTransform: 'none',
                                                fontSize: '1rem', fontWeight: 600,
                                                boxShadow: '0px 10px 20px rgba(5,205,153,0.15)',
                                                '&:hover': { bgcolor: '#04B386',
                                                            boxShadow: '0px 14px 28px rgba(5,205,153,0.2)' },
                                            }}
                                        >
                                            Как физическое лицо
                                        </Button>
                                        <Button
                                            fullWidth variant="outlined"
                                            component={Link}
                                            href={route('application.show', { slug: 'application-legal' })}
                                            sx={{
                                                py: 1.5, borderRadius: '12px',
                                                borderColor: '#05CD99', color: '#04B386', textTransform: 'none',
                                                fontSize: '1rem', fontWeight: 600,
                                                '&:hover': { borderColor: '#04B386', bgcolor: 'rgba(5,205,153,0.05)' },
                                            }}
                                        >
                                            Как юридическое лицо
                                        </Button>
                                    </Box>
                                </Box>
                            </Paper>
                        </Grid>
                    )}
                </Grid>

                <Box sx={{ textAlign: 'center', mt: 5 }}>
                    <Typography variant="body2" sx={{ color: '#A3AED0' }}>
                        Нет доступа к email или возникли проблемы с привязкой?{' '}
                        Обратитесь к нам по телефону{' '}
                        <Typography component="span" fontWeight="bold" sx={{ color: '#4318FF' }}>
                            +7 (38595)-55-0-36
                        </Typography>
                        {' '}или посетите наш офис — сотрудники помогут привязать лицевой счёт вручную.
                    </Typography>
                </Box>
            </Container>

            <Box sx={{ py: 3, px: 4, bgcolor: '#fff', borderTop: '1px solid #E0E5F2',
                        textAlign: 'center' }} />
        </Box>
    );
}
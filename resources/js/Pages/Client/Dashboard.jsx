import React, { useState } from 'react';
import ClientLayout from '@/Layouts/ClientLayout';
import { Grid, Paper, Typography, Box, Alert, Button, Chip, LinearProgress, IconButton, Card, CardContent, Divider } from '@mui/material';
import { 
    AccountCircle as AccountIcon, 
    Description as DocumentIcon,
    CheckCircle as CheckIcon,
    HourglassEmpty as PendingIcon,
    Error as ErrorIcon,
    Close as CloseIcon,
    Message as MessageIcon,
    Add as AddIcon,
    Home as HomeIcon,
    ArrowForwardIos as ArrowIcon,
} from '@mui/icons-material';
import { Link } from '@inertiajs/react';

const statusConfig = {
    pending: {
        label: 'Ожидает рассмотрения',
        color: 'warning',
        description: 'Ваша заявка передана сотрудникам и ожидает рассмотрения.',
        progress: 25,
        modeColor: '#F57C00'
    },
    processing: {
        label: 'В работе',
        color: 'info',
        description: 'Сотрудник уже рассматривает вашу заявку.',
        progress: 75,
        modeColor: '#1976D2'
    },
    approved: {
        label: 'Одобрена',
        color: 'success',
        description: 'Ваша заявка одобрена! Объект добавлен в ваш список.',
        progress: 100,
        modeColor: '#2E7D32'
    },
    rejected: {
        label: 'Отклонена',
        color: 'error',
        description: 'К сожалению, ваша заявка была отклонена. Свяжитесь с нами для уточнения причин.',
        progress: 0,
        modeColor: '#C62828'
    }
};

export default function Dashboard({ auth, client, properties = [], pendingProperties = [], activeApplications = [], hasActiveProperties = false, primaryAccountNumber = null }) {
    const user = auth?.user;

    const applicationsArray = Array.isArray(activeApplications)
        ? activeApplications
        : (activeApplications?.data || Object.values(activeApplications || {}));

    const application = applicationsArray.length > 0 ? applicationsArray[0] : null;
    const applicationStatus = application?.status;

    const isApproved = applicationStatus === 'approved';
    const hasActiveContract = hasActiveProperties || isApproved;
    const storageKey = `dismissed_success_alert_${application?.id}`;

    const [showSuccessBlock, setShowSuccessBlock] = useState(() => {
        if (typeof window !== 'undefined' && isApproved) {
            return localStorage.getItem(storageKey) !== 'true';
        }
        return true;
    });

    const handleCloseSuccess = () => {
        setShowSuccessBlock(false);
        localStorage.setItem(storageKey, 'true');
    };

    const config = statusConfig[applicationStatus] || statusConfig.pending;

    return (
        <ClientLayout
            user={user}
            title="Панель управления"
            statusConfig={statusConfig}
            application={application}
            applicationStatus={applicationStatus}
            properties={properties}
            hasActiveProperties={hasActiveProperties}
        >
            {/*Приветствие / Статус договора / Новый объект*/}
            <Grid container spacing={3} sx={{ mb: 3 }}>
                <Grid item xs={12} md={4}>
                    <Paper sx={{ p: 3, borderRadius: '20px', display: 'flex', alignItems: 'center', gap: 3, height: '100%' }}>
                        <AccountIcon sx={{ fontSize: 60, color: '#4318FF' }} />
                        <Box>
                            <Typography variant="subtitle1" fontWeight="bold">Добро пожаловать!</Typography>
                            <Typography
                                variant="body2"
                                color="text.secondary"
                                noWrap
                                sx={{ maxWidth: '180px', overflow: 'hidden', textOverflow: 'ellipsis' }}
                            >
                                {client ? `${client.last_name || ''} ${client.first_name || ''}`.trim() : user.name}
                            </Typography>
                        </Box>
                    </Paper>
                </Grid>

                <Grid item xs={12} md={4}>
                    <Paper sx={{ p: 3, borderRadius: '20px', bgcolor: '#4318FF', color: '#fff', height: '100%' }}>
                        <Typography variant="subtitle1" fontWeight="bold">Статус договора</Typography>
                        <Typography variant="body2" sx={{ opacity: 0.9 }}>
                            {hasActiveContract ? 'Договор активен' : 'Ожидается оформление'}
                        </Typography>
                        {primaryAccountNumber && (
                            <Typography variant="caption" sx={{ opacity: 0.7, display: 'block', mt: 0.5 }}>
                                ЛС: {primaryAccountNumber}
                            </Typography>
                        )}
                    </Paper>
                </Grid>

                <Grid item xs={12} md={4}>
                    <Button
                        component={Link}
                        href={route('application.show', {
                            slug: client?.client_type === 'legal' ? 'application-legal' : 'application-individual'
                        })}
                        variant="contained"
                        fullWidth
                        startIcon={<AddIcon />}
                        sx={{
                            height: '100%',
                            minHeight: '80px',
                            borderRadius: '20px',
                            bgcolor: '#4318FF',
                            textTransform: 'none',
                            fontSize: '1rem',
                            boxShadow: '0px 10px 20px rgba(67, 24, 255, 0.15)'
                        }}
                    >
                        Новый объект
                    </Button>
                </Grid>
            </Grid>

            {/*Статус заявки*/}
            {application && (
                <Grid container spacing={3} sx={{ mb: 3 }}>
                    <Grid item xs={12} md={4}>
                        {isApproved ? (
                            showSuccessBlock && (
                                <Paper sx={{ p: 3, borderRadius: '20px', bgcolor: '#E8F5E9', border: '1px solid #A5D6A7', position: 'relative'}}>
                                    <IconButton
                                        onClick={handleCloseSuccess}
                                        sx={{ position: 'absolute', top: 8, right: 8 }}
                                        size="small"
                                    >
                                        <CloseIcon fontSize="small" />
                                    </IconButton>
                                    <Box display="flex" alignItems="center" gap={2}>
                                        <CheckIcon sx={{ fontSize: 40, color: '#2E7D32' }} />
                                        <Box>
                                            <Typography variant="h6" fontWeight="bold" color="#2E7D32">Заявка одобрена!</Typography>
                                            <Typography variant="body2" color="text.secondary">
                                                Объект по адресу <strong>{application.address}</strong> успешно подключен.
                                            </Typography>
                                        </Box>
                                    </Box>
                                </Paper>
                            )
                        ) : (
                            <Paper sx={{ p: 4, borderRadius: '20px', boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.08)' }}>
                                <Box display="flex" alignItems="center" gap={2} mb={3}>
                                    <Typography variant="h6" fontWeight="bold">Заявка #{application.id}</Typography>
                                    <Chip label={config.label} color={config.color} size="small" />
                                </Box>
                                <Box mb={2}>
                                    <Box display="flex" justifyContent="space-between" mb={1}>
                                        <Typography variant="caption" color="text.secondary">Статус рассмотрения</Typography>
                                        <Typography variant="caption" fontWeight="bold">{config.progress}%</Typography>
                                    </Box>
                                    <LinearProgress
                                        variant="determinate"
                                        value={config.progress}
                                        sx={{
                                            height: 8,
                                            borderRadius: 4,
                                            bgcolor: '#E0E5F2',
                                            '& .MuiLinearProgress-bar': { bgcolor: config.modeColor }
                                        }}
                                    />
                                </Box>
                                <Typography variant="body2" color="text.secondary">{config.description}</Typography>
                            </Paper>
                        )}
                    </Grid>
                </Grid>
            )}

            {/*Мои объекты / Быстрые действия*/}
            <Grid container spacing={3}>
                {/* Список объектов - только активные с ЛС */}
                <Grid item xs={12}>
                    <Typography variant="h6" fontWeight="bold" sx={{ mb: 2 }}>
                        Мои объекты ({properties.length})
                    </Typography>
                    <Grid container spacing={2}>
                        {properties.length > 0 ? (
                            properties.map((property) => (
                                <Grid item xs={12} sm={6} md={4} key={property.id}>
                                    <Card sx={{
                                        borderRadius: '20px',
                                        border: '1px solid #E0E5F2',
                                        '&:hover': { boxShadow: '0px 10px 20px rgba(0,0,0,0.05)' }
                                    }}>
                                        <CardContent>
                                            <Box display="flex" justifyContent="space-between" mb={2}>
                                                <Box sx={{ bgcolor: '#F4F7FE', p: 1, borderRadius: '10px' }}>
                                                    <HomeIcon sx={{ color: '#4318FF' }} />
                                                </Box>
                                                <Chip label="Активен" size="small" color="success" variant="outlined" />
                                            </Box>
                                            <Typography variant="subtitle2" fontWeight="bold" noWrap>{property.address}</Typography>
                                            <Typography variant="caption" color="text.secondary">ЛС: {property.account_number}</Typography>
                                            <Divider sx={{ my: 1.5 }} />
                                            <Button
                                                fullWidth
                                                component={Link}
                                                href={route('client.readings') + '?property=' + property.id}
                                                endIcon={<ArrowIcon sx={{ fontSize: 10 }} />}
                                                sx={{ justifyContent: 'space-between', color: '#4318FF', textTransform: 'none', fontSize: '0.85rem' }}
                                            >
                                                Передать показания
                                            </Button>
                                        </CardContent>
                                    </Card>
                                </Grid>
                            ))
                        ) : (
                            <Grid item xs={12}>
                                <Alert severity="info" sx={{ borderRadius: '15px' }}>
                                    У вас пока нет активных объектов.
                                </Alert>
                            </Grid>
                        )}
                    </Grid>
                </Grid>

                {/* Быстрые действия
                <Grid item xs={12} md={6}>
                    <Paper sx={{ p: 3, borderRadius: '20px' }}>
                        <Typography variant="subtitle1" fontWeight="bold" gutterBottom>Быстрые действия</Typography>
                        <Box display="flex" gap={2} flexWrap="wrap">
                            <Button
                                component={Link}
                                href={route('client.documents')}
                                variant="outlined"
                                startIcon={<DocumentIcon />}
                                sx={{ borderRadius: '12px', textTransform: 'none' }}
                            >
                                Документы
                            </Button>
                            <Button
                                component={Link}
                                href={route('client.tickets.index')}
                                variant="contained"
                                startIcon={<MessageIcon />}
                                sx={{ borderRadius: '12px', bgcolor: '#4318FF', textTransform: 'none' }}
                            >
                                Обращения
                            </Button>
                        </Box>
                    </Paper>
                </Grid> */}

                {/* QR-код блок */}
                <Grid item xs={12} md={6}>
                    <Paper sx={{ p: 3, borderRadius: '20px' }}>
                        <Box display="flex" gap={2} alignItems="flex-start">
                            <Box sx={{ flexShrink: 0 }}>
                                <a href="https://forms.yandex.ru/cloud/6a0670c7493639178613adf6" target="_blank" rel="noopener noreferrer">
                                    <img 
                                        src="http://qrcoder.ru/code/?https%3A%2F%2Fforms.yandex.ru%2Fcloud%2F6a0670c7493639178613adf6&3&0"
                                        width="123" 
                                        height="123" 
                                        border="0" 
                                        title="QR код"
                                        alt="QR код"
                                    />
                                </a>
                            </Box>
                            <Typography variant="body2">
                                Откажитесь от бумажных квитанций! Заполните заявку на переход на электронные квитанции. Отсканируйте QR-код, либо перейдите по ссылке.<br/>
                                <strong>https://forms.yandex.ru/cloud/6a0670c7493639178613adf6</strong>
                            </Typography>
                        </Box>
                    </Paper>
                </Grid>
            </Grid>
        </ClientLayout>
    );
}
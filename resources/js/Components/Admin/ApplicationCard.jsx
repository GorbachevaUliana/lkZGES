import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Dialog, DialogContent, DialogActions, Box, Typography, Button,
    Tabs, Tab, Grid, TextField, Chip, Divider, Table, TableBody,
    TableCell, TableContainer, TableRow, Paper, IconButton, Alert,
    CircularProgress, MenuItem, Select, FormControl, InputLabel,
} from '@mui/material';
import {
    Download as DownloadIcon,
    Save as SaveIcon,
    CloudUpload as UploadIcon,
    Delete as DeleteIcon,
    Person as PersonIcon,
    Business as BusinessIcon,
    Description as DescriptionIcon,
    CheckCircle as CheckIcon
} from '@mui/icons-material';
import { APPLICATION_STATUS_COLORS } from '@/constants/statuses';

function TabPanel({ children, value, index }) {
    return value === index ? <Box sx={{ py: 3 }}>{children}</Box> : null;
}

export default function ApplicationCard({ open, onClose, application, statuses, showToast, tariffs }) {
    const [tabValue, setTabValue] = useState(0);
    const [accountNumber, setAccountNumber] = useState('');
    const [adminComment, setAdminComment] = useState('');
    const [selectedStatus, setSelectedStatus] = useState('');
    const [processing, setProcessing] = useState(false);
    const [tariffId, setTariffId] = useState('');


    React.useEffect(() => {
        if (application) {
            setSelectedStatus(application.status || '');
            setAdminComment(application.admin_comment || '');
            setAccountNumber(application.client?.account_number || '');
            setTariffId(application.tariff_id || '');
            setTabValue(0);
        }
    }, [application]);

    if (!application) return null;

    const appId = application.id || application.data?.id;
    const colors = statusColors[application.status] || { bg: '#F5F5F5', color: '#666', label: application.status };
    const isLegal = application.client_type === 'legal';

    const handleUpdateStatus = () => {
        if (selectedStatus === 'approved' && !accountNumber) {
            showToast('Введите лицевой счёт', 'error');
            return;
        }

        if (selectedStatus === 'approved' && !tariffId) {
            showToast('Выберите тариф', 'error');
            return;
        }

        setProcessing(true);

        router.post(`/admin/applications/${appId}/status`, {
            status: selectedStatus,
            account_number: accountNumber,
            admin_comment: adminComment,
            tariff_id:
                selectedStatus === 'approved'
                    ? Number(tariffId)
                    : null,
        }, {
            preserveScroll: true,

            onSuccess: () => {
                showToast('Статус обновлён');
                setProcessing(false);
            },

            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                showToast(firstError || 'Ошибка', 'error');
                setProcessing(false);
            }
        });
    };

    const handleUploadContract = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('file', file);
        router.post(`/admin/applications/${appId}/contract`, formData, {
            forceFormData: true,
            onSuccess: () => showToast('Договор загружен'),
            onError: () => showToast('Ошибка загрузки', 'error')
        });
    };

    const handleUploadDocument = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('file', file);
        formData.append('name', file.name);

        router.post(`/admin/applications/${appId}/document`, formData, {
            forceFormData: true,
            onSuccess: () => showToast('Документ загружен'),
            onError: () => showToast('Ошибка загрузки', 'error')
        });
    };

    const formatApplicantName = () => {
        const parts = [];
        if (application.data?.last_name) parts.push(application.data.last_name);
        if (application.data?.first_name) parts.push(application.data.first_name);
        if (application.data?.middle_name) parts.push(application.data.middle_name);
        if (parts.length === 0 && application.applicant_name) {
            return application.applicant_name;
        }
        
        return parts.join(' ');
    };

    const applicantNameFormatted = formatApplicantName();

    return (
        <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
            {/* Шапка */}
            <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3 }}>
                <Box display="flex" justifyContent="space-between" alignItems="center">
                    <Box display="flex" alignItems="center" gap={2}>
                        {isLegal ? <BusinessIcon sx={{ fontSize: 40 }} /> : <PersonIcon sx={{ fontSize: 40 }} />}
                        <Box>
                            <Typography variant="h5" fontWeight="bold">
                                Заявка #{application.id}
                            </Typography>
                            <Typography variant="body2" sx={{ opacity: 0.7 }}>
                                {applicantNameFormatted}{application.client_type_name ? ` • ${application.client_type_name}` : ''}
                            </Typography>
                        </Box>
                    </Box>
                    <Chip
                        label={colors.label}
                        sx={{
                            bgcolor: colors.bg,
                            color: colors.color,
                            fontWeight: 'bold',
                            fontSize: '12px',
                        }}
                    />
                </Box>
            </Box>

            {/* Табы */}
            <Tabs value={tabValue} onChange={(e, v) => setTabValue(v)} sx={{ px: 2, borderBottom: 1, borderColor: 'divider' }}>
                <Tab label="Данные заявки" />
                <Tab label="Документы" />
                <Tab label="Обработка" />
            </Tabs>

            <DialogContent sx={{ minHeight: '400px', bgcolor: '#fafbfd' }}>
                {/* TAB 0: Данные заявки */}
                <TabPanel value={tabValue} index={0}>
                    <Grid container spacing={3}>
                        {/* Информация о заявителе */}
                        <Grid item xs={12}>
                            <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                                Контактная информация
                            </Typography>
                            <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: '12px' }}>
                                <Table size="small">
                                    <TableBody>
                                        <TableRow>
                                            <TableCell sx={{ width: '40%', bgcolor: '#F4F7FE' }}>Email</TableCell>
                                            <TableCell>{application.user?.email}</TableCell>
                                        </TableRow>
                                        <TableRow>
                                            <TableCell sx={{ bgcolor: '#F4F7FE' }}>Телефон</TableCell>
                                            <TableCell>{application.client?.phone || application.data?.phone}</TableCell>
                                        </TableRow>
                                        <TableRow>
                                            <TableCell sx={{ bgcolor: '#F4F7FE' }}>Адрес</TableCell>
                                            <TableCell>{application.client?.address || application.data?.address}</TableCell>
                                        </TableRow>
                                        {isLegal && (
                                            <>
                                                <TableRow>
                                                    <TableCell sx={{ bgcolor: '#F4F7FE' }}>Название организации</TableCell>
                                                    <TableCell>{application.data?.company_name}</TableCell>
                                                </TableRow>
                                                <TableRow>
                                                    <TableCell sx={{ bgcolor: '#F4F7FE' }}>ИНН</TableCell>
                                                    <TableCell>{application.data?.inn}</TableCell>
                                                </TableRow>
                                                <TableRow>
                                                    <TableCell sx={{ bgcolor: '#F4F7FE' }}>КПП</TableCell>
                                                    <TableCell>{application.data?.kpp || '—'}</TableCell>
                                                </TableRow>
                                                <TableRow>
                                                    <TableCell sx={{ bgcolor: '#F4F7FE' }}>Контактное лицо</TableCell>
                                                    <TableCell>{application.data?.contact_person}</TableCell>
                                                </TableRow>
                                            </>
                                        )}
                                    </TableBody>
                                </Table>
                            </TableContainer>
                        </Grid>

                        {/* Дополнительные данные из формы */}
                        {application.data && Object.keys(application.data).length > 0 && (
                            <Grid item xs={12}>
                                <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                                    Данные из формы
                                </Typography>
                                <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: '12px' }}>
                                    <Table size="small">
                                        <TableBody>
                                            {Object.entries(application.data)
                                                .filter(([key]) => !['last_name', 'first_name', 'middle_name', 'address', 'phone', 'client_type', 'company_name', 'inn', 'kpp', 'ogrn', 'contact_person'].includes(key))
                                                .map(([key, value]) => (
                                                    <TableRow key={key}>
                                                        <TableCell sx={{ width: '40%', bgcolor: '#F4F7FE' }}>{key}</TableCell>
                                                        <TableCell>
                                                            {typeof value === 'object' ? JSON.stringify(value) : value}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                        </TableBody>
                                    </Table>
                                </TableContainer>
                            </Grid>
                        )}
                    </Grid>
                </TabPanel>

                {/* TAB 1: Документы */}
                <TabPanel value={tabValue} index={1}>
                    <Box mb={3}>
                        <Typography variant="h6" fontWeight="bold" gutterBottom>
                            Сгенерированная заявка
                        </Typography>
                        {application.generated_pdf_url ? (
                            <Box 
                                display="flex" 
                                alignItems="center" 
                                gap={2}
                                sx={{
                                    p: 2,
                                    bgcolor: '#E8F5E9',
                                    borderRadius: '12px',
                                    border: '1px solid #C8E6C9'
                                }}
                            >
                                <DescriptionIcon sx={{ color: '#2E7D32', fontSize: 28 }} />
                                <Box flex={1}>
                                    <Typography fontWeight="500" color="#2E7D32">
                                        Заявка сформирована
                                    </Typography>
                                    <Typography variant="caption" color="text.secondary">
                                        PDF документ готов к скачиванию
                                    </Typography>
                                </Box>
                                <Button
                                    variant="outlined"
                                    size="small"
                                    startIcon={<DownloadIcon />}
                                    href={application.generated_pdf_url}
                                    target="_blank"
                                    sx={{ 
                                        borderRadius: '8px',
                                        borderColor: '#2E7D32',
                                        color: '#2E7D32',
                                        '&:hover': { 
                                            borderColor: '#1B5E20',
                                            bgcolor: '#C8E6C9'
                                        }
                                    }}
                                >
                                    Скачать
                                </Button>
                            </Box>
                        ) : (
                            <Typography color="text.secondary">Файл не найден</Typography>
                        )}
                    </Box>

                    <Divider sx={{ my: 2 }} />

                    <Box mb={3}>
                        <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
                            <Typography variant="h6" fontWeight="bold">
                                Договор
                            </Typography>
                            <Button variant="outlined" startIcon={<UploadIcon />} component="label" size="small">
                                Загрузить договор
                                <input type="file" hidden accept=".pdf,.jpg,.jpeg,.png" onChange={handleUploadContract} />
                            </Button>
                        </Box>
                        {application.contract_pdf_url ? (
                            <Box 
                                display="flex" 
                                alignItems="center" 
                                gap={2}
                                sx={{
                                    p: 2,
                                    bgcolor: '#E8F5E9',
                                    borderRadius: '12px',
                                    border: '1px solid #C8E6C9'
                                }}
                            >
                                <DescriptionIcon sx={{ color: '#2E7D32', fontSize: 28 }} />
                                <Box flex={1}>
                                    <Typography fontWeight="500" color="#2E7D32">
                                        Договор загружен
                                    </Typography>
                                </Box>
                                <Button
                                    variant="outlined"
                                    size="small"
                                    startIcon={<DownloadIcon />}
                                    href={application.contract_pdf_url}
                                    target="_blank"
                                    sx={{ 
                                        borderRadius: '8px',
                                        borderColor: '#2E7D32',
                                        color: '#2E7D32',
                                        '&:hover': { 
                                            borderColor: '#1B5E20',
                                            bgcolor: '#C8E6C9'
                                        }
                                    }}
                                >
                                    Скачать
                                </Button>
                            </Box>
                        ) : (
                            <Typography color="text.secondary">Договор ещё не загружен</Typography>
                        )}
                    </Box>

                    <Divider sx={{ my: 2 }} />

                    <Box>
                        <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
                            <Typography variant="h6" fontWeight="bold">
                                Документы клиента
                            </Typography>
                            <Button variant="outlined" startIcon={<UploadIcon />} component="label" size="small">
                                Добавить документ
                                <input type="file" hidden accept=".pdf,.jpg,.jpeg,.png" onChange={handleUploadDocument} />
                            </Button>
                        </Box>
                        {application.client?.documents?.length > 0 ? (
                            <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: '12px' }}>
                                <Table size="small">
                                    <TableBody>
                                        {application.client.documents.map((doc) => (
                                            <TableRow key={doc.id}>
                                                <TableCell>
                                                    <Box display="flex" alignItems="center" gap={1}>
                                                        <DescriptionIcon fontSize="small" sx={{ color: '#1976D2' }} />
                                                        <Typography variant="body2">{doc.name}</Typography>
                                                    </Box>
                                                </TableCell>
                                                <TableCell>
                                                    <Typography 
                                                        variant="caption" 
                                                        sx={{ 
                                                            color: '#666',
                                                            bgcolor: '#F5F5F5',
                                                            px: 1,
                                                            py: 0.5,
                                                            borderRadius: '4px'
                                                        }}
                                                    >
                                                        {doc.type_name}
                                                    </Typography>
                                                </TableCell>
                                                <TableCell align="right">
                                                    <IconButton 
                                                        href={doc.url} 
                                                        target="_blank" 
                                                        size="small"
                                                        sx={{ 
                                                            bgcolor: '#E3F2FD',
                                                            '&:hover': { bgcolor: '#BBDEFB' }
                                                        }}
                                                    >
                                                        <DownloadIcon fontSize="small" />
                                                    </IconButton>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </TableContainer>
                        ) : (
                            <Typography color="text.secondary">Нет документов</Typography>
                        )}
                    </Box>
                </TabPanel>

                {/* TAB 2: Обработка */}
                <TabPanel value={tabValue} index={2}>
                    {application.status === 'approved' && (
                        <Alert severity="success" sx={{ mb: 3, borderRadius: '12px' }}>
                            Заявка одобрена! Клиенту присвоен лицевой счёт: <strong>{application.client?.account_number}</strong>
                        </Alert>
                    )}

                    <Grid container spacing={3}>
                        <Grid item xs={12}>
                            <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                                Статус заявки
                            </Typography>
                            <FormControl fullWidth disabled={application.status === 'approved'}>
                                <Select
                                    value={selectedStatus}
                                    onChange={(e) => setSelectedStatus(e.target.value)}
                                    sx={{ 
                                        borderRadius: '12px',
                                        '& .MuiOutlinedInput-notchedOutline': {
                                            borderRadius: '12px',
                                        }
                                    }}
                                >
                                    {Object.entries(statuses || {}).map(([value, label]) => (
                                        <MenuItem key={value} value={value}>{label}</MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </Grid>

                        {/* Лицевой счёт - показываем при одобрении */}
                        {(selectedStatus === 'approved' || application.status === 'approved') && (
                            <Grid item xs={12}>
                                <Alert severity="info" sx={{ mb: 2, borderRadius: '12px' }}>
                                    При одобрении заявки укажите лицевой счёт клиента. Он будет использоваться для входа в личный кабинет.
                                </Alert>
                                <TextField
                                    fullWidth
                                    label="Лицевой счёт"
                                    value={accountNumber}
                                    onChange={(e) => setAccountNumber(e.target.value)}
                                    disabled={application.status === 'approved'}
                                    required
                                    sx={{ '& .MuiOutlinedInput-root': { borderRadius: '12px' } }}
                                />
                                <FormControl fullWidth>
                                    <InputLabel>Тариф</InputLabel>
                                    <Select
                                        value={tariffId}
                                        onChange={(e) => setTariffId(e.target.value)}
                                    >
                                        {tariffs.map(tariff => (
                                            <MenuItem key={tariff.id} value={tariff.id}>
                                                {tariff.name}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                            </Grid>
                        )}

                        {/* 
                        {selectedStatus === 'approved' && (
                            <Grid item xs={12}>
                                <FormControl fullWidth>
                                    <InputLabel>Тариф</InputLabel>
                                    <Select
                                        value={tariffId}
                                        onChange={(e) => setTariffId(e.target.value)}
                                    >
                                        {tariffs.map(tariff => (
                                            <MenuItem key={tariff.id} value={tariff.id}>
                                                {tariff.name}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                            </Grid>
                        )} */}

                        <Grid item xs={12}>
                            <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                                Комментарий для клиента
                            </Typography>
                            <TextField
                                fullWidth
                                multiline
                                rows={3}
                                value={adminComment}
                                onChange={(e) => setAdminComment(e.target.value)}
                                placeholder="Необязательный комментарий, который увидит клиент..."
                                sx={{ '& .MuiOutlinedInput-root': { borderRadius: '12px' } }}
                            />
                        </Grid>

                        {application.processed_at && (
                            <Grid item xs={12}>
                                <Typography variant="body2" color="text.secondary">
                                    Обработано: {application.processed_at} • {application.processed_by_name}
                                </Typography>
                            </Grid>
                        )}
                    </Grid>

                    {application.status !== 'approved' && (
                        <Box display="flex" justifyContent="flex-end" mt={3}>
                            <Button
                                variant="contained"
                                startIcon={processing ? <CircularProgress size={20} color="inherit" /> : <SaveIcon />}
                                onClick={handleUpdateStatus}
                                disabled={processing}
                                sx={{ bgcolor: selectedStatus === 'approved' ? '#2E7D32' : '#4318FF', borderRadius: '12px', px: 4 }}
                            >
                                {selectedStatus === 'approved' ? 'Одобрить и создать договор' : 'Сохранить'}
                            </Button>
                        </Box>
                    )}
                </TabPanel>
            </DialogContent>

            <DialogActions sx={{ p: 2 }}>
                <Button onClick={onClose} color="inherit">Закрыть</Button>
            </DialogActions>
        </Dialog>
    );
}

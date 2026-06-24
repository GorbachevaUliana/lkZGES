import React, { useState } from 'react';
import { 
    Typography, Box, Dialog, DialogContent, TextField, 
    DialogActions, Tabs, Tab, Table, TableBody, TableCell, 
    TableContainer, TableRow, IconButton, Button, Paper,
    FormControl, InputLabel, Select, MenuItem, Grid,
    Divider, List, ListItem, ListItemIcon, ListItemText,
    TableHead, Chip
} from '@mui/material';
import TimelineIcon from '@mui/icons-material/Timeline';
import ReceiptLongIcon from '@mui/icons-material/ReceiptLong';
import { 
    Description as DescriptionIcon, 
    Delete as DeleteIcon, 
    Save as SaveIcon, 
    CloudUpload as CloudUploadIcon 
} from '@mui/icons-material';
import SpeedIcon from '@mui/icons-material/Speed';
import { AddressSuggestions } from 'react-dadata';
import { router } from '@inertiajs/react';
import ClientAvatar from './ClientAvatar';

function TabPanel({ children, value, index }) {
    return value === index ? <Box sx={{ py: 3 }}>{children}</Box> : null;
}

const DADATA_API_KEY = "cd4b88f14527df99bbafecb1c09789391eb6f2ff";

export default function ClientCard({ 
    open, 
    onClose, 
    data, 
    setData, 
    errors,
    onDeleteClient,
    onDeleteDocument,
    showToast,
    tariffs,
}) {
    const [tabValue, setTabValue] = useState(0);

    const handleUpdateSubmit = () => {
        router.post(`/admin/clients/${data.id}`, {
            client_type: data.client_type,
            last_name: data.last_name,
            first_name: data.first_name,
            middle_name: data.middle_name,
            company_name: data.company_name,
            address: data.address,
            phone: data.phone,
            email: data.email,
            tariff_id: data.tariff_id,
            _method: 'PUT',
        }, {
            onSuccess: () => showToast('Данные успешно обновлены'),
        });
    };

    const handleFileUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        router.post(route('admin.clients.upload', data.id), { file }, {
            forceFormData: true, // Здесь он нужен, так как загружается файл
            onSuccess: (page) => {
                const updated = page.props.clients.find(c => c.id === data.id);
                if (updated) setData('documents', updated.documents);
                showToast('Файл загружен');
            }
        });
    };

    // ИСПРАВЛЕНИЕ №2: Надежное вычисление ФИО и отображаемого имени с подстраховками
    const fullName = [data.last_name, data.first_name, data.middle_name]
        .map(str => str?.trim())
        .filter(Boolean)
        .join(' ');

    const displayName = data.client_type === 'legal'
        ? (data.company_name?.trim() || fullName || 'Название компании не указано')
        : (fullName || 'ФИО не указано');

    const avatarName = data.client_type === 'legal' 
        ? (data.company_name || data.last_name || 'Ю') 
        : (data.last_name || 'Ф');

    const inputSx = {
        '& .MuiOutlinedInput-root': {
            borderRadius: '12px',
            backgroundColor: '#fff',
            '& input': { padding: '12px 14px' },
            '& fieldset': { borderColor: '#E0E5F2' },
            '&:hover fieldset': { borderColor: '#B8C1EC' },
            '&.Mui-focused fieldset': { borderColor: '#4318FF' },
        },
    };

    return (
        <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
            {/* Шапка карточки */}
            <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <Box display="flex" alignItems="center" gap={2}>
                    {/* Исправлен нейминг для аватара */}
                    <ClientAvatar name={avatarName} sx={{ width: 56, height: 56 }} />
                    <Box>
                        <Typography variant="h5" fontWeight="bold">
                            {displayName}
                        </Typography>
                        <Box display="flex" gap={2} sx={{ opacity: 0.7 }}>
                            <Typography variant="body2">Л/С: {data.account_number}</Typography>
                            <Typography variant="body2">•</Typography>
                            <Typography variant="body2">
                                {data.client_type === 'legal' ? 'Юр. лицо' : 'Физ. лицо'}
                            </Typography>
                        </Box>
                    </Box>
                </Box>
                <IconButton onClick={() => onDeleteClient(data.id)} sx={{ color: '#FF5B5B' }}>
                    <DeleteIcon />
                </IconButton>
            </Box>
            
            <Tabs value={tabValue} onChange={(e, v) => setTabValue(v)} sx={{ px: 2, borderBottom: 1, borderColor: 'divider' }}>
                <Tab label="Профиль" />
                <Tab label="Документы" />
                <Tab label="Показания" />
            </Tabs>

            <DialogContent sx={{ minHeight: '400px', bgcolor: '#fafbfd' }}>
                <TabPanel value={tabValue} index={0}>
                    <Grid container spacing={2}>
                        {/* Тип клиента и Тариф */}
                        <Grid item xs={12}>
                            <FormControl fullWidth sx={inputSx}>
                                <InputLabel>Тип клиента</InputLabel>
                                <Select 
                                    value={data.client_type || 'individual'} 
                                    onChange={e => setData('client_type', e.target.value)}>
                                    <MenuItem value="individual">Физическое лицо</MenuItem>
                                    <MenuItem value="legal">Юридическое лицо</MenuItem>
                                </Select>
                            </FormControl>
                        </Grid>
                        <Grid item xs={12}>
                            <FormControl fullWidth sx={inputSx}>
                                <InputLabel>Тариф</InputLabel>
                                <Select
                                    value={data.tariff_id || ''}
                                    onChange={e => setData('tariff_id', e.target.value)}>
                                    {tariffs?.map(t => (
                                        <MenuItem key={t.id} value={t.id}>{t.name}</MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </Grid>

                        <Grid item xs={12}>
                            <TextField
                                fullWidth
                                label="Адрес регистрации"
                                value={data.address || ''}
                                onChange={(e) => setData('address', e.target.value)}
                                sx={inputSx}
                                InputProps={{
                                    inputComponent: ({ inputRef, ...props }) => (
                                        <AddressSuggestions
                                            token={DADATA_API_KEY}
                                            {...props}
                                            inputRef={inputRef}
                                            onChange={(s) => setData('address', s.value)}
                                        />
                                    ),
                                }}
                            />
                        </Grid>

                        {/* Условные поля: Компания или ФИО */}
                        {data.client_type === 'legal' ? (
                            <Grid item xs={12}>
                                <TextField fullWidth label="Название компании" variant="outlined" value={data.company_name || ''} onChange={e => setData('company_name', e.target.value)} sx={inputSx} />
                            </Grid>
                        ) : (
                            <>
                                <Grid item xs={12}><TextField fullWidth label="Фамилия" variant="outlined" value={data.last_name || ''} onChange={e => setData('last_name', e.target.value)} sx={inputSx} /></Grid>
                                <Grid item xs={12}><TextField fullWidth label="Имя" variant="outlined" value={data.first_name || ''} onChange={e => setData('first_name', e.target.value)} sx={inputSx} /></Grid>
                                <Grid item xs={12}><TextField fullWidth label="Отчество" variant="outlined" value={data.middle_name || ''} onChange={e => setData('middle_name', e.target.value)} sx={inputSx} /></Grid>
                            </>
                        )}

                        <Grid item xs={12}><TextField fullWidth label="Телефон" variant="outlined" value={data.phone || ''} onChange={e => setData('phone', e.target.value)} sx={inputSx} /></Grid>
                        <Grid item xs={12}><TextField fullWidth label="Email" variant="outlined" value={data.email || ''} onChange={e => setData('email', e.target.value)} sx={inputSx} /></Grid>
                    </Grid>

                    <Box display="flex" justifyContent="flex-end" mt={3}>
                        <Button variant="contained" startIcon={<SaveIcon />} onClick={handleUpdateSubmit} sx={{ bgcolor: '#4318FF', borderRadius: '12px', px: 4 }}>
                            Сохранить изменения
                        </Button>
                    </Box>
                </TabPanel>

                {/* TAB 1: Документы */}
                <TabPanel value={tabValue} index={1}>
                    <Box display="flex" justifyContent="space-between" mb={2} alignItems="center">
                        <Typography variant="h6" fontWeight="bold" color="#2B3674">Файлы</Typography>
                        <Button variant="outlined" startIcon={<CloudUploadIcon />} component="label">
                            Загрузить <input type="file" hidden onChange={handleFileUpload} />
                        </Button>
                    </Box>
                    <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: '15px' }}>
                        <Table size="small">
                            <TableBody>
                                {data.documents?.length > 0 ? data.documents.map(doc => (
                                    <TableRow key={doc.id}>
                                        <TableCell>{doc.name}</TableCell>
                                        <TableCell align="right">
                                            <IconButton href={doc.url} target="_blank" color="primary">
                                                <DescriptionIcon />
                                            </IconButton>
                                            <IconButton onClick={() => onDeleteDocument(doc.id)} color="error">
                                                <DeleteIcon />
                                            </IconButton>
                                        </TableCell>
                                    </TableRow>
                                )) : (
                                    <TableRow>
                                        <TableCell align="center" sx={{ py: 3, color: 'text.secondary' }}>Нет документов</TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </TableContainer>
                </TabPanel>
                
                <TabPanel value={tabValue} index={2}>
                    <Grid container spacing={3}>
                        {/* Мини-статистика сверху */}
                        <Grid item xs={12} md={4}>
                            <Paper variant="outlined" sx={{ p: 2, borderRadius: '15px', textAlign: 'center', bgcolor: '#F4F7FE' }}>
                                <Typography variant="caption" color="text.secondary">Текущий баланс</Typography>
                                <Typography variant="h5" fontWeight="bold" color={data.balance < 0 ? "#FF5B5B" : "#05CD99"}>
                                    {data.balance || 0} ₽
                                </Typography>
                            </Paper>
                        </Grid>
                        <Grid item xs={12} md={4}>
                            <Paper variant="outlined" sx={{ p: 2, borderRadius: '15px', textAlign: 'center', bgcolor: '#F4F7FE' }}>
                                <Typography variant="caption" color="text.secondary">Среднее в месяц</Typography>
                                <Typography variant="h5" fontWeight="bold" color="#2B3674">
                                    {data.readings?.length > 0 ? (data.readings.reduce((acc, curr) => acc + curr.consumed, 0) / data.readings.length).toFixed(1) : 0} кВт
                                </Typography>
                            </Paper>
                        </Grid>
                        <Grid item xs={12} md={4}>
                            <Paper variant="outlined" sx={{ p: 2, borderRadius: '15px', textAlign: 'center', bgcolor: '#F4F7FE' }}>
                                <Typography variant="caption" color="text.secondary">Последняя поверка</Typography>
                                <Typography variant="h5" fontWeight="bold" color="#2B3674">
                                    {data.verification_date || '—'}
                                </Typography>
                            </Paper>
                        </Grid>

                        {/* График или Таблица истории */}
                        <Grid item xs={12}>
                            <Typography variant="h6" fontWeight="bold" color="#2B3674" mb={2}>
                                История потребления
                            </Typography>
                            
                            <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: '15px', maxHeight: '300px' }}>
                                <Table stickyHeader size="small">
                                    <TableHead>
                                        <TableRow>
                                            <TableCell sx={{ bgcolor: '#F4F7FE', fontWeight: 'bold' }}>Период</TableCell>
                                            <TableCell sx={{ bgcolor: '#F4F7FE', fontWeight: 'bold' }}>Показание</TableCell>
                                            <TableCell sx={{ bgcolor: '#F4F7FE', fontWeight: 'bold' }}>Расход</TableCell>
                                            <TableCell sx={{ bgcolor: '#F4F7FE', fontWeight: 'bold' }}>Сумма</TableCell>
                                            <TableCell sx={{ bgcolor: '#F4F7FE', fontWeight: 'bold' }}>Статус</TableCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {data.readings?.length > 0 ? data.readings.map((reading) => (
                                            <TableRow key={reading.id}>
                                                <TableCell>{new Date(reading.reading_date).toLocaleDateString('ru-RU', {month: 'long', year: 'numeric'})}</TableCell>
                                                <TableCell>{reading.current_value}</TableCell>
                                                <TableCell>{reading.current_value - reading.previous_value} кВт</TableCell>
                                                <TableCell><b>{reading.total_sum} ₽</b></TableCell>
                                                <TableCell>
                                                    <Chip 
                                                        label={reading.is_paid ? "Оплачено" : "Долг"} 
                                                        size="small" 
                                                        color={reading.is_paid ? "success" : "error"}
                                                        variant="soft"
                                                        sx={{ borderRadius: '6px', fontSize: '11px' }}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                        )) : (
                                            <TableRow>
                                                <TableCell colSpan={5} align="center" sx={{ py: 4 }}>
                                                    Данные о показаниях отсутствуют
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </TableContainer>
                        </Grid>

                        {/* Кнопка ручной корректировки для админа */}
                        <Grid item xs={12}>
                            <Button 
                                variant="text" 
                                startIcon={<SpeedIcon />} 
                                sx={{ textTransform: 'none', color: '#4318FF' }}
                                onClick={() => {/* Логика добавления показания админом */}}
                            >
                                Внести показание вручную
                            </Button>
                        </Grid>
                    </Grid>
                </TabPanel>
            </DialogContent>

            <DialogActions sx={{ p: 2 }}>
                <Button onClick={onClose} color="inherit">Закрыть</Button>
            </DialogActions>
        </Dialog>
    );
}
import React, { useState, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Container, Typography, Paper, Button, Box,
    Dialog, DialogContent, TextField, DialogActions,
    Snackbar, Alert, InputBase, Grid,
    FormControl, InputLabel, Select, MenuItem, FormHelperText,
    Card, CardContent, IconButton, Divider
} from '@mui/material';
import {
    Add as AddIcon, Search as SearchIcon, Delete as DeleteIcon
} from '@mui/icons-material';
import { DataGrid } from '@mui/x-data-grid';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import ClientCard from '@/Components/Admin/ClientCard';
import { fixKeyboardLayout } from '@/utils/keyboard';

// Пустой объект недвижимости
const emptyProperty = () => ({
    account_number: '',
    tariff_id: '',
    region: 'Алтайский край',
    district: '',
    locality: '',
    street: '',
    house: '',
    building: '',
    apartment: ''
});

export default function ClientsList({ auth, clients, tariffs }) {
    const [editOpen, setEditOpen] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [toast, setToast] = useState({ open: false, message: '', severity: 'success' });
    const [confirmMeta, setConfirmMeta] = useState({ open: false, title: '', content: '', onConfirm: () => {} });

    // Форма с поддержкой массива properties
    const { data, setData, post, reset, processing, errors } = useForm({
        id: '',
        client_type: 'individual',
        last_name: '',
        first_name: '',
        middle_name: '',
        company_name: '',
        phone: '',
        email: '',
        properties: [emptyProperty()],
    });

    // Ошибки для объектов недвижимости
    const [propertyErrors, setPropertyErrors] = useState([{}]);

    const showToast = (message, severity = 'success') => setToast({ open: true, message, severity });

    const handleOpenCreate = () => {
        reset();
        setData({
            id: '',
            client_type: 'individual',
            last_name: '',
            first_name: '',
            middle_name: '',
            company_name: '',
            phone: '',
            email: '',
            properties: [emptyProperty()],
        });
        setPropertyErrors([{}]);
        setCreateOpen(true);
    };

    const handleRowClick = (params) => {
        setData({ ...params.row, documents: params.row.documents || [] });
        setEditOpen(true);
    };

    // Управление объектами недвижимости
    const addProperty = () => {
        setData('properties', [...data.properties, emptyProperty()]);
        setPropertyErrors([...propertyErrors, {}]);
    };

    const removeProperty = (index) => {
        if (data.properties.length > 1) {
            const newProperties = [...data.properties];
            newProperties.splice(index, 1);
            setData('properties', newProperties);
            
            const newErrors = [...propertyErrors];
            newErrors.splice(index, 1);
            setPropertyErrors(newErrors);
        }
    };

    const updateProperty = (index, field, value) => {
        const newProperties = [...data.properties];
        newProperties[index] = { ...newProperties[index], [field]: value };
        setData('properties', newProperties);
    };

    // Валидация объектов
    const validateProperties = () => {
        let isValid = true;
        const newErrors = data.properties.map((prop) => {
            const errs = {};
            if (!prop.account_number || prop.account_number.trim() === '') {
                errs.account_number = 'Обязательно';
                isValid = false;
            }
            if (!prop.tariff_id) {
                errs.tariff_id = 'Обязательно';
                isValid = false;
            }
            if (!prop.locality || prop.locality.trim() === '') {
                errs.locality = 'Обязательно';
                isValid = false;
            }
            if (!prop.street || prop.street.trim() === '') {
                errs.street = 'Обязательно';
                isValid = false;
            }
            if (!prop.house || prop.house.trim() === '') {
                errs.house = 'Обязательно';
                isValid = false;
            }
            return errs;
        });
        setPropertyErrors(newErrors);
        return isValid;
    };

    const handleCreateSubmit = (e) => {
        e.preventDefault();
        
        if (!validateProperties()) {
            showToast('Заполните все обязательные поля объектов', 'error');
            return;
        }

        post(route('admin.clients.store'), {
            onSuccess: () => {
                setCreateOpen(false);
                showToast('Потребитель создан');
            },
            onError: (errs) => {
                showToast(errs.properties || 'Ошибка при создании', 'error');
            }
        });
    };

    const clientsData = Array.isArray(clients) ? clients : (clients?.data || []);

    const filteredClients = useMemo(() => {
        const query = searchQuery.toLowerCase();
        const altQuery = fixKeyboardLayout(query);
        return clientsData.filter(c => {
            const s = `${c.last_name} ${c.first_name} ${c.middle_name} ${c.company_name} ${c.account_number} ${c.address} ${c.phone}`.toLowerCase();
            return s.includes(query) || s.includes(altQuery);
        });
    }, [searchQuery, clients]);

    const columns = [
        {
            field: 'account_number',
            headerName: 'Лицевой счет',
            width: 150,
            valueGetter: (value, row) => row.account_number || row.account_numbers || 'Не указан',
        },
        {
            field: 'display_name',
            headerName: 'Потребитель',
            flex: 1,
            valueGetter: (value, row) => {
                if (row.client_type === 'legal') {
                    return row.company_name || '—';
                }
                return `${row.last_name || ''} ${row.first_name || ''} ${row.middle_name || ''}`.trim() || '—';
            }
        },
        {
            field: 'client_type',
            headerName: 'Тип потребителя',
            width: 200,
            valueFormatter: (value) => {
                const types = {
                    'legal': 'Юридическое лицо',
                    'individual': 'Физическое лицо'
                };
                return types[value] || value || '—';
            }
        },
        {
            field: 'address',
            headerName: 'Адрес',
            width: 250,
            valueGetter: (value, row) => row.address || 'Не указан',
        },
        {
            field: 'phone',
            headerName: 'Телефон',
            width: 150,
            valueGetter: (value, row) => row.phone || 'Не указан',
        },
    ];

    const promptDeleteClient = (id) => {
        setConfirmMeta({
            open: true,
            title: 'Удаление профиля',
            content: `Вы действительно хотите удалить клиента №${id}`,
            onConfirm: () => {
                router.post(`/admin/clients/${data.id}`, {
                    ...data,
                    _method: 'DELETE',
                }, {
                    onSuccess: () => {
                        setConfirmMeta(prev => ({ ...prev, open: false }));
                        setEditOpen(false);
                        showToast('Клиент удален');
                    }
                })
            }
        })
    }

    return (
        <AdminLayout>
            <Head title="Потребители" />
            <Box sx={{ bgcolor: '#f4f7fe', minHeight: '90vh', py: 4 }}>
                <Container maxWidth="xl">
                    <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
                        <Typography variant="h4" fontWeight="800" color="#1B2559">Потребители</Typography>
                        <Box display="flex" gap={2}>
                            <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: 300, border: '1px solid #E0E5F2', boxShadow: 'none' }}>
                                <SearchIcon sx={{ color: '#A3AED0' }} />
                                <InputBase
                                    placeholder="Поиск..."
                                    fullWidth
                                    sx={{ ml: 1 }}
                                    value={searchQuery}
                                    onChange={e => setSearchQuery(e.target.value)}
                                />
                            </Paper>
                            <Button variant="contained" startIcon={<AddIcon />} onClick={handleOpenCreate} sx={{ borderRadius: '16px', bgcolor: '#4318FF' }}>
                                Добавить
                            </Button>
                        </Box>
                    </Box>

                    <Paper sx={{ borderRadius: '20px', overflow: 'hidden', boxShadow: '0px 10px 30px rgba(0,0,0,0.02)' }}>
                        <DataGrid
                            rows={filteredClients}
                            columns={columns}
                            autoHeight
                            onRowDoubleClick={handleRowClick}
                            sx={{ border: 'none' }}
                            getRowId={(row) => row.id}
                        />
                    </Paper>

                    {/* МОДАЛКА СОЗДАНИЯ */}
                    <Dialog open={createOpen} onClose={() => {
                        setCreateOpen(false);
                        reset();
                    }} maxWidth="lg" fullWidth>
                        <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3 }}>
                            <Typography variant="h5" fontWeight="bold">Регистрация потребителя</Typography>
                        </Box>
                        <form onSubmit={handleCreateSubmit}>
                            <DialogContent sx={{ bgcolor: '#fafbfd', p: 4 }}>
                                {/* Данные потребителя */}
                                <Typography variant="h6" fontWeight="bold" sx={{ mb: 2, color: '#1B2559' }}>
                                    Данные потребителя
                                </Typography>
                                <Grid container spacing={3} sx={{ mb: 4 }}>
                                    <Grid item xs={12}>
                                        <FormControl fullWidth variant="standard">
                                            <InputLabel>Тип клиента</InputLabel>
                                            <Select value={data.client_type} onChange={e => setData('client_type', e.target.value)}>
                                                <MenuItem value="individual">Физическое лицо</MenuItem>
                                                <MenuItem value="legal">Юридическое лицо</MenuItem>
                                            </Select>
                                        </FormControl>
                                    </Grid>

                                    {data.client_type === 'legal' ? (
                                        <Grid item xs={12}>
                                            <TextField fullWidth label="Название компании" variant="standard" value={data.company_name} onChange={e => setData('company_name', e.target.value)} />
                                        </Grid>
                                    ) : (
                                        <>
                                            <Grid item xs={4}><TextField fullWidth label="Фамилия" variant="standard" value={data.last_name} onChange={e => setData('last_name', e.target.value)} /></Grid>
                                            <Grid item xs={4}><TextField fullWidth label="Имя" variant="standard" value={data.first_name} onChange={e => setData('first_name', e.target.value)} /></Grid>
                                            <Grid item xs={4}><TextField fullWidth label="Отчество" variant="standard" value={data.middle_name} onChange={e => setData('middle_name', e.target.value)} /></Grid>
                                        </>
                                    )}

                                    <Grid item xs={6}><TextField fullWidth label="Телефон" variant="standard" value={data.phone} onChange={e => setData('phone', e.target.value)} /></Grid>
                                    <Grid item xs={6}><TextField fullWidth label="Email" variant="standard" value={data.email} onChange={e => setData('email', e.target.value)} /></Grid>
                                </Grid>

                                <Divider sx={{ my: 3 }} />

                                {/* Объекты потребителя */}
                                <Typography variant="h6" fontWeight="bold" sx={{ mb: 2, color: '#1B2559' }}>
                                    Объекты потребителя
                                </Typography>

                                {data.properties.map((prop, index) => (
                                    <Card key={index} sx={{ mb: 3, borderRadius: '16px', border: '1px solid #E0E5F2', position: 'relative' }}>
                                        {data.properties.length > 1 && (
                                            <IconButton
                                                onClick={() => removeProperty(index)}
                                                sx={{ position: 'absolute', top: 8, right: 8, color: '#FF5B5B' }}
                                                size="small"
                                            >
                                                <DeleteIcon />
                                            </IconButton>
                                        )}
                                        <CardContent>
                                            <Typography variant="subtitle2" color="text.secondary" sx={{ mb: 2 }}>
                                                Объект #{index + 1}
                                            </Typography>
                                            
                                            <Grid container spacing={2}>
                                                {/* Лицевой счет и тариф */}
                                                <Grid item xs={6}>
                                                    <TextField
                                                        fullWidth
                                                        label="Лицевой счет *"
                                                        variant="standard"
                                                        value={prop.account_number}
                                                        onChange={e => updateProperty(index, 'account_number', e.target.value)}
                                                        error={!!propertyErrors[index]?.account_number}
                                                        helperText={propertyErrors[index]?.account_number}
                                                    />
                                                </Grid>
                                                <Grid item xs={6}>
                                                    <FormControl fullWidth variant="standard" error={!!propertyErrors[index]?.tariff_id}>
                                                        <InputLabel>Тариф *</InputLabel>
                                                        <Select
                                                            value={prop.tariff_id}
                                                            onChange={e => updateProperty(index, 'tariff_id', e.target.value)}
                                                        >
                                                            {tariffs?.map((t) => (
                                                                <MenuItem key={t.id} value={t.id}>{t.name} ({t.price_1} руб.)</MenuItem>
                                                            ))}
                                                        </Select>
                                                        {propertyErrors[index]?.tariff_id && (
                                                            <FormHelperText>{propertyErrors[index].tariff_id}</FormHelperText>
                                                        )}
                                                    </FormControl>
                                                </Grid>

                                                {/* Адрес */}
                                                <Grid item xs={12}>
                                                    <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block' }}>
                                                        Адрес объекта
                                                    </Typography>
                                                </Grid>
                                                
                                                <Grid item xs={12} sm={6}>
                                                    <TextField
                                                        fullWidth
                                                        label="Регион"
                                                        variant="standard"
                                                        value="Алтайский край"
                                                        disabled
                                                        sx={{ '& .MuiInputBase-input': { color: '#666' } }}
                                                    />
                                                </Grid>
                                                <Grid item xs={12} sm={6}>
                                                    <TextField
                                                        fullWidth
                                                        label="Район"
                                                        variant="standard"
                                                        value={prop.district}
                                                        onChange={e => updateProperty(index, 'district', e.target.value)}
                                                        placeholder="Не обязательно"
                                                    />
                                                </Grid>
                                                <Grid item xs={12} sm={6}>
                                                    <TextField
                                                        fullWidth
                                                        label="Населенный пункт *"
                                                        variant="standard"
                                                        value={prop.locality}
                                                        onChange={e => updateProperty(index, 'locality', e.target.value)}
                                                        error={!!propertyErrors[index]?.locality}
                                                        helperText={propertyErrors[index]?.locality}
                                                        placeholder="г. Барнаул, с. Павловск"
                                                    />
                                                </Grid>
                                                <Grid item xs={12} sm={6}>
                                                    <TextField
                                                        fullWidth
                                                        label="Улица *"
                                                        variant="standard"
                                                        value={prop.street}
                                                        onChange={e => updateProperty(index, 'street', e.target.value)}
                                                        error={!!propertyErrors[index]?.street}
                                                        helperText={propertyErrors[index]?.street}
                                                        placeholder="Ленина, Мира"
                                                    />
                                                </Grid>
                                                <Grid item xs={4}>
                                                    <TextField
                                                        fullWidth
                                                        label="Дом *"
                                                        variant="standard"
                                                        value={prop.house}
                                                        onChange={e => updateProperty(index, 'house', e.target.value)}
                                                        error={!!propertyErrors[index]?.house}
                                                        helperText={propertyErrors[index]?.house}
                                                    />
                                                </Grid>
                                                <Grid item xs={4}>
                                                    <TextField
                                                        fullWidth
                                                        label="Корпус"
                                                        variant="standard"
                                                        value={prop.building}
                                                        onChange={e => updateProperty(index, 'building', e.target.value)}
                                                        placeholder="Не обязательно"
                                                    />
                                                </Grid>
                                                <Grid item xs={4}>
                                                    <TextField
                                                        fullWidth
                                                        label="Квартира"
                                                        variant="standard"
                                                        value={prop.apartment}
                                                        onChange={e => updateProperty(index, 'apartment', e.target.value)}
                                                        placeholder="Не обязательно"
                                                    />
                                                </Grid>
                                            </Grid>
                                        </CardContent>
                                    </Card>
                                ))}

                                <Button
                                    variant="outlined"
                                    startIcon={<AddIcon />}
                                    onClick={addProperty}
                                    sx={{ borderRadius: '12px', borderColor: '#4318FF', color: '#4318FF' }}
                                >
                                    + Ещё объект
                                </Button>
                            </DialogContent>
                            <DialogActions sx={{ p: 3 }}>
                                <Button onClick={() => setCreateOpen(false)}>Отмена</Button>
                                <Button type="submit" variant="contained" disabled={processing} sx={{ bgcolor: '#4318FF' }}>Создать</Button>
                            </DialogActions>
                        </form>
                    </Dialog>

                    <ClientCard
                        open={editOpen} onClose={() => setEditOpen(false)}
                        data={data} setData={setData} errors={errors}
                        showToast={showToast} onDeleteClient={promptDeleteClient}
                        tariffs={tariffs}
                    />

                    <ConfirmDialog
                        open={confirmMeta.open} title={confirmMeta.title} content={confirmMeta.content}
                        onConfirm={confirmMeta.onConfirm} onClose={() => setConfirmMeta(p => ({ ...p, open: false }))}
                    />

                    <Snackbar open={toast.open} autoHideDuration={3000} onClose={() => setToast(p => ({ ...p, open: false }))}>
                        <Alert severity={toast.severity} variant="filled">{toast.message}</Alert>
                    </Snackbar>
                </Container>
            </Box>
        </AdminLayout>
    );
}

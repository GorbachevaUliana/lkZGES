import React, { useState, useEffect } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import {
    Container, Grid, Card, CardContent, Typography,
    TextField, Button, Table, TableBody, TableCell,
    TableContainer, TableHead, TableRow, Paper, Chip,
    Box, Divider, Alert, Select, MenuItem, FormControl,
    InputLabel, Tabs, Tab
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import CalculateIcon from '@mui/icons-material/Calculate';
import DownloadIcon from '@mui/icons-material/GetApp';
import HomeIcon from '@mui/icons-material/Home';
import ClientLayout from '@/Layouts/ClientLayout';

import UICard from '@/Components/UI/Card';
import UIButton from '@/Components/UI/Button';
import InfoBox from '@/Components/UI/InfoBox';

export default function Index({
    client,
    property,
    activeProperties = [],
    currentTariff,
    lastReadingValue,
    history,
    auth,
    application
}) {
    const [consumed, setConsumed] = useState(0);
    const [totalSum, setTotalSum] = useState(0);
    const [selectedPropertyId, setSelectedPropertyId] = useState(property?.id || '');

    const { data, setData, post, processing, errors, reset } = useForm({
        current_value: '',
        reading_date: new Date().toISOString().split('T')[0],
        property_id: property?.id || '',
    });

    // Обновляем property_id при смене объекта
    useEffect(() => {
        if (selectedPropertyId && selectedPropertyId !== property?.id) {
            router.visit(route('client.readings') + '?property=' + selectedPropertyId, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    }, [selectedPropertyId]);

    useEffect(() => {
        const current = Number(data.current_value) || 0;
        const last = Number(lastReadingValue) || 0;

        if (current > last) {
            const diff = current - last;
            setConsumed(diff);

            // ИСПРАВЛЕНО: Тариф берётся из объекта (property), а не из клиента!
            if (currentTariff) {
                const p1 = Number(currentTariff.price_1) || 0;
                const p2 = Number(currentTariff.price_2) || 0;
                const p3 = Number(currentTariff.price_3) || 0;

                let sum = 0;
                if (diff <= 3900) sum = diff * p1;
                else if (diff <= 6000) sum = (3900 * p1) + ((diff - 3900) * p2);
                else sum = (3900 * p1) + (2100 * p2) + ((diff - 6000) * p3);

                setTotalSum(sum.toFixed(2));
            }
        } else {
            setConsumed(0);
            setTotalSum(0);
        }
    }, [data.current_value, lastReadingValue, currentTariff]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('client.readings.store'), {
            onSuccess: () => reset('current_value'),
        });
    };

    const columns = [
        {
            field: 'reading_date',
            headerName: 'Дата',
            flex: 1,
            valueGetter: (params) => new Date(params).toLocaleDateString('ru-RU')
        },
        {
            field: 'current_value',
            headerName: 'Показания',
            flex: 1,
            renderCell: (params) => `${params.value} кВт*ч`
        },
        {
            field: 'consumed',
            headerName: 'Расход',
            flex: 1,
            valueGetter: (params, row) => row.current_value - row.previous_value,
            renderCell: (params) => `${params.value} кВт*ч`
        },
        {
            field: 'total_sum',
            headerName: 'Сумма',
            flex: 1,
            renderCell: (params) => (
                <Typography sx={{ fontWeight: 'bold' }}>
                    {params.value ? `${params.value} ₽` : '—'}
                </Typography>
            )
        },
        {
            field: 'is_paid',
            headerName: 'Статус',
            flex: 1,
            renderCell: (params) => (
                <Chip
                    label={params.value ? "Оплачено" : "К оплате"}
                    color={params.value ? "success" : "warning"}
                    size="small"
                    sx={{ borderRadius: '8px', fontWeight: 'bold', fontSize: '10px' }}/>
            )
        },
        {
            field: 'actions',
            headerName: 'Действие',
            flex: 1,
            sortable: false,
            align: 'right',
            renderCell: (params) => (
                !params.row.is_paid ? (
                    <Button
                        variant="contained"
                        size="small"
                        onClick={() => router.post(route('client.readings.pay', params.row.id))}
                        sx={{ bgcolor: '#4318FF', borderRadius: '8px', textTransform: 'none' }}>
                        Оплатить
                    </Button>
                ) : (
                    <Button
                        size="small"
                        startIcon={<DownloadIcon />}
                        sx={{ color: '#A3AED0', textTransform: 'none' }}>
                        PDF
                    </Button>
                )
            )
        }
    ];

    // Получаем название тарифа из объекта
    const tariffName = property?.tariff?.name || currentTariff?.name || 'Не указан';

    return (
        <ClientLayout user={auth.user}
            title="Показания и оплата"
            application={application}>
            <Grid container spacing={3}>
                {/* Форма подачи показаний - ФИКСИРОВАННАЯ ШИРИНА */}
                <Grid item xs={12} md={5} sx={{ minWidth: { md: '41.666%' } }}>
                    <Paper sx={{
                        p: 3,
                        borderRadius: '20px',
                        boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)',
                        width: '100%',
                        boxSizing: 'border-box'
                    }}>
                        {/* Выбор объекта если их несколько */}
                        {activeProperties.length > 1 && (
                            <FormControl fullWidth sx={{ mb: 2 }}>
                                <InputLabel>Выберите объект</InputLabel>
                                <Select
                                    value={selectedPropertyId}
                                    label="Выберите объект"
                                    onChange={(e) => setSelectedPropertyId(e.target.value)}
                                >
                                    {activeProperties.map((prop) => (
                                        <MenuItem key={prop.id} value={prop.id}>
                                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                                <HomeIcon fontSize="small" sx={{ color: '#4318FF' }} />
                                                <Box>
                                                    <Typography variant="body2">{prop.address}</Typography>
                                                    <Typography variant="caption" color="text.secondary">
                                                        ЛС: {prop.account_number}
                                                        {prop.tariff && ` | Тариф: ${prop.tariff.name}`}
                                                    </Typography>
                                                </Box>
                                            </Box>
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        )}

                        {/* Информация о выбранном объекте */}
                        {property && (
                            <Box sx={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 1,
                                mb: 2,
                                p: 1.5,
                                bgcolor: '#F4F7FE',
                                borderRadius: '12px'
                            }}>
                                <HomeIcon sx={{ color: '#4318FF' }} />
                                <Box>
                                    <Typography variant="body2" fontWeight="bold">
                                        {property.address}
                                    </Typography>
                                    <Typography variant="caption" color="text.secondary">
                                        ЛС: {property.account_number}
                                    </Typography>
                                </Box>
                            </Box>
                        )}

                        <Typography variant="h6" gutterBottom color="primary">
                            Передать показания
                        </Typography>
                        <Typography variant="body2" color="textSecondary" sx={{ mb: 2 }}>
                            Предыдущее значение: <b>{lastReadingValue} кВт*ч</b>
                        </Typography>

                        <form onSubmit={handleSubmit}>
                            <TextField
                                fullWidth
                                label="Текущее показание"
                                type="number"
                                value={data.current_value}
                                onChange={e => setData('current_value', e.target.value)}
                                error={!!errors.current_value}
                                helperText={errors.current_value}
                                sx={{ mb: 2 }}
                            />
                            <TextField
                                fullWidth
                                label="Дата снятия"
                                type="date"
                                value={data.reading_date}
                                onChange={e => setData('reading_date', e.target.value)}
                                InputLabelProps={{ shrink: true }}
                                sx={{ mb: 3 }}
                            />

                            {/* Блок расчёта - ФИКСИРОВАННАЯ ВЫСОТА */}
                            <Box sx={{
                                p: 2,
                                bgcolor: '#F4F7FE',
                                borderRadius: '12px',
                                mb: 2,
                                minHeight: '80px',
                                display: 'flex',
                                flexDirection: 'column',
                                justifyContent: 'center'
                            }}>
                                {consumed > 0 ? (
                                    <>
                                        <Typography variant="body2">
                                            Расход: <b>{consumed} кВт*ч</b>
                                        </Typography>
                                        <Typography
                                            variant="h5"
                                            fontWeight="bold"
                                            sx={{ color: '#2E7D32' }}>
                                            {totalSum} ₽
                                        </Typography>
                                    </>
                                ) : (
                                    <Typography variant="body2" color="text.secondary">
                                        Введите показания для расчёта
                                    </Typography>
                                )}
                            </Box>

                            <Button
                                fullWidth
                                variant="contained"
                                sx={{
                                    borderRadius: '12px',
                                    bgcolor: '#4318FF'
                                }}
                                type="submit"
                                disabled={processing || parseFloat(data.current_value) < parseFloat(lastReadingValue)}
                            >
                                Отправить показания
                            </Button>
                        </form>
                    </Paper>
                </Grid>

                {/* Тариф и история */}
                <Grid item xs={12} md={7}>
                    <Paper sx={{
                        p: 3,
                        borderRadius: '20px',
                        border: '1px solid #E0E5F2'
                    }}>
                        <CardContent>
                            {/* ИСПРАВЛЕНО: Тариф показывается из объекта (property) */}
                            <Typography variant="h6" fontWeight="bold">
                                Тариф объекта: {tariffName}
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                                Тариф привязан к данному объекту энергоснабжения
                            </Typography>
                            <Divider sx={{ my: 1 }} />
                            {currentTariff ? (
                                <Grid container spacing={2} sx={{ mt: 1 }}>
                                    <Grid item xs={4}>
                                        <Typography variant="caption" display="block">До 3900 кВт</Typography>
                                        <Typography variant="h6">{currentTariff.price_1} ₽</Typography>
                                    </Grid>
                                    <Grid item xs={4}>
                                        <Typography variant="caption" display="block">3901 - 6000 кВт</Typography>
                                        <Typography variant="h6">{currentTariff.price_2} ₽</Typography>
                                    </Grid>
                                    <Grid item xs={4}>
                                        <Typography variant="caption" display="block">Свыше 6000 кВт</Typography>
                                        <Typography variant="h6">{currentTariff.price_3} ₽</Typography>
                                    </Grid>
                                </Grid>
                            ) : (
                                <Alert severity="warning" sx={{ mt: 2 }}>
                                    Тариф не указан для данного объекта. Обратитесь в службу поддержки.
                                </Alert>
                            )}
                        </CardContent>
                    </Paper>

                    {/* История показаний - ТОЛЬКО ДЛЯ ВЫБРАННОГО ОБЪЕКТА */}
                    <Paper sx={{
                        mt: 3,
                        borderRadius: '20px',
                        overflow: 'hidden',
                        border: 'none',
                        boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.08)'
                    }}>
                        <Box sx={{ p: 2, bgcolor: '#F4F7FE' }}>
                            <Typography variant="subtitle2" fontWeight="bold">
                                История показаний
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                                Только для объекта: {property?.address || 'Не выбран'}
                            </Typography>
                        </Box>
                        <DataGrid
                            rows={history}
                            columns={columns}
                            autoHeight
                            disableRowSelectionOnClick
                            initialState={{
                                sorting: {
                                    sortModel: [{ field: 'reading_date', sort: 'desc' }],
                                },
                                pagination: { paginationModel: { pageSize: 5 } },
                            }}
                            pageSizeOptions={[5, 10, 20]}
                            sx={{
                                border: 'none',

                                '& .MuiDataGrid-columnHeaders': {
                                    bgcolor: '#F4F7FE',
                                    borderBottom: 'none'
                                },

                                '& .MuiDataGrid-cell': {
                                    borderBottom: '1px solid #F4F7FE',
                                    display: 'flex',
                                    alignItems: 'center',
                                },

                                '& .MuiDataGrid-columnHeader': {
                                    display: 'flex',
                                    alignItems: 'center'
                                },

                                '& .MuiDataGrid-footerContainer': {
                                    borderTop: 'none'
                                }
                            }}/>
                    </Paper>
                </Grid>
            </Grid>
        </ClientLayout>
    );
}

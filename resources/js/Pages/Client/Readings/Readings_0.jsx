import React, { useState, useEffect } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { 
    Container, Grid, Card, CardContent, Typography, 
    TextField, Button, Table, TableBody, TableCell, 
    TableContainer, TableHead, TableRow, Paper, Chip, 
    Box, Divider, Alert 
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import CalculateIcon from '@mui/icons-material/Calculate';
import DownloadIcon from '@mui/icons-material/GetApp';
import ClientLayout from '@/Layouts/ClientLayout';

import UICard from '@/Components/UI/Card';
import UIButton from '@/Components/UI/Button';
import InfoBox from '@/Components/UI/InfoBox';

export default function Index({ client, currentTariff, lastReadingValue, history, auth, application }) {
    const [consumed, setConsumed] = useState(0);
    const [totalSum, setTotalSum] = useState(0);

    const { data, setData, post, processing, errors, reset } = useForm({
        current_value: '',
        reading_date: new Date().toISOString().split('T')[0],
    });

    useEffect(() => {
        console.log("Ввод:", data.current_value, "Тип:", typeof data.current_value);

        const current = Number(data.current_value) || 0;
        const last = Number(lastReadingValue) || 0;

        if (current > last) {
            const diff = current - last;
            setConsumed(diff);

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
                    {params.value} ₽
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

    return (
        <ClientLayout user={auth.user}
            title="Показания и оплата"
            application={application}>
            <Grid container spacing={3}>
                {/* Форма подачи показаний */}
                <Grid item xs={12} md={5}>
                    <Paper sx={{ 
                        p: 3, 
                        borderRadius: '20px', 
                        boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' 
                    }}>
                        <CardContent>
                            <Typography variant="h6" gutterBottom color="primary">Передать показания</Typography>
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
                                    sx={{ mb: 2 }}/>
                                <TextField
                                    fullWidth
                                    label="Дата снятия"
                                    type="date"
                                    value={data.reading_date}
                                    onChange={e => setData('reading_date', e.target.value)}
                                    InputLabelProps={{ shrink: true }}
                                    sx={{ mb: 3 }}/>

                                {consumed > 0 && (
                                    <Box sx={{ 
                                        p: 2, 
                                        bgcolor: '#F4F7FE', 
                                        borderRadius: '12px', 
                                        mb: 2 
                                    }}>
                                        <Typography variant="body2">
                                            Расход: <b>{consumed} кВт*ч</b>
                                        </Typography>

                                        <Typography 
                                            variant="h5" 
                                            fontWeight="bold"
                                            sx={{ color: '#2E7D32' }}>
                                            {totalSum} ₽
                                        </Typography>
                                    </Box>
                                )}
                                <Button 
                                    fullWidth 
                                    variant="contained"
                                    sx={{ 
                                        borderRadius: '12px',
                                        bgcolor: '#4318FF'
                                    }}
                                    type="submit"
                                    disabled={processing || parseFloat(data.current_value) < parseFloat(lastReadingValue)}>
                                    Отправить показания
                                </Button>
                            </form>
                        </CardContent>
                    </Paper>
                </Grid>
                <Grid item xs={12} md={7}>
                    <Paper sx={{ 
                        p: 3, 
                        borderRadius: '20px',
                        border: '1px solid #E0E5F2'
                    }}>
                        <CardContent>
                            <Typography variant="h6" fontWeight="bold">
                                Ваш тариф: {client.tariff_category}
                            </Typography>
                            <Divider sx={{ my: 1 }} />
                            <Grid container spacing={2} sx={{ mt: 1 }}>
                                <Grid item xs={4}>
                                    <Typography variant="caption" display="block">До 3900 кВт</Typography>
                                    <Typography variant="h6">{currentTariff?.price_1} ₽</Typography>
                                </Grid>
                                <Grid item xs={4}>
                                    <Typography variant="caption" display="block">3901 - 6000 кВт</Typography>
                                    <Typography variant="h6">{currentTariff?.price_2} ₽</Typography>
                                </Grid>
                                <Grid item xs={4}>
                                    <Typography variant="caption" display="block">Свыше 6000 кВт</Typography>
                                    <Typography variant="h6">{currentTariff?.price_3} ₽</Typography>
                                </Grid>
                            </Grid>
                        </CardContent>
                    </Paper>
                    <Paper sx={{ 
                        mt: 3, 
                        borderRadius: '20px', 
                        overflow: 'hidden', 
                        border: 'none', 
                        boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.08)' 
                    }}>
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
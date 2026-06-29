import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { DataGrid } from '@mui/x-data-grid';
import { Paper, Typography, Box, Chip, Button, IconButton, Container, InputBase} from '@mui/material';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import VisibilityIcon from '@mui/icons-material/Visibility';
import { 
    Add as AddIcon, Search as SearchIcon
} from '@mui/icons-material';

export default function Index({ auth, readings, data, id}) {
    const columns = [
        { field: 'id', headerName: 'ID', width: 70 },
        { 
            field: 'client_name', 
            headerName: 'Клиент', 
            flex: 1.5,
            valueGetter: (params, row) => row.client?.user?.name || 'Удален'
        },
        { 
            field: 'address', 
            headerName: 'Адрес', 
            flex: 2,
            valueGetter: (params, row) => row.client?.address || '—'
        },
        { 
            field: 'reading_date', 
            headerName: 'Дата снятия', 
            flex: 1,
            valueGetter: (params) => new Date(params).toLocaleDateString('ru-RU')
        },
        { 
            field: 'current_value', 
            headerName: 'Показание', 
            flex: 1,
            renderCell: (params) => `${params.value} кВт*ч`
        },
        { 
            field: 'total_sum', 
            headerName: 'Сумма', 
            flex: 1,
            renderCell: (params) => <b>{params.value} ₽</b>
        },
        { 
            field: 'is_paid', 
            headerName: 'Статус', 
            flex: 1,
            renderCell: (params) => (
                <Chip 
                    label={params.value ? "Оплачено" : "Ожидает"} 
                    color={params.value ? "success" : "warning"}
                    variant="outlined"
                    size="small"/>
            )
        },
        {
            field: 'actions',
            headerName: 'Действия',
            flex: 1,
            sortable: false,
            renderCell: (params) => (
                <Box>
                    {!params.row.is_paid && (
                        <IconButton 
                            color="success" 
                            // onClick={() => router.patch(route('admin.readings.verify', params.row.id))}
                            onClick={() => {
                                router.post(`/admin/readings/${params.row.id}/verify`, {
                                    ...data,
                                    _method:'PATCH',
                                }, {
                                    onSuccess: () => showToast('Квитанция оплачена'),
                                    forceFormData: true
                                });
                            }}>
                            <CheckCircleIcon />
                        </IconButton>
                    )}
                </Box>
            )
        }
    ];

    return (
        <AdminLayout user={auth.user} title="Все показания">
            <Head title="Реестр показаний" />
            <Box sx={{ bgcolor: '#f4f7fe', minHeight: '90vh', py: 4 }}>
                <Container maxWidth="xl">
                    <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
                        <Typography variant="h4" fontWeight="800" color="#1B2559">Реестр показаний</Typography>
                    </Box>

                    <Paper sx={{ borderRadius: '20px', overflow: 'hidden', boxShadow: '0px 20px 50px rgba(112, 144, 176, 0.15)' }}>
                        <DataGrid 
                            rows={readings.data ?? readings} 
                            columns={columns} 
                            autoHeight
                            initialState={{
                                pagination: { paginationModel: { pageSize: 15 } },
                                sorting: { sortModel: [{ field: 'reading_date', sort: 'desc' }] }
                            }}
                            sx={{ border: 'none' }}/>
                    </Paper>
                </Container>
            </Box>
        </AdminLayout>
    );
}
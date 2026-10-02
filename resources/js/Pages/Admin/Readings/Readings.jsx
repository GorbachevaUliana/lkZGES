import React, {useState, useRef, useEffect} from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { DataGrid } from '@mui/x-data-grid';
import { Paper, Typography, Box, Chip, Button, IconButton, Container, InputBase} from '@mui/material';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import VisibilityIcon from '@mui/icons-material/Visibility';
import { 
    Add as AddIcon, Search as SearchIcon
} from '@mui/icons-material';

export default function Index({ auth, readings, data, id, search = ''}) {
    const [searchQuery, setSearchQuery] = useState(search);

    const readingsRows = readings.data ?? readings;
    const readingsCurrentPage = readings?.current_page || 1;
    const readingsTotal = readings?.total ?? readingsRows.length;
    const readingsPerPage = readings?.per_page || 50;

    const goToReadingsPage = (zeroBasedPage) => {
        router.get(route('admin.readings.index'), {
            page: zeroBasedPage + 1,
            search: searchQuery,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Поиск уходит на сервер: фильтровать загруженную страницу нельзя,
    // иначе найдётся только то, что попало в текущие 50 записей.
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timer = setTimeout(() => {
            router.get(route('admin.readings.index'), { search: searchQuery, page: 1 }, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 400);

        return () => clearTimeout(timer);
    }, [searchQuery]);

    const columns = [
        { field: 'id', headerName: 'ID', width: 70 },
        {
            field: 'account_number',
            headerName: 'Лицевой счёт',
            width: 130,
            valueGetter: (params, row) => row.property?.account_number || '—'
        },
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
            valueGetter: (params, row) => row.property?.address || '—'
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
                        <Typography variant="h4" fontWeight="800" color="#1B2559" sx={{ fontSize: { xs: '1.6rem', md: '2.125rem' } }}>
                            Реестр показаний
                        </Typography>
                        <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: { xs: '100%', md: 350 }, boxShadow: 'none', border: '1px solid #E0E5F2', flexShrink: 0 }}>
                            <SearchIcon sx={{ color: '#A3AED0' }} />
                            <InputBase
                                placeholder="Поиск по счёту, адресу, фамилии..."
                                fullWidth
                                sx={{ ml: 1 }}
                                value={searchQuery}
                                onChange={e => setSearchQuery(e.target.value)}
                            />
                        </Paper>
                    </Box>

                    <Paper sx={{ borderRadius: '20px', overflow: 'hidden', boxShadow: '0px 20px 50px rgba(112, 144, 176, 0.15)' }}>
                        <DataGrid 
                            rows={readingsRows} 
                            columns={columns} 
                            autoHeight
                            paginationMode="server"
                            rowCount={readingsTotal}
                            paginationModel={{ page: readingsCurrentPage - 1, pageSize: readingsPerPage }}
                            onPaginationModelChange={(model) => goToReadingsPage(model.page)}
                            pageSizeOptions={[readingsPerPage]}
                            initialState={{
                                sorting: { sortModel: [{ field: 'reading_date', sort: 'desc' }] }
                            }}
                            sx={{ border: 'none' }}/>
                    </Paper>
                </Container>
            </Box>
        </AdminLayout>
    );
}

//Добавить плашку "В разработке" под оплатой
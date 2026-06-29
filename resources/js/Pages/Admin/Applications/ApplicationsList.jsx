import React, { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    Container, Typography, Paper, Box, Button, Chip,
    Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
    IconButton, InputBase, Tabs, Tab, Dialog, DialogContent, DialogActions,
    TextField, Snackbar, Alert, Tooltip
} from '@mui/material';
import {
    Search as SearchIcon,
    Description as DescriptionIcon,
    CheckCircle as ApproveIcon,
    HourglassEmpty as ProcessIcon,
    CloudUpload as UploadIcon
} from '@mui/icons-material';
import { DataGrid } from '@mui/x-data-grid';
import AdminLayout from '@/Layouts/AdminLayout';
import ApplicationCard from '@/Components/Admin/ApplicationCard';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';

const fixKeyboardLayout = (text) => {
    if (!text) return '';
    const map = {
        'q':'й', 'w':'ц', 'e':'у', 'r':'к', 't':'е', 'y':'н', 'u':'г', 'i':'ш', 'o':'щ', 'p':'з',
        '[':'х', ']':'ъ', 'a':'ф', 's':'ы', 'd':'в', 'f':'а', 'g':'п', 'h':'р', 'j':'о', 'k':'л',
        'l':'д', ';':'ж', "'":'э', 'z':'я', 'x':'ч', 'c':'с', 'v':'м', 'b':'и', 'n':'т', 'm':'ь',
        ',':'б', '.':'ю'
    };
    return text.toLowerCase().split('').map(char => map[char] || char).join('');
};

export default function ApplicationsList({ auth, applications, statuses, clientTypes, tariffs }) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [selectedApplication, setSelectedApplication] = useState(null);
    const [cardOpen, setCardOpen] = useState(false);
    const [toast, setToast] = useState({ open: false, message: '', severity: 'success' });
    const [confirmMeta, setConfirmMeta] = useState({ open: false, title: '', content: '', onConfirm: () => {} });
    const appData = Array.isArray(applications) ? applications : (applications.data || []);

    const showToast = (message, severity = 'success') => {
        setToast({ open: true, message, severity });
    };

    const filteredApplications = useMemo(() => {
        const query = searchQuery.toLowerCase();
        const altQuery = fixKeyboardLayout(query);
        
        return appData.filter(app => {
            const matchesSearch = 
                app.applicant_name?.toLowerCase().includes(query) ||
                app.applicant_name?.toLowerCase().includes(altQuery) ||
                app.user_email?.toLowerCase().includes(query) ||
                app.user_email?.toLowerCase().includes(altQuery);
            
            // const matchesStatus = statusFilter === 'all' || app.status === statusFilter;
            let matchesStatus = statusFilter === 'all' || app.status === statusFilter;
            if (statusFilter === 'pending') {
                matchesStatus = app.status === 'new' || app.status === 'pending';
            }
            
            return matchesSearch && matchesStatus;
        });
    }, [searchQuery, statusFilter, appData]);

    // const stats = useMemo(() => {
    //     return {
    //         all: appData.length,
    //         pending: appData.filter(a => a.status === 'pending').length,
    //         processing: appData.filter(a => a.status === 'processing').length,
    //         approved: appData.filter(a => a.status === 'approved').length,
    //     };
    // }, [appData]);

    const statusColors = {
        pending: { bg: '#FFF3E0', color: '#F57C00' },
        processing: { bg: '#E3F2FD', color: '#1976D2' },
        approved: { bg: '#E8F5E9', color: '#2E7D32' },
        rejected: { bg: '#FFEBEE', color: '#C62828' },
    };
    const handleRowDoubleClick = async (params) => {
        try {
            const response = await fetch(`/admin/applications/${params.row.id}`);
            const data = await response.json();
            setSelectedApplication(data.application);
            setCardOpen(true);
        } catch (error) {
            console.error('Error loading application:', error);
            showToast('Ошибка при загрузке данных заявки', 'error');
        }
    };
    const handleTakeToWork = (id) => {
        setConfirmMeta({
            open: true,
            title: 'Взять заявку в работу',
            content: 'Вы хотите взять эту заявку в работу?',
            onConfirm: () => {
                router.post(`/admin/applications/${id}/take-to-work`, {}, {
                    onSuccess: () => {
                        setConfirmMeta(prev => ({ ...prev, open: false }));
                        showToast('Заявка взята в работу');
                    },
                    onError: () => showToast('Ошибка', 'error')
                });
            }
        });
    };

    const columns = [
        {
            field: 'id',
            headerName: '№',
            width: 60
        },
        { 
            field: 'applicant_name',
            headerName: 'Заявитель',
            flex: 1,
            renderCell: (params) =>  params.value
        },
        {
            field: 'client_type',
            headerName: 'Тип потребителя',
            width: 200,
            valueFormatter: (value) => {
                const types = {
                    'legal' : 'Юридическое лицо',
                    'individual' : 'Физическое лицо'
                };

                return types[value] || value;
            }
        },
        { field: 'created_at', headerName: 'Дата подачи', width: 140},
        { 
            field: 'status_name',
            headerName: 'Статус',
            width: 140,
            renderCell: (params) => {
                const colors = statusColors[params.row.status] || {bg: '#F5F5F5', color: '#666'};
                const statusLabels ={
                    pending: 'Ожидает',
                    processing: 'В работе',
                    approved: 'Одобрена',
                    rejected: 'Отклонена'
                };

                const label = params.value || statusLabels[params.row.status] || params.row.status;

                return(
                    <Chip
                        label={label}
                        sx = {{
                            bgcolor: colors.bg,
                            color: colors.color,
                            fontWeight: 'bold',
                            minWidth: '100px',
                        }}/>
                );
            }
        },
        {
            field: 'generated_pdf_url',
            headerName: 'Заявка',
            width: 90,
            sortable: false,
            renderCell: (params) => params.value ? (
                <Tooltip title="Скачать заявку">
                    <IconButton
                        href={params.value}
                        target="_blank"
                        color="primary"
                        onClick={(e) => e.stopPropagation()}>
                        <DescriptionIcon fontSize="small"/>
                    </IconButton>
                </Tooltip>
            ) : <Typography variant="caption" color="text.secondary">Заявка не сформирована</Typography>
        },
        {
            field: 'account_number',
            headerName: 'Лицевой счет',
            width: 130,
            renderCell: (params) => params.row.account_number || 'Не указан' 
        },
        {
            field: 'take_to_work',
            headerName: '',
            minwidth: 50,
            sortable: false,
            renderCell: (params) => params.row.status === 'pending' ? (
                <Tooltip title="Взять в работу">
                    <IconButton
                        onClick={(e) => {
                            e.stopPropagation();
                            handleTakeToWork(params.row.id);
                        }}>
                        <ProcessIcon fontSize="small" />
                    </IconButton>
                </Tooltip>
            ) : 'В работе'
        },
    ];

    return (
        <AdminLayout>
            <Head title="Заявки на заключение договора" />
            <Box sx={{ bgcolor: '#f4f7fe', minHeight: '90vh', py: 4 }}>
                <Container maxWidth="xl">
                    <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
                        <Typography variant="h4" fontWeight="800" color="#1B2559">
                            Заявки на заключение договора
                        </Typography>
                        <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: 350, boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                            <SearchIcon sx={{ color: '#A3AED0' }} />
                            <InputBase
                                placeholder="Поиск по имени или email..."
                                fullWidth
                                sx={{ ml: 1 }}
                                value={searchQuery}
                                onChange={e => setSearchQuery(e.target.value)}/>
                        </Paper>
                    </Box>

                    <Box display="flex" gap={2} mb={3}>
                        {[
                            { label: 'Все', value: stats.all, status: 'all' },
                            { label: 'Ожидают', value: stats.pending, status: 'pending', color: '#F57C00' },
                            { label: 'В работе', value: stats.processing, status: 'processing', color: '#1976D2' },
                            { label: 'Одобрены', value: stats.approved, status: 'approved', color: '#2E7D32' },
                        ].map(item => (
                            <Paper
                                key={item.status}
                                sx={{
                                    px: 3,
                                    py: 2,
                                    borderRadius: '15px',
                                    cursor: 'pointer',
                                    border: statusFilter === item.status ? '2px solid #4318FF' : '1px solid #E0E5F2',
                                    bgcolor: statusFilter === item.status ? '#F4F7FE' : '#fff',
                                    minWidth: '120px',
                                    width: '140px',
                                    height: '80px',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    justifyContent: 'center',
                                    alignItems: 'center',
                                    transition: 'all 0.2s ease',
                                    '&:hover': {
                                        boxShadow: '0 4px 12px rgba(67, 24, 255, 0.1)',
                                    }
                                }}
                                onClick={() => setStatusFilter(item.status)}>
                                <Typography variant="h4" fontWeight="bold" color={item.color || '#1B2559'}>
                                    {item.value}
                                </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mt: 0.5 }}>
                                    {item.label}
                                </Typography>
                            </Paper>
                        ))}
                    </Box>

                    {/* Таблица */}
                    <Paper sx={{ borderRadius: '20px', overflow: 'hidden', boxShadow: '0px 10px 30px rgba(0,0,0,0.02)' }}>
                        <DataGrid
                            rows={filteredApplications}
                            columns={columns}
                            autoHeight
                            onRowDoubleClick={handleRowDoubleClick}
                            disableRowSelectionOnClick
                            sx={{ border: 'none'}}/>
                    </Paper>
                    {/* Карточка заявки */}
                    <ApplicationCard
                        open={cardOpen}
                        onClose={() => setCardOpen(false)}
                        application={selectedApplication}
                        statuses={statuses}
                        showToast={showToast}
                        tariffs={tariffs}/>

                    {/* Диалог подтверждения */}
                    <ConfirmDialog
                        open={confirmMeta.open}
                        title={confirmMeta.title}
                        content={confirmMeta.content}
                        onConfirm={confirmMeta.onConfirm}
                        onClose={() => setConfirmMeta(p => ({ ...p, open: false }))}/>

                    {/* Toast уведомления */}
                    <Snackbar
                        open={toast.open}
                        autoHideDuration={3000}
                        onClose={() => setToast(p => ({ ...p, open: false }))}
                        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}>
                        <Alert severity={toast.severity} variant="filled" sx={{ borderRadius: '12px' }}>
                            {toast.message}
                        </Alert>
                    </Snackbar>
                </Container>
            </Box>
        </AdminLayout>
    );
}

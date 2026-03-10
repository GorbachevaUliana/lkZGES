import React, { useState, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Container, Typography, Paper, Button, Box, 
    Dialog, DialogContent, TextField, DialogActions, 
    Tabs, Tab, Table, TableBody, TableCell,
    TableContainer, TableHead, TableRow, IconButton,
    Snackbar, Alert, InputBase } from '@mui/material';
import Grid from '@mui/material/Grid'; 
import { Add as AddIcon, Description as DescriptionIcon,
    Delete as DeleteIcon, Save as SaveIcon,
    CloudUpload as CloudUploadIcon,
    Search as SearchIcon } from '@mui/icons-material';
import SpeedIcon from '@mui/icons-material/Speed';
import { AddressSuggestions } from 'react-dadata';
import { DataGrid } from '@mui/x-data-grid';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import ClientCard from '@/Components/Admin/ClientCard';

const fixKeyboardLayout = (text) => {
    if (!text) return '';
    const map = {'q':'й', 'w':'ц', 'e':'у', 'r':'к', 't':'е', 'y':'н', 'u':'г', 'i':'ш', 'o':'щ', 'p':'з', '[':'х', ']':'ъ', 'a':'ф', 's':'ы', 'd':'в', 'f':'а', 'g':'п', 'h':'р', 'j':'о', 'k':'л', 'l':'д', ';':'ж', "'":'э', 'z':'я', 'x':'ч', 'c':'с', 'v':'м', 'b':'и', 'n':'т', 'm':'ь', ',':'б', '.':'ю'};
    return text.toLowerCase().split('').map(char => map[char] || char).join('');
};

const DadataInput = React.forwardRef((props, ref) => (
    <TextField {...props} inputRef={ref} fullWidth variant="standard" label={props.label || "Адрес"} />
));

function TabPanel({ children, value, index }) {
    return value === index ? <Box sx={{ p: 3 }}>{children}</Box> : null;
}

export default function ClientsList({ auth, clients }) {
    const [editOpen, setEditOpen] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [tabValue, setTabValue] = useState(0);
    const [searchQuery, setSearchQuery] = useState('');
    const [toast, setToast] = useState({ open: false, message: '', severity: 'success' });
    const [confirmMeta, setConfirmMeta] = useState({ open: false, title: '', content: '', onConfirm: () => {} });

    const DADATA_API_KEY = "cd4b88f14527df99bbafecb1c09789391eb6f2ff";

    const { data, setData, post, put, reset, processing, errors } = useForm({
        id: '', account_number: '', last_name: '', first_name: '',
        middle_name: '', address: '', phone: '', email: '', documents: [],
    });

    const showToast = (message, severity = 'success') => setToast({ open: true, message, severity });

    const handleOpenCreate = () => {
        reset();
        setData({
            id: '', account_number: '', last_name: '', first_name: '',
            middle_name: '', address: '', phone: '', email: '', documents: [],
        });
        setCreateOpen(true);
    };

    const handleRowClick = (params) => {
        setData({
            id: params.row.id,
            account_number: params.row.account_number || '',
            last_name: params.row.last_name || '',
            first_name: params.row.first_name || '',
            middle_name: params.row.middle_name || '',
            address: params.row.address || '',
            phone: params.row.phone || '',
            email: params.row.email || '',
            documents: params.row.documents || [],
        });
        setTabValue(0);
        setEditOpen(true);
    };

    const handleCreateSubmit = (e) => {
        e.preventDefault();
        post(route('admin.clients.store'), {
            onSuccess: () => {
                setCreateOpen(false);
                showToast('Потребитель создан');
            },
            onError: () => {
                showToast('Ошибка при создании', 'error');
            }
        });
    };



    const filteredClients = useMemo(() => {
        const query = searchQuery.toLowerCase();
        const altQuery = fixKeyboardLayout(query);
        return (clients || []).filter(c => {
            const s = `${c.last_name} ${c.first_name} ${c.account_number} ${c.address}`.toLowerCase();
            return s.includes(query) || s.includes(altQuery);
        });
    }, [searchQuery, clients]);

    const columns = [
        { field: 'account_number', headerName: 'Лицевой счет', width: 150 },
        { 
            field: 'full_name', headerName: 'ФИО Потребителя', width: 350,
            valueGetter: (p, row) => `${row.last_name || ''} ${row.first_name || ''} ${row.middle_name || ''}`
        },
        { field: 'address', headerName: 'Адрес', flex: 1, minWidth: 250 },
        { field: 'phone', headerName: 'Телефон', width: 180 },
    ];

    const promptDeleteClient = (id) => {
        setConfirmMeta({
            open: true,
            title: 'Удаление профиля',
            content: `Вы действительно хотите удалить клиента №${id}?`,
            onConfirm: () => {
                router.post(
                    `/admin/clients/${data.id}`,
                    {
                        _method:'DELETE',
                    } ,{
                        onSuccess: () => {
                            setConfirmMeta(prev => ({ ...prev, open: false }));
                            setEditOpen(false);
                        }
                    }
                );
            }
        });
    }

    const promptDeleteDocument = (docId) => {
        setConfirmMeta({
            open: true,
            title: 'Удаление документа',
            onClick: true,
            content: 'Удалить этот файл навсегда?',
            onConfirm: () => {
                router.delete(route('admin.documents.destroy', docId), {
                    onSuccess: (page) => {
                        setConfirmMeta(prev => ({ ...prev, open: false }));
                        const updatedClient = page.props.clients.find(c => c.id === data.id);
                        if (updatedClient) setData('documents', updatedClient.documents);
                        showToast('Документ успешно удален');
                    }
                });
            }
        });
    };

    return (
        <AdminLayout>
            <Head title="Потребители" />
            <Box sx={{ bgcolor: '#f4f7fe', minHeight: '90vh', py: 4 }}>
                <Container maxWidth="xl">
                    <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
                        <Typography variant="h4" fontWeight="800" color="#1B2559">Потребители</Typography>
                        <Box display="flex" gap={2}>
                            <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: 300, boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                                <SearchIcon sx={{ color: '#A3AED0' }} />
                                <InputBase
                                    placeholder="Поиск..." fullWidth sx={{ ml: 1, "& input:focus": { boxShadow: 'none', outline: 'none' },
        "& .Mui-focused": { outline: 'none' }}} 
                                    value={searchQuery} onChange={e => setSearchQuery(e.target.value) }
                                />
                            </Paper>
                            <Button variant="contained" startIcon={<AddIcon />} onClick={handleOpenCreate} sx={{ borderRadius: '16px', bgcolor: '#4318FF', px: 3 }}>
                                Добавить
                            </Button>
                        </Box>
                    </Box>

                    <Paper sx={{ borderRadius: '20px', overflow: 'hidden', border: 'none', boxShadow: '0px 10px 30px rgba(0,0,0,0.02)' }}>
                        <DataGrid 
                            rows={filteredClients} columns={columns} autoHeight onRowDoubleClick={handleRowClick}
                            sx={{ border: 'none', '& .MuiDataGrid-columnHeaders': { bgcolor: '#F4F7FE' } }}
                        />
                    </Paper>

                    {/* МОДАЛКА СОЗДАНИЯ */}
                    <Dialog open={createOpen} onClose={() => setCreateOpen(false)} maxWidth="md" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
                        <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3 }}>
                            <Typography variant="h5" fontWeight="bold">Регистрация потребителя</Typography>
                        </Box>
                        <form onSubmit={handleCreateSubmit}>
                            <DialogContent sx={{ bgcolor: '#fafbfd', p: 4 }}>
                                <Grid container spacing={3}>
                                    <Grid item xs={12}>
                                        <AddressSuggestions 
                                            token={DADATA_API_KEY} 
                                            value={data.address} 
                                            onChange={s => setData('address', s.value)} 
                                            customInput={DadataInput} 
                                        />
                                    </Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Л/С" variant="standard" value={data.account_number || ''} onChange={e => setData('account_number', e.target.value)} error={!!errors.account_number} helperText={errors.account_number} /></Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Фамилия" variant="standard" value={data.last_name || ''} onChange={e => setData('last_name', e.target.value)} /></Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Имя" variant="standard" value={data.first_name || ''} onChange={e => setData('first_name', e.target.value)} /></Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Отчество" variant="standard" value={data.middle_name || ''} onChange={e => setData('middle_name', e.target.value)} /></Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Телефон" variant="standard" value={data.phone || ''} onChange={e => setData('phone', e.target.value)} /></Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Email" variant="standard" value={data.email || ''} onChange={e => setData('email', e.target.value)} /></Grid>
                                </Grid>
                            </DialogContent>
                            <DialogActions sx={{ p: 3, bgcolor: '#fafbfd' }}>
                                <Button onClick={() => setCreateOpen(false)}>Отмена</Button>
                                <Button type="submit" variant="contained" sx={{ bgcolor: '#4318FF' }} disabled={processing}>Создать</Button>
                            </DialogActions>
                        </form>
                    </Dialog>

                    {/* МОДАЛКА КАРТОЧКИ */}
                    <ClientCard 
                    open={editOpen}
                    onClose={() => setEditOpen(false)}
                    data={data}
                    setData={setData}
                    errors={errors}
                    showToast={showToast}
                    onDeleteClient={promptDeleteClient}
                    onDeleteDocument={promptDeleteDocument}
                />

                    <ConfirmDialog 
                        open={confirmMeta.open} title={confirmMeta.title} content={confirmMeta.content} 
                        onConfirm={confirmMeta.onConfirm} onClose={() => setConfirmMeta(p => ({ ...p, open: false }))} 
                    />
                    <Snackbar open={toast.open} autoHideDuration={3000} onClose={() => setToast(p => ({ ...p, open: false }))} anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}>
                        <Alert severity={toast.severity} variant="filled" sx={{ borderRadius: '12px' }}>{toast.message}</Alert>
                    </Snackbar>
                </Container>
            </Box>
        </AdminLayout>
    );
}
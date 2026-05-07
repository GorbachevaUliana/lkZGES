import React, { useState, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { 
    Container, Typography, Paper, Button, Box, 
    Dialog, DialogContent, TextField, DialogActions, 
    Tabs, Tab, Table, TableBody, TableCell,
    TableContainer, TableHead, TableRow, IconButton,
    Snackbar, Alert, InputBase, Grid,
    FormControl, InputLabel, Select, MenuItem, FormHelperText 
} from '@mui/material';
import { 
    Add as AddIcon, Search as SearchIcon
} from '@mui/icons-material';
import { AddressSuggestions } from 'react-dadata';
import { DataGrid } from '@mui/x-data-grid';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import ClientCard from '@/Components/Admin/ClientCard';

const DADATA_API_KEY = import.meta.env.VITE_DADATA_API_KEY || "cd4b88f14527df99bbafecb1c09789391eb6f2ff";

const fixKeyboardLayout = (text) => {
    if (!text) return '';
    const map = {'q':'й', 'w':'ц', 'e':'у', 'r':'к', 't':'е', 'y':'н', 'u':'г', 'i':'ш', 'o':'щ', 'p':'з', '[':'х', ']':'ъ', 'a':'ф', 's':'ы', 'd':'в', 'f':'а', 'g':'п', 'h':'р', 'j':'о', 'k':'л', 'l':'д', ';':'ж', "'":'э', 'z':'я', 'x':'ч', 'c':'с', 'v':'м', 'b':'и', 'n':'т', 'm':'ь', ',':'б', '.':'ю'};
    return text.toLowerCase().split('').map(char => map[char] || char).join('');
};

const DadataInput = React.forwardRef((props, ref) => (
    <TextField {...props} inputRef={ref} fullWidth variant="standard" label={props.label || "Адрес"} />
));

export default function ClientsList({ auth, clients, tariffs }) {
    const [editOpen, setEditOpen] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [toast, setToast] = useState({ open: false, message: '', severity: 'success' });
    const [confirmMeta, setConfirmMeta] = useState({ open: false, title: '', content: '', onConfirm: () => {} });

    const { data, setData, post, reset, processing, errors } = useForm({
        id: '', 
        account_number: '', 
        client_type: 'individual',
        status: 'active',
        last_name: '', 
        first_name: '',
        middle_name: '', 
        company_name: '',
        address: '', 
        phone: '', 
        email: '', 
        tariff_id: '',
    });

    const showToast = (message, severity = 'success') => setToast({ open: true, message, severity });

    const handleOpenCreate = () => {
        reset();
        setCreateOpen(true);
    };

    const handleRowClick = (params) => {
        setData({ ...params.row, documents: params.row.documents || [] });
        setEditOpen(true);
    };

    const handleCreateSubmit = (e) => {
        e.preventDefault();
        post(route('admin.clients.store'), {
            onSuccess: () => {
                setCreateOpen(false);
                showToast('Потребитель создан');
            },
            onError: () => showToast('Ошибка при создании', 'error')
        });
    };

    const filteredClients = useMemo(() => {
        const query = searchQuery.toLowerCase();
        const altQuery = fixKeyboardLayout(query);
        return (clients || []).filter(c => {
            const s = `${c.last_name} ${c.first_name} ${c.middle_name} ${c.company_name} ${c.account_number} ${c.address}`.toLowerCase();
            return s.includes(query) || s.includes(altQuery);
        });
    }, [searchQuery, clients]);

    const columns = [
        {
            field: 'account_number',
            headerName: 'Лицевой счет',
            width: 150,
            renderCell: (params) => params.value ? (
                params.value
            ):(
                <Typography component="span" variant="body2" color="text.secondary">Не указан</Typography>
            )
        },
        { 
            field: 'display_name', 
            headerName: 'Потребитель', 
            flex: 1,
            valueGetter: (params, row) => row.client_type === 'legal' ? row.company_name : `${row.last_name} ${row.first_name} ${row.middle_name}`.trim()
        },
        {
            field: 'client_type',
            headerName: 'Тип потребителя',
            width: 250,
            valueFormatter: (value) => {
                const types = {
                    'legal' : 'Юридическое лицо',
                    'individual' : 'Физическое лицо'
                };

                return types[value] || value;
            }
        },
        { field: 'address', headerName: 'Адрес', width: 250 },
        { field: 'phone', headerName: 'Телефон', width: 180 },
    ];

    const promptDeleteClient = (id) => {
        setConfirmMeta({
            open:true,
            title:'Удаление профиля',
            content:`Вы действительно хотите удалить клиента №${id}`,
            onConfirm: () => {
                router.post(`/admin/clients/${data.id}`, {
                    ...data,
                    _method:'DELETE',
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
                        />
                    </Paper>

                    {/* МОДАЛКА СОЗДАНИЯ */}
                    <Dialog open={createOpen} onClose={() => {
                        setCreateOpen(false);
                        reset();}
                    } maxWidth="md" fullWidth>
                        <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3 }}>
                            <Typography variant="h5" fontWeight="bold">Регистрация потребителя</Typography>
                        </Box>
                        <form onSubmit={handleCreateSubmit}>
                            <DialogContent sx={{ bgcolor: '#fafbfd', p: 4 }}>
                                <Grid container spacing={3}>
                                    <Grid item xs={12}>
                                        <FormControl fullWidth variant="standard">
                                            <InputLabel>Тип клиента</InputLabel>
                                            <Select value={data.client_type} onChange={e => setData('client_type', e.target.value)}>
                                                <MenuItem value="individual">Физическое лицо</MenuItem>
                                                <MenuItem value="legal">Юридическое лицо</MenuItem>
                                            </Select>
                                        </FormControl>
                                    </Grid>
                                    
                                    <Grid item xs={12}>
                                        <AddressSuggestions
                                            token={DADATA_API_KEY}
                                            value={data.address || ''}
                                            onChange={s => setData('address', s?.value || '')}
                                            customInput={DadataInput}/>
                                    </Grid>

                                    <Grid item xs={4}>
                                        <TextField fullWidth label="Л/С" variant="standard" value={data.account_number} onChange={e => setData('account_number', e.target.value)} error={!!errors.account_number} helperText={errors.account_number} />
                                    </Grid>

                                    {data.client_type === 'legal' ? (
                                        <Grid item xs={8}>
                                            <TextField fullWidth label="Название компании" variant="standard" value={data.company_name} onChange={e => setData('company_name', e.target.value)} />
                                        </Grid>
                                    ) : (
                                        <>
                                            <Grid item xs={4}><TextField fullWidth label="Фамилия" variant="standard" value={data.last_name} onChange={e => setData('last_name', e.target.value)} /></Grid>
                                            <Grid item xs={4}><TextField fullWidth label="Имя" variant="standard" value={data.first_name} onChange={e => setData('first_name', e.target.value)} /></Grid>
                                            <Grid item xs={4}><TextField fullWidth label="Отчество" variant="standard" value={data.middle_name} onChange={e => setData('middle_name', e.target.value)} error={!!errors.middle_name} helperText={errors.middle_name} /></Grid>
                                        </>
                                    )}

                                    <Grid item xs={4}><TextField fullWidth label="Телефон" variant="standard" value={data.phone} onChange={e => setData('phone', e.target.value)} /></Grid>
                                    <Grid item xs={4}><TextField fullWidth label="Email" variant="standard" value={data.email} onChange={e => setData('email', e.target.value)} /></Grid>
                                    
                                    <Grid item xs={4}>
                                        <FormControl fullWidth variant="standard" error={!!errors.tariff_id}>
                                            <InputLabel>Тариф</InputLabel>
                                            <Select value={data.tariff_id} onChange={e => setData('tariff_id', e.target.value)}>
                                                {tariffs?.map((t) => (
                                                    <MenuItem key={t.id} value={t.id}>{t.name} ({t.price_1} руб.)</MenuItem>
                                                ))}
                                            </Select>
                                            {errors.tariff_id && <FormHelperText>{errors.tariff_id}</FormHelperText>}
                                        </FormControl>
                                    </Grid>
                                </Grid>
                            </DialogContent>
                            <DialogActions sx={{ p: 3 }}>
                                <Button onClick={() => setCreateOpen(false)}>Отмена</Button>
                                <Button type="submit" variant="contained" disabled={processing}>Создать</Button>
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
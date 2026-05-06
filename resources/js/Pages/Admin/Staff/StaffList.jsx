import React, { useState, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Container, Typography, Paper, Button, Box, 
    Dialog, DialogContent, TextField, DialogActions, 
    IconButton, Snackbar, Alert, InputBase, MenuItem,
    Select, FormControl, InputLabel } from '@mui/material';
import Grid from '@mui/material/Grid'; 
import { Add as AddIcon, Delete as DeleteIcon, Search as SearchIcon } from '@mui/icons-material';
import { DataGrid } from '@mui/x-data-grid';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import StaffCard from '@/Components/Admin/StaffCard';

const fixKeyboardLayout = (text) => {
    if (!text) return '';
    const map = {'q':'й', 'w':'ц', 'e':'у', 'r':'к', 't':'е', 'y':'н', 'u':'г', 'i':'ш', 'o':'щ', 'p':'з', '[':'х', ']':'ъ', 'a':'ф', 's':'ы', 'd':'в', 'f':'а', 'g':'п', 'h':'р', 'j':'о', 'k':'л', 'l':'д', ';':'ж', "'":'э', 'z':'я', 'x':'ч', 'c':'с', 'v':'м', 'b':'и', 'n':'т', 'm':'ь', ',':'б', '.':'ю'};
    return text.toLowerCase().split('').map(char => map[char] || char).join('');
};

export default function StaffList({ auth, staff }) {
    const [editOpen, setEditOpen] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [toast, setToast] = useState({ open: false, message: '', severity: 'success' });
    const [confirmMeta, setConfirmMeta] = useState({ open: false, title: '', content: '', onConfirm: () => {} });
    const { data, setData, post, reset, processing, errors } = useForm({
        id: '', name: '', email: '', role: 'staff', permissions: [], password: '', password_confirmation: ''
    });

    const showToast = (message, severity = 'success') => setToast({ open: true, message, severity });

    const handleOpenCreate = () => {
        reset();
        setCreateOpen(true);
    };

    const handleRowClick = (params) => {
        setData({
            id: params.row.id,
            name: params.row.name || '',
            email: params.row.email || '',
            role: params.row.role || 'staff',
            permissions: params.row.permissions || [],
            password: '',
            password_confirmation: ''
        });
        setEditOpen(true);
    };

    const handleCreateSubmit = (e) => {
        e.preventDefault();
        post(route('admin.staff.store'), {
            preserveScroll: true, 
            onSuccess: () => {
                setCreateOpen(false);
                showToast('Сотрудник успешно создан');
                reset();
            },
            onError: (errors) => {
                console.error('Ошибки валидации:', errors);
                showToast('Ошибка при создании', 'error');
            }
        });
    };

    const promptDeleteStaff = (id) => {
        setConfirmMeta({
            open: true,
            title: 'Удаление профиля',
            content: `Вы действительно хотите удалить сотрудника №${id}?`,
            onConfirm: () => {
                router.post(
                    `/admin/staff/${data.id}`,
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

    const filteredStaff = useMemo(() => {
        const query = searchQuery.toLowerCase();
        const altQuery = fixKeyboardLayout(query);
        
        return (staff || []).filter(u => {
            // Защита от null/undefined через опциональную цепочку или пустую строку
            const name = u.name?.toLowerCase() || '';
            const role = u.role?.toLowerCase() || '';
            const email = u.email?.toLowerCase() || '';
            const s = `${name} ${role} ${email}`;
            
            return s.includes(query) || s.includes(altQuery);
        });
    }, [searchQuery, staff]);

    const columns = [
        { field: 'name', headerName: 'ФИО Сотрудника', flex: 1, minWidth: 250 },
        {
            field: 'role', 
            headerName: 'Роль', 
            width: 150,
            renderCell: (params) => (
                <Box sx={{
                    color: params.value === 'admin' ? '#4318FF' : '#A3AED0',
                    fontWeight: 'bold'}}>
                    {params.value === 'admin' ? 'Администратор' : 'Оператор'}
                </Box>
            )
        },
        { field: 'email', headerName: 'Email', width: 250 },
    ];

    return (
        <AdminLayout user={auth.user}>
            <Head title="Сотрудники" />
            <Box sx={{ bgcolor: '#f4f7fe', minHeight: '90vh', py: 4 }}>
                <Container maxWidth="xl">
                    <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
                        <Typography variant="h4" fontWeight="800" color="#1B2559">Сотрудники</Typography>
                        <Box display="flex" gap={2}>
                            <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: 300, boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                                <SearchIcon sx={{ color: '#A3AED0' }} />
                                <InputBase
                                    placeholder="Поиск по ФИО или Email..."
                                    fullWidth
                                    sx={{ ml: 1 }}
                                    value={searchQuery}
                                    onChange={e => setSearchQuery(e.target.value)}
                                    inputProps = {{autoComplete: 'off', name: 'search-staff-unique'}}/>
                            </Paper>
                            <Button variant="contained" startIcon={<AddIcon />} onClick={handleOpenCreate} sx={{ borderRadius: '16px', bgcolor: '#4318FF', px: 3 }}>
                                Добавить сотрудника
                            </Button>
                        </Box>
                    </Box>

                    <Paper sx={{ borderRadius: '20px', overflow: 'hidden', boxShadow: '0px 10px 30px rgba(0,0,0,0.02)' }}>
                        <DataGrid 
                            rows={filteredStaff}
                            columns={columns}
                            autoHeight
                            onRowDoubleClick={handleRowClick}
                            sx={{ border: 'none', '& .MuiDataGrid-columnHeaders': { bgcolor: '#F4F7FE' } }}/>
                    </Paper>

                    {/* ДИАЛОГ СОЗДАНИЯ */}
                    <Dialog open={createOpen} onClose={() => setCreateOpen(false)} maxWidth="sm" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
                        <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3 }}>
                            <Typography variant="h5" fontWeight="bold">Новый сотрудник</Typography>
                        </Box>
                        <form onSubmit={handleCreateSubmit}>
                            <DialogContent sx={{ p: 4 }}>
                                <Grid container spacing={3}>
                                    <Grid item xs={12}>
                                        <TextField fullWidth label="ФИО" variant="standard" value={data.name} onChange={e => setData('name', e.target.value)} error={!!errors.name} helperText={errors.name} required />
                                    </Grid>
                                    <Grid item xs={12}>
                                        <TextField fullWidth label="Email" variant="standard" value={data.email} onChange={e => setData('email', e.target.value)} error={!!errors.email} helperText={errors.email} required />
                                    </Grid>
                                    <Grid item xs={12}>
                                        <TextField fullWidth type="password" label="Пароль" variant="standard" value={data.password} onChange={e => setData('password', e.target.value)} error={!!errors.password} helperText={errors.password} required />
                                    </Grid>
                                    <Grid item xs={12}>
                                        <FormControl fullWidth variant="standard">
                                            <InputLabel>Роль</InputLabel>
                                            <Select value={data.role} onChange={e => setData('role', e.target.value)}>
                                                <MenuItem value="staff">Оператор</MenuItem>
                                                <MenuItem value="admin">Администратор</MenuItem>
                                            </Select>
                                        </FormControl>
                                    </Grid>
                                </Grid>
                            </DialogContent>
                            <DialogActions sx={{ p: 3 }}>
                                <Button onClick={() => setCreateOpen(false)}>Отмена</Button>
                                <Button type="submit" variant="contained" sx={{ bgcolor: '#4318FF' }} disabled={processing}>Создать</Button>
                            </DialogActions>
                        </form>
                    </Dialog>

                    {/* КАРТОЧКА РЕДАКТИРОВАНИЯ */}
                    <StaffCard
                        open={editOpen} 
                        onClose={() => setEditOpen(false)} 
                        data={data} 
                        setData={setData} 
                        authUser={auth.user}
                        showToast={showToast}
                        onDeleteStaff={promptDeleteStaff}/>
                    <ConfirmDialog 
                        open={confirmMeta.open} title={confirmMeta.title} content={confirmMeta.content} 
                        onConfirm={confirmMeta.onConfirm} onClose={() => setConfirmMeta(p => ({ ...p, open: false }))} />

                    <Snackbar open={toast.open} autoHideDuration={3000} onClose={() => setToast(p => ({ ...p, open: false }))} anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}>
                        <Alert severity={toast.severity} variant="filled">{toast.message}</Alert>
                    </Snackbar>
                </Container>
            </Box>
        </AdminLayout>
    );
}
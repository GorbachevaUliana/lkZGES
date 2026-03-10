import React, { useMemo, useState } from 'react';
import { Typography, Box, Dialog, DialogContent, TextField,
    DialogActions, Tabs, Tab, Button, MenuItem, Select, 
    FormControl, InputLabel, FormGroup, FormControlLabel,
    Divider, Checkbox, IconButton } from '@mui/material';
import { Save as SaveIcon, Delete as DeleteIcon } from '@mui/icons-material';
// import { 
//     Description as DescriptionIcon, 
//     Delete as DeleteIcon, 
//     Save as SaveIcon, 
//     CloudUpload as CloudUploadIcon 
// } from '@mui/icons-material';
import { router } from '@inertiajs/react';

const AVAILABLE_PAGES = [
    {id:'dashboard', name:'Главная'},
    {id:'clients', name:'Потребители'},
    {id:'tickets', name:'Обращения'},
    {id:'staff', name:'Сотрудники'},
]

export default function StaffCard({ open, onClose, data, setData, authUser, showToast, onDeleteStaff }) {
    const [tabValue, setTabValue] = useState(0);
    const isAdmin = authUser.role === 'admin';
    const isCurrentUserAdmin = isAdmin;
    const handleUpdate = () => {
        // console.log('ID:', data.id);
        // console.log(route('admin.staff.update', { staff: data.id }));
        router.post(
            `/admin/staff/${data.id}`,
            {
                ...data,
                _method: 'PUT'
            },
            {
                onSuccess: () => showToast('Данные успешно обновлены')
            }
        );
    };

    const handlePermissionChange = (pageId, isChecked) => {
        let nextPermissions = [...currentPermissions];
        if (isChecked) {
            if (!nextPermissions.includes(pageId)) {
                nextPermissions.push(pageId);
            }
        } else {
            nextPermissions = nextPermissions.filter(p => p !== pageId);
        }
        setData('permissions', nextPermissions);
    };

    const currentPermissions = useMemo(() => {
        if (!data.permissions) return [];
        if (typeof data.permissions === 'string') {
            try { 
                return JSON.parse(data.permissions); 
            } catch (e) { 
                return []; 
            }
        }
        return Array.isArray(data.permissions) ? data.permissions : [];
    }, [data.permissions]);

    return (
        <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
            <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3}}>
                <Typography variant="h5" fontWeight="bold">Карточка сотрудника</Typography>
                <IconButton onClick={() => onDeleteStaff(data.id)} sx={{ color: '#FF5B5B' }}>
                    <DeleteIcon />
                </IconButton>
            </Box>

            <Tabs value={tabValue} onChange={(e, v) => setTabValue(v)} sx={{ borderBottom: 1, borderColor: 'divider' }}>
                <Tab label="Информация" />
                <Tab label="Права доступа" />
            </Tabs>

            <DialogContent>
                {tabValue === 0 && (
                    <Box sx={{ pt: 2, display: 'flex', flexDirection: 'column', gap: 3 }}>
                        <TextField label="ФИО" fullWidth value={data.name} onChange={e => setData('name', e.target.value)} disabled={!isCurrentUserAdmin} />
                        <TextField label="Email" fullWidth value={data.email} onChange={e => setData('email', e.target.value)} disabled={!isCurrentUserAdmin} />

                        <Box sx={{ display: 'flex', gap: 2 }}>
                            <TextField 
                                label="Новый пароль" 
                                type="password"
                                fullWidth 
                                value={data.password || ''} 
                                onChange={e => setData('password', e.target.value)} 
                                disabled={!isAdmin} 
                                autoComplete="new-password" // Важно!
                                helperText={isAdmin ? "Оставьте пустым, если не хотите менять" : ""}
                            />
                            <TextField 
                                label="Повторите пароль" 
                                type="password"
                                fullWidth 
                                value={data.password_confirmation || ''} 
                                onChange={e => setData('password_confirmation', e.target.value)} 
                                disabled={!isAdmin} 
                                autoComplete="new-password" // Важно!
                            />
                        </Box>
                        
                        <FormControl fullWidth disabled={!isCurrentUserAdmin}>
                            <InputLabel>Основная роль</InputLabel>
                            <Select value={data.role} label="Основная роль" onChange={e => setData('role', e.target.value)}>
                                <MenuItem value="staff">Оператор</MenuItem>
                                <MenuItem value="admin">Администратор</MenuItem>
                            </Select>
                        </FormControl>
                    </Box>
                )}

                {tabValue === 1 && (
                <Box sx={{ pt: 2 }}>
                    <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
                        Доступ к разделам:
                    </Typography>
                    <Divider sx={{ mb: 2 }} />
                    <FormGroup>
                        {AVAILABLE_PAGES.map((page) => (
                            <FormControlLabel
                                key={page.id}
                                control={
                                    <Checkbox 
                                        checked={currentPermissions.includes(page.id)}
                                        onChange={(e) => handlePermissionChange(page.id, e.target.checked)}
                                        disabled={data.role === 'admin' || !isCurrentUserAdmin} 
                                    />
                                }
                                label={page.name}
                            />
                        ))}
                    </FormGroup>
                    {data.role === 'admin' && (
                        <Typography variant="caption" color="primary" sx={{ mt: 1, display: 'block' }}>
                            * Администраторы имеют доступ ко всем разделам по умолчанию.
                        </Typography>
                    )}
                </Box>
            )}
            </DialogContent>

            <DialogActions>
                <Button onClick={onClose}>Закрыть</Button>
                {isAdmin && (
                    <Button variant="contained" startIcon={<SaveIcon />} onClick={handleUpdate} sx={{ bgcolor: '#4318FF' }}>
                        Сохранить
                    </Button>
                )}
            </DialogActions>
        </Dialog>
    );
}
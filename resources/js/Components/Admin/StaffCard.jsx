import React, { useState } from 'react';
import { Typography, Box, Dialog, DialogContent, TextField,
    DialogActions, Tabs, Tab, Button, MenuItem, Select, 
    FormControl, InputLabel, FormGroup, FormControlLabel } from '@mui/material';
import { Save as SaveIcon } from '@mui/icons-material';
import { router } from '@inertiajs/react';

const AVAILABLE_PAGES = [
    {id:'dashboard', name:'Главная'},
    {id:'clients', name:'Потребители'},
    {id:'tickets', name:'Обращения'},
    {id:'staff', name:'Сотрудники'},
]

export default function StaffCard({ open, onClose, data, setData, authUser, showToast }) {
    const [tabValue, setTabValue] = useState(0);
    const isAdmin = authUser.role === 'admin';
    const isCurrentUserAdmin = isAdmin;
    const handleUpdate = () => {
        router.put(route('admin.staff.update', data.id), data, {
            onSuccess: () => showToast('Данные успешно обновлены')
        });
    };

    const handlePermissionChange = (pageId) => {
        const currentPerms = data.permissions || [];
        if (currentPerms.includes(pageId)) {
            setData('permissions', currentPerms.filter(p => p !== pageId));
        } else {
            setData('permissions', [...currentPerms, pageId]);
        }
    };

    return (
        <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
            <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3 }}>
                <Typography variant="h5" fontWeight="bold">Карточка сотрудника</Typography>
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
                                        checked={data.permissions?.includes(page.id) || false}
                                        onChange={(e) => {
                                            const checked = e.target.checked;
                                            const current = data.permissions || [];
                                            if (checked) {
                                                setData('permissions', [...current, page.id]);
                                            } else {
                                                setData('permissions', current.filter(p => p !== page.id));
                                            }
                                        }}
                                        disabled={data.role === 'admin' || authUser.role !== 'admin'} 
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
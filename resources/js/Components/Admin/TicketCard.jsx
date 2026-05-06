import React from 'react';
import { 
    Typography, Box, Dialog, DialogContent, TextField, Paper, Grid,
    DialogActions, Button, Chip, Divider, MenuItem, Select, FormControl, InputLabel,
    IconButton
} from '@mui/material';
import { Save as SaveIcon, Close as CloseIcon, CloudUpload as UploadIcon } from '@mui/icons-material';
import { router } from '@inertiajs/react';

export default function TicketCard({ auth, open, onClose, data, setData, showToast, staff_members, onOpenClientCard }) {

    const clientFiles = data.attachments?.filter(a => !Boolean(a.is_admin)) || [];
    const adminFiles = data.attachments?.filter(a => Boolean(a.is_admin)) || [];
            
    const handleUpdate = () => {
        router.post(`/admin/tickets/${data.id}`, {
            ...data,
            _method: 'PUT',
        }, {
            onSuccess: (page) => {
                showToast('Обращение успешно обновлено');
                const updatedTicket = page.props.tickets.find(t => t.id === data.id);
                if (updatedTicket) {
                    setData(updatedTicket);
                }
            },
            forceFormData: true
        });
    };

    const removeFileBeforeUpload = (index) => {
        const updatedFiles = data.admin_files.filter((_, i) => i !== index);
        setData('admin_files', updatedFiles);
    };

    return (
        <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
            {/* Шапка */}
            <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <Box>
                    <Typography variant="h6" fontWeight="bold">Обращение №{data.id}</Typography>
                    <Button 
                        onClick={() => onOpenClientCard(data.user_id)}
                        sx={{ color: 'rgba(255,255,255,0.8)', textTransform: 'none', p: 0, '&:hover': { color: '#fff', bgcolor: 'transparent' } }}>
                        От: {data.user?.name || `Пользователь #${data.user_id}`}
                    </Button>
                </Box>
            </Box>

            <DialogContent sx={{ p: 4, bgcolor: '#fafbfd' }}>
                <Grid container spacing={4}>
                    {/* ЛЕВАЯ КОЛОНКА: ИНФОРМАЦИЯ КЛИЕНТА */}
                    <Grid item xs={12} md={6}>
                        <Typography variant="subtitle2" color="textSecondary" gutterBottom>Тема и сообщение</Typography>
                        <Typography variant="h6" fontWeight="bold" sx={{ color: '#1B2559' }}>{data.subject}</Typography>
                        <Paper variant="outlined" sx={{ p: 2, mt: 1, bgcolor: '#fff', borderRadius: '12px', mb: 3 }}>
                            <Typography variant="body1">{data.message}</Typography>
                        </Paper>

                        <Typography variant="subtitle2" color="textSecondary">Файлы от клиента</Typography>
                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1, mb: 3 }}>
                            {clientFiles.length > 0 ? clientFiles.map((file) => (
                                <Chip 
                                    key={file.id} 
                                    label={file.file_name} 
                                    onClick={() => window.open(file.url, '_blank')}
                                    variant="outlined" 
                                    size="small" 
                                    clickable/>
                            
                            )) : <Typography variant="body2" color="textDisabled">Нет файлов</Typography>}
                        </Box>

                        <Box mt={4}>
                            <FormControl fullWidth variant="standard">
                                <InputLabel>Статус обращения</InputLabel>
                                <Select
                                    value={data.status || ''}
                                    onChange={(e) => setData('status', e.target.value)}>
                                    <MenuItem value="open">Новое</MenuItem>
                                    <MenuItem value="closed">Решено</MenuItem>
                                    <MenuItem value="pending">В работе</MenuItem>
                                </Select>
                            </FormControl>
                        </Box>

                        <Divider sx={{ my: 2 }} />
                        
                        <FormControl fullWidth sx={{ mt: 2 }}>
                            <InputLabel>Ответственный</InputLabel>
                            <Select 
                                value={data.staff_id || ''} 
                                label="Ответственный" 
                                onChange={e => setData('staff_id', e.target.value)}>
                                <MenuItem value=""><em>Не назначен</em></MenuItem>
                                
                                {staff_members.map(m => (
                                    <MenuItem key={m.id} value={m.id}>
                                        {m.name}
                                    </MenuItem>
                                ))}

                                {/* Если текущий ответственный не попал в список разрешенных, 
                                    показываем его, чтобы Select не был пустым, но помечаем ошибкой */}
                                {data.staff_id && !staff_members.find(m => m.id === data.staff_id) && (
                                    <MenuItem value={data.staff_id} disabled>
                                        {data.staff?.name} (Доступ отозван)
                                    </MenuItem>
                                )}
                            </Select>
                        </FormControl>
                    </Grid>

                    <Grid item xs={12} md={6} sx={{ borderLeft: { md: '1px solid #E0E5F2' }, pl: { md: 4 } }}>
                        {/* Отображение уже существующего ответа */}
                        {data.replied_at && (
                            <Box sx={{ mb: 4, p: 2, bgcolor: '#F4F7FE', borderRadius: '16px', border: '1px solid #E0E5F2' }}>
                                <Typography variant="caption" color="primary" fontWeight="bold" sx={{ display: 'block', mb: 1 }}>
                                    ОТВЕТИЛ: {data.replied_by?.name || 'Сотрудник'} ({new Date(data.replied_at).toLocaleString()})
                                </Typography>
                                <Typography variant="body2" sx={{ color: '#1B2559', whiteSpace: 'pre-line' }}>
                                    {data.admin_reply}
                                </Typography>
                                
                                {/* Файлы, которые прикрепил админ (фильтруем по пути или типу) */}
                                <Box sx={{ mt: 2, display: 'flex', flexWrap: 'wrap', gap: 1 }}>
                                    {adminFiles.map((file) => (
                                        <Chip 
                                            key={file.id} 
                                            label={file.file_name} 
                                            size="small" 
                                            onClick={() => window.open(file.url, '_blank')}
                                            sx={{ bgcolor: '#fff', border: '1px solid #E0E5F2' }}/>
                                    ))}
                                </Box>
                            </Box>
                        )}

                        {/* Форма для написания нового ответа */}
                        <Typography variant="subtitle2" color="textSecondary" gutterBottom>
                            {data.replied_at ? 'Дополнить ответ:' : 'Написать ответ:'}
                        </Typography>

                        <TextField
                            fullWidth multiline rows={6}
                            placeholder="Введите текст ответа потребителю..."
                            value={data.admin_reply || ''}
                            onChange={e => setData('admin_reply', e.target.value)}
                            sx={{ bgcolor: '#fff', '& .MuiOutlinedInput-root': { borderRadius: '12px' } }}/>
                        
                        <Button
                            variant="outlined"
                            component="label"
                            startIcon={<UploadIcon />}
                            sx={{ mt: 2, borderRadius: '10px', textTransform: 'none', borderColor: '#E0E5F2', color: '#1B2559' }}>
                            Прикрепить документы (Админ)
                            <input type="file" hidden multiple onChange={e => setData('admin_files', Array.from(e.target.files))} />
                        </Button>

                        {data.admin_files?.map((f, i) => (
                            <Box key={i} sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                                <Typography variant="caption" sx={{ color: '#4318FF' }}>📎 {f.name}</Typography>
                                <IconButton size="small" onClick={() => removeFileBeforeUpload(i)}>
                                    <CloseIcon sx={{ fontSize: 14, color: 'red' }} />
                                </IconButton>
                            </Box>
                        ))}
                    </Grid>
                </Grid>
            </DialogContent>

            <DialogActions sx={{ p: 3, bgcolor: '#fafbfd' }}>
                <Button onClick={onClose} startIcon={<CloseIcon />}>Закрыть</Button>
                <Button 
                    variant="contained" 
                    onClick={handleUpdate}
                    startIcon={<SaveIcon />}
                    sx={{ bgcolor: '#4318FF', borderRadius: '12px', px: 4, py: 1 }}>
                    Сохранить и ответить
                </Button>
            </DialogActions>
        </Dialog>
    );
}
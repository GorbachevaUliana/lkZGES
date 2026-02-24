import React, { useState } from 'react';
import { 
    Typography, Box, Dialog, DialogContent, TextField, 
    DialogActions, Tabs, Tab, Table, TableBody, TableCell, 
    TableContainer, TableRow, IconButton, Button, Paper
} from '@mui/material';
import Grid from '@mui/material/Grid'; 
import { 
    Description as DescriptionIcon, 
    Delete as DeleteIcon, 
    Save as SaveIcon, 
    CloudUpload as CloudUploadIcon 
} from '@mui/icons-material';
import SpeedIcon from '@mui/icons-material/Speed';
import { AddressSuggestions } from 'react-dadata';
import { router } from '@inertiajs/react';
import ClientAvatar from './ClientAvatar';

function TabPanel({ children, value, index }) {
    return value === index ? <Box sx={{ py: 3 }}>{children}</Box> : null;
}

export default function ClientCard({ 
    open, 
    onClose, 
    data, 
    setData, 
    errors,
    onDeleteClient,
    onDeleteDocument,
    showToast 
}) {
    const [tabValue, setTabValue] = useState(0);

    // 1. Обновление данных клиента
    const handleUpdateSubmit = () => {
        router.put(route('admin.clients.update', data.id), data, {
            onSuccess: () => showToast('Данные успешно обновлены'),
        });
    };

    // 2. Загрузка файла
    const handleFileUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        router.post(route('admin.documents.upload', data.id), { file }, {
            forceFormData: true,
            onSuccess: (page) => {

                const updated = page.props.clients.find(c => c.id === data.id);
                if (updated) setData('documents', updated.documents);
                showToast('Файл загружен');
            }
        });
    };

    return (
        <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth sx={{ '& .MuiDialog-paper': { borderRadius: '24px' } }}>
            {/* Шапка карточки */}
            <Box sx={{ bgcolor: '#0B1437', color: 'white', p: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <Box display="flex" alignItems="center" gap={2}>
                    <ClientAvatar name={data.last_name} sx={{ width: 56, height: 56 }} />
                    <Box>
                        <Typography variant="h5" fontWeight="bold">
                            {data.last_name} {data.first_name} {data.middle_name}
                        </Typography>
                        <Typography variant="body2" sx={{ opacity: 0.7 }}>Л/С: {data.account_number}</Typography>
                    </Box>
                </Box>
                <IconButton onClick={() => onDeleteClient(data.id)} sx={{ color: '#FF5B5B' }}>
                    <DeleteIcon />
                </IconButton>
            </Box>
            
            <Tabs value={tabValue} onChange={(e, v) => setTabValue(v)} sx={{ px: 2, borderBottom: 1, borderColor: 'divider' }}>
                <Tab label="Профиль" />
                <Tab label="Документы" />
                <Tab label="Показания" />
            </Tabs>

            <DialogContent sx={{ minHeight: '400px', bgcolor: '#fafbfd' }}>
                {/* TAB 0: Профиль */}
                <TabPanel value={tabValue} index={0}>
                    <Grid container spacing={3}>
                        <Grid item xs={12}>
                            <TextField 
                                fullWidth 
                                label="Адрес регистрации" 
                                variant="standard" 
                                value={data.address || ''} 
                                onChange={e => setData('address', e.target.value)} 
                            />
                        </Grid>
                        <Grid item xs={4}><TextField fullWidth label="Фамилия" variant="standard" value={data.last_name || ''} onChange={e => setData('last_name', e.target.value)} /></Grid>
                        <Grid item xs={4}><TextField fullWidth label="Имя" variant="standard" value={data.first_name || ''} onChange={e => setData('first_name', e.target.value)} /></Grid>
                        <Grid item xs={4}><TextField fullWidth label="Отчество" variant="standard" value={data.middle_name || ''} onChange={e => setData('middle_name', e.target.value)} /></Grid>
                        <Grid item xs={6}><TextField fullWidth label="Телефон" variant="standard" value={data.phone || ''} onChange={e => setData('phone', e.target.value)} /></Grid>
                        <Grid item xs={6}><TextField fullWidth label="Email" variant="standard" value={data.email || ''} onChange={e => setData('email', e.target.value)} /></Grid>
                    </Grid>
                    <Box display="flex" justifyContent="flex-end" mt={3}>
                        <Button variant="contained" startIcon={<SaveIcon />} onClick={handleUpdateSubmit} sx={{ bgcolor: '#4318FF' }}>
                            Сохранить изменения
                        </Button>
                    </Box>
                </TabPanel>

                {/* TAB 1: Документы */}
                <TabPanel value={tabValue} index={1}>
                    <Box display="flex" justifyContent="space-between" mb={2} alignItems="center">
                        <Typography variant="h6" fontWeight="bold" color="#2B3674">Файлы</Typography>
                        <Button variant="outlined" startIcon={<CloudUploadIcon />} component="label">
                            Загрузить <input type="file" hidden onChange={handleFileUpload} />
                        </Button>
                    </Box>
                    <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: '15px' }}>
                        <Table size="small">
                            <TableBody>
                                {data.documents?.length > 0 ? data.documents.map(doc => (
                                    <TableRow key={doc.id}>
                                        <TableCell>{doc.name}</TableCell>
                                        <TableCell align="right">
                                            <IconButton href={`/storage/${doc.file_path}`} target="_blank" color="primary">
                                                <DescriptionIcon />
                                            </IconButton>
                                            <IconButton onClick={() => onDeleteDocument(doc.id)} color="error">
                                                <DeleteIcon />
                                            </IconButton>
                                        </TableCell>
                                    </TableRow>
                                )) : (
                                    <TableRow>
                                        <TableCell align="center" sx={{ py: 3, color: 'text.secondary' }}>Нет документов</TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </TableContainer>
                </TabPanel>
                
                {/* TAB 2: Показания */}
                <TabPanel value={tabValue} index={2}>
                    <Box textAlign="center" py={4} color="text.secondary">
                        <SpeedIcon sx={{ fontSize: 48, mb: 1, opacity: 0.5 }} />
                        <Typography>Блок «ПОКАЗАНИЯ» будет реализован здесь.</Typography>
                    </Box>
                </TabPanel>
            </DialogContent>

            <DialogActions sx={{ p: 2 }}>
                <Button onClick={onClose} color="inherit">Закрыть</Button>
            </DialogActions>
        </Dialog>
    );
}
import React, { useState } from 'react';
import ClientLayout from '@/Layouts/ClientLayout';
import { useForm } from '@inertiajs/react';
import { 
    Paper, TextField, Button, Box, Typography, Grid, 
    Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Chip 
} from '@mui/material';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import SendIcon from '@mui/icons-material/Send';
import AddIcon from '@mui/icons-material/Add';

export default function Tickets({ auth, tickets }) {
    const [showForm, setShowForm] = useState(false);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        subject: '',
        message: '',
        files: [],
    });

    const statusMap = {
        new: { label: 'Новое', color: 'primary' },
        in_progress: { label: 'В работе', color: 'warning' },
        closed: { label: 'Закрыто', color: 'success' },
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('client.tickets.store'), {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    };

    return (
        <ClientLayout user={auth.user} title="Обращения">
            <Box sx={{ mb: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <Typography variant="h6">История ваших запросов</Typography>
                <Button 
                    variant="contained" 
                    startIcon={showForm ? null : <AddIcon />} 
                    onClick={() => setShowForm(!showForm)}
                    sx={{ bgcolor: showForm ? '#FF5B5B' : '#4318FF' }}
                >
                    {showForm ? 'Отмена' : 'Новое обращение'}
                </Button>
            </Box>

            {showForm && (
                <Paper sx={{ p: 4, borderRadius: '20px', mb: 4, boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' }}>
                    <form onSubmit={handleSubmit}>
                        <Grid container spacing={3}>
                            <Grid item xs={12}>
                                <TextField 
                                    fullWidth label="Тема" 
                                    value={data.subject} 
                                    onChange={e => setData('subject', e.target.value)}
                                    error={!!errors.subject} helperText={errors.subject}
                                />
                            </Grid>
                            <Grid item xs={12}>
                                <TextField 
                                    fullWidth multiline rows={4} 
                                    label="Опишите проблему" 
                                    value={data.message} 
                                    onChange={e => setData('message', e.target.value)}
                                    error={!!errors.message} helperText={errors.message}
                                />
                            </Grid>
                            <Grid item xs={12}>
                                <Button variant="outlined" component="label" startIcon={<CloudUploadIcon />}>
                                    Прикрепить файлы
                                    <input type="file" multiple hidden onChange={e => setData('files', Array.from(e.target.files))} />
                                </Button>
                                {data.files.map((f, i) => (
                                    <Typography key={i} variant="caption" display="block">• {f.name}</Typography>
                                ))}
                            </Grid>
                            <Grid item xs={12}>
                                <Button type="submit" variant="contained" disabled={processing} startIcon={<SendIcon />}>
                                    Отправить
                                </Button>
                            </Grid>
                        </Grid>
                    </form>
                </Paper>
            )}

            <TableContainer component={Paper} sx={{ borderRadius: '20px', boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                <Table>
                    <TableHead sx={{ bgcolor: '#F4F7FE' }}>
                        <TableRow>
                            <TableCell>Дата</TableCell>
                            <TableCell>Тема</TableCell>
                            <TableCell>Статус</TableCell>
                            <TableCell>Файлы</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {tickets.length === 0 ? (
                            <TableRow><TableCell colSpan={4} align="center">У вас пока нет обращений</TableCell></TableRow>
                        ) : (
                            tickets.map((ticket) => (
                                <TableRow key={ticket.id}>
                                    <TableCell>{new Date(ticket.created_at).toLocaleDateString()}</TableCell>
                                    <TableCell sx={{ fontWeight: 'bold' }}>{ticket.subject}</TableCell>
                                    <TableCell>
                                        <Chip 
                                            label={statusMap[ticket.status].label} 
                                            color={statusMap[ticket.status].color} 
                                            size="small" 
                                        />
                                    </TableCell>
                                    <TableCell>{ticket.attachments.length} шт.</TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </TableContainer>
        </ClientLayout>
    );
}
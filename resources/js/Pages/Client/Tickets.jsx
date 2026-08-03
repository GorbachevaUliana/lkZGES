import React, { useState } from 'react';
import { ToastProvider, useToast } from '@/contexts/ToastContext';
import { TICKET_CATEGORIES, getTicketCategoryLabel, TICKET_STATUS_MAP } from '@/constants/statuses';
import ClientLayout from '@/Layouts/ClientLayout';
import { useForm } from '@inertiajs/react';
import SearchIcon from '@mui/icons-material/Search';
import { DataGrid } from '@mui/x-data-grid';
import {
    Paper, TextField, Button, Box, Typography, Grid,
    Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Chip,
    Select, MenuItem, FormControl, InputLabel, InputAdornment, TableSortLabel, InputBase
} from '@mui/material';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import SendIcon from '@mui/icons-material/Send';
import AddIcon from '@mui/icons-material/Add';
import TicketsCardClient from './TicketsCardClient';
import { motion, AnimatePresence } from 'framer-motion';

export default function Tickets({ auth, tickets }) {
    return (
        <ToastProvider>
            <TicketsContent auth={auth} tickets={tickets} />
        </ToastProvider>
    );
}

function TicketsContent({ auth, tickets }) {
    const { showToast } = useToast();
    const [showForm, setShowForm] = useState(false);
    const [selectedTicket, setSelectedTicket] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');

    const columns = [
        {
            field: 'created_at',
            headerName: 'Дата',
            flex: 1,
            valueGetter: (params) => new Date(params).toLocaleDateString()
        },
        {
            field: 'subject',
            headerName: 'Тема',
            flex: 2,
            renderCell: (params) => (
                <Box sx={{ alignItems: 'center' }}>
                    <Typography paddingTop={'3%'}>
                        {params.value}
                    </Typography>
                </Box>
            )
        },
        {
            field: 'category',
            headerName: 'Категория',
            flex: 1.5,
            renderCell: (params) => getTicketCategoryLabel(params.value)
        },
        {
            field: 'status',
            headerName: 'Статус',
            flex: 1,
            renderCell: (params) => {
                const status = TICKET_STATUS_MAP[params.value] || { label: params.value, color: 'default' };

                return (
                    <Chip
                        label={status.label}
                        color={status.color}
                        size="small"
                        sx={{
                            borderRadius: '8px',
                            fontWeight: '700',
                            textTransform: 'uppercase',
                            fontSize: '10px'
                        }}
                    />
                );
            }
        },
        {
            field: 'attachments',
            headerName: 'Файлы',
            flex: 1,
            valueGetter: (params) => `${params?.length || 0} шт.`
        }
    ];

    const { data, setData, post, processing, errors, reset } = useForm({
        user: '',
        category: '',
        subject: '',
        message: '',
        files: [],
    });

    const formVariants = {
        hidden: { opacity: 0, y: -20, scale: 0.95 },
        visible: {
            opacity: 1,
            y: 0,
            scale: 1,
            transition: {
                type: 'spring',
                stiffness: 100,
                damping: 15,
                staggerChildren: 0.1
            }
        },
        exit: { opacity: 0, y: -20, scale: 0.95, transition: { duration: 0.2 } }
    };

    const itemVariants = {
        hidden: { opacity: 0, x: -10 },
        visible: { opacity: 1, x: 0 }
    };

    const filteredRows = tickets.filter(ticket =>
        ticket.subject.toLowerCase().includes(searchQuery.toLowerCase()) ||
        getTicketCategoryLabel(ticket.category).toLowerCase().includes(searchQuery.toLowerCase())
    );

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('client.tickets.store'), {
            onSuccess: () => {
                reset();
                setShowForm(false);
                showToast('Обращение отправлено', 'success');
            },
            onError: (formErrors) => {
                const fileErrorKey = Object.keys(formErrors).find(k => k.startsWith('files'));
                showToast(
                    fileErrorKey ? formErrors[fileErrorKey] : 'Проверьте правильность заполнения формы',
                    'error'
                );
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
                    sx={{ bgcolor: showForm ? '#FF5B5B' : '#4318FF' }}>
                    {showForm ? 'Отмена' : 'Новое обращение'}
                </Button>
            </Box>

            <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
                <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: 400, boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                    <SearchIcon sx={{ color: '#A3AED0' }} />
                    <InputBase
                        placeholder="Поиск по теме..."
                        fullWidth
                        sx={{ ml: 1, py: 1 }}
                        value={searchQuery}
                        onChange={e => setSearchQuery(e.target.value)} />
                </Paper>
            </Box>

            <AnimatePresence>
                {showForm && (
                    <motion.div
                        variants={formVariants}
                        initial="hidden"
                        animate="visible"
                        exit="exit"
                        style={{ overflow: 'hidden' }}>
                        <Paper sx={{ p: 4, borderRadius: '24px', mb: 4, boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' }}>
                            <form onSubmit={handleSubmit}>
                                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>

                                    {/* 1. Выпадающий список темы/категории */}
                                    <motion.div variants={itemVariants}>
                                        <FormControl fullWidth error={!!errors.category}>
                                            <InputLabel id="category-select-label">Выберите тему</InputLabel>
                                            <Select
                                                labelId="category-select-label"
                                                label="Выберите тему"
                                                value={data.category}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    const found = TICKET_CATEGORIES.find(c => c.value === val);
                                                    setData('category', val);
                                                    setData('subject', val === 'other' ? '' : (found?.label || ''));
                                                }}
                                                sx={{
                                                    bgcolor: '#F4F7FE',
                                                    borderRadius: '14px',
                                                    "& .MuiOutlinedInput-notchedOutline": { border: "none" },
                                                    "&:hover .MuiOutlinedInput-notchedOutline": { border: "none" },
                                                    "&.Mui-focused .MuiOutlinedInput-notchedOutline": { border: "none" },
                                                }}>
                                                {TICKET_CATEGORIES.map((c) => (
                                                    <MenuItem key={c.value} value={c.value}>{c.label}</MenuItem>
                                                ))}
                                            </Select>
                                        </FormControl>
                                    </motion.div>

                                    {/* 2. Поле ручного ввода — только для категории "Другое" */}
                                    {data.category === 'other' && (
                                        <motion.div variants={itemVariants} key="manual-subject">
                                            <TextField
                                                fullWidth
                                                label="Уточните тему обращения"
                                                placeholder="Напишите свою тему..."
                                                value={data.subject}
                                                onChange={e => setData('subject', e.target.value)}
                                                error={!!errors.subject}
                                                helperText={errors.subject}
                                                sx={{
                                                    "& .MuiOutlinedInput-root": {
                                                        bgcolor: '#F4F7FE',
                                                        borderRadius: '14px',
                                                        "& fieldset": { border: 'none' },
                                                        "&:hover fieldset": { border: 'none' },
                                                        "&.Mui-focused fieldset": { border: 'none' },
                                                    }
                                                }} />
                                        </motion.div>
                                    )}

                                    {/* 3. Поле сообщения */}
                                    <motion.div variants={itemVariants}>
                                        <TextField
                                            fullWidth
                                            multiline
                                            rows={5}
                                            label="Опишите проблему"
                                            value={data.message}
                                            onChange={e => setData('message', e.target.value)}
                                            error={!!errors.message}
                                            helperText={errors.message}
                                            sx={{
                                                "& .MuiOutlinedInput-root": {
                                                    bgcolor: '#F4F7FE',
                                                    borderRadius: '14px',
                                                    "& fieldset": { border: 'none' },
                                                    "&:hover fieldset": { border: 'none' },
                                                    "&.Mui-focused fieldset": { border: 'none' },
                                                }
                                            }} />
                                    </motion.div>

                                    {/* 4. Кнопки управления */}
                                    <motion.div variants={itemVariants} style={{ display: 'flex', flexDirection: 'column', gap: '16px', alignItems: 'flex-start' }}>
                                        <Button variant="outlined" component="label" startIcon={<CloudUploadIcon />} sx={{ borderRadius: '12px', px: 3, py: 1, textTransform: 'none' }}>
                                            Прикрепить файлы
                                            <input type="file" multiple hidden onChange={e => setData('files', Array.from(e.target.files))} />
                                        </Button>
                                        {Object.keys(errors).filter(k => k.startsWith('files')).map((key) => (
                                            <Typography key={key} variant="caption" color="error" sx={{ display: 'block' }}>
                                                {errors[key]}
                                            </Typography>
                                        ))}

                                        {data.files.map((f, i) => (
                                            <Typography key={i} variant="caption" color="text.secondary" sx={{ ml: 1 }}>• {f.name}</Typography>
                                        ))}

                                        <Button
                                            type="submit"
                                            variant="contained"
                                            disabled={processing}
                                            startIcon={<SendIcon />}
                                            sx={{
                                                bgcolor: '#4318FF',
                                                borderRadius: '14px',
                                                px: 5, py: 1.8,
                                                fontWeight: 'bold',
                                                textTransform: 'none',
                                                '&:hover': { bgcolor: '#3311CC' }
                                            }}>
                                            Отправить запрос
                                        </Button>
                                    </motion.div>
                                </Box>
                            </form>
                        </Paper>
                    </motion.div>
                )}
            </AnimatePresence>

            <Paper sx={{ borderRadius: '20px', overflow: 'hidden', border: 'none', boxShadow: '0px 10px 30px rgba(0,0,0,0.02)' }}>
                <Box sx={{ width: '100%', overflowX: 'auto' }}>
                    <DataGrid
                        rows={filteredRows}
                        columns={columns}
                        autoHeight
                        onRowDoubleClick={(params) => setSelectedTicket(params.row)}
                        disableRowSelectionOnClick
                        initialState={{
                            sorting: {
                                sortModel: [{ field: 'created_at', sort: 'desc' }],
                            },
                            pagination: { paginationModel: { pageSize: 10 } },
                        }}
                        pageSizeOptions={[5, 10, 20]}
                        sx={{
                            border: 'none',
                            '& .MuiDataGrid-columnHeaders': { bgcolor: '#F4F7FE', borderBottom: 'none' },
                            '& .MuiDataGrid-cell': { borderBottom: '1px solid #F4F7FE' },
                            cursor: 'pointer'
                        }} />
                </Box>
            </Paper>

            <TicketsCardClient
                open={Boolean(selectedTicket)}
                onClose={() => setSelectedTicket(null)}
                ticket={selectedTicket} />
        </ClientLayout>
    );
}
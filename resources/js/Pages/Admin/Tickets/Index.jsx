import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Paper, Table, TableBody, TableCell, TableHead, TableRow, Chip, InputBase, Box, Typography, TableContainer } from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';

export default function TicketsIndex({ auth, tickets }) {
    return (
        <AdminLayout user={auth.user}>
            <Box sx={{ p: 3 }}>
                <Typography variant="h4" sx={{ mb: 3, fontWeight: 'bold', color: '#1B2559' }}>Обращения</Typography>
                <Paper sx={{ p: '2px 4px', display: 'flex', alignItems: 'center', width: 400, mb: 3, borderRadius: '50px', boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                    <SearchIcon sx={{ ml: 2, color: '#A3AED0' }} />
                    <InputBase
                        sx={{ ml: 1, flex: 1, "& input:focus": { boxShadow: 'none' } }}
                        placeholder="Поиск по обращениям..."
                    />
                </Paper>

                <TableContainer component={Paper} sx={{ borderRadius: '20px', boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.08)' }}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>ID</TableCell>
                                <TableCell>Пользователь</TableCell>
                                <TableCell>Тема</TableCell>
                                <TableCell>Статус</TableCell>
                                <TableCell>Дата</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {tickets.map((ticket) => (
                                <TableRow key={ticket.id} hover sx={{ cursor: 'pointer' }}>
                                    <TableCell>#{ticket.id}</TableCell>
                                    <TableCell>{ticket.user.name}</TableCell>
                                    <TableCell sx={{ fontWeight: '500' }}>{ticket.subject}</TableCell>
                                    <TableCell>
                                        <Chip label={ticket.status} color="info" variant="outlined" />
                                    </TableCell>
                                    <TableCell>{new Date(ticket.created_at).toLocaleDateString()}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>
            </Box>
        </AdminLayout>
    );
}
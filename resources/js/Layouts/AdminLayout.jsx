import React, { useState } from 'react';
import { Box, AppBar, Toolbar, Typography, Button, Container, Avatar, Menu, MenuItem } from '@mui/material';
import { Link, router } from '@inertiajs/react';

export default function AdminLayout({ user, children }) {
    const [anchorEl, setAnchorEl] = useState(null);

    const hasAccess = (pageId) => {
        if (user.role === 'admin') return true;
        return user.permissions?.includes(pageId);
    };

    return (
        <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh', bgcolor: '#F4F7FE' }}>
            {/* Верхняя панель */}
            <AppBar position="static" sx={{ bgcolor: '#fff', color: '#2B3674', boxShadow: 'none', borderBottom: '1px solid #E0E5F2' }}>
                <Container maxWidth="xl">
                    <Toolbar sx={{ justifyContent: 'space-between' }}>
                        <Box sx={{ display: 'flex', gap: 3, alignItems: 'center' }}>
                            <Typography variant="h6" sx={{ fontWeight: 'bold', mr: 2 }}>Админ-панель</Typography>
                            
                            {/* Навигация */}
                            {hasAccess('dashboard') && (
                            <Button component={Link} href={route('admin.dashboard')} sx={{ color: route().current('admin.dashboard') ? '#4318FF' : '#A3AED0' }}>
                                Dashboard
                            </Button>
                            )}
                            {hasAccess('clients') && (
                            <Button component={Link} href={route('admin.clients.index')} sx={{ color: route().current('admin.clients.index') ? '#4318FF' : '#A3AED0' }}>
                                Потребители
                            </Button>
                            )}
                            {hasAccess('tickets') && (
                            <Button component={Link} href={route('admin.tickets.index')} sx={{ color: route().current('admin.tickets.index') ? '#4318FF' : '#A3AED0' }}>
                                Обращения
                            </Button>
                            )}
                            {hasAccess('staff') && (
                            <Button component={Link} href={route('admin.staff.index')} sx={{ color: route().current('admin.staff.index') ? '#4318FF' : '#A3AED0' }}>
                                Сотрудники
                            </Button>
                            )}
                        </Box>

                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                            <Typography variant="body2" sx={{ fontWeight: '500' }}>{user.name}</Typography>
                            <Avatar 
                                sx={{ cursor: 'pointer', bgcolor: '#4318FF' }} 
                                onClick={(e) => setAnchorEl(e.currentTarget)}
                            >
                                {user.name[0]}
                            </Avatar>
                            <Menu anchorEl={anchorEl} open={Boolean(anchorEl)} onClose={() => setAnchorEl(null)}>
                                <MenuItem onClick={() => router.post(route('logout'))}>Выход</MenuItem>
                            </Menu>
                        </Box>
                    </Toolbar>
                </Container>
            </AppBar>

            {/* Контент страницы */}
            <Container maxWidth="xl" sx={{ mt: 4, mb: 4, flexGrow: 1 }}>
                {children}
            </Container>
        </Box>
    );
}
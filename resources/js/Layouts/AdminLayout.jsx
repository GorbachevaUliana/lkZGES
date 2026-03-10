import { usePage, router } from '@inertiajs/react';
import React, {useState} from 'react';
import { Box, AppBar, Container, Toolbar, Typography, Button, Link, Avatar, Menu, MenuItem } from '@mui/material';

export default function AdminLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    
    const [anchorEl, setAnchorEl] = useState(null);
    const userPermissions = user?.permissions || [];
    const userRole = user?.role;

    const hasAccess = (pageId) => {
        if (userRole === 'admin') return true;
        return userPermissions.includes(pageId);
    };

    const menuItems = [
        { id: 'dashboard', label: 'Главная', route: 'admin.dashboard' },
        { id: 'clients', label: 'Потребители', route: 'admin.clients.index' },
        { id: 'tickets', label: 'Обращения', route: 'admin.tickets.index' },
        { id: 'staff', label: 'Сотрудники', route: 'admin.staff.index' },
    ];
    return (
        <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh', bgcolor: '#F4F7FE' }}>
            {/* Верхняя панель */}
            <AppBar position="static" sx={{ bgcolor: '#fff', color: '#2B3674', boxShadow: 'none', borderBottom: '1px solid #E0E5F2' }}>
                <Container maxWidth="xl">
                    <Toolbar sx={{ justifyContent: 'space-between' }}>
                        <Box sx={{ display: 'flex', gap: 3, alignItems: 'center' }}>
                            <Typography variant="h6" sx={{ fontWeight: 'bold', mr: 2 }}>Админ-панель</Typography>
                            
                            {menuItems.map(item => (
                                hasAccess(item.id) && (
                                    <Button 
                                        key={item.id}
                                        component={Link} 
                                        href={route(item.route)} 
                                        sx={{ color: route().current(item.route) ? '#4318FF' : '#A3AED0' }}
                                    >
                                        {item.label}
                                    </Button>
                                )
                            ))}
                        </Box>

                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                            <Typography variant="body2" sx={{ fontWeight: '500' }}>
                                {user?.name || 'Загрузка...'}
                            </Typography>
                            
                            <Avatar 
                                sx={{ cursor: 'pointer', bgcolor: '#4318FF' }} 
                                onClick={(e) => setAnchorEl(e.currentTarget)}
                            >
                                {user?.name ? user.name[0] : '?'}
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
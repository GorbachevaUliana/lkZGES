import { usePage, router } from '@inertiajs/react';
import React, {useState} from 'react';
import { Box, AppBar, Container, Toolbar, Typography, Button, Link, Avatar, Menu, MenuItem, IconButton, Drawer, List, ListItem, ListItemButton, ListItemText, useMediaQuery, useTheme } from '@mui/material';
import MenuIcon from '@mui/icons-material/Menu';

export default function AdminLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const theme = useTheme();
    // Проблема №19: ряд кнопок в AppBar никак не адаптировался под узкий
    // экран — на мобильном либо переносился некрасиво, либо вылезал за
    // пределы экрана. Теперь на мобильном — гамбургер с выпадающим списком.
    const isMobile = useMediaQuery(theme.breakpoints.down('md'));
    const [mobileNavOpen, setMobileNavOpen] = useState(false);

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
        { id: 'applications', label: 'Заявки', route: 'admin.applications.index' },
        { id: 'readings', label: 'Показания', route: 'admin.readings.index' }
    ];
    return (
        <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh', bgcolor: '#F4F7FE', width: '100%', maxWidth: '100%',}}>
            {/* Верхняя панель */}
            <AppBar position="sticky" sx={{ bgcolor: '#fff', color: '#2B3674', boxShadow: 'none', borderBottom: '1px solid #E0E5F2'}}>
                <Container maxWidth="xl">
                    <Toolbar sx={{ justifyContent: 'space-between' }}>
                        <Box sx={{ display: 'flex', gap: 3, alignItems: 'center' }}>
                            {isMobile && (
                                <IconButton onClick={() => setMobileNavOpen(true)} edge="start" sx={{ mr: 1 }}>
                                    <MenuIcon />
                                </IconButton>
                            )}
                            <Typography variant="h6" sx={{ fontWeight: 'bold', mr: 2 }}>Админ-панель</Typography>

                            {!isMobile && menuItems.map(item => (
                                hasAccess(item.id) && (
                                    <Button 
                                        key={item.id}
                                        component={Link} 
                                        href={route(item.route)} 
                                        sx={{ color: route().current(item.route) ? '#4318FF' : '#A3AED0' }}>
                                        {item.label}
                                    </Button>
                                )
                            ))}
                        </Box>

                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                            <Typography variant="body2" sx={{ fontWeight: '500', display: { xs: 'none', sm: 'block' } }}>
                                {user?.name || 'Загрузка...'}
                            </Typography>
                            
                            <Avatar 
                                sx={{ cursor: 'pointer', bgcolor: '#4318FF' }} 
                                onClick={(e) => setAnchorEl(e.currentTarget)}>
                                {user?.name ? user.name[0] : '?'}
                            </Avatar>

                            <Menu anchorEl={anchorEl} open={Boolean(anchorEl)} onClose={() => setAnchorEl(null)}>
                                <MenuItem onClick={() => router.post(route('logout'))}>Выход</MenuItem>
                            </Menu>
                        </Box>
                    </Toolbar>
                </Container>
            </AppBar>

            {isMobile && (
                <Drawer anchor="left" open={mobileNavOpen} onClose={() => setMobileNavOpen(false)}>
                    <Box sx={{ width: 260, pt: 2 }} role="presentation">
                        <List>
                            {menuItems.map(item => (
                                hasAccess(item.id) && (
                                    <ListItem key={item.id} disablePadding>
                                        <ListItemButton
                                            component={Link}
                                            href={route(item.route)}
                                            onClick={() => setMobileNavOpen(false)}
                                            sx={{ color: route().current(item.route) ? '#4318FF' : '#2B3674' }}>
                                            <ListItemText primary={item.label} />
                                        </ListItemButton>
                                    </ListItem>
                                )
                            ))}
                        </List>
                    </Box>
                </Drawer>
            )}

            {/* Контент страницы */}
            <Container
                maxWidth="xl"
                sx={{
                    mt: 4,
                    mb: 4,
                    flexGrow: 1,
                    minWidth: 0,
                    maxWidth: '100%',
                    boxSizing: 'border-box',
                }}
            >
                {children}
            </Container>
        </Box>
    );
}
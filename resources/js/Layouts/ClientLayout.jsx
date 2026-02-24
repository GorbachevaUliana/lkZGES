import React from 'react';
import { Box, Drawer, List, ListItem, ListItemButton, ListItemIcon, ListItemText, Typography, Avatar, Divider } from '@mui/material';
import { Person as PersonIcon, Description as DescriptionIcon, QuestionAnswer as MessageIcon, Logout as LogoutIcon, Home as HomeIcon } from '@mui/icons-material';
import { Link, router } from '@inertiajs/react';

const drawerWidth = 280;

export default function ClientLayout({ user, children, title }) {
    const menuItems = [
        { label: 'Главная', icon: <HomeIcon />, href: route('client.dashboard'), active: route().current('client.dashboard') },
        { label: 'Мой профиль', icon: <PersonIcon />, href: route('client.profile'), active: route().current('client.profile') },
        { label: 'Документы', icon: <DescriptionIcon />, href: route('client.documents'), active: route().current('client.documents') },
        { label: 'Обращения', icon: <MessageIcon />, href: route('client.tickets.index'), active: route().current('client.tickets.index') },
    ];

    return (
        <Box sx={{ display: 'flex', bgcolor: '#F4F7FE', minHeight: '100vh' }}>
            <Drawer
                variant="permanent"
                sx={{
                    width: drawerWidth,
                    flexShrink: 0,
                    '& .MuiDrawer-paper': { width: drawerWidth, boxSizing: 'border-box', borderRight: 'none', bgcolor: '#fff' },
                }}
            >
                <Box sx={{ p: 3, display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Avatar sx={{ bgcolor: '#4318FF' }}>{user.name[0]}</Avatar>
                    <Box>
                        <Typography variant="subtitle1" fontWeight="bold">{user.name}</Typography>
                        <Typography variant="body2" color="text.secondary">Потребитель</Typography>
                    </Box>
                </Box>
                <Divider sx={{ mx: 2, mb: 2 }} />
                <List sx={{ px: 2 }}>
                    {menuItems.map((item) => (
                        <ListItem key={item.label} disablePadding sx={{ mb: 1 }}>
                            <ListItemButton 
                                component={Link} 
                                href={item.href}
                                sx={{ 
                                    borderRadius: '12px',
                                    bgcolor: item.active ? '#F4F7FE' : 'transparent',
                                    color: item.active ? '#4318FF' : '#A3AED0',
                                    '& .MuiListItemIcon-root': { color: item.active ? '#4318FF' : '#A3AED0' }
                                }}
                            >
                                <ListItemIcon>{item.icon}</ListItemIcon>
                                <ListItemText primary={item.label} primaryTypographyProps={{ fontWeight: item.active ? 'bold' : 'medium' }} />
                            </ListItemButton>
                        </ListItem>
                    ))}
                </List>
                <Box sx={{ mt: 'auto', p: 2 }}>
                    <ListItemButton onClick={() => router.post(route('logout'))} sx={{ borderRadius: '12px', color: '#FF5B5B' }}>
                        <ListItemIcon sx={{ color: '#FF5B5B' }}><LogoutIcon /></ListItemIcon>
                        <ListItemText primary="Выход" />
                    </ListItemButton>
                </Box>
            </Drawer>

            <Box component="main" sx={{ flexGrow: 1, p: 4 }}>
                <Typography variant="h4" fontWeight="bold" sx={{ mb: 4, color: '#1B2559' }}>{title}</Typography>
                {children}
            </Box>
        </Box>
    );
}
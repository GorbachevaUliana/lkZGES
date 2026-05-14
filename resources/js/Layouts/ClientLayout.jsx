import React from 'react';
import { Box, Drawer, List, ListItem, ListItemButton, ListItemIcon, ListItemText, Typography, Avatar, Divider, Chip, Alert } from '@mui/material';
import { 
    Person as PersonIcon, 
    Description as DescriptionIcon, 
    QuestionAnswer as MessageIcon, 
    Logout as LogoutIcon, 
    Home as HomeIcon,
    HourglassEmpty as PendingIcon,
    CheckCircle as ActiveIcon,
    Error as ErrorIcon,
    Autorenew as ProcessingIcon
} from '@mui/icons-material';
import { ElectricMeter } from '@mui/icons-material';
import { Link, router, usePage } from '@inertiajs/react';

const drawerWidth = 280;

export default function ClientLayout({ user, children, title, application, properties, hasActiveProperties }) {
    const { props } = usePage();
    const userData = user || props?.auth?.user;
    const applicationData = application || props?.application;
    const propertiesData = properties || props?.properties;
    const hasActive = hasActiveProperties ?? props?.hasActiveProperties ?? false;

    // ИСПРАВЛЕНИЕ: Проверяем наличие активных объектов, а не только статус заявки
    // Пользователь с активными properties имеет полный доступ
    const canUseFullFeatures = hasActive || (propertiesData && propertiesData.some(p => p.status === 'active' && p.account_number));

    const currentStatus = applicationData?.status || 'pending';
    const isApproved = currentStatus === 'approved';
    const isRejected = currentStatus === 'rejected';
    const hasApplication = !!applicationData;

    // ИСПРАВЛЕНИЕ: Меню зависит от наличия активных объектов
    const menuItems = !canUseFullFeatures && hasApplication 
        ? [
            // Для pending/processing/rejected - урезанное меню
            { label: 'Главная', icon: <HomeIcon />, href: route('client.dashboard'), active: route().current('client.dashboard') },
            { label: 'Мои документы', icon: <DescriptionIcon />, href: route('client.documents'), active: route().current('client.documents') },
        ]
        : [
            // Полное меню при наличии активных объектов
            { label: 'Главная', icon: <HomeIcon />, href: route('client.dashboard'), active: route().current('client.dashboard') },
            { label: 'Мой профиль', icon: <PersonIcon />, href: route('client.profile'), active: route().current('client.profile') },
            { label: 'Документы', icon: <DescriptionIcon />, href: route('client.documents'), active: route().current('client.documents') },
            { label: 'Обращения', icon: <MessageIcon />, href: route('client.tickets.index'), active: route().current('client.tickets.index') },
            { label: 'Показания', icon: <ElectricMeter />, href: route('client.readings'), active: route().current('client.readings')},
        ];

    return (
        <Box sx={{ display: 'flex', bgcolor: '#F4F7FE', minHeight: '100vh' }}>
            <Drawer
                variant="permanent"
                sx={{
                    width: drawerWidth,
                    flexShrink: 0,
                    '& .MuiDrawer-paper': { width: drawerWidth, boxSizing: 'border-box', borderRight: 'none', bgcolor: '#fff' },
                }}>
                {/* Шапка профиля с динамическим статусом */}
                <Box sx={{ p: 3, display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Avatar sx={{ bgcolor: '#4318FF'}}>
                        {userData?.name?.[0] || '?'}
                    </Avatar>
                    <Box>
                        <Typography variant="subtitle1" fontWeight="bold">{userData?.name}</Typography>
                        <Box display="flex" alignItems="center" gap={1}>
                        </Box>
                    </Box>
                </Box>
                <Divider sx={{ mx: 2, mb: 2 }} />
                {/* ИСПРАВЛЕНИЕ: Предупреждение показываем только без активных объектов */}
                {!canUseFullFeatures && hasApplication && (
                    <Alert 
                        severity={isRejected ? 'error' : 'info'} 
                        sx={{ mx: 2, mb: 2, borderRadius: '12px', fontSize: '12px' }}>
                        {isRejected ? (
                            <>
                                Ваша заявка была отклонена.
                                {applicationData?.admin_comment && (
                                    <><br /><strong>Причина:</strong> {applicationData.admin_comment}</>
                                )}
                            </>
                        ) : (
                            <>
                                Ваша заявка на заключение договора рассматривается. 
                                Функция обращений станет доступна после одобрения.
                            </>
                        )}
                    </Alert>
                )}
                
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
                                }}>
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
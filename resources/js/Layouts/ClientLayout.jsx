import React from 'react';
import { Box, Drawer, List, ListItem, ListItemButton, ListItemIcon, ListItemText, Typography, Avatar, Divider, Chip, Alert, AppBar, Toolbar, IconButton, useMediaQuery, useTheme } from '@mui/material';
import {
    Person as PersonIcon,
    Description as DescriptionIcon,
    QuestionAnswer as MessageIcon,
    Logout as LogoutIcon,
    Home as HomeIcon,
    HourglassEmpty as PendingIcon,
    CheckCircle as ActiveIcon,
    Error as ErrorIcon,
    Autorenew as ProcessingIcon,
    Menu as MenuIcon
} from '@mui/icons-material';
import { ElectricMeter } from '@mui/icons-material';
import { Link, router, usePage } from '@inertiajs/react';
import DraftBanner from '@/Components/DraftBanner';

const drawerWidth = 280;

export default function ClientLayout({ user, children, title, application, properties, hasActiveProperties }) {
    const { props } = usePage();
    const theme = useTheme();
    const isMobile = useMediaQuery(theme.breakpoints.down('md'));
    const [mobileOpen, setMobileOpen] = React.useState(false);
    const draftData = props?.draft;
    const userData = user || props?.auth?.user;
    const applicationData = application || props?.application;
    const propertiesData = properties || props?.properties;
    const hasActive = hasActiveProperties ?? props?.hasActiveProperties ?? false;

    const canUseFullFeatures = hasActive || (propertiesData && propertiesData.some(p => p.status === 'active' && p.account_number));

    const currentStatus = applicationData?.status || 'pending';
    const isApproved = currentStatus === 'approved';
    const isRejected = currentStatus === 'rejected';
    const hasApplication = !!applicationData;

    const menuItems = !canUseFullFeatures && hasApplication 
        ? [
            { label: 'Главная', icon: <HomeIcon />, href: route('client.dashboard'), active: route().current('client.dashboard') },
            { label: 'Мои документы', icon: <DescriptionIcon />, href: route('client.documents'), active: route().current('client.documents') },
        ]
        : [
            { label: 'Главная', icon: <HomeIcon />, href: route('client.dashboard'), active: route().current('client.dashboard') },
            { label: 'Мой профиль', icon: <PersonIcon />, href: route('client.profile'), active: route().current('client.profile') },
            { label: 'Документы', icon: <DescriptionIcon />, href: route('client.documents'), active: route().current('client.documents') },
            { label: 'Обращения', icon: <MessageIcon />, href: route('client.tickets.index'), active: route().current('client.tickets.index') },
            { label: 'Показания', icon: <ElectricMeter />, href: route('client.readings'), active: route().current('client.readings')},
        ];

    const drawerContent = (
        <>
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
                            Function ticket will be available after approval
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
                            onClick={() => isMobile && setMobileOpen(false)}
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
        </>
    );

    return (
        <Box
            sx={{
                display: 'flex',
                bgcolor: '#F4F7FE',
                minHeight: '100vh',
                width: '100%',
                maxWidth: '100%',
            }}
        >
            {isMobile && (
                <AppBar
                    position="fixed"
                    elevation={0}
                    sx={{ bgcolor: '#fff', color: '#1B2559', borderBottom: '1px solid #E9EDF7' }}
                >
                    <Toolbar>
                        <IconButton onClick={() => setMobileOpen(true)} edge="start" sx={{ mr: 2 }}>
                            <MenuIcon />
                        </IconButton>
                        <Typography variant="subtitle1" fontWeight="bold" sx={{ color: '#1B2559' }}>
                            {title}
                        </Typography>
                    </Toolbar>
                </AppBar>
            )}

            <Drawer
                variant={isMobile ? 'temporary' : 'permanent'}
                open={isMobile ? mobileOpen : true}
                onClose={() => setMobileOpen(false)}
                ModalProps={{ keepMounted: true }}
                sx={{
                    width: isMobile ? 0 : drawerWidth,
                    flexShrink: 0,
                    '& .MuiDrawer-paper': { width: drawerWidth, boxSizing: 'border-box', borderRight: 'none', bgcolor: '#fff' },
                }}>
                {drawerContent}
            </Drawer>

            <Box
                component="main"

                sx={{
                    flexGrow: 1,
                    minWidth: 0,
                    width: isMobile ? '100%' : 0,
                    p: { xs: 2, md: 4 },
                    pt: isMobile ? 10 : 4,
                    boxSizing: 'border-box',
                    pb: draftData ? 16 : { xs: 2, md: 4 },
                }}
            >
                {!isMobile && (
                    <Typography variant="h4" fontWeight="bold" sx={{ mb: 4, color: '#1B2559' }}>
                        {title}
                    </Typography>
                )}
                {children}
            </Box>
            <DraftBanner draft={draftData}/>
        </Box>
    );
}
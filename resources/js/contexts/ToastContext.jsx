import React, { createContext, useContext, useState } from 'react';
import { Box, Paper, Typography, IconButton } from '@mui/material';
import {
    CheckCircle as CheckCircleIcon,
    Warning as WarningIcon,
    Error as ErrorIcon,
    Info as InfoIcon,
    Close as CloseIcon,
} from '@mui/icons-material';

const ToastContext = createContext(null);

export const useToast = () => {
    const ctx = useContext(ToastContext);
    if (!ctx) throw new Error('useToast must be used within ToastProvider');
    return ctx;
};

const ICONS = {
    success: <CheckCircleIcon sx={{ color: '#22C55E' }} />,
    warning: <WarningIcon    sx={{ color: '#F59E0B' }} />,
    error:   <ErrorIcon      sx={{ color: '#EF4444' }} />,
    info:    <InfoIcon       sx={{ color: '#3B82F6' }} />,
};

const BG_COLORS = {
    success: '#ECFDF5',
    warning: '#FFFBEB',
    error:   '#FEF2F2',
    info:    '#EFF6FF',
};

const BORDER_COLORS = {
    success: '#22C55E',
    warning: '#F59E0B',
    error:   '#EF4444',
    info:    '#3B82F6',
};

const ToastItem = ({ toast, onClose }) => (
    <Paper
        elevation={6}
        sx={{
            display: 'flex',
            alignItems: 'center',
            gap: 1.5,
            p: 2,
            mb: 1,
            minWidth: 320,
            maxWidth: 450,
            bgcolor: BG_COLORS[toast.type]     ?? BG_COLORS.info,
            borderLeft: `4px solid ${BORDER_COLORS[toast.type] ?? BORDER_COLORS.info}`,
            borderRadius: '8px',
            animation: 'slideIn 0.3s ease-out',
            '@keyframes slideIn': {
                '0%':   { transform: 'translateX(100%)', opacity: 0 },
                '100%': { transform: 'translateX(0)',    opacity: 1 },
            },
        }}
    >
        {ICONS[toast.type] ?? ICONS.info}
        <Typography variant="body2" sx={{ flex: 1, color: '#1F2937' }}>
            {toast.message}
        </Typography>
        <IconButton size="small" onClick={() => onClose(toast.id)}>
            <CloseIcon fontSize="small" />
        </IconButton>
    </Paper>
);

export const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const showToast = (message, type = 'info', duration = 5000) => {
        const id = Date.now();
        setToasts(prev => [...prev, { id, message, type }]);
        if (duration > 0) {
            setTimeout(() => setToasts(prev => prev.filter(t => t.id !== id)), duration);
        }
    };

    const removeToast = (id) => setToasts(prev => prev.filter(t => t.id !== id));

    return (
        <ToastContext.Provider value={{ showToast, removeToast }}>
            {children}
            <Box sx={{ position: 'fixed', top: 20, right: 20, zIndex: 9999, display: 'flex', flexDirection: 'column', alignItems: 'flex-end' }}>
                {toasts.map(toast => (
                    <ToastItem key={toast.id} toast={toast} onClose={removeToast} />
                ))}
            </Box>
        </ToastContext.Provider>
    );
};
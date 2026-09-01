import React from 'react';
import { Paper, Box, Typography, Button, IconButton } from '@mui/material';
import { Close as CloseIcon, EditNote as EditNoteIcon } from '@mui/icons-material';
import { Link, router } from '@inertiajs/react';

const DRAWER_WIDTH = 280;

export default function DraftBanner({ draft }) {
    if (!draft) {
        return null;
    }

    const slug = `application-${draft.client_type}`;

    const handleDelete = () => {
        if (window.confirm('Удалить черновик заявки? Это действие нельзя отменить.')) {
            router.delete(route('client.draft.destroy'), { preserveScroll: true });
        }
    };

    return (
        <Box
            sx={{
                position: 'fixed',
                bottom: 0,
                right: 0,
                left: { xs: 0, md: `${DRAWER_WIDTH}px` }, // не залезаем под боковое меню
                zIndex: (theme) => theme.zIndex.drawer + 2,
                p: 2,
                pointerEvents: 'none', // пустые зоны панели не перехватывают клики
            }}
        >
            <Paper
                elevation={6}
                sx={{
                    pointerEvents: 'auto',
                    p: 2,
                    display: 'flex',
                    alignItems: 'center',
                    gap: 2,
                    borderRadius: '16px',
                    border: '1px dashed #4318FF',
                    bgcolor: 'rgba(238, 234, 255, 0.98)',
                    backdropFilter: 'blur(4px)',
                }}
            >
                <Box sx={{ color: '#4318FF', display: 'flex' }}>
                    <EditNoteIcon />
                </Box>
                <Box sx={{ flexGrow: 1 }}>
                    <Typography variant="body1" fontWeight="bold">
                        У вас есть незаполненный черновик
                    </Typography>
                    <Typography variant="caption" color="text.secondary">
                        Вы начали оформление заявки, но не завершили её.
                    </Typography>
                </Box>
                <Button
                    component={Link}
                    href={route('application.show', { slug })}
                    variant="contained"
                    sx={{ borderRadius: '10px', textTransform: 'none' }}
                >
                    Продолжить
                </Button>
                <IconButton onClick={handleDelete} size="small" aria-label="Удалить черновик">
                    <CloseIcon fontSize="small" />
                </IconButton>
            </Paper>
        </Box>
    );
}
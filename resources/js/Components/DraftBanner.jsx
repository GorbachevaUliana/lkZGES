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
                    // На мобильном плашка перестраивается вертикально, иначе
                    // иконка+текст+кнопка+крестик не влезают в одну строку и
                    // кнопка «Продолжить» ломается по слогам.
                    flexDirection: { xs: 'column', sm: 'row' },
                    alignItems: { xs: 'stretch', sm: 'center' },
                    gap: { xs: 1.5, sm: 2 },
                    borderRadius: '16px',
                    border: '1px dashed #4318FF',
                    bgcolor: 'rgba(238, 234, 255, 0.98)',
                    backdropFilter: 'blur(4px)',
                }}
            >
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexGrow: 1 }}>
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
                    {/* Крестик прижат к тексту в обоих режимах */}
                    <IconButton onClick={handleDelete} size="small" aria-label="Удалить черновик">
                        <CloseIcon fontSize="small" />
                    </IconButton>
                </Box>
                <Button
                    component={Link}
                    href={route('application.show', { slug })}
                    variant="contained"
                    fullWidth={false}
                    sx={{
                        borderRadius: '10px',
                        textTransform: 'none',
                        whiteSpace: 'nowrap',
                        width: { xs: '100%', sm: 'auto' },
                    }}
                >
                    Продолжить
                </Button>
            </Paper>
        </Box>
    );
}
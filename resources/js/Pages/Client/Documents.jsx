import React from 'react';
import ClientLayout from '@/Layouts/ClientLayout';
import { Grid, Paper, Typography, Box, IconButton } from '@mui/material';
import { Download as DownloadIcon, InsertDriveFile as FileIcon } from '@mui/icons-material';

export default function Documents({ auth, documents, application }) {

    const applicationStatus = application?.status;
    return (
        <ClientLayout user={auth.user}
        title="Мои документы"
        application={application}
        applicationStatus={applicationStatus}>
            {documents.length === 0 ? (
                <Box textAlign="center" py={10}>
                    <Typography color="text.secondary">Администратор еще не загрузил документы для вашего аккаунта.</Typography>
                </Box>
            ) : (
                <Grid container spacing={3}>
                    {documents.map((doc) => (
                        <Grid item xs={12} sm={6} md={4} key={doc.id}>
                            <Paper sx={{ 
                                p: 2, 
                                borderRadius: '16px',
                                display: 'flex',
                                alignItems: 'center',
                                gap: 2,
                                border: '1px solid #E0E5F2',
                                transition: '0.3s',
                                '&:hover': { boxShadow: '0px 10px 20px rgba(112, 144, 176, 0.15)' }
                            }}>
                                <Box sx={{ p: 1.5, bgcolor: '#F4F7FE', borderRadius: '12px', color: '#4318FF' }}>
                                    <FileIcon />
                                </Box>
                                <Box sx={{ flexGrow: 1, overflow: 'hidden' }}>
                                    <Typography variant="body1" fontWeight="bold" noWrap>{doc.name}</Typography>
                                    <Typography variant="caption" color="text.secondary">Файл системы</Typography>
                                </Box>
                                <IconButton href={doc.url} target="_blank" color="primary">
                                    <DownloadIcon />
                                </IconButton>
                            </Paper>
                        </Grid>
                    ))}
                </Grid>
            )}
        </ClientLayout>
    );
}
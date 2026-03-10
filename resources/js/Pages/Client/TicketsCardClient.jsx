import React from 'react';
import { 
    Dialog, DialogTitle, DialogContent, IconButton, 
    Typography, Box, Chip, Paper, Divider, Button
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';

export default function TicketsCardClient({ open, onClose, ticket }) {
    if (!ticket) return null;
    const clientFiles = ticket.attachments?.filter(a => !a.is_admin) || [];
    const adminFiles = ticket.attachments?.filter(a => a.is_admin) || [];

    const statusMap = {
        open: { label: 'Новое', color: 'primary' },
        in_progress: { label: 'В работе', color: 'warning' },
        closed: { label: 'Закрыто', color: 'success' },
    };

    return (
        <Dialog 
            open={open} 
            onClose={onClose}
            maxWidth="sm"
            fullWidth
            PaperProps={{ 
                sx: { borderRadius: '24px', p: 1, boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' } 
            }}
        >
            <DialogTitle sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', pb: 1 }}>
                <Typography variant="h6" sx={{ fontWeight: 'bold', color: '#1B2559' }}>
                    Просмотр обращения №{ticket.id}
                </Typography>
                <IconButton onClick={onClose}>
                    <CloseIcon />
                </IconButton>
            </DialogTitle>
            
            <DialogContent>
                <Box sx={{ mb: 3 }}>
                    <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
                        <Chip 
                            label={statusMap[ticket.status].label} 
                            color={statusMap[ticket.status].color} 
                            size="small" 
                            sx={{ fontWeight: 'bold', borderRadius: '8px' }}
                        />
                        <Typography variant="caption" sx={{ color: '#A3AED0', alignSelf: 'center' }}>
                            Отправлено: {new Date(ticket.created_at).toLocaleString()}
                        </Typography>
                    </Box>
                    
                    <Typography variant="subtitle1" sx={{ fontWeight: 'bold', mb: 1, color: '#2B3674' }}>
                        Тема: {ticket.subject}
                    </Typography>
                    
                    <Typography variant="caption" sx={{ color: '#A3AED0', display: 'block', mb: 0.5 }}>
                        Ваше сообщение:
                    </Typography>
                    <Paper variant="outlined" sx={{ p: 2, borderRadius: '12px', bgcolor: '#F4F7FE', border: 'none', mb: 3 }}>
                        <Typography variant="body2" sx={{ whiteSpace: 'pre-line', color: '#2B3674' }}>
                            {ticket.message}
                        </Typography>
                    </Paper>

                    {/* ВЛОЖЕНИЯ КЛИЕНТА */}
                    {clientFiles.length > 0 && (
                        <Box sx={{ mt: 2, mb: 3 }}>
                            <Typography variant="caption" sx={{ color: '#A3AED0', fontWeight: 'bold' }}>
                                Ваши прикрепленные файлы:
                            </Typography>
                            <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                                {clientFiles.map((file) => (
                                    <Button
                                        key={file.id}
                                        href={file.url} 
                                        target="_blank"
                                        download={file.file_name}
                                        size="small"
                                        variant="outlined"
                                        sx={{ borderRadius: '8px', textTransform: 'none', fontSize: '12px' }}
                                    >
                                        📄 {file.file_name}
                                    </Button>
                                ))}
                            </Box>
                        </Box>
                    )}

                    <Divider sx={{ my: 2 }} />

                    {ticket.admin_reply && (
                        <Box sx={{ mt: 2 }}>
                            <Typography variant="subtitle2" sx={{ color: '#4318FF', fontWeight: 'bold', mb: 1 }}>
                                Ответ организации:
                            </Typography>
                            <Paper sx={{ p: 2, borderRadius: '12px', bgcolor: '#E9EDF7', border: '1px solid #E0E5F2' }}>
                                <Typography variant="body2" sx={{ color: '#1B2559' }}>
                                    {ticket.admin_reply}
                                </Typography>
                                
                                {/* ФАЙЛЫ ОТ АДМИНА */}
                                {adminFiles.length > 0 && (
                                    <Box sx={{ mt: 2, pt: 2, borderTop: '1px dashed #A3AED0' }}>
                                        <Typography variant="caption" sx={{ color: '#707EAE', display: 'block', mb: 1 }}>
                                            Приложенные документы к ответу:
                                        </Typography>
                                        {adminFiles.map((file) => (
                                            <Button
                                                key={file.id}
                                                href={file.url}
                                                target="_blank"
                                                download={file.file_name}
                                                size="small"
                                                sx={{ color: '#4318FF', textTransform: 'none', justifyContent: 'flex-start' }}
                                            >
                                                📎 {file.file_name}
                                            </Button>
                                        ))}
                                    </Box>
                                )}
                            </Paper>
                        </Box>
                    )}
                </Box>
            </DialogContent>
        </Dialog>
    );
}
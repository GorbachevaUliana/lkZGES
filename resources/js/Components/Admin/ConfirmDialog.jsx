import React from 'react';
import { Dialog, DialogContent, DialogContentText, DialogActions, Button, Box, Typography } from '@mui/material';

export default function ConfirmDialog({ open, title, content, onConfirm, onClose }) {
    return (
        <Dialog open={open} onClose={onClose} sx={{ '& .MuiDialog-paper': { borderRadius: '20px' } }}>
            <Box sx={{ bgcolor: '#111C44', color: 'white', px: 3, py: 2 }}>
                <Typography variant="h6" fontWeight="bold">{title}</Typography>
            </Box>
            <DialogContent sx={{ mt: 2 }}>
                <DialogContentText sx={{ color: '#2B3674' }}>{content}</DialogContentText>
            </DialogContent>
            <DialogActions sx={{ p: 3 }}>
                <Button onClick={onClose} sx={{ color: '#A3AED0', fontWeight: 'bold' }}>Отмена</Button>
                <Button 
                    onClick={() => onConfirm()}
                    variant="contained"
                    sx={{ bgcolor: '#EE5D50', '&:hover': { bgcolor: '#E31A1A' }, borderRadius: '12px', px: 3 }}
                >
                    Удалить
                </Button>
            </DialogActions>
        </Dialog>
    );
}
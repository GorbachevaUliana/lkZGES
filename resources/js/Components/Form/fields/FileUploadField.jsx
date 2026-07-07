import React, { useState } from 'react';
import { Grid, Box, Typography, Button, IconButton } from '@mui/material';
import { CloudUpload as CloudUploadIcon, AttachFile as AttachFileIcon, Add as AddIcon, Close as CloseIcon } from '@mui/icons-material';
import { useToast } from '@/contexts/ToastContext';

const formatFileSize = (bytes) => {
    if (bytes < 1024)        return bytes + ' байт';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' КБ';
    return (bytes / (1024 * 1024)).toFixed(1) + ' МБ';
};

export default function FileUploadField({ block, fieldKey, value, onChange, error }) {
    const { label, is_required, allow_multiple, accepted_types, max_size, max_files, helper_text } = block.data;
    const { showToast } = useToast();
    const [dragOver, setDragOver] = useState(false);

    const files            = value || [];
    const allowMultiple    = allow_multiple !== false;
    const maxSizeMb        = max_size  || 10;
    const maxFilesCount    = max_files || 5;
    const acceptedExts     = accepted_types ? Object.keys(accepted_types) : [];
    const acceptString     = acceptedExts.length > 0 ? acceptedExts.map(e => `.${e}`).join(',') : '*';

    const addFiles = (newFiles) => {
        const maxBytes  = maxSizeMb * 1024 * 1024;
        const validFiles = [];

        for (const file of newFiles) {
            if (file.size > maxBytes) {
                showToast(`Файл "${file.name}" превышает ${maxSizeMb} МБ`, 'error');
                continue;
            }
            if (acceptedExts.length > 0) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!acceptedExts.includes(ext)) {
                    showToast(`Формат .${ext} не разрешён. Разрешены: ${acceptedExts.join(', ').toUpperCase()}`, 'error');
                    continue;
                }
            }
            validFiles.push(file);
        }

        if (!allowMultiple) {
            if (validFiles.length > 0) {
                onChange([validFiles[0]]);
                showToast(`Файл "${validFiles[0].name}" добавлен`, 'success');
            }
            return;
        }

        const canAdd = maxFilesCount - files.length;
        if (canAdd <= 0) {
            showToast(`Достигнут лимит (${maxFilesCount} файлов)`, 'warning');
            return;
        }
        const toAdd = validFiles.slice(0, canAdd);
        onChange([...files, ...toAdd]);
        if (toAdd.length > 0) showToast(`Добавлено ${toAdd.length} файл(ов)`, 'success');
        if (toAdd.length < validFiles.length) showToast(`Некоторые файлы не добавлены — лимит ${maxFilesCount}`, 'warning');
    };

    const removeFile = (i) => {
        onChange(files.filter((_, idx) => idx !== i));
        showToast('Файл удалён', 'info');
    };

    const handleSelect = (e) => { addFiles(Array.from(e.target.files)); e.target.value = ''; };
    const handleDrop   = (e) => { e.preventDefault(); setDragOver(false); addFiles(Array.from(e.dataTransfer.files)); };

    return (
        <>
            <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                {label}
                {is_required && <span style={{ color: '#EF4444', marginLeft: '4px' }}>*</span>}
            </Typography>

            {helper_text && (
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 1 }}>
                    {helper_text}
                </Typography>
            )}

            <Box
                onDrop={handleDrop}
                onDragOver={e => { e.preventDefault(); setDragOver(true); }}
                onDragLeave={e => { e.preventDefault(); setDragOver(false); }}
                onClick={() => document.getElementById(`file-input-${fieldKey}`).click()}
                sx={{
                    border: dragOver ? '2px dashed #4318FF' : '2px dashed #E0E5F2',
                    borderRadius: '12px', p: 3, textAlign: 'center',
                    bgcolor: dragOver ? '#F4F7FE' : '#FAFBFC',
                    cursor: 'pointer', transition: 'all 0.2s',
                    '&:hover': { borderColor: '#4318FF', bgcolor: '#F4F7FE' },
                }}
            >
                <input
                    id={`file-input-${fieldKey}`}
                    type="file"
                    accept={acceptString}
                    multiple={allowMultiple}
                    onChange={handleSelect}
                    style={{ display: 'none' }}
                />
                <CloudUploadIcon sx={{ fontSize: 48, color: dragOver ? '#4318FF' : '#A3AED0', mb: 1 }} />
                <Typography variant="body2" color="text.secondary">
                    Перетащите файлы сюда или{' '}
                    <span style={{ color: '#4318FF', fontWeight: 'bold' }}>выберите на устройстве</span>
                </Typography>
                <Typography variant="caption" color="text.secondary">
                    {acceptedExts.length > 0 ? `Форматы: ${acceptedExts.join(', ').toUpperCase()}` : 'Любые форматы'}
                    {' • '}Макс. {maxSizeMb} МБ
                    {allowMultiple && ` • До ${maxFilesCount} файлов`}
                </Typography>
            </Box>

            {files.length > 0 && (
                <Box sx={{ mt: 2 }}>
                    {files.map((file, i) => (
                        <Box key={i} sx={{ display: 'flex', alignItems: 'center', gap: 1, p: 1.5, mb: 1, bgcolor: '#F4F7FE', borderRadius: '8px', border: '1px solid #E0E5F2' }}>
                            <AttachFileIcon sx={{ color: '#4318FF' }} />
                            <Box sx={{ flex: 1, minWidth: 0 }}>
                                <Typography variant="body2" noWrap fontWeight="medium">{file.name}</Typography>
                                <Typography variant="caption" color="text.secondary">{formatFileSize(file.size)}</Typography>
                            </Box>
                            <IconButton size="small" onClick={() => removeFile(i)} sx={{ color: '#FF4D4D' }}>
                                <CloseIcon fontSize="small" />
                            </IconButton>
                        </Box>
                    ))}
                </Box>
            )}

            {allowMultiple && files.length > 0 && files.length < maxFilesCount && (
                <Button size="small" startIcon={<AddIcon />} onClick={() => document.getElementById(`file-input-${fieldKey}`).click()} sx={{ mt: 1, color: '#4318FF' }}>
                    Добавить файл
                </Button>
            )}

            {error && <Typography variant="caption" color="error">{error}</Typography>}
        </>
    );
}
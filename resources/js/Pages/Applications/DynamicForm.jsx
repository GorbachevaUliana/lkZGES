import React, { useState, useMemo, useEffect, createContext, useContext } from "react";
import { Head, useForm } from '@inertiajs/react';
import {
    Container, Typography, TextField, Button, Box, Paper,
    Stepper, Step, StepLabel, Card, CardContent, Grid, Divider,
    Checkbox, Alert, IconButton, LinearProgress
} from "@mui/material";
import {
    Person as PersonIcon,
    Business as BusinessIcon,
    ArrowForward as ArrowIcon,
    ArrowBack as BackIcon,
    Send as SendIcon,
    Lock as LockIcon,
    Add as AddIcon,
    Delete as DeleteIcon,
    AttachFile as AttachFileIcon,
    CloudUpload as CloudUploadIcon,
    Close as CloseIcon,
    CheckCircle as CheckCircleIcon,
    Warning as WarningIcon,
    Error as ErrorIcon,
    Info as InfoIcon,
} from '@mui/icons-material';
import ClientLayout from '@/Layouts/ClientLayout';
import { IMaskInput } from "react-imask";


const steps = ['Тип клиента', 'Заполнение данных', 'Проверка'];

// ==================== SHOWTOAST COMPONENT ====================
const ToastContext = createContext(null);

export const useToast = () => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
};

const ToastItem = ({ toast, onClose }) => {
    const icons = {
        success: <CheckCircleIcon sx={{ color: '#22C55E' }} />,
        warning: <WarningIcon sx={{ color: '#F59E0B' }} />,
        error: <ErrorIcon sx={{ color: '#EF4444' }} />,
        info: <InfoIcon sx={{ color: '#3B82F6' }} />,
    };

    const bgColors = {
        success: '#ECFDF5',
        warning: '#FFFBEB',
        error: '#FEF2F2',
        info: '#EFF6FF',
    };

    const borderColors = {
        success: '#22C55E',
        warning: '#F59E0B',
        error: '#EF4444',
        info: '#3B82F6',
    };

    return (
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
                bgcolor: bgColors[toast.type] || bgColors.info,
                borderLeft: `4px solid ${borderColors[toast.type] || borderColors.info}`,
                borderRadius: '8px',
                animation: 'slideIn 0.3s ease-out',
                '@keyframes slideIn': {
                    '0%': { transform: 'translateX(100%)', opacity: 0 },
                    '100%': { transform: 'translateX(0)', opacity: 1 },
                },
            }}
        >
            {icons[toast.type] || icons.info}
            <Typography variant="body2" sx={{ flex: 1, color: '#1F2937' }}>
                {toast.message}
            </Typography>
            <IconButton size="small" onClick={() => onClose(toast.id)}>
                <CloseIcon fontSize="small" />
            </IconButton>
        </Paper>
    );
};

const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const showToast = (message, type = 'info', duration = 5000) => {
        const id = Date.now();
        setToasts(prev => [...prev, { id, message, type }]);

        if (duration > 0) {
            setTimeout(() => {
                setToasts(prev => prev.filter(t => t.id !== id));
            }, duration);
        }
    };

    const removeToast = (id) => {
        setToasts(prev => prev.filter(t => t.id !== id));
    };

    return (
        <ToastContext.Provider value={{ showToast, removeToast }}>
            {children}
            <Box
                sx={{
                    position: 'fixed',
                    top: 20,
                    right: 20,
                    zIndex: 9999,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'flex-end',
                }}
            >
                {toasts.map(toast => (
                    <ToastItem key={toast.id} toast={toast} onClose={removeToast} />
                ))}
            </Box>
        </ToastContext.Provider>
    );
};

// ==================== MASK COMPONENTS WITH PLACEHOLDERS ====================

const PassportMask = React.forwardRef(function PassportMask(props, ref) {
    const { onChange, value, ...other } = props;
    const safeValue = value != null ? String(value) : '';
    return (
        <IMaskInput
            {...other}
            value={safeValue}
            mask="0000 000000"
            definitions={{ '#': /[1-9]/ }}
            inputRef={ref}
            onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
            overwrite
            placeholder="____ ______"
        />
    );
});

const PhoneMask = React.forwardRef(function PhoneMask(props, ref) {
    const { onChange, value, ...other } = props;
    const safeValue = value != null ? String(value) : '';
    return (
        <IMaskInput
            {...other}
            value={safeValue}
            mask="+7 (000) 000-00-00"
            inputRef={ref}
            onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
            overwrite
            placeholder="+7 (___) ___-__-__"
        />
    );
});

const SnilsMask = React.forwardRef(function SnilsMask(props, ref) {
    const { onChange, value, ...other } = props;
    const safeValue = value != null ? String(value) : '';
    return (
        <IMaskInput
            {...other}
            value={safeValue}
            mask="000-000-000 00"
            inputRef={ref}
            onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
            overwrite
            placeholder="___-___-___ __"
        />
    );
});

const RangeNumberMask = React.forwardRef(function RangeNumberMask(props, ref) {
    const { onChange, value, ...other } = props;
    const safeValue = value != null ? String(value) : '';
    return (
        <IMaskInput
            {...other}
            value={safeValue}
            mask="0[00000000000] - 0[0000000000]"
            inputRef={ref}
            onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
            overwrite
            placeholder="от - до"
        />
    );
});

const RangeDateMask = React.forwardRef(function RangeDateMask(props, ref) {
    const { onChange, value, ...other } = props;
    const safeValue = value != null ? String(value) : '';
    return (
        <IMaskInput
            {...other}
            value={safeValue}
            mask="00.00.0000 - 00.00.0000"
            inputRef={ref}
            onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
            overwrite
            placeholder="дд.мм.гггг - дд.мм.гггг"
        />
    );
});

// ==================== DATE MASK FOR SINGLE DATE FIELD ====================
const DateMask = React.forwardRef(function DateMask(props, ref) {
    const { onChange, value, ...other } = props;
    const safeValue = value != null ? String(value) : '';
    return (
        <IMaskInput
            {...other}
            value={safeValue}
            mask="00.00.0000"
            inputRef={ref}
            onAccept={(val) => onChange({ target: { name: props.name, value: val } })}
            overwrite
            placeholder="дд.мм.гггг"
        />
    );
});

// ==================== FILE UPLOAD COMPONENT ====================
const FileUploadField = ({ fieldKey, label, isRequired, allowMultiple, acceptedTypes, maxSize, maxFiles, helperText, value, onChange, error, showToast }) => {
    const [dragOver, setDragOver] = useState(false);

    const files = value || [];
    const acceptedExtensions = acceptedTypes ? Object.keys(acceptedTypes) : [];
    const acceptString = acceptedExtensions.length > 0 ? acceptedExtensions.map(ext => `.${ext}`).join(',') : '*';

    const formatFileSize = (bytes) => {
        if (bytes < 1024) return bytes + ' байт';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' КБ';
        return (bytes / (1024 * 1024)).toFixed(1) + ' МБ';
    };

    const handleFileSelect = (e) => {
        const selectedFiles = Array.from(e.target.files);
        addFiles(selectedFiles);
        e.target.value = '';
    };

    const addFiles = (newFiles) => {
        const maxSizeBytes = (maxSize || 10) * 1024 * 1024;
        const validFiles = [];

        for (const file of newFiles) {
            if (file.size > maxSizeBytes) {
                showToast(`Файл "${file.name}" превышает максимальный размер ${maxSize} МБ`, 'error');
                continue;
            }

            if (acceptedExtensions.length > 0) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!acceptedExtensions.includes(ext)) {
                    showToast(`Тип файла ".${ext}" не разрешён. Разрешённые форматы: ${acceptedExtensions.join(', ').toUpperCase()}`, 'error');
                    continue;
                }
            }

            validFiles.push(file);
        }

        const totalFiles = files.length + validFiles.length;
        const limit = maxFiles || 5;

        if (allowMultiple && totalFiles > limit) {
            const canAdd = limit - files.length;
            if (canAdd > 0) {
                onChange([...files, ...validFiles.slice(0, canAdd)]);
                showToast(`Добавлено ${canAdd} из ${validFiles.length} файлов. Максимум: ${limit} файлов.`, 'warning');
            } else {
                showToast(`Достигнут лимит файлов (${limit}). Удалите файлы чтобы добавить новые.`, 'warning');
            }
        } else {
            if (allowMultiple) {
                onChange([...files, ...validFiles]);
                if (validFiles.length > 0) {
                    showToast(`Успешно добавлено ${validFiles.length} файл(ов)`, 'success');
                }
            } else {
                onChange(validFiles.length > 0 ? [validFiles[0]] : files);
                if (validFiles.length > 0) {
                    showToast(`Файл "${validFiles[0].name}" успешно добавлен`, 'success');
                }
            }
        }
    };

    const removeFile = (index) => {
        const newFiles = files.filter((_, i) => i !== index);
        onChange(newFiles);
        showToast('Файл удалён', 'info');
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setDragOver(false);
        const droppedFiles = Array.from(e.dataTransfer.files);
        addFiles(droppedFiles);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        setDragOver(true);
    };

    const handleDragLeave = (e) => {
        e.preventDefault();
        setDragOver(false);
    };

    return (
        <Box>
            <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                {label}
                {isRequired && <span style={{ color: '#EF4444', marginLeft: '4px' }}>*</span>}
            </Typography>

            {helperText && (
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 1 }}>
                    {helperText}
                </Typography>
            )}

            <Box
                onDrop={handleDrop}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                sx={{
                    border: dragOver ? '2px dashed #4318FF' : '2px dashed #E0E5F2',
                    borderRadius: '12px',
                    p: 3,
                    textAlign: 'center',
                    bgcolor: dragOver ? '#F4F7FE' : '#FAFBFC',
                    cursor: 'pointer',
                    transition: 'all 0.2s',
                    '&:hover': { borderColor: '#4318FF', bgcolor: '#F4F7FE' },
                }}
                onClick={() => document.getElementById(`file-input-${fieldKey}`).click()}
            >
                <input
                    id={`file-input-${fieldKey}`}
                    type="file"
                    accept={acceptString}
                    multiple={allowMultiple}
                    onChange={handleFileSelect}
                    style={{ display: 'none' }}
                />
                <CloudUploadIcon sx={{ fontSize: 48, color: dragOver ? '#4318FF' : '#A3AED0', mb: 1 }} />
                <Typography variant="body2" color="text.secondary">
                    Перетащите файлы сюда или <span style={{ color: '#4318FF', fontWeight: 'bold' }}>выберите на устройстве</span>
                </Typography>
                <Typography variant="caption" color="text.secondary">
                    {acceptedExtensions.length > 0 ? `Разрешённые форматы: ${acceptedExtensions.join(', ').toUpperCase()}` : 'Любые форматы'}
                    {' • '}Макс. размер: {maxSize || 10} МБ
                    {allowMultiple && ` • До ${maxFiles || 5} файлов`}
                </Typography>
            </Box>

            {files.length > 0 && (
                <Box sx={{ mt: 2 }}>
                    {files.map((file, index) => (
                        <Box
                            key={index}
                            sx={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 1,
                                p: 1.5,
                                mb: 1,
                                bgcolor: '#F4F7FE',
                                borderRadius: '8px',
                                border: '1px solid #E0E5F2',
                            }}
                        >
                            <AttachFileIcon sx={{ color: '#4318FF' }} />
                            <Box sx={{ flex: 1, minWidth: 0 }}>
                                <Typography variant="body2" noWrap fontWeight="medium">
                                    {file.name}
                                </Typography>
                                <Typography variant="caption" color="text.secondary">
                                    {formatFileSize(file.size)}
                                </Typography>
                            </Box>
                            <IconButton
                                size="small"
                                onClick={() => removeFile(index)}
                                sx={{ color: '#FF4D4D' }}
                            >
                                <CloseIcon fontSize="small" />
                            </IconButton>
                        </Box>
                    ))}
                </Box>
            )}

            {allowMultiple && files.length > 0 && files.length < (maxFiles || 5) && (
                <Button
                    size="small"
                    startIcon={<AddIcon />}
                    onClick={() => document.getElementById(`file-input-${fieldKey}`).click()}
                    sx={{ mt: 1, color: '#4318FF' }}
                >
                    Добавить файл
                </Button>
            )}

            {error && (
                <Typography variant="caption" color="error">
                    {error}
                </Typography>
            )}
        </Box>
    );
};

// ==================== MAIN COMPONENT ====================
export default function DynamicForm({ template, existingClientType, hasExistingClient }) {
    return (
        <ToastProvider>
            <DynamicFormContent template={template} existingClientType={existingClientType} hasExistingClient={hasExistingClient} />
        </ToastProvider>
    );
}

function DynamicFormContent({ template, existingClientType, hasExistingClient }) {
    const { showToast } = useToast();
    
    const [clientType, setClientType] = useState(existingClientType || 'individual');
    const [activeStep, setActiveStep] = useState(hasExistingClient ? 1 : 0);

    const clientTypeLabel = clientType === 'individual' ? 'Физическое лицо' : 'Юридическое лицо';

    const initialData = useMemo(() => {
        const fields = { client_type: clientType };
        template.content?.forEach(block => {
            const key = block.data.key || block.data.label;

            if (block.type === 'checkbox_group') {
                fields[key] = { preset: [], custom: [] };
            } else if (block.type === 'select_field') {
                fields[key] = { value: '', customValue: '' };
            } else if (block.type === 'file_upload') {
                fields[key] = [];
            } else if (block.type === 'dynamic_input') {
                fields[key] = { selected: '', inputValue: '' };
            } else if (block.type === 'input_field') {
                fields[key] = block.data.default_value || '';
            }
        });
        return fields;
    }, [template, clientType]);

    const { data, setData, post, processing, errors } = useForm(initialData);

    const visibleFields = useMemo(() => {
        return template.content?.filter(block => {
            const allowedTypes = ['input_field', 'select_field', 'checkbox_group', 'section_header', 'file_upload', 'dynamic_input', 'text_block'];
            if (!allowedTypes.includes(block.type)) return false;
            const visibility = block.data.visibility || 'all';
            return visibility === 'all' || visibility === clientType;
        }) || [];
    }, [template, clientType]);

    const handleNext = () => setActiveStep((prev) => prev + 1);
    const handleBack = () => setActiveStep((prev) => prev - 1);

    const handleClientTypeChange = (type) => {
        if (hasExistingClient) return;
        setClientType(type);
        setData('client_type', type);
    };

    const getFieldKey = (block) => block.data.key || block.data.label;

    const isStepValid = (step) => {
        if (step === 0) {
            if (hasExistingClient) return true;
            return !!clientType;
        }
        if (step === 1) {
            return visibleFields
                .filter(f => f.data.is_required)
                .every(f => {
                    const key = getFieldKey(f);
                    if (f.type === 'input_field' && f.data.is_readonly && f.data.default_value) {
                        return true;
                    }
                    const val = data[key];
                    if (f.type === 'checkbox_group') {
                        return val.preset.length > 0 || val.custom.some(c => c.value.trim() !== '');
                    }
                    if (f.type === 'file_upload') {
                        return val && val.length > 0;
                    }
                    if (f.type === 'select_field') {
                        return val.value === 'other' ? !!val.customValue : !!val.value;
                    }
                    if (f.type === 'dynamic_input') {
                        if (!val.selected) return false;
                        const selectedOption = f.data.options?.find(o => o.value === val.selected);
                        if (selectedOption?.input_type && selectedOption.input_type !== 'none') {
                            return !!val.inputValue.trim();
                        }
                        return true;
                    }
                    return !!val;
                });
        }
        return true;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('application.store', template.slug));
    };

    const getInputComponent = (specialFormat, fieldType) => {
        // Для полей с типом date используем DateMask
        if (fieldType === 'date') {
            return DateMask;
        }
        switch (specialFormat) {
            case 'passport': return PassportMask;
            case 'phone': return PhoneMask;
            case 'snils': return SnilsMask;
            case 'range_numbers': return RangeNumberMask;
            case 'range_date': return RangeDateMask;
            default: return undefined;
        }
    };

    const renderValue = (block, value) => {
        if (!value) return '—';

        if (block.type === 'checkbox_group') {
            const selected = [
                ...(value.preset || []),
                ...(value.custom?.map(c => c.value).filter(Boolean) || [])
            ];
            return selected.length > 0 ? selected.join(', ') : '—';
        }

        if (block.type === 'select_field') {
            if (value.value === 'other') return value.customValue || '—';
            return value.value || '—';
        }

        if (block.type === 'file_upload') {
            if (Array.isArray(value) && value.length > 0) {
                return value.map(f => f.name).join(', ');
            }
            return '—';
        }

        if (block.type === 'dynamic_input') {
            if(!value.selected) return '-';
            const selectedOption = block.data.options?.find(o => o.value === value.selected);
            if (selectedOption?.input_type && selectedOption.input_type !== 'none' && value.inputValue) {
                return ` ${value.selected} : ${value.inputValue}`;
            }
            return value.selected || '-';
        }

        return typeof value === 'string' ? value : JSON.stringify(value);
    };

    const displaySteps = hasExistingClient
        ? ['Заполнение данных', 'Проверка']
        : steps;

    const displayActiveStep = hasExistingClient ? activeStep - 1 : activeStep;

    return (
        <ClientLayout>
            <Head title={template.title} />
            <Container maxWidth="md" sx={{ mt: 4, mb: 4 }}>
                <Paper sx={{ p: 4, borderRadius: 3, boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' }}>
                    <Typography variant="h4" gutterBottom fontWeight="bold" color="#1B2559">
                        {template.title}
                    </Typography>

                    {hasExistingClient && (
                        <Alert
                            severity="info"
                            icon={<LockIcon />}
                            sx={{ mb: 3, borderRadius: '12px' }}
                        >
                            <Typography variant="body2">
                                Вы уже зарегистрированы как <strong>{clientTypeLabel}</strong>.
                                Создание нового объекта возможно только с вашим текущим типом лица.
                            </Typography>
                        </Alert>
                    )}

                    <Stepper activeStep={displayActiveStep} sx={{ mb: 4 }}>
                        {displaySteps.map((label) => (
                            <Step key={label}>
                                <StepLabel>{label}</StepLabel>
                            </Step>
                        ))}
                    </Stepper>

                    <form onSubmit={handleSubmit}>
                        {activeStep === 0 && !hasExistingClient && (
                            <Box>
                                <Typography variant="h6" gutterBottom>
                                    Выберите тип клиента
                                </Typography>
                                <Grid container spacing={3}>
                                    <Grid item xs={12} sm={6}>
                                        <Card
                                            sx={{
                                                cursor: 'pointer',
                                                border: clientType === 'individual' ? '2px solid #4318FF' : '1px solid #E0E5F2',
                                                borderRadius: '16px',
                                                transition: 'all 0.2s',
                                                '&:hover': { borderColor: '#4318FF' },
                                            }}
                                            onClick={() => handleClientTypeChange('individual')}
                                        >
                                            <CardContent sx={{ textAlign: 'center', py: 4 }}>
                                                <PersonIcon sx={{ fontSize: 48, color: clientType === 'individual' ? '#4318FF' : '#A3AED0', mb: 2 }} />
                                                <Typography variant="h6" fontWeight="bold">
                                                    Физическое лицо
                                                </Typography>
                                                <Typography variant="body2" color="text.secondary">
                                                    Для граждан РФ
                                                </Typography>
                                            </CardContent>
                                        </Card>
                                    </Grid>
                                    <Grid item xs={12} sm={6}>
                                        <Card
                                            sx={{
                                                cursor: 'pointer',
                                                border: clientType === 'legal' ? '2px solid #4318FF' : '1px solid #E0E5F2',
                                                borderRadius: '16px',
                                                transition: 'all 0.2s',
                                                '&:hover': { borderColor: '#4318FF' },
                                            }}
                                            onClick={() => handleClientTypeChange('legal')}
                                        >
                                            <CardContent sx={{ textAlign: 'center', py: 4 }}>
                                                <BusinessIcon sx={{ fontSize: 48, color: clientType === 'legal' ? '#4318FF' : '#A3AED0', mb: 2 }} />
                                                <Typography variant="h6" fontWeight="bold">
                                                    Юридическое лицо
                                                </Typography>
                                                <Typography variant="body2" color="text.secondary">
                                                    Для организаций
                                                </Typography>
                                            </CardContent>
                                        </Card>
                                    </Grid>
                                </Grid>
                            </Box>
                        )}

                        {activeStep === 1 && (
                            <Box>
                                <Typography variant="h6" sx={{ mb: 3 }}>
                                    Данные заявителя
                                    {hasExistingClient && (
                                        <Typography component="span" color="primary" sx={{ ml: 2, fontWeight: 'normal' }}>
                                            ({clientTypeLabel})
                                        </Typography>
                                    )}
                                </Typography>
                                <Grid container spacing={3}>
                                    {visibleFields.map((block, index) => {
                                        const { label, key, is_required, options, allow_custom, allow_multiple_custom, special_format, type } = block.data;
                                        const fieldKey = key || label;

                                        switch (block.type) {
                                            case 'section_header':
                                                return (
                                                    <Grid item xs={12} sx={{ mt: 2 }} key={index}>
                                                        <Typography variant={block.data.level || 'h6'} fontWeight="bold" color="#2B3674">
                                                            {block.data.title}
                                                        </Typography>
                                                        <Divider sx={{ mb: 1 }} />
                                                    </Grid>
                                                );

                                            case 'text_block':
                                                return (
                                                    <Grid item xs={12} key={index}>
                                                        <Paper 
                                                            variant="outlined" 
                                                            sx={{ 
                                                                p: 2, 
                                                                borderRadius: '12px', 
                                                                bgcolor: '#F0F9FF',
                                                                borderColor: '#BAE6FD'
                                                            }}
                                                        >
                                                            <Typography 
                                                                variant="body2" 
                                                                component="div"
                                                                sx={{ 
                                                                    color: '#0369A1',
                                                                    '& p': { mb: 1 },
                                                                    '& p:last-child': { mb: 0 }
                                                                }}
                                                                dangerouslySetInnerHTML={{ __html: block.data.body || '' }}
                                                            />
                                                        </Paper>
                                                    </Grid>
                                                );

                                            case 'input_field':
                                                // Для полей с датой используем текстовое поле с маской
                                                const isDateField = type === 'date';
                                                
                                                return (
                                                    <Grid item xs={12} key={index} sx={{ width: '100%' }}>
                                                        <TextField
                                                            fullWidth
                                                            label={
                                                                <>
                                                                    {label}
                                                                    {is_required && <span style={{ color: '#EF4444' }}> *</span>}
                                                                </>
                                                            }
                                                            value={data[fieldKey] || ''}
                                                            onChange={e => setData(fieldKey, e.target.value)}
                                                            type={type === 'number' ? 'number' : 'text'}
                                                            InputProps={special_format && special_format !== 'none' ? {
                                                                inputComponent: getInputComponent(special_format),
                                                            } : isDateField ? {
                                                                inputComponent: DateMask,
                                                            } : {}}
                                                            placeholder={isDateField ? 'дд.мм.гггг' : (block.data.placeholder || '')}
                                                            disabled={block.data.is_readonly}
                                                            helperText={block.data.helper_text}
                                                            error={!!errors[fieldKey]}
                                                            sx={{ width: '100%' }}
                                                        />
                                                    </Grid>
                                                );

                                            case 'select_field':
                                                return (
                                                    <Grid item xs={12} key={index} sx={{ width: '100%' }}>
                                                        <TextField
                                                            select
                                                            fullWidth
                                                            label={
                                                                <>
                                                                    {label}
                                                                    {is_required && <span style={{ color: '#EF4444' }}> *</span>}
                                                                </>
                                                            }
                                                            value={data[fieldKey]?.value || ''}
                                                            onChange={e => setData(fieldKey, { ...data[fieldKey], value: e.target.value })}
                                                            SelectProps={{ native: true }}
                                                            sx={{ width: '100%' }}
                                                        >
                                                            <option value=""></option>
                                                            {options?.map((opt, i) => (
                                                                <option key={i} value={opt.value}>{opt.value}</option>
                                                            ))}
                                                            {allow_custom && <option value="other">Другое (ввести свой вариант)</option>}
                                                        </TextField>
                                                        {allow_custom && data[fieldKey]?.value === 'other' && (
                                                            <TextField
                                                                fullWidth
                                                                sx={{ mt: 2 }}
                                                                placeholder="Введите свой вариант..."
                                                                value={data[fieldKey]?.customValue || ''}
                                                                onChange={e => setData(fieldKey, { ...data[fieldKey], customValue: e.target.value })}
                                                            />
                                                        )}
                                                    </Grid>
                                                );

                                            case 'checkbox_group':
                                                return (
                                                    <Grid item xs={12} key={index} sx={{ width: '100%' }}>
                                                        <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                                                            {label}
                                                            {is_required && <span style={{ color: '#EF4444', marginLeft: '4px' }}>*</span>}
                                                        </Typography>
                                                        <Box sx={{ pl: 1 }}>
                                                            {options?.map((opt, i) => (
                                                                <Box key={i} sx={{ display: 'flex', alignItems: 'center' }}>
                                                                    <Checkbox
                                                                        checked={data[fieldKey]?.preset?.includes(opt.value) || false}
                                                                        onChange={e => {
                                                                            const next = e.target.checked
                                                                                ? [...(data[fieldKey]?.preset || []), opt.value]
                                                                                : data[fieldKey].preset.filter(v => v !== opt.value);
                                                                            setData(fieldKey, { ...data[fieldKey], preset: next });
                                                                        }}
                                                                    />
                                                                    <Typography>{opt.value}</Typography>
                                                                </Box>
                                                            ))}
                                                            
                                                            {/* Custom варианты с возможностью удаления */}
                                                            {data[fieldKey]?.custom?.map((item, i) => (
                                                                <Box key={item.id} sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1 }}>
                                                                    <Checkbox checked disabled />
                                                                    <TextField
                                                                        size="small"
                                                                        fullWidth
                                                                        value={item.value}
                                                                        onChange={e => {
                                                                            const next = [...data[fieldKey].custom];
                                                                            next[i].value = e.target.value;
                                                                            setData(fieldKey, { ...data[fieldKey], custom: next });
                                                                        }}
                                                                    />
                                                                    {/* КНОПКА УДАЛЕНИЯ */}
                                                                    <IconButton 
                                                                        size="small" 
                                                                        onClick={() => {
                                                                            const next = data[fieldKey].custom.filter((_, idx) => idx !== i);
                                                                            setData(fieldKey, { ...data[fieldKey], custom: next });
                                                                        }}
                                                                        sx={{ color: '#FF4D4D' }}
                                                                    >
                                                                        <DeleteIcon fontSize="small" />
                                                                    </IconButton>
                                                                </Box>
                                                            ))}
                                                            
                                                            {allow_multiple_custom && (
                                                                <Button
                                                                    size="small"
                                                                    variant="text"
                                                                    startIcon={<AddIcon />}
                                                                    onClick={() => setData(fieldKey, {
                                                                        ...data[fieldKey],
                                                                        custom: [...(data[fieldKey]?.custom || []), { id: Date.now(), value: '' }]
                                                                    })}
                                                                    sx={{ color: '#4318FF' }}
                                                                >
                                                                    Добавить свой вариант
                                                                </Button>
                                                            )}
                                                        </Box>
                                                    </Grid>
                                                );

                                            case 'file_upload':
                                                return (
                                                    <Grid item xs={12} key={index} sx={{ width: '100%' }}>
                                                        <FileUploadField
                                                            fieldKey={fieldKey}
                                                            label={label}
                                                            isRequired={is_required}
                                                            allowMultiple={block.data.allow_multiple !== false}
                                                            acceptedTypes={block.data.accepted_types}
                                                            maxSize={block.data.max_size || 10}
                                                            maxFiles={block.data.max_files || 5}
                                                            helperText={block.data.helper_text}
                                                            value={data[fieldKey]}
                                                            onChange={(files) => setData(fieldKey, files)}
                                                            error={errors[fieldKey]}
                                                            showToast={showToast}
                                                        />
                                                    </Grid>
                                                );

                                            case 'dynamic_input':
                                                const dynamicOptions = options || [];
                                                const selectedOption = dynamicOptions.find(o => o.value === data[fieldKey]?.selected);
                                                const showInput = selectedOption?.input_type && selectedOption.input_type !== 'none';
                                                return (
                                                    <Grid item xs={12} key={index} sx={{ width: '100%' }}>
                                                        <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                                                            {label}
                                                            {is_required && <span style={{ color: '#EF4444', marginLeft: '4px' }}>*</span>}
                                                        </Typography>
                                                        <TextField
                                                            select
                                                            fullWidth
                                                            value={data[fieldKey]?.selected || ''}
                                                            onChange={e => setData(fieldKey, { ...data[fieldKey], selected: e.target.value, inputValue: '' })}
                                                            SelectProps={{ native: true }}
                                                            sx={{ mb: showInput ? 1 : 0, width: '100%' }}
                                                        >
                                                            <option value="">Выберите вариант...</option>
                                                            {dynamicOptions.map((opt, i) => (
                                                                <option key={i} value={opt.value}>{opt.value}</option>
                                                            ))}
                                                        </TextField>
                                                        {showInput && (
                                                            <TextField
                                                                fullWidth
                                                                placeholder={selectedOption.input_label || 'Введите значение...'}
                                                                value={data[fieldKey]?.inputValue || ''}
                                                                onChange={e => setData(fieldKey, { ...data[fieldKey], inputValue: e.target.value })}
                                                                type={selectedOption.input_type === 'email' ? 'email' : selectedOption.input_type === 'phone' ? 'tel' : 'text'}
                                                                InputProps={selectedOption.input_type === 'phone' ? {
                                                                    inputComponent: PhoneMask,
                                                                } : {}}
                                                                sx={{ mt: 1 }}
                                                            />
                                                        )}
                                                    </Grid>
                                                );

                                            default:
                                                return null;
                                        }
                                    })}
                                </Grid>
                            </Box>
                        )}

                        {activeStep === 2 && (
                            <Box>
                                <Typography variant="h6" gutterBottom>
                                    Проверьте введённые данные
                                </Typography>
                                <Card variant="outlined" sx={{ borderRadius: '12px' }}>
                                    <CardContent>
                                        <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                                            Тип клиента: <strong>{clientTypeLabel}</strong>
                                        </Typography>
                                        <Divider sx={{ my: 2 }} />
                                        {visibleFields.map((block, index) => {
                                            if (block.type === 'section_header') {
                                                return (
                                                    <Typography key={index} variant="subtitle1" fontWeight="bold" sx={{ mt: 2, mb: 1 }}>
                                                        {block.data.title}
                                                    </Typography>
                                                );
                                            }
                                            if (block.type === 'text_block') {
                                                return null;
                                            }
                                            const fieldKey = getFieldKey(block);
                                            return (
                                                <Box key={index} sx={{ mb: 2 }}>
                                                    <Typography variant="body2" color="text.secondary">
                                                        {block.data.label}
                                                    </Typography>
                                                    <Typography variant="body1">
                                                        {renderValue(block, data[fieldKey])}
                                                    </Typography>
                                                </Box>
                                            );
                                        })}
                                    </CardContent>
                                </Card>
                            </Box>
                        )}

                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 4 }}>
                            <Button
                                disabled={activeStep === 0}
                                onClick={handleBack}
                                startIcon={<BackIcon />}
                                sx={{ color: '#4318FF' }}
                            >
                                Назад
                            </Button>
                            {activeStep < 2 ? (
                                <Button
                                    variant="contained"
                                    onClick={handleNext}
                                    disabled={!isStepValid(activeStep)}
                                    endIcon={<ArrowIcon />}
                                    sx={{
                                        bgcolor: '#4318FF',
                                        '&:hover': { bgcolor: '#3614B8' },
                                        borderRadius: '12px',
                                        px: 3,
                                    }}
                                >
                                    {activeStep === 0 ? 'Далее' : 'Проверить'}
                                </Button>
                            ) : (
                                <Button
                                    type="submit"
                                    variant="contained"
                                    disabled={processing}
                                    endIcon={<SendIcon />}
                                    sx={{
                                        bgcolor: '#22C55E',
                                        '&:hover': { bgcolor: '#16A34A' },
                                        borderRadius: '12px',
                                        px: 3,
                                    }}
                                >
                                    Отправить
                                </Button>
                            )}
                        </Box>
                    </form>
                </Paper>
            </Container>
        </ClientLayout>
    );
}

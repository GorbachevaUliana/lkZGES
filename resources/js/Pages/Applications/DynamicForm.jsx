import React, { useState, useMemo, useEffect } from "react";
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
} from '@mui/icons-material';
import ClientLayout from '@/Layouts/ClientLayout';
import { IMaskInput } from "react-imask";


const steps = ['Тип клиента', 'Заполнение данных', 'Проверка'];

// Маска для паспорта
const PassportMask = React.forwardRef(function PassportMask(props, ref) {
    const { onChange, ...other } = props;
    return (
        <IMaskInput
            {...other}
            mask="0000 000000"
            definitions={{ '#': /[1-9]/ }}
            inputRef={ref}
            onAccept={(value) => onChange({ target: { name: props.name, value } })}
            overwrite
        />
    );
});

// Маска для телефона
const PhoneMask = React.forwardRef(function PhoneMask(props, ref) {
    const { onChange, ...other } = props;
    return (
        <IMaskInput
            {...other}
            mask="+7 (000) 000-00-00"
            inputRef={ref}
            onAccept={(value) => onChange({ target: { name: props.name, value } })}
            overwrite
        />
    );
});

// Маска для СНИЛС
const SnilsMask = React.forwardRef(function SnilsMask(props, ref) {
    const { onChange, ...other } = props;
    return (
        <IMaskInput
            {...other}
            mask="000-000-000 00"
            inputRef={ref}
            onAccept={(value) => onChange({ target: { name: props.name, value } })}
            overwrite
        />
    );
});

//Маска для диапазона чисел
const RangeNumberMask = React.forwardRef(function RangeNumberMask(props, ref) {
    const {onChange, ...other} = props;
    return (
        <IMaskInput
            {...other}
            mask = "0[00000000000] - 0[0000000000]"
            inputRef = {ref}
            onAccept={(value) => onChange({ target: { name: props.name, value } })}
            overwrite
        />
    );
});

//Маска для диапазона дат
const RangeDateMask = React.forwardRef(function RangeDateMask(props,ref) {
    const {onChange, ...other} = props;
    return (
        <IMaskInput
            {...other}
            mask = "00.00.0000 - 00.00.0000"
            inputRef={(value) => onChange({ target: { name: props.name, value } })}
            overwrite
        />
    );
});

// Компонент загрузки файлов
const FileUploadField = ({ fieldKey, label, isRequired, allowMultiple, acceptedTypes, maxSize, maxFiles, helperText, value, onChange, error }) => {
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
        e.target.value = ''; // Сброс для повторного выбора того же файла
    };

    const addFiles = (newFiles) => {
        const maxSizeBytes = (maxSize || 10) * 1024 * 1024;
        const validFiles = [];

        for (const file of newFiles) {
            // Проверка размера
            if (file.size > maxSizeBytes) {
                alert(`Файл "${file.name}" превышает макс. размер ${maxSize} МБ`);
                continue;
            }

            // Проверка типа
            if (acceptedExtensions.length > 0) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!acceptedExtensions.includes(ext)) {
                    alert(`Тип файла ".${ext}" не разрешён`);
                    continue;
                }
            }

            validFiles.push(file);
        }

        // Проверка лимита количества
        const totalFiles = files.length + validFiles.length;
        const limit = maxFiles || 5;

        if (allowMultiple && totalFiles > limit) {
            const canAdd = limit - files.length;
            if (canAdd > 0) {
                onChange([...files, ...validFiles.slice(0, canAdd)]);
            }
            alert(`Максимум ${limit} файлов. Добавлено ${canAdd > 0 ? canAdd : 0} файлов.`);
        } else {
            if (allowMultiple) {
                onChange([...files, ...validFiles]);
            } else {
                onChange(validFiles.length > 0 ? [validFiles[0]] : files);
            }
        }
    };

    const removeFile = (index) => {
        const newFiles = files.filter((_, i) => i !== index);
        onChange(newFiles);
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
                {isRequired && ' *'}
            </Typography>

            {helperText && (
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 1 }}>
                    {helperText}
                </Typography>
            )}

            {/* Зона перетаскивания */}
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

            {/* Список загруженных файлов */}
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

            {/* Кнопка добавления ещё файлов */}
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

export default function DynamicForm({ template, existingClientType, hasExistingClient }) {
    // Если у пользователя уже есть клиент - используем его тип и пропускаем шаг выбора
    const [clientType, setClientType] = useState(existingClientType || 'individual');

    // Если пользователь уже имеет клиентский профиль - начинаем с шага 1 (пропускаем выбор типа)
    const [activeStep, setActiveStep] = useState(hasExistingClient ? 1 : 0);

    // Информация о существующем типе клиента
    const clientTypeLabel = clientType === 'individual' ? 'Физическое лицо' : 'Юридическое лицо';

    // Инициализация данных формы
    const initialData = useMemo(() => {
        const fields = { client_type: clientType };
        template.content?.forEach(block => {
            const key = block.data.key || block.data.label;

            if (block.type === 'checkbox_group') {
                fields[key] = { preset: [], custom: [] };
            } else if (block.type === 'select_field') {
                fields[key] = { value: '', customValue: '' };
            } else if (block.type === 'file_upload') {
                fields[key] = []; // Массив файлов
            } else if (block.type === 'input_field') {
                fields[key] = block.data.default_value || '';
            }
        });
        return fields;
    }, [template, clientType]);

    const { data, setData, post, processing, errors } = useForm(initialData);

    // Фильтрация полей по видимости
    const visibleFields = useMemo(() => {
        return template.content?.filter(block => {
            const allowedTypes = ['input_field', 'select_field', 'checkbox_group', 'section_header', 'file_upload', 'dynamic_input'];
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

    // Проверка валидности шага
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
                            return !val.inputValue.trim();
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

    const getInputComponent = (specialFormat) => {
        switch (specialFormat) {
            case 'passport': return PassportMask;
            case 'phone': return PhoneMask;
            case 'snils': return SnilsMask;
            case 'range_numbers': return RangeNumberMask;
            case 'range_date': return RangeDateMask;
            default: return undefined;
        }
    };

    // Рендер значения для превью
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
                            <Step key={label}><StepLabel>{label}</StepLabel></Step>
                        ))}
                    </Stepper>

                    {Object.keys(errors).length > 0 && (
                        <Box sx={{ mb: 3, p: 2, bgcolor: '#ffebee', borderRadius: 2 }}>
                            <Typography color="error" variant="subtitle2">Исправьте следующие ошибки:</Typography>
                            <ul>
                                {Object.entries(errors).map(([key, value]) => (
                                    <li key={key}><Typography color="error" variant="caption">{value}</Typography></li>
                                ))}
                            </ul>
                        </Box>
                    )}

                    {processing && (
                        <Box sx={{ mb: 3 }}>
                            <LinearProgress />
                            <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block' }}>
                                Отправка заявки...
                            </Typography>
                        </Box>
                    )}

                    <form onSubmit={handleSubmit}>
                        {/* ШАГ 0: Тип заявителя */}
                        {activeStep === 0 && !hasExistingClient && (
                            <Box>
                                <Typography variant="h6" sx={{ mb: 3 }}>Выберите тип заявителя</Typography>
                                <Grid container spacing={3}>
                                    {[
                                        { id: 'individual', icon: PersonIcon, title: 'Физическое лицо', desc: 'Для граждан РФ' },
                                        { id: 'legal', icon: BusinessIcon, title: 'Юридическое лицо', desc: 'Для организаций' }
                                    ].map(item => (
                                        <Grid item xs={12} md={6} key={item.id}>
                                            <Card
                                                onClick={() => handleClientTypeChange(item.id)}
                                                sx={{
                                                    cursor: 'pointer',
                                                    border: clientType === item.id ? '2px solid #4318FF' : '1px solid #E0E5F2',
                                                    borderRadius: '20px',
                                                    transition: '0.2s',
                                                    '&:hover': { boxShadow: '0px 10px 30px rgba(67, 24, 255, 0.1)' }
                                                }}>
                                                <CardContent sx={{ textAlign: 'center', py: 4 }}>
                                                    <item.icon sx={{ fontSize: 60, color: clientType === item.id ? '#4318FF' : '#A3AED0', mb: 2 }} />
                                                    <Typography variant="h6" fontWeight="bold">{item.title}</Typography>
                                                    <Typography variant="body2" color="text.secondary">{item.desc}</Typography>
                                                </CardContent>
                                            </Card>
                                        </Grid>
                                    ))}
                                </Grid>
                            </Box>
                        )}

                        {/* ШАГ 1: Поля формы */}
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

                                            case 'input_field':
                                                return (
                                                    <Grid item xs={12} sm={6} key={index}>
                                                        <TextField
                                                            fullWidth
                                                            label={label}
                                                            required={is_required}
                                                            value={data[fieldKey] || ''}
                                                            onChange={e => setData(fieldKey, e.target.value)}
                                                            type={type === 'number' ? 'number' : type === 'date' ? 'date' : 'text'}
                                                            InputProps={special_format && special_format !== 'none' ? {
                                                                inputComponent: getInputComponent(special_format),
                                                            } : {}}
                                                        />
                                                    </Grid>
                                                );

                                            case 'select_field':
                                                return (
                                                    <Grid item xs={12} sm={6} key={index}>
                                                        <TextField
                                                            select
                                                            fullWidth
                                                            label={label}
                                                            required={is_required}
                                                            value={data[fieldKey]?.value || ''}
                                                            onChange={e => setData(fieldKey, { ...data[fieldKey], value: e.target.value })}
                                                            SelectProps={{ native: true }}
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
                                                    <Grid item xs={12} key={index}>
                                                        <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
                                                            {label}
                                                            {is_required && ' *'}
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
                                                                </Box>
                                                            ))}
                                                            {allow_multiple_custom && (
                                                                <Button
                                                                    size="small"
                                                                    variant="text"
                                                                    onClick={() => setData(fieldKey, {
                                                                        ...data[fieldKey],
                                                                        custom: [...(data[fieldKey]?.custom || []), { id: Date.now(), value: '' }]
                                                                    })}
                                                                >
                                                                    + Добавить свой вариант
                                                                </Button>
                                                            )}
                                                        </Box>
                                                    </Grid>
                                                );

                                            case 'file_upload':
                                                return (
                                                    <Grid item xs={12} key={index}>
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
                                                        />
                                                    </Grid>
                                                );

                                            default:
                                                return null;
                                        }
                                    })}
                                </Grid>
                            </Box>
                        )}

                        {/* ШАГ 2: Подтверждение */}
                        {activeStep === 2 && (
                            <Box>
                                <Typography variant="h6" sx={{ mb: 3 }}>
                                    Проверьте введённые данные
                                    {hasExistingClient && (
                                        <Typography component="span" color="primary" sx={{ ml: 2, fontWeight: 'normal' }}>
                                            (Тип: {clientTypeLabel})
                                        </Typography>
                                    )}
                                </Typography>
                                <Grid container spacing={2}>
                                    {visibleFields.map((block, index) => {
                                        if (block.type === 'section_header') return null;

                                        const { label, key } = block.data;
                                        const fieldKey = key || label;
                                        const value = data[fieldKey];

                                        return (
                                            <Grid item xs={12} sm={6} key={index}>
                                                <Paper variant="outlined" sx={{ p: 2, borderRadius: '12px', bgcolor: '#F4F7FE' }}>
                                                    <Typography variant="caption" color="text.secondary">{label}</Typography>
                                                    <Typography variant="body1" fontWeight="bold">
                                                        {renderValue(block, value)}
                                                    </Typography>
                                                </Paper>
                                            </Grid>
                                        );
                                    })}
                                </Grid>
                            </Box>
                        )}

                        <Divider sx={{ my: 4 }} />

                        <Box display="flex" justifyContent="space-between">
                            <Button
                                variant="text"
                                disabled={activeStep === 0 || (hasExistingClient && activeStep === 1)}
                                onClick={handleBack}
                                startIcon={<BackIcon />}>
                                Назад
                            </Button>

                            {activeStep < (hasExistingClient ? 2 : steps.length - 1) ? (
                                <Button
                                    variant="contained"
                                    onClick={handleNext}
                                    disabled={!isStepValid(activeStep)}
                                    endIcon={<ArrowIcon />}
                                    sx={{ bgcolor: '#4318FF', borderRadius: '12px', px: 4 }}>
                                    Далее
                                </Button>
                            ) : (
                                <Button
                                    type="submit"
                                    variant="contained"
                                    disabled={processing}
                                    startIcon={<SendIcon />}
                                    sx={{ bgcolor: '#2E7D32', borderRadius: '12px', px: 4, '&:hover': { bgcolor: '#1b5e20' } }}>
                                    Отправить заявку
                                </Button>
                            )}
                        </Box>
                    </form>
                </Paper>
            </Container>
        </ClientLayout>
    );
}
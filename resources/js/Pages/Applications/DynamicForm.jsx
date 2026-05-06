import React, { useState, useMemo } from "react";
import { Head, useForm } from '@inertiajs/react';
import { 
    Container, Typography, TextField, Button, Box, Paper, 
    Stepper, Step, StepLabel, Card, CardContent, Grid, Divider,
    Checkbox, Select, MenuItem, FormControlLabel
} from "@mui/material";
import { 
    Person as PersonIcon, 
    Business as BusinessIcon,
    ArrowForward as ArrowIcon,
    ArrowBack as BackIcon,
    Send as SendIcon,
    CheckBox
} from '@mui/icons-material';
// import GuestLayout from '@/Layouts/GuestLayout';
import ClientLayout from '@/Layouts/ClientLayout';
import { IMaskInput } from "react-imask";


const steps = ['Тип клиента', 'Заполнение данных', 'Проверка'];

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

export default function DynamicForm({template}) {
    const [activeStep, setActiveStep] = useState(0);
    const [clientType, setClientType] = useState('individual');
    const initialData = useMemo(() => {
        const fields = { client_type: 'individual' };
        template.content?.forEach(block => {

            
            const { label, type } = block.data;
            if (block.type === 'checkbox_group') {
                fields[label] = { preset: [], custom: [] };
            } else if (block.type === 'select_field') {
                fields[label] = { value: '', customValue: '' };
            } else if (block.type === 'input_field') {
                fields[label] = '';
            }
        });
        return fields;
    }, [template]);


    const { data, setData, post, processing, errors } = useForm(initialData);

    const visibleFields = useMemo(() => {
        return template.content?.filter(block => {
            const allowedTypes = ['input_field', 'select_field', 'checkbox_group', 'section_header'];
            if (!allowedTypes.includes(block.type)) return false;
            const visibility = block.data.visibility || 'all';
            return visibility === 'all' || visibility === clientType;
        }) || [];
    }, [template, clientType]);

    const handleNext = () => setActiveStep((prev) => prev + 1);
    const handleBack = () => setActiveStep((prev) => prev - 1);

    const handleClientTypeChange = (type) => {
        setClientType(type);
        setData('client_type', type);
    };

    const isStepValid = (step) => {
        if (step === 0) return !!clientType;
        if (step === 1) {
            return visibleFields
                .filter(f => f.data.is_required)
                .every(f => {
                    const val = data[f.data.label];
                    if (f.type === 'checkbox_group') {
                        return val.preset.length > 0 || val.custom.some(c => c.value.trim() !== '');
                    }
                    if (f.type === 'select_field') {
                        return val.value === 'other' ? !!val.customValue : !!val.value;
                    }
                    return !!val;
                });
        }
        return true;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('application.store', template.slug ));
    };

    return (
        // <GuestLayout>
        <ClientLayout>
            <Head title={template.title} />
            <Container maxWidth="md" sx={{ mt: 4, mb: 4 }}>
                <Paper sx={{ p: 4, borderRadius: 3, boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' }}>
                    <Typography variant="h4" gutterBottom fontWeight="bold" color="#1B2559">
                        {template.title}
                    </Typography>
                    
                    <Stepper activeStep={activeStep} sx={{ mb: 4 }}>
                        {steps.map((label) => (
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

                    <form onSubmit={handleSubmit}>
                        {/* ШАГ 0: Тип заявителя */}
                        {activeStep === 0 && (
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

                        {/* ШАГ 1: Поля из БД */}
                        {activeStep === 1 && (
                            <Box>
                                <Typography variant="h6" sx={{ mb: 3 }}>Данные заявителя</Typography>
                                <Grid container spacing={3}>
                                    {visibleFields.map((block, index) => {
                                        const { label, is_required, options, allow_custom, allow_multiple_custom, special_format } = block.data;

                                        // Важно: возвращаем результат switch
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
                                                    <Grid item xs={12} key={index}>
                                                        <TextField
                                                            fullWidth
                                                            label={label}
                                                            required={is_required}
                                                            value={data[label] || ''}
                                                            onChange={e => setData(label, e.target.value)}
                                                            InputProps={special_format === 'passport' ? {
                                                                inputComponent: PassportMask,
                                                            } : {}}
                                                        />
                                                    </Grid>
                                                );

                                            case 'select_field':
                                                return (
                                                    <Grid item xs={12} key={index}>
                                                        <TextField
                                                            select
                                                            fullWidth
                                                            label={label}
                                                            value={data[label]?.value || ''}
                                                            onChange={e => setData(label, { ...data[label], value: e.target.value })}
                                                            SelectProps={{ native: true }}
                                                        >
                                                            <option value=""></option>
                                                            {options?.map(opt => <option key={opt.value} value={opt.value}>{opt.value}</option>)}
                                                            {allow_custom && <option value="other">Другое (ввести свой вариант)</option>}
                                                        </TextField>
                                                        {allow_custom && data[label]?.value === 'other' && (
                                                            <TextField
                                                                fullWidth
                                                                sx={{ mt: 2 }}
                                                                placeholder="Введите наименование..."
                                                                value={data[label]?.customValue || ''}
                                                                onChange={e => setData(label, { ...data[label], customValue: e.target.value })}
                                                            />
                                                        )}
                                                    </Grid>
                                                );

                                            case 'checkbox_group':
                                                return (
                                                    <Grid item xs={12} key={index}>
                                                        <Typography variant="subtitle2" sx={{ mb: 1, color: '#A3AED0' }}>{label}</Typography>
                                                        {options?.map((opt, i) => (
                                                            <Box key={i} sx={{ display: 'flex', alignItems: 'center' }}>
                                                                <Checkbox 
                                                                    checked={data[label]?.preset?.includes(opt.value) || false}
                                                                    onChange={e => {
                                                                        const next = e.target.checked 
                                                                            ? [...(data[label]?.preset || []), opt.value]
                                                                            : data[label].preset.filter(v => v !== opt.value);
                                                                        setData(label, { ...data[label], preset: next });
                                                                    }}
                                                                />
                                                                <Typography>{opt.value}</Typography>
                                                            </Box>
                                                        ))}
                                                        {/* Кнопка "Ещё" и кастомные поля */}
                                                        {data[label]?.custom?.map((item, i) => (
                                                            <Box key={item.id} sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1 }}>
                                                                <Checkbox checked disabled />
                                                                <TextField 
                                                                    size="small" 
                                                                    fullWidth 
                                                                    value={item.value} 
                                                                    onChange={e => {
                                                                        const next = [...data[label].custom];
                                                                        next[i].value = e.target.value;
                                                                        setData(label, { ...data[label], custom: next });
                                                                    }}
                                                                />
                                                            </Box>
                                                        ))}
                                                        {allow_multiple_custom && (
                                                            <Button 
                                                                size="small" 
                                                                onClick={() => setData(label, {
                                                                    ...data[label],
                                                                    custom: [...(data[label]?.custom || []), { id: Date.now(), value: '' }]
                                                                })}
                                                            >
                                                                + Ещё
                                                            </Button>
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

                        {/* ШАГ 2: Подтверждение */}
                        {/* ШАГ 2: Подтверждение */}
                        {activeStep === 2 && (
                            <Box>
                                <Typography variant="h6" sx={{ mb: 3 }}>Проверьте введённые данные</Typography>
                                <Grid container spacing={2}>
                                    {visibleFields.map((block, index) => {
                                        const label = block.data.label;
                                        const value = data[label];

                                        // Пропускаем заголовки секций
                                        if (block.type === 'section_header') return null;

                                        // Функция для красивого вывода значений
                                        const renderValue = () => {
                                            if (!value) return '—';

                                            // Если это чекбоксы
                                            if (block.type === 'checkbox_group') {
                                                const selected = [
                                                    ...(value.preset || []),
                                                    ...(value.custom?.map(c => c.value).filter(Boolean) || [])
                                                ];
                                                return selected.length > 0 ? selected.join(', ') : '—';
                                            }

                                            // Если это селект с "Другое"
                                            if (block.type === 'select_field') {
                                                if (value.value === 'other') return value.customValue || '—';
                                                return value.value || '—';
                                            }

                                            // Обычная строка (input_field)
                                            return typeof value === 'string' ? value : JSON.stringify(value);
                                        };

                                        return (
                                            <Grid item xs={12} md={6} key={index}>
                                                <Paper variant="outlined" sx={{ p: 2, borderRadius: '12px', bgcolor: '#F4F7FE' }}>
                                                    <Typography variant="caption" color="text.secondary">{label}</Typography>
                                                    <Typography variant="body1" fontWeight="bold">
                                                        {renderValue()}
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
                                disabled={activeStep === 0} 
                                onClick={handleBack} 
                                startIcon={<BackIcon />}>
                                Назад
                            </Button>
                            
                            {activeStep < steps.length - 1 ? (
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
                                    Отправить
                                </Button>
                            )}
                        </Box>
                    </form>
                </Paper>
            </Container>
        {/* </GuestLayout> */}
        </ClientLayout>
    );
}
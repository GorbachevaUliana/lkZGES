import React, { useState, useMemo } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Container, Typography, Paper, Box, Stepper, Step, StepLabel, Button } from '@mui/material';
import { ArrowForward as ArrowIcon, ArrowBack as BackIcon, Send as SendIcon } from '@mui/icons-material';
import ClientLayout from '@/Layouts/ClientLayout';
import { ToastProvider } from '@/contexts/ToastContext';
import FormStep   from '@/Components/Form/steps/FormStep';
import FormReview from '@/Components/Form/steps/FormReview';

const STEPS = ['Заполнение данных', 'Проверка'];

export default function DynamicForm({ template }) {
    return (
        <ToastProvider>
            <DynamicFormContent template={template} />
        </ToastProvider>
    );
}

function DynamicFormContent({ template }) {
    const [activeStep, setActiveStep] = useState(0);
    const clientType      = template.client_type;
    const clientTypeLabel = clientType === 'individual' ? 'Физическое лицо' : 'Юридическое лицо';

    const initialData = useMemo(() => {
        const fields = {};
        template.content?.forEach(block => {
            const key = block.data.key || block.data.label;
            if      (block.type === 'checkbox_group') fields[key] = { preset: [], custom: [] };
            else if (block.type === 'select_field')   fields[key] = { value: '', customValue: '' };
            else if (block.type === 'file_upload')    fields[key] = [];
            else if (block.type === 'dynamic_input')  fields[key] = { selected: '', inputValue: '' };
            else if (block.type === 'input_field')    fields[key] = block.data.default_value || '';
        });
        return fields;
    }, [template]);

    const { data, setData, post, processing, errors } = useForm(initialData);

    const visibleFields = useMemo(() => {
        const allowed = ['input_field', 'select_field', 'checkbox_group', 'section_header', 'file_upload', 'dynamic_input', 'text_block'];
        return template.content?.filter(block => allowed.includes(block.type)) || [];
    }, [template]);

    const handleFieldChange = (key, value) => setData(key, value);

    const isStepValid = (step) => {
        if (step !== 0) return true;
        return visibleFields.filter(f => f.data.is_required).every(f => {
            const key = f.data.key || f.data.label;
            if (f.type === 'input_field' && f.data.is_readonly && f.data.default_value) return true;
            const val = data[key];
            if (f.type === 'checkbox_group') return val.preset.length > 0 || val.custom.some(c => c.value.trim());
            if (f.type === 'file_upload')     return val && val.length > 0;
            if (f.type === 'select_field')    return val.value === 'other' ? !!val.customValue : !!val.value;
            if (f.type === 'dynamic_input') {
                if (!val.selected) return false;
                const opt = f.data.options?.find(o => o.value === val.selected);
                return opt?.input_type && opt.input_type !== 'none' ? !!val.inputValue.trim() : true;
            }
            return !!val;
        });
    };

    return (
        <ClientLayout>
            <Head title={template.title} />
            <Container maxWidth="md" sx={{ mt: 4, mb: 4 }}>
                <Paper sx={{ p: 4, borderRadius: 3, boxShadow: '0px 18px 40px rgba(112, 144, 176, 0.12)' }}>
                    <Typography variant="h4" gutterBottom fontWeight="bold" color="#1B2559">
                        {template.title}
                    </Typography>
                    <Typography variant="subtitle1" color="text.secondary" sx={{ mb: 3 }}>
                        {clientTypeLabel}
                    </Typography>

                    <Stepper activeStep={activeStep} sx={{ mb: 4 }}>
                        {STEPS.map(label => (
                            <Step key={label}><StepLabel>{label}</StepLabel></Step>
                        ))}
                    </Stepper>

                    <form onSubmit={e => { e.preventDefault(); post(route('application.store', template.slug)); }}>
                        {activeStep === 0 && (
                            <FormStep
                                visibleFields={visibleFields}
                                data={data}
                                onChange={handleFieldChange}
                                errors={errors}
                            />
                        )}
                        {activeStep === 1 && (
                            <FormReview
                                visibleFields={visibleFields}
                                data={data}
                                clientTypeLabel={clientTypeLabel}
                            />
                        )}

                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 4 }}>
                            <Button
                                disabled={activeStep === 0}
                                onClick={() => setActiveStep(p => p - 1)}
                                startIcon={<BackIcon />}
                                sx={{ color: '#4318FF' }}
                            >
                                Назад
                            </Button>
                            {activeStep === 0 ? (
                                <Button
                                    variant="contained"
                                    onClick={() => setActiveStep(1)}
                                    disabled={!isStepValid(0)}
                                    endIcon={<ArrowIcon />}
                                    sx={{ bgcolor: '#4318FF', '&:hover': { bgcolor: '#3614B8' }, borderRadius: '12px', px: 3 }}
                                >
                                    Проверить
                                </Button>
                            ) : (
                                <Button
                                    type="submit"
                                    variant="contained"
                                    disabled={processing}
                                    endIcon={<SendIcon />}
                                    sx={{ bgcolor: '#22C55E', '&:hover': { bgcolor: '#16A34A' }, borderRadius: '12px', px: 3 }}
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
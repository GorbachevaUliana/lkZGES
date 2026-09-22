import React from 'react';
import {
    Box, Typography, Radio, RadioGroup, FormControlLabel, FormControl, Alert,
} from '@mui/material';

// Та же нормализация, что на сервере в extractMaxPower:
// убираем обычные и неразрывные пробелы, запятую меняем на точку.
function parsePower(raw) {
    if (raw === null || raw === undefined || raw === '') return null;
    const normalized = String(raw).replace(/[\s\u00A0]/g, '').replace(',', '.');
    const value = Number(normalized);
    return Number.isFinite(value) ? value : null;
}

export function isSigningMandatory(maxPower, thresholdKw) {
    const power = parsePower(maxPower);
    return power !== null && power >= thresholdKw;
}

export default function SigningChoice({ clientType, maxPower, thresholdKw, value, onChange, error }) {
    const mandatory = isSigningMandatory(maxPower, thresholdKw);

    // При обязательном подписании выбор заблокирован на «подписанном».
    // В данные формы это не пишем: сервер при такой мощности
    // выбор всё равно не читает.
    const current = mandatory
        ? 'true'
        : (value === null || value === undefined ? '' : String(value));

    const signedDescription = clientType === 'individual'
        ? 'вводом кода подтверждения, который придёт на вашу почту или телефон'
        : 'электронной подписью организации (УКЭП)';

    return (
        <Box sx={{
            mt: 4, p: 3, borderRadius: 3,
            border: '1px solid',
            borderColor: error ? 'error.main' : 'divider',
        }}>
            <Typography variant="h6" fontWeight="bold" color="#1B2559" gutterBottom>
                Подписание договора
            </Typography>

            {mandatory && (
                <Alert severity="info" sx={{ mb: 2, borderRadius: '12px' }}>
                    При запрашиваемой мощности {thresholdKw} кВт и выше подписание
                    договора обеими сторонами обязательно. Отказаться от подписания нельзя.
                </Alert>
            )}

            <FormControl disabled={mandatory} fullWidth>
                <RadioGroup value={current} onChange={(e) => onChange(e.target.value === 'true')}>
                    <FormControlLabel
                        value="false"
                        control={<Radio />}
                        sx={{ alignItems: 'flex-start', mb: 2 }}
                        label={
                            <Box sx={{ pt: 1 }}>
                                <Typography fontWeight="500">Договор без подписания</Typography>
                                <Typography variant="body2" color="text.secondary">
                                    Договор будет размещён в вашем личном кабинете.
                                    Подтверждать ничего не потребуется.
                                </Typography>
                            </Box>
                        }
                    />
                    <FormControlLabel
                        value="true"
                        control={<Radio />}
                        sx={{ alignItems: 'flex-start' }}
                        label={
                            <Box sx={{ pt: 1 }}>
                                <Typography fontWeight="500">Подписанный договор</Typography>
                                <Typography variant="body2" color="text.secondary">
                                    Договор будет подписан со стороны ООО «Заринская горэлектросеть»,
                                    после чего вам нужно будет подписать его в личном кабинете — {signedDescription}.
                                </Typography>
                            </Box>
                        }
                    />
                </RadioGroup>
            </FormControl>

            {error && (
                <Typography variant="body2" color="error" mt={1}>{error}</Typography>
            )}
        </Box>
    );
}
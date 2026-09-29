import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { Paper, Box, Typography, Button, TextField, Alert, CircularProgress, IconButton } from '@mui/material';
import {
    Description as FileIcon,
    CheckCircle as CheckIcon,
    Download as DownloadIcon,
    Close as CloseIcon,
} from '@mui/icons-material';

export default function ContractSigningCard({ contract }) {
    const [codeSent, setCodeSent] = useState(false);
    const [code, setCode]         = useState('');
    const [busy, setBusy]         = useState(false);
    const [error, setError]       = useState(null);
    const [notice, setNotice]     = useState(null);
    const [sigFile, setSigFile]   = useState(null);

    const storageKey = `dismissed_contract_card_${contract?.id}`;

    const [dismissed, setDismissed] = useState(() => {
        if (typeof window === 'undefined') return false;
        return localStorage.getItem(storageKey) === 'true';
    });

    const dismiss = () => {
        localStorage.setItem(storageKey, 'true');
        setDismissed(true);
    };

    if (!contract) return null;

    const signed = !contract.needs_signing;
    const isUkep = contract.method === 'ukep';

    if (signed && dismissed) return null;

    const sendCode = () => {
        setBusy(true);
        setError(null);
        router.post(`/client/contracts/${contract.id}/signing-code`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                setCodeSent(true);
                setNotice(page.props.flash?.success ?? 'Код отправлен');
            },
            onError: (errors) => setError(Object.values(errors)[0] ?? 'Не удалось отправить код'),
            onFinish: () => setBusy(false),
        });
    };

    const sign = () => {
        setBusy(true);
        setError(null);
        router.post(`/client/contracts/${contract.id}/sign`, { code }, {
            preserveScroll: true,
            onError: (errors) => setError(Object.values(errors)[0] ?? 'Не удалось подписать'),
            onFinish: () => setBusy(false),
        });
    };
    
    const signWithUkep = () => {
        if (!sigFile) return;

        const formData = new FormData();
        formData.append('file', sigFile);

        setBusy(true);
        setError(null);
        router.post(`/client/contracts/${contract.id}/sign-ukep`, formData, {
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => setError(Object.values(errors)[0] ?? 'Не удалось подписать'),
            onFinish: () => setBusy(false),
        });
    };

    return (
        <Paper sx={{
            p: 3, mb: 3, borderRadius: '16px',
            border: '1px solid',
            borderColor: signed ? '#C8E6C9' : '#FFE082',
            bgcolor: signed ? '#E8F5E9' : '#FFF8E1',
        }}>
            <Box display="flex" alignItems="center" gap={2} mb={signed ? 0 : 2}>
                {signed
                    ? <CheckIcon sx={{ color: '#2E7D32', fontSize: 32 }} />
                    : <FileIcon sx={{ color: '#F57F17', fontSize: 32 }} />}
                <Box flexGrow={1}>
                    <Typography fontWeight="bold" color={signed ? '#2E7D32' : '#F57F17'}>
                        Договор №{contract.number} · {contract.status_label}
                    </Typography>
                    {signed && contract.signed_at && (
                        <Typography variant="body2" color="text.secondary">
                            Подписан {contract.signed_at}
                        </Typography>
                    )}
                </Box>
                {signed && (
                    <IconButton onClick={dismiss} size="small" sx={{ color: '#2E7D32' }} aria-label="Скрыть">
                        <CloseIcon fontSize="small" />
                    </IconButton>
                )}
            </Box>
            {!signed && (
                <>
                    <Typography variant="body2" color="text.secondary" mb={2}>
                        Договор подписан со стороны ООО «Заринская горэлектросеть».
                        {isUkep
                            ? ' Ознакомьтесь с ним, подпишите своей электронной подписью и загрузите файл подписи.'
                            : ' Ознакомьтесь с ним и подпишите кодом подтверждения. Вводя код, вы подписываете договор простой электронной подписью.'}
                    </Typography>

                    <Paper
                        variant="outlined"
                        sx={{
                            p: 2, mb: 2, borderRadius: '12px', bgcolor: '#fff',
                            display: 'flex', alignItems: 'center', gap: 2,
                        }}
                    >
                        <Box sx={{ p: 1, bgcolor: '#F4F7FE', borderRadius: '10px', color: '#4318FF', display: 'flex' }}>
                            <FileIcon />
                        </Box>
                        <Typography sx={{ flexGrow: 1, overflow: 'hidden', textOverflow: 'ellipsis' }} noWrap>
                            {contract.file_name}
                        </Typography>
                        <Button
                            href={contract.url}
                            target="_blank"
                            startIcon={<DownloadIcon />}
                            sx={{ flexShrink: 0, borderRadius: '8px' }}
                        >
                            Скачать
                        </Button>
                    </Paper>

                        {notice && <Alert severity="info" sx={{ mb: 2, borderRadius: '12px' }}>{notice}</Alert>}
                        {error  && <Alert severity="error" sx={{ mb: 2, borderRadius: '12px' }}>{error}</Alert>}


                    {isUkep ? (
                        <Box display="flex" gap={2} alignItems="center" flexWrap="wrap">
                            <Button
                                variant="outlined"
                                component="label"
                                sx={{ borderRadius: '12px' }}
                            >
                                {sigFile ? 'Выбрать другой файл' : 'Выбрать файл подписи'}
                                <input
                                    type="file"
                                    hidden
                                    accept=".sig,.p7s"
                                    onChange={(e) => setSigFile(e.target.files[0] ?? null)}
                                />
                            </Button>

                            {sigFile && (
                                <Typography variant="body2" color="text.secondary">
                                    {sigFile.name}
                                </Typography>
                            )}

                            <Button
                                variant="contained"
                                onClick={signWithUkep}
                                disabled={busy || !sigFile}
                                startIcon={busy ? <CircularProgress size={18} color="inherit" /> : null}
                                sx={{ borderRadius: '12px' }}
                            >
                                Подписать
                            </Button>
                        </Box>
                    ) : (
                        <>
                            {!codeSent ? (
                                <Button
                                    variant="contained"
                                    onClick={sendCode}
                                    disabled={busy}
                                    startIcon={busy ? <CircularProgress size={18} color="inherit" /> : null}
                                    sx={{ borderRadius: '12px' }}
                                >
                                    Получить код для подписания
                                </Button>
                            ) : (
                                <Box display="flex" gap={2} alignItems="flex-start" flexWrap="wrap">
                                    <TextField
                                        label="Код из письма"
                                        value={code}
                                        onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
                                        inputProps={{ inputMode: 'numeric', maxLength: 6 }}
                                        sx={{ width: 180, '& .MuiOutlinedInput-root': { borderRadius: '12px' } }}
                                    />
                                    <Button
                                        variant="contained"
                                        onClick={sign}
                                        disabled={busy || code.length !== 6}
                                        sx={{ borderRadius: '12px', mt: 1 }}
                                    >
                                        Подписать
                                    </Button>
                                    <Button onClick={sendCode} disabled={busy} sx={{ mt: 1 }}>
                                        Отправить код заново
                                    </Button>
                                </Box>
                            )}
                        </>
                    )}
                </>
            )}
        </Paper>
    );
}
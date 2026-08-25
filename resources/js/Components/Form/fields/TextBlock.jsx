import React from 'react';
import { Paper, Typography } from '@mui/material';
import DOMPurify from 'dompurify';

// Проблема №18: body приходит из ApplicationTemplate.content, который
// редактируется через Filament — то есть источник доверенный (доступ
// в /manager только у role=admin, см. Проблему из Аудита Frontend), но
// это единственный рубеж защиты. Если аккаунт админа когда-нибудь
// скомпрометируют, необработанный dangerouslySetInnerHTML исполнялся бы
// в браузере у каждого, кто открывает форму заявки. DOMPurify оставляет
// форматирование (жирный, ссылки, списки и т.п.), но вырезает
// исполняемый код.
export default function TextBlock({ block }) {
    const sanitizedBody = DOMPurify.sanitize(block.data.body || '');

    return (
        <Paper variant="outlined" sx={{ p: 2, borderRadius: '12px', bgcolor: '#F0F9FF', borderColor: '#BAE6FD' }}>
            <Typography
                variant="body2"
                component="div"
                sx={{ color: '#0369A1', '& p': { mb: 1 }, '& p:last-child': { mb: 0 } }}
                dangerouslySetInnerHTML={{ __html: sanitizedBody }}
            />
        </Paper>
    );
}
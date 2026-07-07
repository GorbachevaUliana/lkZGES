import React from 'react';
import { Paper, Typography } from '@mui/material';

export default function TextBlock({ block }) {
    return (
        <Paper variant="outlined" sx={{ p: 2, borderRadius: '12px', bgcolor: '#F0F9FF', borderColor: '#BAE6FD' }}>
            <Typography
                variant="body2"
                component="div"
                sx={{ color: '#0369A1', '& p': { mb: 1 }, '& p:last-child': { mb: 0 } }}
                dangerouslySetInnerHTML={{ __html: block.data.body || '' }}
            />
        </Paper>
    );
}
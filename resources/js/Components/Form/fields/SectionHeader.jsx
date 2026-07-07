import React from 'react';
import { Typography, Divider } from '@mui/material';

export default function SectionHeader({ block }) {
    return (
        <>
            <Typography variant={block.data.level || 'h6'} fontWeight="bold" color="#2B3674">
                {block.data.title}
            </Typography>
            <Divider sx={{ mb: 1 }} />
        </>
    );
}
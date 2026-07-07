import React from 'react';
import { Grid, Typography, Divider } from '@mui/material';

export default function SectionHeader({ block, index }) {
    return (
        <Grid item xs={12} sx={{ mt: 2 }} key={index}>
            <Typography variant={block.data.level || 'h6'} fontWeight="bold" color="#2B3674">
                {block.data.title}
            </Typography>
            <Divider sx={{ mb: 1 }} />
        </Grid>
    );
}
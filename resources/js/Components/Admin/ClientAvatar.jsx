import React, { useMemo } from 'react';
import { Avatar } from '@mui/material';

const stringToColor = (string) => {
    if (!string) return '#3f51b5';
    let hash = 0;
    for (let i = 0; i < string.length; i++) {
        hash = string.charCodeAt(i) + ((hash << 5) - hash);
    }
    const colors = ['#F44336', '#E91E63', '#9C27B0', '#673AB7', '#3F51B5', '#2196F3', '#03A9F4', '#009688', '#4CAF50', '#8BC34A', '#FF9800'];
    return colors[Math.abs(hash) % colors.length];
};

export default function ClientAvatar({ name, sx = {} }) {
    const bgColor = useMemo(() => stringToColor(name), [name]);
    return (
        <Avatar sx={{ ...sx, bgcolor: bgColor, fontWeight: 'bold' }}>
            {name ? name[0].toUpperCase() : '?'}
        </Avatar>
    );
}
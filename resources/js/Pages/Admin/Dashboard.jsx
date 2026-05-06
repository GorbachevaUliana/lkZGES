import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Grid, Paper, Typography, Box, ToggleButton, ToggleButtonGroup } from '@mui/material';
import { 
    LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, 
    PieChart, Pie, Cell, Label 
} from 'recharts';

const COLORS = ['#1976d2', '#2e7d32', '#ed6c02', '#9c27b0', '#d32f2f'];

export default function Dashboard({ auth, stats, chartData, pieData }) {
    const [period, setPeriod] = React.useState('month');

    const handlePeriodChange = (event, newPeriod) => {
        if (newPeriod !== null) setPeriod(newPeriod);
    }

    const totalTickets = pieData.reduce((sum, entry) => sum + entry.value, 0);

    return (
        <AdminLayout user={auth.user}>
            {/* Глобальный стиль для удаления черной рамки при клике на графики */}
            <style>
                {`
                    .recharts-surface:focus, .recharts-sector:focus, .recharts-curve:focus {
                        outline: none !important;
                    }
                `}
            </style>

            <Typography variant="h4" sx={{ mb: 4, fontWeight: 'bold' }}>
                Панель управления
            </Typography>

            <Grid container spacing={3} alignItems="stretch">
                {/* ЛЕВАЯ КОЛОНКА */}
                <Grid item xs={12} md={6}>
                    <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column', gap: 3 }}>
                        <Grid container spacing={3}>
                            <Grid item xs={12} sm={6}>
                                <Paper sx={{ p: 3, borderRadius: '15px', height: 140 }}>
                                    <Typography color="text.secondary">Всего потребителей</Typography>
                                    <Typography variant="h3" fontWeight="bold">{stats.clients_count}</Typography>
                                </Paper>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Paper sx={{ p: 3, borderRadius: '15px', height: 140 }}>
                                    <Typography color="text.secondary">Новых обращений</Typography>
                                    <Typography variant="h3" fontWeight="bold" color="primary">{stats.tickets_count}</Typography>
                                </Paper>
                            </Grid>
                        </Grid>

                        <Paper sx={{ p: 3, borderRadius: '15px', flexGrow: 1, minHeight: 420 }}>
                            <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
                                <Typography variant="h6" fontWeight="bold">Динамика обращений</Typography>
                                <ToggleButtonGroup value={period} exclusive onChange={handlePeriodChange} size="small">
                                    <ToggleButton value="week">Неделя</ToggleButton>
                                    <ToggleButton value="month">Месяц</ToggleButton>
                                    <ToggleButton value="year">Год</ToggleButton>
                                </ToggleButtonGroup>
                            </Box>
                            <ResponsiveContainer width="100%" height={330}>
                                <LineChart data={chartData}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f0f0f0" />
                                    <XAxis dataKey="date" />
                                    <YAxis allowDecimals={false} />
                                    {/* Добавлена настройка Tooltip для удаления рамки */}
                                    <Tooltip wrapperStyle={{ outline: 'none' }} />
                                    <Line 
                                        type="monotone" 
                                        dataKey="count" 
                                        stroke="#1976d2" 
                                        strokeWidth={4} 
                                        dot={{ r: 4 }} 
                                        activeDot={{ r: 6 }} 
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </Paper>
                    </Box>
                </Grid>

                {/* ПРАВАЯ КОЛОНКА */}
                {/* ПРАВАЯ КОЛОНКА — PREMIUM PIE CHART */}
                <Grid item xs={12} md={6}>
                    <Paper
                        elevation={3}
                        sx={{
                            p: 4,
                            borderRadius: '22px',
                            height: '100%',
                            minHeight: 590,
                            display: 'flex',
                            flexDirection: 'column',
                            background:
                                'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
                            boxShadow:
                                '0 10px 35px rgba(0,0,0,0.06)'
                        }}
                    >
                        {/* Заголовок */}
                        <Box
                            sx={{
                                display: 'flex',
                                justifyContent: 'space-between',
                                alignItems: 'center',
                                mb: 3
                            }}
                        >
                            <Typography
                                variant="h6"
                                fontWeight="bold"
                                sx={{ color: '#111827' }}
                            >
                                Темы обращений
                            </Typography>

                            <Typography
                                sx={{
                                    fontSize: 13,
                                    color: '#64748b',
                                    fontWeight: 600
                                }}
                            >
                                Всего: {totalTickets}
                            </Typography>
                        </Box>

                        {/* Диаграмма */}
                        <Box sx={{ flexGrow: 1 }}>
                            <ResponsiveContainer width="100%" height={400}>
                                <PieChart>
                                    <Pie
                                        data={pieData}
                                        dataKey="value"
                                        cx="50%"
                                        cy="50%"
                                        innerRadius={95}
                                        outerRadius={150}
                                        paddingAngle={3}
                                        cornerRadius={10}
                                        stroke="none"
                                        isAnimationActive={true}
                                        animationDuration={700}
                                    >
                                        {pieData.map((entry, index) => (
                                            <Cell
                                                key={index}
                                                fill={COLORS[index % COLORS.length]}
                                                style={{
                                                    outline: 'none',
                                                    cursor: 'pointer',
                                                    filter:
                                                        'drop-shadow(0 6px 12px rgba(0,0,0,0.08))'
                                                }}
                                            />
                                        ))}

                                    </Pie>

                                    {/* Tooltip */}
                                    <Tooltip
                                        wrapperStyle={{ outline: 'none' }}
                                        contentStyle={{
                                            border: 'none',
                                            borderRadius: '14px',
                                            padding: '12px 14px',
                                            boxShadow:
                                                '0 10px 25px rgba(0,0,0,0.08)',
                                            background: '#ffffff'
                                        }}
                                        formatter={(value, name) => [
                                            `${value}`,
                                            `${name}`
                                        ]}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </Box>

                        {/* PREMIUM LEGEND */}
                        <Box
                            sx={{
                                mt: 2,
                                display: 'flex',
                                flexDirection: 'column',
                                gap: 1.6
                            }}
                        >
                            {pieData.map((entry, index) => (
                                <Box
                                    key={index}
                                    sx={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'space-between',
                                        px: 2,
                                        py: 1.3,
                                        borderRadius: '14px',
                                        background: '#f8fafc',
                                        transition: '0.2s',
                                        '&:hover': {
                                            background: '#eef2ff',
                                            transform: 'translateX(3px)'
                                        }
                                    }}
                                >
                                    <Box
                                        sx={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 1.5
                                        }}
                                    >
                                        <Box
                                            sx={{
                                                width: 12,
                                                height: 12,
                                                borderRadius: '50%',
                                                bgcolor:
                                                    COLORS[index % COLORS.length]
                                            }}
                                        />

                                        <Typography
                                            sx={{
                                                fontSize: 14,
                                                fontWeight: 500,
                                                color: '#334155'
                                            }}
                                        >
                                            {entry.name}
                                        </Typography>
                                    </Box>

                                    <Typography
                                        sx={{
                                            fontWeight: 700,
                                            color: '#111827'
                                        }}
                                    >
                                        {entry.value}
                                    </Typography>
                                </Box>
                            ))}
                        </Box>
                    </Paper>
                </Grid>
            </Grid>
        </AdminLayout>
    );
}
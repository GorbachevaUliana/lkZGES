import { Chip } from '@mui/material';
import { ui } from '@/theme/ui';

const map = {
    success: { bg: '#E8F5E9', color: ui.colors.success },
    warning: { bg: '#FFF3E0', color: ui.colors.warning },
    error: { bg: '#FFEBEE', color: ui.colors.error },
    info: { bg: '#E3F2FD', color: '#1976D2' }
};

export default function StatusChip({ label, type = 'info' }) {
    const style = map[type];

    return (
        <Chip
            label={label}
            sx={{
                bgcolor: style.bg,
                color: style.color,
                fontWeight: 'bold'
            }}
        />
    );
}
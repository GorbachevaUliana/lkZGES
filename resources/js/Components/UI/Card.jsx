import { Paper } from '@mui/material';
import { ui } from '@/theme/ui';

export default function UICard({ children, sx = {} }) {
    return (
        <Paper
            sx={{
                p: 3,
                borderRadius: ui.radius.card,
                boxShadow: ui.shadow.card,
                ...sx
            }}
        >
            {children}
        </Paper>
    );
}
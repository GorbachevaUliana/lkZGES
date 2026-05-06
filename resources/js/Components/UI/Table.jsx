import { TableContainer, Paper } from '@mui/material';
import { ui } from '@/theme/ui';

export function UITableContainer({ children, sx = {} }) {
    return (
        <TableContainer
            component={Paper}
            sx={{
                borderRadius: '20px',
                border: `1px solid ${ui.colors.border}`,
                boxShadow: '0px 10px 30px rgba(0,0,0,0.02)',
                overflow: 'hidden',
                ...sx
            }}
        >
            {children}
        </TableContainer>
    );
}
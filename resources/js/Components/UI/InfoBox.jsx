import { Box } from '@mui/material';
import { ui } from '@/theme/ui';

export default function InfoBox({ children }) {
    return (
        <Box
            sx={{
                p: 2,
                bgcolor: ui.colors.primaryLight,
                borderRadius: ui.radius.inner
            }}
        >
            {children}
        </Box>
    );
}
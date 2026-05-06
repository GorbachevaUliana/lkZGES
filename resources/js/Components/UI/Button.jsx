import { Button } from '@mui/material';
import { ui } from '@/theme/ui';

export default function UIButton({ children, sx = {}, ...props }) {
    return (
        <Button
            variant="contained"
            sx={{
                borderRadius: ui.radius.inner,
                bgcolor: ui.colors.primary,
                textTransform: 'none',
                '&:hover': {
                    bgcolor: '#2F0EDB'
                },
                ...sx
            }}
            {...props}
        >
            {children}
        </Button>
    );
}
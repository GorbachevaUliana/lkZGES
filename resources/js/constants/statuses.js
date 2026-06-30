export const APPLICATION_STATUS_COLORS = {
    pending:    { bg: '#FFF3E0', color: '#F57C00', label: 'Ожидает' },
    processing: { bg: '#E3F2FD', color: '#1976D2', label: 'В работе' },
    approved:   { bg: '#E8F5E9', color: '#2E7D32', label: 'Одобрена' },
    rejected:   { bg: '#FFEBEE', color: '#C62828', label: 'Отклонена' },
};

export const TICKET_STATUS_MAP = {
    new:     { label: 'Новое',    color: 'primary' },
    closed:  { label: 'Решено',   color: 'success' },
    pending: { label: 'В работе', color: 'warning' },
};
export const CLIENT_TYPE_LABELS = {
    individual:   'Физическое лицо',
    legal:        'Юридическое лицо',
    entrepreneur: 'Индивидуальный предприниматель',
};

export const CLIENT_TYPE_SHORT_LABELS = {
    individual:   'Физлицо',
    legal:        'Юрлицо',
    entrepreneur: 'ИП',
};

export const CLIENT_TYPE_FORM_SLUGS = {
    individual:   'application-individual',
    legal:        'application-legal',
    entrepreneur: 'application-entrepreneur',
};

export const getApplicationSlug = (clientType) =>
    CLIENT_TYPE_FORM_SLUGS[clientType] || CLIENT_TYPE_FORM_SLUGS.individual;

export const getClientTypeLabel = (value) =>
    CLIENT_TYPE_LABELS[value] || value;

export const getClientTypeShortLabel = (value) =>
    CLIENT_TYPE_SHORT_LABELS[value] || value;
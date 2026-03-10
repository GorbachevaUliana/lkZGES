import React, {useState, useMemo} from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Paper, Table, TableBody, TableCell, TableHead,
    TableRow, Chip, InputBase, Box, Typography, TableContainer } from '@mui/material';
    import { DataGrid } from '@mui/x-data-grid';
import SearchIcon from '@mui/icons-material/Search';
import TicketCard from '@/Components/Admin/TicketCard';
import { useForm } from '@inertiajs/react';
import ClientCard from '@/Components/Admin/ClientCard';
import { router } from '@inertiajs/react';

const fixKeyboardLayout = (text) => {
    if (!text) return '';
    const map = {'q':'й', 'w':'ц', 'e':'у', 'r':'к', 't':'е', 'y':'н', 'u':'г', 'i':'ш', 'o':'щ', 'p':'з', '[':'х', ']':'ъ', 'a':'ф', 's':'ы', 'd':'в', 'f':'а', 'g':'п', 'h':'р', 'j':'о', 'k':'л', 'l':'д', ';':'ж', "'":'э', 'z':'я', 'x':'ч', 'c':'с', 'v':'м', 'b':'и', 'n':'т', 'm':'ь', ',':'б', '.':'ю'};
    return text.toLowerCase().split('').map(char => map[char] || char).join('');
};

export default function TicketsIndex({ auth, tickets, staff_members }) {
    const [editOpen, setEditOpen] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [tabValue, setTabValue] = useState(0);
    const [searchQuery, setSearchQuery] = useState('');
    const [toast, setToast] = useState({ open: false, message: '', severity: 'success' });
    const [confirmMeta, setConfirmMeta] = useState({ open: false, title: '', content: '', onConfirm: () => {} });
    const [clientModalOpen, setClientModalOpen] = useState(false);
    const [selectedClient, setSelectedClient] = useState(null);
    
    const { data, setData, post, put, reset, processing, errors } = useForm({
        id: '', user_id:'', subject:'', message: '', staff_id:'', status:'', user: null, staff: null,
    });

    const showToast = (message, severity = 'success') => setToast({ open: true, message, severity });

    const handleOpenCreate = () => {
        reset();
        setData({
            id: '', user_id:'', subject:'', message: '', staff_id: '', status:'',
        });
        setCreateOpen(true);
    };

    const handleRowClick = (params) => {
        setData({
            ...params.row, 
            admin_reply: params.row.admin_reply || '',
            admin_files: [],
            attachments: params.row.attachments || [], 
        });
        setEditOpen(true);
    };

    const handleOpenClientCard = (userId) => {
        const ticket = tickets.find(t => t.user_id === userId);
        const clientData = ticket?.user?.client;

        if (clientData) {
            setSelectedClient(clientData);
            setClientModalOpen(true);
        } else {
            showToast('Данные потребителя не найдены', 'error');
        }
    };

    const filteredTickets = useMemo(() => {
        const query = searchQuery.toLowerCase();
        const altQuery = fixKeyboardLayout(query);
        return (tickets || []).filter(t => {
            const s = `${t.subject} ${t.message} ${t.status}`.toLowerCase();
            return s.includes(query) || s.includes(altQuery);
        });
    }, [searchQuery, tickets]);

    const columns = [
        { 
            field: 'user', 
            headerName: 'Потребитель', 
            width: 200, 
            renderCell: (params) => params.row.user?.name || 'Не указан'
        },
        { 
            field: 'subject', 
            headerName: 'Тема заявки', 
            width: 250 
        },
        { 
            field: 'message', 
            headerName: 'Текст обращения', 
            flex: 1, 
            minWidth: 300,
            renderCell: (params) => {
                const text = params.value || '';
                return text.length > 50 ? text.substring(0, 50) + '...' : text;
            }
        },
        { 
            field: 'staff', 
            headerName: 'Ответственный', 
            width: 200, 
            renderCell: (params) => {
                return params.row.staff?.name || (
                    <Typography variant="caption" color="text.secondary">Не назначен</Typography>
                );
            }
        },
        { 
            field: 'status', 
            headerName: 'Статус', 
            width: 150,
            renderCell: (params) => {
                const statusMap = {
                    'new' : { label: 'Новое', color: 'primary'},
                    'open': { label: 'Новое', color: 'primary' },
                    'closed': { label: 'Решено', color: 'success' },
                    'pending': { label: 'В работе', color: 'warning' }
                };
                const current = statusMap[params.value] || { label: params.value, color: 'default' };
                return <Chip label={current.label} color={current.color} size="small" variant="outlined" />;
            }
        },
    ];

    const promptDeleteClient = (id) => {
        setConfirmMeta({
            open: true,
            title: 'Удаление профиля',
            content: 'Вы действительно хотите удалить этого потребителя?',
            onConfirm: () => {
                router.delete(`/admin/clients/${id}`, {
                    onSuccess: () => {
                        setEditOpen(false);
                        showToast('Потребитель удален', 'warning');
                    }
                });
            }
        });
    };

    const promptDeleteDocument = (docId) => {
        setConfirmMeta({
            open: true,
            title: 'Удаление документа',
            onClick: true,
            content: 'Удалить этот файл навсегда?',
            onConfirm: () => {
                router.delete(route('admin.documents.destroy', docId), {
                    onSuccess: (page) => {
                        setConfirmMeta(prev => ({ ...prev, open: false }));
                        const updatedClient = page.props.clients.find(c => c.id === data.id);
                        if (updatedClient) setData('documents', updatedClient.documents);
                        showToast('Документ успешно удален');
                    }
                });
            }
        });
    };

    return (
        <AdminLayout user={auth.user}>
            <Box sx={{ p: 4, bgcolor: '#f4f7fe', minHeight: '100vh' }}>
                <Typography variant="h4" fontWeight="800" color="#1B2559" mb={4}>
                    Обращения
                </Typography>

                <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
                    <Paper sx={{ px: 2, display: 'flex', alignItems: 'center', borderRadius: '30px', width: 400, boxShadow: 'none', border: '1px solid #E0E5F2' }}>
                        <SearchIcon sx={{ color: '#A3AED0' }} />
                        <InputBase
                            placeholder="Поиск по теме или тексту..."
                            fullWidth
                            sx={{ ml: 1, py: 1 }}
                            value={searchQuery}
                            onChange={e => setSearchQuery(e.target.value)}
                        />
                    </Paper>
                </Box>

                <Paper sx={{ borderRadius: '20px', overflow: 'hidden', border: 'none', boxShadow: '0px 10px 30px rgba(0,0,0,0.02)' }}>
                    <DataGrid 
                        rows={filteredTickets} 
                        columns={columns} 
                        autoHeight 
                        onRowDoubleClick={handleRowClick}
                        disableRowSelectionOnClick
                        sx={{ 
                            border: 'none', 
                            '& .MuiDataGrid-columnHeaders': { bgcolor: '#F4F7FE', borderBottom: 'none' },
                            '& .MuiDataGrid-cell': { borderBottom: '1px solid #F4F7FE' },
                            cursor: 'pointer'
                        }}
                    />
                </Paper>
                
                <TicketCard 
                    open={editOpen}
                    onClose={() => setEditOpen(false)}
                    data={data}
                    setData={setData}
                    showToast={showToast}
                    staff_members={staff_members}
                    auth={auth}
                    onOpenClientCard={handleOpenClientCard}
                />
            </Box>
            {clientModalOpen && selectedClient && (
                <ClientCard 
                    open={clientModalOpen}
                    onClose={() => setClientModalOpen(false)}
                    data={selectedClient}
                    setData={(key, val) => setSelectedClient(prev => ({...prev, [key]: val}))}
                    errors={errors}
                    showToast={showToast}
                    onDeleteClient={promptDeleteClient}
                    onDeleteDocument={promptDeleteDocument}
                />
            )}
        </AdminLayout>
    );
}





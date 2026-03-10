import React from "react";
import {Head, useForm} from '@inertiajs/react';
import { Container, Typography, TextField, Button, Box, Paper } from "@mui/material";

export default function DynamicForm({template}) {
    const initialState = {};
    template.content.forEach(block => {
        if(block.type === 'input_field') {
            initialState[block.data.label] = '';
        }
    });

    const {data, setData, post, processing, errors} = useForm(initialState);
    const handleSubmit = (e) => {
        e.preventDefault();

        post(route('application.store', template.slug), {
            forceFormData: true
        });
    };

    return (
        <Container maxWidth="md" sx={{mt: 4, mb: 4}}>
            <Head title={template.title}/>
            <Paper sx={{p:4, borderRadius:2}}>
                <Typography variant="h4" gutterBottom fontWeight="bold">
                    {template.title}
                </Typography>

                <form onSubmit={handleSubmit}>
                {template.content.map((block, index) => {
                    if (block.type === 'text_block') {
                        return (
                            <Box
                                key={index}
                                sx={{mb: 3}}
                                dangerouslySetInnerHTML={{__html:block.data.body}}/>
                        );
                    }

                    if (block.type === "input_field") {
                        const {label, type, is_required} = block.data;
                        return (
                            <Box key={index} sx={{mb:3}}>
                                <TextField
                                    fullWidth
                                    label={label}
                                    type={type === 'file'?'file':type}
                                    required={is_required}
                                    InputLabelProps={type === 'date' || type === 'file'?{shrink:true}:{}}
                                    onChange={e => setData(label, type === 'file'?e.target.files[0]:e.target.value)}/>
                            </Box>
                        );
                    }

                    return null;
                })}

                <Button
                    type="submit"
                    variant="contained"
                    size="large"
                    disabled={processing}
                    sx={{mt:2}}>
                    Отправить заявку
                </Button>
                </form>
            </Paper>
        </Container>
    )
}
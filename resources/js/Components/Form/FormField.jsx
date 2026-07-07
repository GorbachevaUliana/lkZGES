import React from 'react';
import InputField        from './fields/InputField';
import SelectField       from './fields/SelectField';
import CheckboxGroupField from './fields/CheckboxGroupField';
import FileUploadField   from './fields/FileUploadField';
import DynamicInputField from './fields/DynamicInputField';
import SectionHeader     from './fields/SectionHeader';
import TextBlock         from './fields/TextBlock';

/**
 * Роутер по типу блока формы.
 * Принимает блок из шаблона и отдаёт нужный компонент.
 */
export default function FormField({ block, index, data, onChange, errors }) {
    const fieldKey = block.data.key || block.data.label;

    switch (block.type) {
        case 'section_header':
            return <SectionHeader block={block} index={index} />;

        case 'text_block':
            return <TextBlock block={block} />;

        case 'input_field':
            return (
                <InputField
                    block={block}
                    fieldKey={fieldKey}
                    value={data[fieldKey]}
                    onChange={onChange}
                    error={errors?.[fieldKey]}
                />
            );

        case 'select_field':
            return (
                <SelectField
                    block={block}
                    fieldKey={fieldKey}
                    value={data[fieldKey]}
                    onChange={onChange}
                />
            );

        case 'checkbox_group':
            return (
                <CheckboxGroupField
                    block={block}
                    fieldKey={fieldKey}
                    value={data[fieldKey]}
                    onChange={onChange}
                />
            );

        case 'file_upload':
            return (
                <FileUploadField
                    block={block}
                    fieldKey={fieldKey}
                    value={data[fieldKey]}
                    onChange={(files) => onChange(fieldKey, files)}
                    error={errors?.[fieldKey]}
                />
            );

        case 'dynamic_input':
            return (
                <DynamicInputField
                    block={block}
                    fieldKey={fieldKey}
                    value={data[fieldKey]}
                    onChange={onChange}
                />
            );

        default:
            return null;
    }
}
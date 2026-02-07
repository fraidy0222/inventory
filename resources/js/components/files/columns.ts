import DropdownAction from '@/components/files/DataTableDropDown.vue';
import type { File } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export const columns: ColumnDef<File>[] = [
    {
        accessorKey: 'id',
        header: '#',
        cell: ({ row }) => {
            return row.original.id;
        },
    },
    {
        accessorKey: 'nombre',
        header: 'Nombre',
        cell: ({ row }) => {
            return row.original.nombre;
        },
    },
    {
        accessorKey: 'archivo',
        header: 'Archivo',
        cell: ({ row }) => {
            return row.original.archivo;
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Creado',
        cell: ({ row }) => {
            return new Date(row.original.created_at).toLocaleDateString();
        },
    },

    {
        id: 'actions',
        header: 'Acciones',
        enableHiding: false,
        cell: ({ row }) => {
            const file = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    file,
                }),
            );
        },
    },
];

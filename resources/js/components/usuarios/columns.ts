import DropdownAction from '@/components/usuarios/DataTableDropDown.vue';
import { type User } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: ({ row }) => {
            return row.original.id;
        },
    },
    {
        accessorKey: 'name',
        header: 'Usuario',
        cell: ({ row }) => {
            return row.original.name;
        },
    },
    {
        accessorKey: 'email',
        header: 'Correo',
        cell: ({ row }) => {
            return row.original.email;
        },
    },
    {
        accessorKey: 'email_verified_at',
        header: 'Verificado',
        cell: ({ row }) => {
            return row.original.email_verified_at
                ? new Date(row.original.email_verified_at).toLocaleDateString()
                : 'No verificado';
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
        enableHiding: false,
        cell: ({ row }) => {
            const payment = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    payment,
                }),
            );
        },
    },
];

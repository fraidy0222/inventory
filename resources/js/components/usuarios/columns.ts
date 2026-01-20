import { Badge } from '@/components/ui/badge';
import DropdownAction from '@/components/usuarios/DataTableDropDown.vue';
import { type User } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'id',
        header: '#',
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
    // {
    //     accessorKey: 'email_verified_at',
    //     header: 'Verificado',
    //     cell: ({ row }) => {
    //         return row.original.email_verified_at
    //             ? new Date(row.original.email_verified_at).toLocaleDateString()
    //             : 'No verificado';
    //     },
    // },
    {
        accessorKey: 'created_at',
        header: 'Creado',
        cell: ({ row }) => {
            return new Date(row.original.created_at).toLocaleDateString();
        },
    },
    {
        accessorKey: 'role',
        header: 'Rol',
        cell: ({ row }) => {
            return h(
                'div',
                {
                    class: 'uppercase text-[12px]',
                },
                row.original.role,
            );
        },
    },
    {
        accessorKey: 'is_active',
        header: 'Estado',
        cell: ({ row }) => {
            const activo = row.getValue('is_active') as boolean;
            return h(
                Badge,
                { variant: activo ? 'default' : 'secondary' },
                () => (activo ? 'Activo' : 'Inactivo'),
            );
        },
    },

    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const user = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    user,
                }),
            );
        },
    },
];

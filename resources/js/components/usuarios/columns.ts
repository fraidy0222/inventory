import DropdownAction from '@/components/usuarios/DataTableDropDown.vue';
import { type User } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { CircleCheck, X } from 'lucide-vue-next';
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
                { class: 'text-gray-300 font-semibold uppercase text-xs' },
                row.original.role,
            );
        },
    },
    {
        accessorKey: 'is_active',
        header: 'Estado',
        cell: ({ row }) => {
            const isActive = row.original.is_active;
            return h('div', { class: 'flex items-center gap-2' }, [
                h(isActive ? CircleCheck : X, {
                    class: isActive
                        ? 'text-green-500 w-5 h-5'
                        : 'text-red-500 w-5 h-5',
                }),
                h('span', isActive ? 'Activo' : 'Inactivo'),
            ]);
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

import { type Tienda } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { CircleCheck, X } from 'lucide-vue-next';
import { h } from 'vue';
import DropdownAction from './DataTableDropDown.vue';

export const columns: ColumnDef<Tienda>[] = [
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
        accessorKey: 'created_at',
        header: 'Creado',
        cell: ({ row }) => {
            return new Date(row.original.created_at).toLocaleDateString();
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
                h('span', isActive ? 'Activa' : 'Inactiva'),
            ]);
        },
    },

    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const tienda = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    tienda,
                }),
            );
        },
    },
];

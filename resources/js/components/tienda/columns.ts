import { Badge } from '@/components/ui/badge';
import { type Tienda } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
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
            const activo = row.getValue('is_active') as boolean;
            return h(
                Badge,
                { variant: activo ? 'default' : 'secondary' },
                () => (activo ? 'Activa' : 'Inactiva'),
            );
        },
    },

    {
        id: 'actions',
        header: 'Acciones',
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

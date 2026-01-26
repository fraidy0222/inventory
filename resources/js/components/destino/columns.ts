import DropdownAction from '@/components/destino/DataTableDropDown.vue';
import { type Destino } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export const columns: ColumnDef<Destino>[] = [
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
        id: 'actions',
        header: 'Acciones',
        enableHiding: false,
        cell: ({ row }) => {
            const destino = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    destino,
                }),
            );
        },
    },
];

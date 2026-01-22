import { Badge } from '@/components/ui/badge';
import { TiendaAgrupada } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { ChevronRight } from 'lucide-vue-next';
import { h } from 'vue';

export const columns: ColumnDef<TiendaAgrupada>[] = [
    {
        id: 'expander',
        enableHiding: false,
        cell: ({ row }) => {
            return h(
                'div',
                {
                    class: 'cursor-pointer p-2',
                    onClick: row.getToggleExpandedHandler(),
                },
                h(ChevronRight, {
                    class: [
                        'h-4 w-4 transition-transform duration-200',
                        row.getIsExpanded() ? 'rotate-90' : '',
                    ],
                }),
            );
        },
    },
    {
        accessorKey: 'tienda_nombre',
        header: 'Tienda',
        cell: ({ row }) => {
            const tienda = row.original.tienda_nombre;
            return h('div', { class: 'font-medium' }, tienda);
        },
    },
    {
        accessorKey: 'tienda_is_active',
        header: 'Estado',
        cell: ({ row }) => {
            const activo = row.getValue('tienda_is_active') as boolean;
            return h(
                Badge,
                { variant: activo ? 'default' : 'secondary' },
                () => (activo ? 'Activa' : 'Inactiva'),
            );
        },
    },
];

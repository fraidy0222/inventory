import { Badge } from '@/components/ui/badge';
import { Producto } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DropdownAction from './DataTableDropDown.vue';

export const columns: ColumnDef<Producto>[] = [
    {
        accessorKey: 'nombre',
        header: 'Nombre',
    },
    {
        accessorKey: 'categoria',
        header: 'Categoría',
        cell: ({ row }) => {
            const categoria = row.getValue('categoria') as string | null;
            return (
                categoria ||
                h('span', { class: 'text-muted-foreground' }, 'Sin categoría')
            );
        },
    },
    {
        accessorKey: 'costo_promedio',
        header: 'Costo Promedio',
        cell: ({ row }) => {
            const costo = parseFloat(row.getValue('costo_promedio'));
            return `$${costo.toFixed(2)}`;
        },
    },
    {
        accessorKey: 'precio_venta',
        header: 'Precio Venta',
        cell: ({ row }) => {
            const precio = parseFloat(row.getValue('precio_venta'));
            return `$${precio.toFixed(2)}`;
        },
    },

    {
        accessorKey: 'descripcion',
        header: 'Descripción',
    },

    {
        accessorKey: 'activo',
        header: 'Estado',
        cell: ({ row }) => {
            const activo = row.getValue('activo') as boolean;
            return h(
                Badge,
                { variant: activo ? 'default' : 'secondary' },
                () => (activo ? 'Activo' : 'Inactivo'),
            );
        },
    },
    {
        id: 'actions',
        header: 'Acciones',
        enableHiding: false,
        cell: ({ row }) => {
            const producto = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    producto,
                }),
            );
        },
    },
];

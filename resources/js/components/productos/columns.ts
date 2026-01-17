import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import productos from '@/routes/productos';
import { Producto } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { MoreHorizontal, Pencil, Trash } from 'lucide-vue-next';
import { h } from 'vue';

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
        cell: ({ row }) => {
            const producto = row.original;

            return h(
                DropdownMenu,
                {},
                {
                    default: () => [
                        h(
                            DropdownMenuTrigger,
                            { asChild: true },
                            {
                                default: () =>
                                    h(
                                        Button,
                                        {
                                            variant: 'ghost',
                                            class: 'h-8 w-8 p-0',
                                        },
                                        {
                                            default: () => [
                                                h(
                                                    'span',
                                                    { class: 'sr-only' },
                                                    'Abrir menú',
                                                ),
                                                h(MoreHorizontal, {
                                                    class: 'h-4 w-4',
                                                }),
                                            ],
                                        },
                                    ),
                            },
                        ),
                        h(
                            DropdownMenuContent,
                            { align: 'end' },
                            {
                                default: () => [
                                    h(DropdownMenuLabel, {}, () => 'Acciones'),
                                    h(
                                        DropdownMenuItem,
                                        { asChild: true },
                                        {
                                            default: () =>
                                                h(
                                                    Link,
                                                    {
                                                        href: productos.edit(
                                                            producto.id,
                                                        ),
                                                        class: 'flex items-center gap-2',
                                                    },
                                                    {
                                                        default: () => [
                                                            h(Pencil, {
                                                                class: 'h-4 w-4',
                                                            }),
                                                            'Editar',
                                                        ],
                                                    },
                                                ),
                                        },
                                    ),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () => {
                                                if (
                                                    confirm(
                                                        '¿Está seguro de eliminar este producto?',
                                                    )
                                                ) {
                                                    router.delete(
                                                        productos.destroy(
                                                            producto.id,
                                                        ),
                                                    );
                                                }
                                            },
                                            class: 'flex items-center gap-2 text-destructive',
                                        },
                                        {
                                            default: () => [
                                                h(Trash, { class: 'h-4 w-4' }),
                                                'Eliminar',
                                            ],
                                        },
                                    ),
                                ],
                            },
                        ),
                    ],
                },
            );
        },
    },
];

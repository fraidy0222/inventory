<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef } from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    useVueTable,
} from '@tanstack/vue-table';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
}>();

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    getRowCanExpand: () => true,
});
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow
                    v-for="headerGroup in table.getHeaderGroups()"
                    :key="headerGroup.id"
                >
                    <TableHead
                        v-for="header in headerGroup.headers"
                        :key="header.id"
                    >
                        <FlexRender
                            v-if="!header.isPlaceholder"
                            :render="header.column.columnDef.header"
                            :props="header.getContext()"
                        />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <template
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                    >
                        <TableRow
                            :data-state="
                                row.getIsSelected() ? 'selected' : undefined
                            "
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="row.getIsExpanded()">
                            <TableCell :colspan="row.getVisibleCells().length">
                                <div class="rounded-md bg-muted/50 p-4">
                                    <h4 class="mb-2 font-semibold">
                                        Productos en Inventario
                                    </h4>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Producto</TableHead>
                                                <TableHead>Cantidad</TableHead>
                                                <TableHead
                                                    >Cantidad Mínima</TableHead
                                                >
                                                <TableHead
                                                    >Cantidad Máxima</TableHead
                                                >
                                                <TableHead
                                                    >Última
                                                    Actualización</TableHead
                                                >
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow
                                                v-for="producto in row.original
                                                    .productos"
                                                :key="producto.id"
                                            >
                                                <TableCell>{{
                                                    producto.nombre
                                                }}</TableCell>
                                                <TableCell>{{
                                                    producto.cantidad
                                                }}</TableCell>
                                                <TableCell>{{
                                                    producto.cantidad_minima
                                                }}</TableCell>
                                                <TableCell>{{
                                                    producto.cantidad_maxima
                                                }}</TableCell>
                                                <TableCell>{{
                                                    new Date(
                                                        producto.ultima_actualizacion,
                                                    ).toLocaleDateString()
                                                }}</TableCell>
                                            </TableRow>
                                            <TableRow
                                                v-if="
                                                    !row.original.productos
                                                        ?.length
                                                "
                                            >
                                                <TableCell
                                                    colspan="5"
                                                    class="text-center text-muted-foreground"
                                                >
                                                    Sin productos en esta tienda
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell
                            :colspan="columns.length"
                            class="h-24 text-center"
                        >
                            No hay resultados.
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>

<script setup lang="ts">
import { TiendaAgrupada } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    useVueTable,
} from '@tanstack/vue-table';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import inventarioTienda from '@/routes/inventarioTienda';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    columns: ColumnDef<TiendaAgrupada>[];
    data: TiendaAgrupada[];
}>();

const producto = ref({
    id: 0,
    nombre: '',
});
const deleteDialog = ref(false);

const showDialog = (id: number, nombre: string) => {
    producto.value.id = id;
    producto.value.nombre = nombre;
    deleteDialog.value = !deleteDialog.value;
};

const destroy = () => {
    router.delete(inventarioTienda.destroy(producto.value.id).url, {
        onSuccess: () => {
            toast.success('Producto de la tienda eliminado exitosamente');
        },
        onError: () => {
            toast.error('Error al eliminar el producto de la tienda');
        },
    });
};

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
                                                <TableHead>Acciones</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow
                                                v-for="producto in row.original
                                                    .productos"
                                                :key="producto.id"
                                            >
                                                <TableCell>{{
                                                    producto.producto_nombre
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
                                                    producto.ultima_actualizacion
                                                }}</TableCell>
                                                <TableCell>
                                                    <Button
                                                        size="icon-sm"
                                                        variant="ghost"
                                                        as-child
                                                    >
                                                        <Link
                                                            :href="
                                                                inventarioTienda.edit(
                                                                    producto.id,
                                                                )
                                                            "
                                                        >
                                                            <Pencil />
                                                        </Link>
                                                    </Button>
                                                    <Button
                                                        size="icon-sm"
                                                        variant="ghost"
                                                        @click="
                                                            showDialog(
                                                                producto.id,
                                                                producto.producto_nombre,
                                                            )
                                                        "
                                                    >
                                                        <Trash />
                                                    </Button>
                                                </TableCell>
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

        <AlertDialog v-model:open="deleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle
                        >Estás seguro de eliminar producto
                        {{ producto.nombre }} ?</AlertDialogTitle
                    >
                    <AlertDialogDescription>
                        Esta acción no puede ser deshecha. Esta acción eliminará
                        permanentemente el producto y eliminará sus datos de
                        nuestros servidores.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction
                        @click="destroy()"
                        class="bg-red-500/10 text-red-500 hover:bg-red-500/20"
                        >Eliminar</AlertDialogAction
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>

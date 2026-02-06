<script setup lang="ts">
import { TiendaMovimientos } from '@/types';
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
import movimientosRoute from '@/routes/movimientos';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    columns: ColumnDef<TiendaMovimientos>[];
    data: TiendaMovimientos[];
}>();

const movimiento = ref({
    id: 0,
    nombre: '',
});
const deleteDialog = ref(false);

const showDialog = (id: number, nombre: string) => {
    movimiento.value.id = id;
    movimiento.value.nombre = nombre;
    deleteDialog.value = !deleteDialog.value;
};

const destroy = () => {
    router.delete(movimientosRoute.destroy(movimiento.value.id).url, {
        onSuccess: () => {
            toast.success('Movimiento eliminado exitosamente');
        },
        onError: () => {
            toast.error('Error al eliminar el movimiento');
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
                                        Registros de Movimientos
                                    </h4>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Producto</TableHead>
                                                <TableHead>Entradas</TableHead>
                                                <TableHead>Salidas</TableHead>
                                                <TableHead>Traslados</TableHead>
                                                <TableHead
                                                    >Venta Diaria</TableHead
                                                >
                                                <TableHead
                                                    >Destino / Origen</TableHead
                                                >
                                                <TableHead>Usuario</TableHead>
                                                <TableHead
                                                    >Inventario
                                                    Actual</TableHead
                                                >
                                                <TableHead>Acciones</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow
                                                v-for="registro in row.original
                                                    .registros"
                                                :key="registro.id"
                                            >
                                                <TableCell>{{
                                                    registro.producto_nombre
                                                }}</TableCell>
                                                <TableCell>{{
                                                    registro.entradas
                                                }}</TableCell>
                                                <TableCell>{{
                                                    registro.salidas
                                                }}</TableCell>
                                                <TableCell>{{
                                                    registro.traslados
                                                }}</TableCell>
                                                <TableCell>{{
                                                    registro.venta_diaria
                                                }}</TableCell>
                                                <TableCell>
                                                    <span
                                                        v-if="
                                                            registro.destino_nombre
                                                        "
                                                        >{{
                                                            registro.destino_nombre
                                                        }}</span
                                                    >
                                                    <span
                                                        v-else-if="
                                                            registro.tienda_relacionada_nombre
                                                        "
                                                        class="text-blue-600"
                                                    >
                                                        <span
                                                            v-if="
                                                                registro.entradas >
                                                                0
                                                            "
                                                            >De:
                                                            {{
                                                                registro.tienda_relacionada_nombre
                                                            }}</span
                                                        >
                                                        <span
                                                            v-else-if="
                                                                registro.traslados >
                                                                0
                                                            "
                                                            >Para:
                                                            {{
                                                                registro.tienda_relacionada_nombre
                                                            }}</span
                                                        >
                                                        <span v-else>{{
                                                            registro.tienda_relacionada_nombre
                                                        }}</span>
                                                    </span>
                                                    <span v-else>-</span>
                                                </TableCell>
                                                <TableCell>{{
                                                    registro.usuario_nombre
                                                }}</TableCell>
                                                <TableCell>{{
                                                    registro.inventario_actual
                                                }}</TableCell>
                                                <TableCell>
                                                    <Button
                                                        size="icon-sm"
                                                        variant="ghost"
                                                        as-child
                                                    >
                                                        <Link
                                                            :href="
                                                                movimientosRoute.edit(
                                                                    registro.id,
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
                                                                registro.id,
                                                                registro.producto_nombre,
                                                            )
                                                        "
                                                    >
                                                        <Trash />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                            <TableRow
                                                v-if="
                                                    !row.original.registros
                                                        ?.length
                                                "
                                            >
                                                <TableCell
                                                    colspan="9"
                                                    class="text-center text-muted-foreground"
                                                >
                                                    Sin movimientos en esta
                                                    tienda
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
                        >Estás seguro de eliminar movimiento
                        {{ movimiento.nombre }} ?</AlertDialogTitle
                    >
                    <AlertDialogDescription>
                        Esta acción no puede ser deshecha. Esta acción eliminará
                        permanentemente el movimiento y eliminará sus datos de
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

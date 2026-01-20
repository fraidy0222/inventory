<script setup lang="ts">
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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import productos from '@/routes/productos';
import { router } from '@inertiajs/vue3';
import { MoreHorizontal, Pencil, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    producto: {
        id: number;
        nombre: string;
    };
}>();

const deleteDialog = ref(false);

const showDialog = () => {
    deleteDialog.value = !deleteDialog.value;
};

const destroy = (id: number) => {
    router.delete(productos.destroy(id).url, {
        onSuccess: () => {
            toast.success('Producto eliminado exitosamente');
        },
        onError: () => {
            toast.error('Error al eliminar el producto');
        },
    });
};

const edit = () => {
    return router.visit(productos.edit(props.producto.id).url);
};
</script>

<template>
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
                    @click="destroy(producto.id)"
                    class="bg-red-500/10 text-red-500 hover:bg-red-500/20"
                    >Eliminar</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0">
                <span class="sr-only">Abrir Menú</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Acciones</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="edit">
                <Pencil class="h-3 w-3" />
                <!-- <Link :href="productos.edit(producto.id).url">Editar</Link> -->
                Editar
            </DropdownMenuItem>
            <DropdownMenuItem @click="showDialog">
                <Trash class="h-3 w-3" />
                Eliminar
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

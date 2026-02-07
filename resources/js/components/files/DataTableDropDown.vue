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
import archivosRoutes from '@/routes/archivos';
import { router } from '@inertiajs/vue3';
import { Download, MoreHorizontal, Pencil, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

import type { File } from '@/types';

const props = defineProps<{
    file: File;
}>();

const deleteDialog = ref(false);

const showDialog = () => {
    deleteDialog.value = !deleteDialog.value;
};

const destroy = (id: number) => {
    router.delete(archivosRoutes.destroy(id).url, {
        onSuccess: () => {
            toast.success('Archivo eliminado exitosamente');
        },
        onError: () => {
            toast.error('Error al eliminar el archivo');
        },
    });
};

const edit = () => {
    return router.visit(archivosRoutes.edit(props.file.id).url);
};

const download = () => {
    // Use the archivo field (filename) instead of id for download
    window.location.href = archivosRoutes.download(props.file.archivo).url;
};
</script>

<template>
    <AlertDialog v-model:open="deleteDialog">
        <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle
                    >Estás seguro de eliminar este archivo
                    {{ file.nombre }} ?</AlertDialogTitle
                >
                <AlertDialogDescription>
                    Esta acción no puede ser deshecha. Esta acción eliminará
                    permanentemente el archivo y eliminará sus datos de nuestros
                    servidores.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction
                    @click="destroy(file.id)"
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
                Editar
            </DropdownMenuItem>
            <DropdownMenuItem @click="download">
                <Download class="h-3 w-3" />
                Descargar
            </DropdownMenuItem>
            <DropdownMenuItem @click="showDialog">
                <Trash class="h-3 w-3" />
                Eliminar
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import UsersController from '@/actions/App/Http/Controllers/UsersController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import usuarios from '@/routes/usuarios';
import { Link } from '@inertiajs/vue3';
import { MoreHorizontal, Pencil, Trash } from 'lucide-vue-next';

defineProps<{
    payment: {
        id: string;
    };
}>();
</script>

<template>
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
            <DropdownMenuItem>
                <Pencil class="h-3 w-3" />
                <Link :href="UsersController.edit('payment.id')">Editar</Link>
                <!-- Editar -->
            </DropdownMenuItem>
            <DropdownMenuItem @click="usuarios.destroy(payment.id)">
                <Trash class="h-3 w-3" />
                Eliminar
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

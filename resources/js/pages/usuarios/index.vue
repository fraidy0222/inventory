<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { columns } from '@/components/usuarios/columns';
import DataTable from '@/components/usuarios/data-table.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import usuarios from '@/routes/usuarios';
import { type BreadcrumbItem, User } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

interface Props {
    users: User[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: usuarios.index().url,
    },
];

defineProps<Props>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Usuarios" />
        <div class="container mx-auto px-4 py-6">
            <div class="mb-8 flex items-center justify-between">
                <Heading
                    title="Listado de Usuarios"
                    :has-class="true"
                    class="mb-0 space-y-0"
                />
                <Button as-child>
                    <div>
                        <Plus />
                        <Link :href="usuarios.create()">Añadir Usuario</Link>
                    </div>
                </Button>
            </div>
            <DataTable :columns="columns" :data="users" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import User, { columns } from '@/components/usuarios/columns';
import DataTable from '@/components/usuarios/data-table.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import usuarios from '@/routes/usuarios';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

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

        <div class="container mx-auto px-4 py-10">
            <DataTable :columns="columns" :data="users" />
        </div>
    </AppLayout>
</template>

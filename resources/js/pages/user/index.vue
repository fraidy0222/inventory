<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

interface User {
    id: string;
    name: string;
    email: string;
}

interface Props {
    users: User[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Usuarios" />

        <div class="mx-4 mt-8 rounded-md border py-6">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-25px"> # </TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users" :key="user.id">
                        <TableCell class="font-medium">
                            {{ user.id }}
                        </TableCell>
                        <TableCell class="font-medium">
                            {{ user.name }}
                        </TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import archivosRoutes from '@/routes/archivos';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Archivos',
        href: archivosRoutes.index().url,
    },
    {
        title: 'Crear Archivo',
        href: archivosRoutes.create().url,
    },
];

const form = useForm({
    nombre: '',
    archivo: null,
});

const submit = () => {
    form.post('/archivos', {
        _method: 'put',
        archivo: form.archivo,
        onSuccess: () => {
            toast.success('Archivo creado exitosamente');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Archivo" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Crear Archivo</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-6" @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="nombre">Nombre de Archivo</Label>
                                <Input
                                    id="nombre"
                                    type="text"
                                    v-model="form.nombre"
                                    name="nombre"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="Nombre de Archivo"
                                />
                                <InputError :message="form.errors.nombre" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="archivo">Archivo</Label>
                                <Input
                                    id="archivo"
                                    type="file"
                                    @input="
                                        form.archivo = $event.target.files[0]
                                    "
                                    name="archivo"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="archivo"
                                    placeholder="Archivo"
                                />
                                <InputError :message="form.errors.archivo" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button
                                as-child
                                variant="outline"
                                type="button"
                                class="mt-4"
                                :tabindex="4"
                                data-test="cancel-button"
                            >
                                <Link :href="archivosRoutes.index().url">
                                    Cancelar
                                </Link>
                            </Button>
                            <Button
                                type="submit"
                                class="mt-4"
                                :tabindex="4"
                                :disabled="form.processing"
                                data-test="login-button"
                            >
                                <Spinner v-if="form.processing" />
                                Guardar
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

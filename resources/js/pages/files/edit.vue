<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import archivosRoutes from '@/routes/archivos';
import type { BreadcrumbItem, File } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    file: File;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Archivos',
        href: archivosRoutes.index().url,
    },
    {
        title: 'Editar Archivo',
        href: archivosRoutes.edit(props.file.id).url,
    },
];

const form = useForm({
    nombre: props.file.nombre,
    archivo: null as File | null,
    _method: 'put',
});

const submit = () => {
    form.post(archivosRoutes.update(props.file.id).url, {
        onSuccess: () => {
            toast.success('Archivo actualizado exitosamente');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Archivo" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Editar Archivo</CardTitle>
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
                                <Label for="archivo">Archivo (Opcional)</Label>
                                <Input
                                    id="archivo"
                                    type="file"
                                    @input="
                                        form.archivo = $event.target.files[0]
                                    "
                                    name="archivo"
                                    :tabindex="2"
                                    autocomplete="archivo"
                                    placeholder="Archivo"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Dejar en blanco para mantener el archivo
                                    actual: {{ file.archivo }}
                                </p>
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
                                data-test="save-button"
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

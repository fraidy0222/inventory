<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import destinos from '@/routes/destinos';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Destinos',
        href: destinos.index().url,
    },
    {
        title: 'Crear Destino',
        href: destinos.create().url,
    },
];

const form = useForm({
    nombre: '',
});

const submit = () => {
    form.post(destinos.store().url, {
        onSuccess: () => {
            toast.success('Destino creado exitosamente');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Destino" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Crear Destino</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-6" @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="nombre">Nombre de Destino</Label>
                                <Input
                                    id="nombre"
                                    type="text"
                                    v-model="form.nombre"
                                    name="nombre"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="nombre"
                                    placeholder="Nombre de Destino"
                                />
                                <InputError :message="form.errors.nombre" />
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
                                <Link :href="destinos.index().url">
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

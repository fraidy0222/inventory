<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import inventarioTienda from '@/routes/inventarioTienda';
import type { BreadcrumbItem, Producto, Tienda } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    tiendas: Tienda[];
    productos: Producto[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventario Tienda',
        href: inventarioTienda.index().url,
    },
    {
        title: 'Crear Inventario Tienda',
        href: inventarioTienda.create().url,
    },
];

const form = useForm({
    producto_id: '',
    tienda_id: '',
    cantidad: 0,
    cantidad_minima: 0,
    cantidad_maxima: 0,
});

const submit = () => {
    form.post('/inventarioTienda', {
        onSuccess: () => {
            toast.success('Inventario Tienda creado exitosamente');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Inventario Tienda" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Crear Inventario Tienda</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-6" @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="tienda">Tienda</Label>

                                <Select
                                    name="tienda_id"
                                    v-model="form.tienda_id"
                                    :tabindex="1"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Seleccione una tienda"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            :value="tienda.id"
                                            v-for="tienda in tiendas"
                                            :key="tienda.id"
                                        >
                                            {{ tienda.nombre }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.tienda_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="producto">Producto</Label>

                                <Select
                                    name="producto_id"
                                    v-model="form.producto_id"
                                    :tabindex="2"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Seleccione un producto"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            :value="producto.id"
                                            v-for="producto in productos"
                                            :key="producto.id"
                                        >
                                            {{ producto.nombre }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.tienda_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="cantidad">Cantidad</Label>
                                <Input
                                    id="cantidad"
                                    type="number"
                                    v-model="form.cantidad"
                                    autofocus
                                    :tabindex="3"
                                    name="cantidad"
                                    step="0.01"
                                    placeholder="Cantidad del producto"
                                />
                                <InputError :message="form.errors.cantidad" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="cantidad_minima"
                                    >Cantidad Mínima</Label
                                >
                                <Input
                                    id="cantidad_minima"
                                    type="number"
                                    v-model="form.cantidad_minima"
                                    autofocus
                                    :tabindex="4"
                                    name="cantidad_minima"
                                    step="0.01"
                                    placeholder="Cantidad mínima del producto"
                                />
                                <InputError
                                    :message="form.errors.cantidad_minima"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="cantidad_maxima"
                                    >Cantidad Máxima</Label
                                >
                                <Input
                                    id="cantidad_maxima"
                                    type="number"
                                    v-model="form.cantidad_maxima"
                                    autofocus
                                    :tabindex="5"
                                    name="cantidad_maxima"
                                    step="0.01"
                                    placeholder="Cantidad máxima del producto"
                                />
                                <InputError
                                    :message="form.errors.cantidad_maxima"
                                />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button
                                as-child
                                variant="outline"
                                type="button"
                                class="mt-4"
                                :tabindex="6"
                                data-test="cancel-button"
                            >
                                <Link :href="inventarioTienda.index().url">
                                    Cancelar
                                </Link>
                            </Button>
                            <Button
                                type="submit"
                                class="mt-4"
                                :tabindex="7"
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

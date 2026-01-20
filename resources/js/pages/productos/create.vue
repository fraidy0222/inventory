<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import productos from '@/routes/productos';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: productos.index().url,
    },
    {
        title: 'Crear Producto',
        href: productos.create().url,
    },
];

const isActive = ref(true);

const form = useForm({
    nombre: '',
    descripcion: '',
    categoria: '',
    costo_promedio: '',
    precio_venta: '',
    activo: isActive.value,
});

// Sincronizar valores
watch(isActive, (newValue) => {
    form.activo = newValue;
});

// O manejar el cambio manualmente
const handleCheckboxChange = (checked: boolean) => {
    form.activo = checked;
    isActive.value = checked;
};

const submit = () => {
    form.post('/productos', {
        onSuccess: () => {
            toast.success('Producto creado exitosamente');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Producto" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Crear Producto</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-6" @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="nombre">Nombre</Label>
                                <Input
                                    id="nombre"
                                    type="string"
                                    v-model="form.nombre"
                                    name="nombre"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="nombre"
                                    placeholder="Nombre del producto"
                                />
                                <InputError :message="form.errors.nombre" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="categoria">Categoría</Label>
                                <Input
                                    id="categoria"
                                    type="text"
                                    v-model="form.categoria"
                                    name="categoria"
                                    :tabindex="2"
                                    autocomplete="categoria"
                                    placeholder="Categoría del producto"
                                    maxlength="100"
                                />
                                <InputError :message="form.errors.categoria" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="nombre">Costo Promedio</Label>
                                <Input
                                    id="costo_promedio"
                                    type="number"
                                    v-model="form.costo_promedio"
                                    autofocus
                                    :tabindex="2"
                                    name="costo_promedio"
                                    step="0.01"
                                    placeholder="Costo promedio del producto"
                                />
                                <InputError
                                    :message="form.errors.costo_promedio"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="nombre">Precio de Venta</Label>
                                <Input
                                    id="precio_venta"
                                    type="number"
                                    v-model="form.precio_venta"
                                    autofocus
                                    :tabindex="2"
                                    name="precio_venta"
                                    step="0.01"
                                    placeholder="Precio de venta del producto"
                                />
                                <InputError
                                    :message="form.errors.precio_venta"
                                />
                            </div>

                            <div class="grid gap-2">
                                <div class="flex items-center gap-3">
                                    <Label
                                        class="flex w-full items-start gap-3 rounded-lg border p-3 hover:bg-accent/50 has-[[aria-checked=true]]:border-blue-600 has-[[aria-checked=true]]:bg-blue-50 dark:has-[[aria-checked=true]]:border-blue-900 dark:has-[[aria-checked=true]]:bg-blue-950"
                                    >
                                        <Checkbox
                                            id="toggle-2"
                                            name="activo"
                                            value="1"
                                            v-model="form.activo"
                                            :tabindex="2"
                                            @update:checked="
                                                handleCheckboxChange
                                            "
                                            class="data-[state=checked]:border-blue-600 data-[state=checked]:bg-blue-600 data-[state=checked]:text-white dark:data-[state=checked]:border-blue-700 dark:data-[state=checked]:bg-blue-700"
                                        />
                                        <div class="grid gap-1.5 font-normal">
                                            <p
                                                class="text-sm leading-none font-medium"
                                            >
                                                {{
                                                    form.activo
                                                        ? 'Activa'
                                                        : 'Inactiva'
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    form.activo
                                                        ? 'El producto está activo en el sistema'
                                                        : 'El producto está inactivo en el sistema'
                                                }}
                                            </p>
                                        </div>
                                    </Label>
                                </div>
                                <InputError :message="form.errors.activo" />
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="nombre">Descripción</Label>
                            <Textarea
                                id="nombre"
                                type="nombre"
                                v-model="form.descripcion"
                                name="nombre"
                                autofocus
                                :tabindex="1"
                                autocomplete="descripcion"
                                placeholder="Descripción del producto"
                            />
                            <InputError :message="form.errors.descripcion" />
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
                                <Link :href="productos.index().url">
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

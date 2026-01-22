<script setup lang="ts">
import ProductoController from '@/actions/App/Http/Controllers/ProductoController';
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
import axios from 'axios';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    tiendas: Tienda[];
    productos: Producto[];
    productosEnTiendas?: Record<string, number[]>;
}>();

// Estado reactivo
const productosFiltrados = ref<Producto[]>(props.productos);
const cargandoProductos = ref(false);

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

// Función para cargar productos no asignados
const cargarProductosNoAsignados = async (tiendaId: string) => {
    if (!tiendaId) {
        // Si no hay tienda seleccionada, mostrar todos los productos
        productosFiltrados.value = props.productos;
        form.producto_id = '';
        return;
    }

    try {
        const response = await axios.get(
            `/api/productos-no-asignados/${tiendaId}`,
        );
        productosFiltrados.value = response.data;

        // Resetear el producto seleccionado si ya no está disponible
        if (
            form.producto_id &&
            !productosFiltrados.value.some((p) => p.id == form.producto_id)
        ) {
            form.producto_id = '';
            toast.warning('El producto seleccionado ya está en esta tienda');
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
        productosFiltrados.value = [];
    }
};

// Watcher para cuando cambia la tienda
watch(
    () => form.tienda_id,
    (nuevaTiendaId) => {
        cargarProductosNoAsignados(nuevaTiendaId);
    },
);

const submit = () => {
    form.post('/inventarioTienda', {
        onSuccess: () => {
            toast.success('Inventario Tienda creado exitosamente');
        },
        onError: (errors) => {
            if (errors.producto_id?.includes('ya está registrado')) {
                toast.error('Este producto ya está en la tienda seleccionada');
            }
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
                                    :disabled="!form.tienda_id"
                                    required
                                >
                                    <SelectTrigger class="w-full">
                                        <div class="flex items-center gap-2">
                                            <Spinner
                                                v-if="cargandoProductos"
                                                class="h-4 w-4"
                                            />
                                            <SelectValue
                                                :placeholder="
                                                    form.tienda_id
                                                        ? cargandoProductos
                                                            ? 'Cargando productos...'
                                                            : 'Seleccione un producto'
                                                        : 'Primero seleccione una tienda'
                                                "
                                            />
                                        </div>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <template
                                            v-if="productosFiltrados.length > 0"
                                        >
                                            <SelectItem
                                                :value="producto.id.toString()"
                                                v-for="producto in productosFiltrados"
                                                :key="producto.id"
                                            >
                                                {{ producto.nombre }}
                                            </SelectItem>
                                        </template>
                                        <template v-else>
                                            <div
                                                class="px-3 py-2 text-sm text-gray-500"
                                            >
                                                {{
                                                    form.tienda_id &&
                                                    !cargandoProductos
                                                        ? 'No hay productos disponibles para esta tienda'
                                                        : 'Seleccione una tienda primero'
                                                }}
                                            </div>
                                        </template>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="form.errors.producto_id"
                                />

                                <div
                                    v-if="
                                        form.tienda_id &&
                                        productosFiltrados.length === 0 &&
                                        !cargandoProductos
                                    "
                                    class="mt-1 text-sm text-amber-600"
                                >
                                    Todos los productos ya están asignados a
                                    esta tienda.
                                    <Link
                                        :href="ProductoController.create()"
                                        class="ml-1 text-blue-600 hover:underline"
                                    >
                                        Crear nuevo producto
                                    </Link>
                                </div>
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

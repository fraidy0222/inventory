<script setup lang="ts">
import ProductoController from '@/actions/App/Http/Controllers/ProductoController';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import movimientos from '@/routes/movimientos';
import type { BreadcrumbItem, Destino, Producto, Tienda, User } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { AlertCircleIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    tiendas: Tienda[];
    productos: Producto[];
    productosEnTiendas?: Record<string, number[]>;
    destinos: Destino[];
    usuarios: User[];
    auth: {
        user: User;
    };
}>();

// Estado reactivo
const productosFiltrados = ref<Producto[]>(props.productos);
const cargandoProductos = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Movimientos',
        href: movimientos.index().url,
    },
    {
        title: 'Crear Movimiento',
        href: movimientos.create().url,
    },
];

const form = useForm({
    entradas: 0,
    salidas: 0,
    traslados: 0,
    venta_diaria: 0,
    producto_id: '',
    destino_id: '',
    usuario_id: props.auth.user.id.toString(), // Usuario autenticado
    tienda_id: '',
    inventario_tienda_id: '',
});

// Función para cargar productos que están en el inventario de la tienda
const cargarProductosNoAsignados = async (tiendaId: string) => {
    if (!tiendaId) {
        // Si no hay tienda seleccionada, limpiar productos
        productosFiltrados.value = [];
        form.producto_id = '';
        return;
    }

    try {
        cargandoProductos.value = true;
        const response = await axios.get(
            `/api/movimientos/productos-en-tienda/${tiendaId}`,
        );
        productosFiltrados.value = response.data;

        // Resetear el producto seleccionado si ya no está disponible
        if (
            form.producto_id &&
            !productosFiltrados.value.some(
                (p) => p.id.toString() === form.producto_id,
            )
        ) {
            form.producto_id = '';
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
        productosFiltrados.value = [];
        toast.error('Error al cargar los productos de la tienda');
    } finally {
        cargandoProductos.value = false;
    }
};

// Watcher para cuando cambia la tienda
watch(
    () => form.tienda_id,
    (nuevaTiendaId) => {
        cargarProductosNoAsignados(nuevaTiendaId);
        // Resetear inventario_tienda_id cuando cambia la tienda
        form.inventario_tienda_id = '';
    },
);

// Watcher para cuando cambian tienda y producto - obtener inventario_tienda_id
watch(
    [() => form.tienda_id, () => form.producto_id],
    async ([tiendaId, productoId]) => {
        if (tiendaId && productoId) {
            try {
                const response = await axios.get(
                    `/api/movimientos/inventario/${tiendaId}/${productoId}`,
                );
                form.inventario_tienda_id = response.data.id.toString();
            } catch (error) {
                console.error('Error al obtener inventario:', error);
                form.inventario_tienda_id = '';
                toast.error(
                    'No se encontró inventario para esta combinación de tienda y producto',
                );
            }
        } else {
            form.inventario_tienda_id = '';
        }
    },
);

const submit = () => {
    form.post('/movimientos', {
        onSuccess: () => {
            toast.success('El movimiento ha sido añadido exitosamente');
        },
        onError: (errors) => {
            if (errors.producto_id?.includes('ya está registrado')) {
                toast.error(
                    'Este movimiento ya está en la tienda seleccionada',
                );
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Movimiento" />
        <div class="container mx-auto px-4 py-10">
            <Alert
                variant="destructive"
                class="mb-4"
                v-if="form.errors.inventario_tienda_id"
            >
                <AlertCircleIcon />
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>
                    <p>
                        {{ form.errors.inventario_tienda_id }}
                    </p>
                </AlertDescription>
            </Alert>

            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Crear Movimiento</CardTitle>
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
                                <Label for="entradas">Entradas</Label>
                                <Input
                                    id="entradas"
                                    type="number"
                                    v-model="form.entradas"
                                    autofocus
                                    :tabindex="3"
                                    name="entradas"
                                    step="0.01"
                                    placeholder="Cantidad de entradas"
                                />
                                <InputError :message="form.errors.entradas" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="salidas">Salidas</Label>
                                <Input
                                    id="salidas"
                                    type="number"
                                    v-model="form.salidas"
                                    autofocus
                                    :tabindex="4"
                                    name="salidas"
                                    step="0.01"
                                    placeholder="Cantidad de salidas"
                                />
                                <InputError :message="form.errors.salidas" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="traslados">Traslados</Label>
                                <Input
                                    id="traslados"
                                    type="number"
                                    v-model="form.traslados"
                                    autofocus
                                    :tabindex="5"
                                    name="traslados"
                                    step="0.01"
                                    placeholder="Cantidad de traslados"
                                />
                                <InputError :message="form.errors.traslados" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="venta_diaria">Venta Diaria</Label>
                                <Input
                                    id="venta_diaria"
                                    type="number"
                                    v-model="form.venta_diaria"
                                    autofocus
                                    :tabindex="5"
                                    name="venta_diaria"
                                    step="0.01"
                                    placeholder="Cantidad de venta diaria"
                                />
                                <InputError
                                    :message="form.errors.venta_diaria"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="destino_id">Destino</Label>

                                <Select
                                    name="destino_id"
                                    v-model="form.destino_id"
                                    :tabindex="1"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Seleccione un destino"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            :value="destino.id"
                                            v-for="destino in destinos"
                                            :key="destino.id"
                                        >
                                            {{ destino.nombre }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.destino_id" />
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
                                <Link :href="movimientos.index().url">
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

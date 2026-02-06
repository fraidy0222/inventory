<script setup lang="ts">
import ProductoController from '@/actions/App/Http/Controllers/ProductoController';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import movimientos from '@/routes/movimientos';
import type { BreadcrumbItem, Destino, Producto, Tienda, User } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { AlertCircleIcon } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
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
    movimiento: any;
}>();

// Estado reactivo
const productosFiltrados = ref<Producto[]>(props.productos);
const cargandoProductos = ref(false);

// Estado para control de inventario y transferencias
const inventarioDisponible = ref<number>(0);
const showTransferModal = ref(false);
const transferOptions = ref<any[]>([]);
const checkingStock = ref(false);
const transferring = ref(false);
const cantidadTransferir = ref<number>(0);
const tiendaOrigenSeleccionada = ref<string>('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Movimientos',
        href: movimientos.index().url,
    },
    {
        title: 'Editar Movimiento',
        href: '#',
    },
];

const form = useForm({
    entradas: props.movimiento.entradas,
    salidas: props.movimiento.salidas,
    traslados: props.movimiento.traslados,
    venta_diaria: props.movimiento.venta_diaria,
    producto_id: props.movimiento.producto_id.toString(),
    destino_id: props.movimiento.destino_id,
    usuario_id: props.auth.user.id.toString(), // Usuario autenticado
    tienda_id: props.movimiento.tienda_id.toString(),
    inventario_tienda_id: props.movimiento.inventario_tienda_id.toString(),
    tienda_relacionada_id: props.movimiento.tienda_relacionada_id
        ? props.movimiento.tienda_relacionada_id.toString()
        : '',
});

// Transferencia a otra tienda - Inicializar basado en si hay tienda relacionada
const esTransferenciaTienda = ref(!!props.movimiento.tienda_relacionada_id);

const toggleTransferMode = (checked: boolean) => {
    esTransferenciaTienda.value = checked;
    if (checked) {
        form.destino_id = '';
    } else {
        form.tienda_relacionada_id = '';
    }
};

const tiendasDisponiblesTransferencia = computed(() => {
    return props.tiendas.filter((t) => t.id.toString() !== form.tienda_id);
});

// Calcular déficit
const deficit = computed(() => {
    const traslados = parseFloat(form.traslados?.toString() || '0');
    const venta_diaria = parseFloat(form.venta_diaria?.toString() || '0');

    // Si estamos editando el mismo inventario, sumamos lo que ya consumía este movimiento
    let disponible = inventarioDisponible.value;
    if (
        form.inventario_tienda_id ===
        props.movimiento.inventario_tienda_id.toString()
    ) {
        // Devolver al inventario lo que este movimiento ya estaba consumiendo
        const usoOriginal =
            props.movimiento.traslados + props.movimiento.venta_diaria;
        disponible += usoOriginal;
    }

    const totalRequerido = traslados + venta_diaria;
    return totalRequerido > disponible ? totalRequerido - disponible : 0;
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
        // Nota: Al editar, el producto actual DEBERÍA estar en la lista.
        // Pero verificamos por seguridad si cambiamos de tienda.
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

// Cargar productos al montar el componente si hay tienda seleccionada
// import { onMounted } from 'vue'; // Already imported above

onMounted(() => {
    if (form.tienda_id) {
        cargarProductosNoAsignados(form.tienda_id.toString());
    }
    // Set initial inventory from props if available
    if (props.movimiento.inventario_tienda) {
        inventarioDisponible.value =
            props.movimiento.inventario_tienda.cantidad;
    }
});

// Watcher para cuando cambia la tienda
watch(
    () => form.tienda_id,
    (nuevaTiendaId, oldVal) => {
        // Evitar recargar si es el valor inicial (controlado por onMounted) o si no cambió realmente
        if (nuevaTiendaId !== oldVal && oldVal !== undefined) {
            cargarProductosNoAsignados(
                nuevaTiendaId ? nuevaTiendaId.toString() : '',
            );
            // Resetear inventario_tienda_id cuando cambia la tienda
            form.inventario_tienda_id = '';
        }
    },
);

// Watcher para cuando cambian tienda y producto - obtener inventario_tienda_id
watch(
    [() => form.tienda_id, () => form.producto_id],
    async ([tiendaId, productoId]) => {
        // Solo buscar si ambos valores existen y han cambiado o si inventario_tienda_id está vacío
        // (Al cargar, inventario_tienda_id ya tiene valor, así que evitamos la llamada redundante inicial)
        if (tiendaId && productoId) {
            // Si es el inicial
            if (
                form.inventario_tienda_id &&
                tiendaId == props.movimiento.inventario_tienda.tienda_id &&
                productoId == props.movimiento.producto_id.toString()
            ) {
                // Inicializar cantidad con lo que hay + lo que consumió
                // Pero 'inventario_actual' en props.movimiento calculaba (cantidad - uso).
                // Mejor usamos inventario_tienda.cantidad directo del prop si está cargado
                // El prop movimiento trae 'inventario_tienda' relation.
                if (props.movimiento.inventario_tienda) {
                    inventarioDisponible.value =
                        props.movimiento.inventario_tienda.cantidad;
                }
                return;
            }

            try {
                const response = await axios.get(
                    `/api/movimientos/inventario/${tiendaId}/${productoId}`,
                );
                form.inventario_tienda_id = response.data.id.toString();
                inventarioDisponible.value = response.data.cantidad;
            } catch (error) {
                console.error('Error al obtener inventario:', error);
                form.inventario_tienda_id = '';
                inventarioDisponible.value = 0;
                // toast.error... (ya manejado)
            }
        } else {
            form.inventario_tienda_id = '';
            inventarioDisponible.value = 0;
        }
    },
);

const checkStock = async () => {
    if (!form.producto_id) return;

    try {
        checkingStock.value = true;
        const response = await axios.get(
            `/api/movimientos/check-stock/${form.producto_id}`,
            {
                params: { exclude_tienda_id: form.tienda_id },
            },
        );
        transferOptions.value = response.data;
        showTransferModal.value = true;
    } catch (error) {
        toast.error('Error al verificar stock en otras tiendas');
    } finally {
        checkingStock.value = false;
    }
};

const iniciarTransferencia = (
    tiendaOrigenId: string,
    stockDisponible: number,
) => {
    tiendaOrigenSeleccionada.value = tiendaOrigenId;
    // Por defecto sugerimos el déficit, o todo el stock si no alcanza
    const falta = deficit.value;
    cantidadTransferir.value =
        stockDisponible >= falta ? falta : stockDisponible;
};

const confirmarTransferencia = async () => {
    if (!tiendaOrigenSeleccionada.value || cantidadTransferir.value <= 0)
        return;

    try {
        transferring.value = true;
        await axios.post('/api/movimientos/transfer-and-use', {
            source_tienda_id: tiendaOrigenSeleccionada.value,
            target_tienda_id: form.tienda_id,
            producto_id: form.producto_id,
            cantidad_transferir: cantidadTransferir.value,
            movimiento_data: form.data(),
        });

        toast.success('Transferencia realizada y movimiento registrado');
        // Redirigir o recargar
        window.location.href = movimientos.index().url;
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            if (errors.cantidad_transferir) {
                toast.error(errors.cantidad_transferir[0]);
            } else if (errors.movimiento_error) {
                toast.error(errors.movimiento_error[0]);
            } else {
                toast.error(
                    'Error de validación al realizar la transferencia.',
                );
            }
        } else {
            toast.error('Error al realizar la transferencia');
        }
    } finally {
        transferring.value = false;
        showTransferModal.value = false;
    }
};

const submit = () => {
    form.put(movimientos.update(props.movimiento.id).url, {
        onSuccess: () => {
            toast.success('El movimiento ha sido actualizado exitosamente');
        },
        onError: (errors) => {
            // Manejo genérico de errores si es necesario
            if (errors.inventario_tienda_id) {
                toast.error(errors.inventario_tienda_id);
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Movimiento" />
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
                    <CardTitle>Editar Movimiento</CardTitle>
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
                                    @blur="form.entradas = form.entradas || 0"
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
                                    @blur="form.salidas = form.salidas || 0"
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
                                    @blur="form.traslados = form.traslados || 0"
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
                                    @blur="
                                        form.venta_diaria =
                                            form.venta_diaria || 0
                                    "
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

                            <!-- Sección de Destino / Transferencia -->
                            <div class="grid gap-2">
                                <Label>Tipo de Movimiento (Destino)</Label>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="mode-transfer"
                                        v-model="esTransferenciaTienda"
                                        @update:checked="toggleTransferMode"
                                    />
                                    <Label for="mode-transfer">
                                        {{
                                            esTransferenciaTienda
                                                ? 'Transferir a otra Tienda'
                                                : 'Salida a Destino Externo'
                                        }}
                                    </Label>
                                </div>
                            </div>

                            <div
                                class="grid gap-2"
                                v-if="!esTransferenciaTienda"
                            >
                                <Label for="destino_id">Destino</Label>
                                <Select
                                    name="destino_id"
                                    v-model="form.destino_id"
                                    :tabindex="6"
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

                            <div class="grid gap-2" v-else>
                                <Label for="tienda_relacionada_id"
                                    >Tienda Destino</Label
                                >
                                <Select
                                    name="tienda_relacionada_id"
                                    v-model="form.tienda_relacionada_id"
                                    :tabindex="6"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Seleccione tienda destino"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            :value="tienda.id.toString()"
                                            v-for="tienda in tiendasDisponiblesTransferencia"
                                            :key="tienda.id"
                                        >
                                            {{ tienda.nombre }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="form.errors.tienda_relacionada_id"
                                />
                            </div>

                            <!-- Alert and Button for Stock Transfer -->
                            <div class="md:col-span-2" v-if="deficit > 0">
                                <Alert variant="destructive">
                                    <AlertCircleIcon class="h-4 w-4" />
                                    <AlertTitle
                                        >Inventario Insuficiente</AlertTitle
                                    >
                                    <AlertDescription
                                        class="flex flex-col gap-2"
                                    >
                                        <p>
                                            La cantidad solicitada excede el
                                            inventario disponible en esta
                                            tienda. (Faltan: {{ deficit }})
                                        </p>
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            class="mt-2 w-fit"
                                            @click="checkStock"
                                            :disabled="checkingStock"
                                        >
                                            <Spinner
                                                v-if="checkingStock"
                                                class="mr-2 h-4 w-4"
                                            />
                                            Buscar en otras tiendas
                                        </Button>
                                    </AlertDescription>
                                </Alert>
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
        <Dialog v-model:open="showTransferModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Stock en otras tiendas</DialogTitle>
                    <DialogDescription>
                        Seleccione una tienda para transferir el stock faltante.
                    </DialogDescription>
                </DialogHeader>

                <div class="py-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tienda</TableHead>
                                <TableHead>Disponible</TableHead>
                                <TableHead></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="store in transferOptions"
                                :key="store.tienda_id"
                            >
                                <TableCell>{{ store.tienda_nombre }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{
                                        store.cantidad
                                    }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="
                                            iniciarTransferencia(
                                                store.tienda_id,
                                                store.cantidad,
                                            )
                                        "
                                    >
                                        Seleccionar
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="transferOptions.length === 0">
                                <TableCell
                                    colspan="3"
                                    class="text-center text-muted-foreground"
                                >
                                    No se encontró stock en otras tiendas.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div
                        v-if="tiendaOrigenSeleccionada"
                        class="mt-4 rounded-md bg-muted p-4"
                    >
                        <Label>Cantidad a transferir:</Label>
                        <Input
                            type="number"
                            v-model="cantidadTransferir"
                            class="mt-1"
                            :min="0.01"
                            :max="deficit"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Se transferirá de la tienda seleccionada a la actual
                            y se aplicará el movimiento.
                        </p>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        variant="secondary"
                        @click="showTransferModal = false"
                    >
                        Cancelar
                    </Button>
                    <Button
                        @click="confirmarTransferencia"
                        :disabled="!tiendaOrigenSeleccionada || transferring"
                    >
                        <Spinner v-if="transferring" class="mr-2 h-4 w-4" />
                        Transferir y Guardar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import tiendas from '@/routes/tiendas';
import { Tienda, type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{ tienda: Tienda }>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tiendas',
        href: tiendas.index().url,
    },
    {
        title: 'Editar Tienda',
        href: tiendas.edit(props.tienda.id).url,
    },
];

const isActive = ref(true);

const form = useForm({
    nombre: props.tienda.nombre,
    is_active: props.tienda.is_active,
});

// Sincronizar valores
watch(isActive, (newValue) => {
    form.is_active = newValue;
});

// O manejar el cambio manualmente
const handleCheckboxChange = (checked: boolean) => {
    form.is_active = checked;
    isActive.value = checked;
};

const toggleCheckbox = () => {
    form.is_active = !form.is_active;
};

function update() {
    form.put('/tiendas/' + props.tienda.id, {
        onSuccess: () => {
            toast.success('Tienda editada exitosamente');
        },
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Tienda" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Editar Tienda</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-6" @submit.prevent="update">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="nombre">Nombre</Label>
                                <Input
                                    id="nombre"
                                    type="nombre"
                                    v-model="form.nombre"
                                    name="nombre"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="nombre"
                                    placeholder="Nombre de la tienda"
                                />
                                <InputError :message="form.errors.nombre" />
                            </div>

                            <div class="grid gap-2">
                                <div class="flex items-center gap-3">
                                    <Label
                                        class="flex w-full items-start gap-3 rounded-lg border p-3 hover:bg-accent/50 has-[[aria-checked=true]]:border-blue-600 has-[[aria-checked=true]]:bg-blue-50 dark:has-[[aria-checked=true]]:border-blue-900 dark:has-[[aria-checked=true]]:bg-blue-950"
                                    >
                                        <Checkbox
                                            id="toggle-2"
                                            name="is_active"
                                            value="1"
                                            v-model="form.is_active"
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
                                                    form.is_active
                                                        ? 'Activa'
                                                        : 'Inactiva'
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    form.is_active
                                                        ? 'La tienda está activa'
                                                        : 'La tienda está inactiva'
                                                }}
                                            </p>
                                        </div>
                                    </Label>
                                </div>
                                <InputError :message="form.errors.is_active" />
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
                                <Link :href="tiendas.index().url">
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

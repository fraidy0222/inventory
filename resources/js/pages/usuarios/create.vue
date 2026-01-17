<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import usuarios from '@/routes/usuarios';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: usuarios.index().url,
    },
    {
        title: 'Crear Usuario',
        href: usuarios.create().url,
    },
];

const roles = [
    { value: 'admin', label: 'Admin' },
    { value: 'supervisor', label: 'Supervisor' },
    { value: 'empleado', label: 'Empleado' },
];

const isActive = ref(true);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    is_active: isActive.value,
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

const submit = () => {
    form.post('/usuarios', {
        onSuccess: () => {
            toast.success('Usuario creado exitosamente');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Usuario" />

        <div class="container mx-auto px-4 py-10">
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Crear Usuario</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-6" @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="email">Nombre</Label>
                                <Input
                                    id="name"
                                    type="name"
                                    v-model="form.name"
                                    name="name"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="Nombre"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Correo</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    name="email"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="email@example.com"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password">Contraseña</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    name="password"
                                    :tabindex="1"
                                    autocomplete="current-password"
                                    placeholder="Contraseña"
                                />
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation"
                                    >Confirmar Contraseña</Label
                                >
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    name="password_confirmation"
                                    :tabindex="1"
                                    autocomplete="current-password"
                                    placeholder="Confirmar Contraseña"
                                />
                                <InputError
                                    :message="form.errors.password_confirmation"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="role">Rol</Label>
                                <Select name="role" v-model="form.role">
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Selecciona un rol"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Roles</SelectLabel>
                                            <SelectItem
                                                :value="role.value"
                                                v-for="role in roles"
                                                :key="role.value"
                                            >
                                                {{ role.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.role" />
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
                                                        ? 'Activo'
                                                        : 'Inactivo'
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    form.is_active
                                                        ? 'El usuario está activo en el sistema'
                                                        : 'El usuario está inactivo en el sistema'
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
                                <Link :href="usuarios.index().url">
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

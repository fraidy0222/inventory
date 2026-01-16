<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import usuarios, { store } from '@/routes/usuarios';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

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
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['name', 'email']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="grid gap-2">
                                <Label for="email">Nombre</Label>
                                <Input
                                    id="name"
                                    type="name"
                                    name="name"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="Nombre"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Correo</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="email@example.com"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password">Contraseña</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Contraseña"
                                />
                                <InputError :message="errors.password" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="password_confirmation"
                                    >Confirmar Contraseña</Label
                                >
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Confirmar Contraseña"
                                />
                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="role">Rol</Label>
                               <Select>
                                    <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Select a fruit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Fruits</SelectLabel>
                                        <SelectItem value="apple">
                                        Apple
                                        </SelectItem>
                                        <SelectItem value="banana">
                                        Banana
                                        </SelectItem>
                                        <SelectItem value="blueberry">
                                        Blueberry
                                        </SelectItem>
                                        <SelectItem value="grapes">
                                        Grapes
                                        </SelectItem>
                                        <SelectItem value="pineapple">
                                        Pineapple
                                        </SelectItem>
                                    </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>

                            <Button
                                type="submit"
                                class="mt-4 w-full"
                                :tabindex="4"
                                :disabled="processing"
                                data-test="login-button"
                            >
                                <Spinner v-if="processing" />
                                Guardar
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

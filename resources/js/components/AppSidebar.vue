<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import archivos from '@/routes/archivos';
import destinos from '@/routes/destinos';
import inventarioTienda from '@/routes/inventarioTienda';
import movimientos from '@/routes/movimientos';
import productos from '@/routes/productos';
import tiendas from '@/routes/tiendas';
import usuarios from '@/routes/usuarios';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    File,
    LayoutGrid,
    MapPin,
    Package,
    ShoppingBasket,
    Store,
    Truck,
    Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Usuarios',
        href: usuarios.index().url,
        icon: Users,
    },
    {
        title: 'Tiendas',
        href: tiendas.index().url,
        icon: Store,
    },
    {
        title: 'Productos',
        href: productos.index().url,
        icon: ShoppingBasket,
    },
    {
        title: 'Inventarios',
        href: inventarioTienda.index().url,
        icon: Package,
    },
    {
        title: 'Destinos',
        href: destinos.index().url,
        icon: MapPin,
    },
    {
        title: 'Movimientos',
        href: movimientos.index().url,
        icon: Truck,
    },
    {
        title: 'Archivos',
        href: archivos.index().url,
        icon: File,
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

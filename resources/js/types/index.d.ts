import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role: string;
    is_active: boolean;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Tienda {
    id: number;
    nombre: string;
    is_active: boolean;
    updated_at: string;
    created_at: string;
    inventario_tiendas?: (InventarioTienda & { producto: Producto })[];
}

export interface InventarioTienda {
    id: number;
    cantidad: number;
    cantidad_minima: number;
    cantidad_maxima: number;
    ultima_actualizacion: string;
    producto_id: number;
    tienda_id: number;
    created_at: string;
    updated_at: string;
}

export interface ProductoInventario {
    id: number;
    tienda_id: number;
    tienda_nombre: string;
    producto_id: number;
    producto_nombre: string;
    producto_categoria: string | null;
    cantidad: number;
    cantidad_minima: number;
    cantidad_maxima: number;
    ultima_actualizacion: string;
}

export interface TiendaAgrupada {
    tienda_id: number;
    tienda_nombre: string;
    tienda_is_active: boolean;
    productos: ProductoInventario[];
}

export interface Producto {
    id: number;
    nombre: string;
    descripcion: string | null;
    categoria: string | null;
    activo: boolean;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export interface Destino {
    id: number;
    nombre: string;
    updated_at: string;
    created_at: string;
}

export interface Movimiento {
    id: number;
    producto_id: number;
    tienda_id: number;
    destino_id: number;
    entradas: number;
    salidas: number;
    traslados: number;
    venta_diaria: number;
    inventario_actual: number;
    producto: Producto;
    tienda: Tienda;
    destino: Destino;
    usuario: User;
    inventario_tienda: InventarioTienda & { tienda: Tienda };
    created_at: string;
    updated_at: string;
}

export interface RegistroMovimiento {
    id: number;
    producto_id: number;
    producto_nombre: string;
    producto_categoria: string | null;
    entradas: number;
    salidas: number;
    traslados: number;
    venta_diaria: number;
    destino_id: number;
    destino_nombre: string;
    tienda_relacionada_id?: number;
    tienda_relacionada_nombre?: string;
    usuario_id: number;
    usuario_nombre: string;
    inventario_actual: number;
    created_at: string;
    updated_at: string;
}

export interface TiendaMovimientos {
    tienda_id: number;
    tienda_nombre: string;
    tienda_is_active: boolean;
    registros: RegistroMovimiento[];
}

export interface File {
    id: number;
    nombre: string;
    archivo: string;
    created_at: string;
    updated_at: string;
}

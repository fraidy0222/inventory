<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { columns } from '@/components/inventarioTienda/columns';
import DataTable from '@/components/inventarioTienda/data-table.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import inventarioTienda from '@/routes/inventarioTienda';
import type { BreadcrumbItem, PaginatedData, TiendaAgrupada } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Plus,
    Search,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    inventariosTienda: PaginatedData<TiendaAgrupada>;
    tiendas: Array<{ id: number; nombre: string }>;
    filters: {
        search?: string;
        tienda_id?: string;
        per_page?: string | number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search ?? '');

const handleSearch = useDebounceFn((value: string | number) => {
    const searchValue = String(value);
    searchQuery.value = searchValue;
    const params = new URLSearchParams();
    if (searchValue) params.set('search', searchValue);
    params.set('per_page', String(props.inventariosTienda.per_page));
    router.visit(`${props.inventariosTienda.path}?${params.toString()}`, {
        preserveScroll: true,
        preserveState: true,
    });
}, 500);

const handlePerPageChange = (value: any) => {
    if (!value) return;
    const params = new URLSearchParams();
    params.set('per_page', String(value));
    if (searchQuery.value) params.set('search', searchQuery.value);
    router.visit(`${props.inventariosTienda.path}?${params.toString()}`, {
        preserveScroll: true,
    });
};

const displayedPerPage = computed(() => {
    if (props.inventariosTienda.per_page === props.inventariosTienda.total) {
        return '-1';
    }
    return String(props.inventariosTienda.per_page);
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventarios',
        href: inventarioTienda.index().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Inventarios" />
        <div class="container mx-auto px-4 py-6">
            <div class="mb-8 flex items-center justify-between">
                <Heading
                    title="Inventarios"
                    :has-class="true"
                    class="mb-0 space-y-0"
                />
                <Button as-child>
                    <Link :href="inventarioTienda.create()">
                        <Plus />
                        Añadir Inventario
                    </Link>
                </Button>
            </div>

            <div
                class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="relative w-full sm:w-64">
                    <Search
                        class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        :model-value="searchQuery"
                        placeholder="Buscar..."
                        class="pl-8"
                        @update:model-value="handleSearch"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <DataTable :columns="columns" :data="inventariosTienda.data" />
            </div>

            <div
                class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="flex flex-col gap-2 text-sm text-muted-foreground sm:flex-row sm:items-center sm:gap-4"
                >
                    <span>
                        Mostrando {{ inventariosTienda.from ?? 0 }} a
                        {{ inventariosTienda.to ?? 0 }} de
                        {{ inventariosTienda.total }} registros
                    </span>
                    <div class="flex items-center gap-2">
                        <span>Mostrar:</span>
                        <Select
                            :model-value="displayedPerPage"
                            @update:model-value="handlePerPageChange"
                        >
                            <SelectTrigger class="h-8 w-[90px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="5">5</SelectItem>
                                <SelectItem value="10">10</SelectItem>
                                <SelectItem value="20">20</SelectItem>
                                <SelectItem value="-1">Todos</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <Pagination
                    :total="inventariosTienda.total"
                    :sibling-count="1"
                    show-edges
                    :default-page="inventariosTienda.current_page"
                    :items-per-page="inventariosTienda.per_page"
                >
                    <PaginationContent
                        v-slot="{ items }"
                        class="flex items-center gap-1"
                    >
                        <PaginationFirst
                            @click="
                                $inertia.visit(
                                    inventariosTienda.first_page_url,
                                    {
                                        preserveScroll: true,
                                    },
                                )
                            "
                        >
                            <ChevronsLeft class="h-4 w-4" />
                        </PaginationFirst>
                        <PaginationPrevious
                            @click="
                                inventariosTienda.prev_page_url
                                    ? $inertia.visit(
                                          inventariosTienda.prev_page_url,
                                          {
                                              preserveScroll: true,
                                          },
                                      )
                                    : null
                            "
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </PaginationPrevious>

                        <template v-for="(item, index) in items">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :key="index"
                                :value="item.value"
                                as-child
                            >
                                <Button
                                    class="h-9 w-9 p-0"
                                    :variant="
                                        item.value ===
                                        inventariosTienda.current_page
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="
                                        $inertia.visit(
                                            `${inventariosTienda.path}?page=${item.value}&per_page=${inventariosTienda.per_page}`,
                                            { preserveScroll: true },
                                        )
                                    "
                                >
                                    {{ item.value }}
                                </Button>
                            </PaginationItem>
                            <PaginationEllipsis
                                v-else
                                :key="item.type"
                                :index="index"
                            />
                        </template>

                        <PaginationNext
                            @click="
                                inventariosTienda.next_page_url
                                    ? $inertia.visit(
                                          inventariosTienda.next_page_url,
                                          {
                                              preserveScroll: true,
                                          },
                                      )
                                    : null
                            "
                        >
                            <ChevronRight class="h-4 w-4" />
                        </PaginationNext>
                        <PaginationLast
                            @click="
                                $inertia.visit(
                                    inventariosTienda.last_page_url,
                                    {
                                        preserveScroll: true,
                                    },
                                )
                            "
                        >
                            <ChevronsRight class="h-4 w-4" />
                        </PaginationLast>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>

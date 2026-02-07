<script setup lang="ts">
import { columns } from '@/components/files/columns';
import DataTable from '@/components/files/data-table.vue';
import Heading from '@/components/Heading.vue';
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
import archivosRoutes from '@/routes/archivos';
import { type BreadcrumbItem, type File, type PaginatedData } from '@/types';
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
    archivos: PaginatedData<File>;
    search?: string;
}

const props = defineProps<Props>();

const searchQuery = ref(props.search ?? '');

const handleSearch = useDebounceFn((value: string | number) => {
    const searchValue = String(value);
    searchQuery.value = searchValue;
    const params = new URLSearchParams();
    if (searchValue) params.set('search', searchValue);
    params.set('per_page', String(props.archivos.per_page));
    router.visit(`${archivosRoutes.index().url}?${params.toString()}`, {
        preserveScroll: true,
        preserveState: true,
    });
}, 500);

const handlePerPageChange = (value: any) => {
    if (!value) return;
    const params = new URLSearchParams();
    params.set('per_page', String(value));
    if (searchQuery.value) params.set('search', searchQuery.value);
    router.visit(`${archivosRoutes.index().url}?${params.toString()}`, {
        preserveScroll: true,
    });
};

// Determine the display value for the select
const displayedPerPage = computed(() => {
    // If per_page equals total users, we're showing all
    if (props.archivos.per_page === props.archivos.total) {
        return '-1';
    }
    return String(props.archivos.per_page);
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Archivos',
        href: archivosRoutes.index().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Archivos" />
        <div class="container mx-auto px-4 py-6">
            <div class="mb-8 flex items-center justify-between">
                <Heading
                    title="Archivos"
                    :has-class="true"
                    class="mb-0 space-y-0"
                />
                <Button as-child>
                    <Link :href="archivosRoutes.create()">
                        <Plus />
                        Añadir Archivo
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
                <DataTable :columns="columns" :data="archivos.data" />
            </div>

            <div
                class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="flex flex-col gap-2 text-sm text-muted-foreground sm:flex-row sm:items-center sm:gap-4"
                >
                    <span>
                        Mostrando {{ archivos.from ?? 0 }} a
                        {{ archivos.to ?? 0 }} de {{ archivos.total }} registros
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
                    :total="archivos.total"
                    :sibling-count="1"
                    show-edges
                    :default-page="archivos.current_page"
                    :items-per-page="archivos.per_page"
                >
                    <PaginationContent
                        v-slot="{ items }"
                        class="flex items-center gap-1"
                    >
                        <PaginationFirst
                            @click="
                                $inertia.visit(archivos.first_page_url, {
                                    preserveScroll: true,
                                })
                            "
                        >
                            <ChevronsLeft class="h-4 w-4" />
                        </PaginationFirst>
                        <PaginationPrevious
                            @click="
                                archivos.prev_page_url
                                    ? $inertia.visit(archivos.prev_page_url, {
                                          preserveScroll: true,
                                      })
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
                                        item.value === archivos.current_page
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="
                                        $inertia.visit(
                                            `${archivos.path}?page=${item.value}&per_page=${archivos.per_page}`,
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
                                archivos.next_page_url
                                    ? $inertia.visit(archivos.next_page_url, {
                                          preserveScroll: true,
                                      })
                                    : null
                            "
                        >
                            <ChevronRight class="h-4 w-4" />
                        </PaginationNext>
                        <PaginationLast
                            @click="
                                $inertia.visit(archivos.last_page_url, {
                                    preserveScroll: true,
                                })
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

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CarFront,
    LayoutGrid,
    PhoneCall,
    Users,
    UsersRound,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { index as agentsIndex } from '@/routes/agents';
import { index as callsIndex } from '@/routes/calls';
import { index as clientsIndex } from '@/routes/clients';
import { index as reservationsIndex } from '@/routes/reservations';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Tableau de bord',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Appels',
        href: callsIndex(),
        icon: PhoneCall,
    },
    {
        title: 'Clients',
        href: clientsIndex(),
        icon: UsersRound,
    },
    {
        title: 'Réservations',
        href: reservationsIndex(),
        icon: CarFront,
    },
    {
        title: 'Agents',
        href: agentsIndex(),
        icon: Users,
    },
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

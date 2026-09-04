<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { MailPlus, RotateCcw, UserPlus } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index as agentsIndex, store } from '@/routes/agents';
import { resend } from '@/routes/agents/invitation';
import type { AgentAccount } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Agents', href: agentsIndex() }],
    },
});

defineProps<{
    agents: AgentAccount[];
}>();

const form = useForm({
    name: '',
    email: '',
});

const invite = (): void => {
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const resendInvitation = (agent: AgentAccount): void => {
    router.post(resend(agent.id).url, {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Agents" />

    <div class="flex flex-col gap-6 p-4">
        <h1 class="sr-only">Agents du service client</h1>

        <section class="space-y-4" aria-labelledby="invite-heading">
            <Heading
                variant="small"
                title="Inviter un agent"
                description="L'agent reçoit par e-mail un lien pour définir lui-même son mot de passe. Aucun mot de passe ne circule."
            />
            <span id="invite-heading" class="sr-only">Inviter un agent</span>

            <form
                class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-start"
                @submit.prevent="invite"
            >
                <div class="grid gap-2">
                    <Label for="name">Nom complet</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="off"
                        placeholder="Aya Touré"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Adresse e-mail professionnelle</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="off"
                        placeholder="aya.toure@bollirental.africa"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <Button
                    type="submit"
                    class="sm:mt-6"
                    :disabled="form.processing"
                    data-test="invite-agent-button"
                >
                    <Spinner v-if="form.processing" />
                    <UserPlus v-else class="size-4" />
                    Envoyer l'invitation
                </Button>
            </form>
        </section>

        <section class="space-y-4" aria-labelledby="agents-heading">
            <Heading
                variant="small"
                title="Plateau"
                description="Les agents habilités à enregistrer des appels."
            />
            <span id="agents-heading" class="sr-only">Plateau</span>

            <div class="rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Agent</TableHead>
                            <TableHead>Adresse e-mail</TableHead>
                            <TableHead class="text-right">
                                Appels traités
                            </TableHead>
                            <TableHead class="text-right">Accès</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="agent in agents" :key="agent.id">
                            <TableCell class="font-medium">
                                {{ agent.name }}
                                <Badge v-if="agent.is_current" variant="secondary">
                                    vous
                                </Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ agent.email }}
                            </TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ agent.calls_count }}
                            </TableCell>
                            <TableCell class="text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="resendInvitation(agent)"
                                >
                                    <RotateCcw class="size-4" />
                                    Renvoyer l'accès
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="agents.length === 0">
                            <TableCell
                                colspan="4"
                                class="text-muted-foreground py-10 text-center"
                            >
                                <MailPlus class="mx-auto mb-2 size-6" />
                                Aucun agent pour l'instant. Invitez le premier
                                ci-dessus.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDark } = useDarkMode();

const props = defineProps({
    torneo: Object,
    inscripciones: Array,
    pagosPorEquipo: Array,
    constantes: Object,
    can: Object,
    flash: Object,
});

const constantes = props.constantes || {};

const metodosPagoLabels = constantes.metodos_pago || {};
const estadosPagoLabels = constantes.estados_pago || {};

const showPagoModal = ref(false);
const selectedInscripcion = ref(null);
const pagoForm = ref({
    metodo_pago: 'transferencia',
    referencia: '',
    notas: '',
    comprobante: null,
});

const isSubmitting = ref(false);

function abrirModalPago(inscripcion) {
    selectedInscripcion.value = inscripcion;
    pagoForm.value = { metodo_pago: 'transferencia', referencia: '', notas: '', comprobante: null };
    showPagoModal.value = true;
}

function cerrarModal() {
    showPagoModal.value = false;
    selectedInscripcion.value = null;
}

function handleFileChange(e) {
    pagoForm.value.comprobante = e.target.files[0] || null;
}

function enviarPago() {
    if (!selectedInscripcion.value) return;

    isSubmitting.value = true;
    const formData = new FormData();
    formData.append('metodo_pago', pagoForm.value.metodo_pago);
    formData.append('team_id', selectedInscripcion.value.equipo.id);
    formData.append('referencia', pagoForm.value.referencia);
    formData.append('notas', pagoForm.value.notas);
    if (pagoForm.value.comprobante) {
        formData.append('comprobante', pagoForm.value.comprobante);
    }

    router.post(route('pagos.store', props.torneo.id), formData, {
        onSuccess: () => { cerrarModal(); },
        onFinish: () => { isSubmitting.value = false; },
    });
}

function confirmarPago(pagoId) {
    Swal.fire({
        title: '¿Confirmar pago?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b77f',
        cancelButtonColor: isDark.value ? '#334155' : '#94a3b8',
        background: isDark.value ? '#1A2C26' : '#fff',
        color: isDark.value ? '#e2e8f0' : '#1e293b',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('pagos.confirmar', [props.torneo.id, pagoId]));
        }
    });
}

function rechazarPago(pagoId) {
    Swal.fire({
        title: 'Rechazar pago',
        input: 'text',
        inputLabel: 'Motivo del rechazo',
        inputPlaceholder: 'Escribe el motivo...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: isDark.value ? '#334155' : '#94a3b8',
        background: isDark.value ? '#1A2C26' : '#fff',
        color: isDark.value ? '#e2e8f0' : '#1e293b',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) return 'Debes escribir un motivo';
        },
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            router.patch(route('pagos.rechazar', [props.torneo.id, pagoId]), {
                motivo: result.value,
            });
        }
    });
}

const badgeClass = (estado) => {
    const map = {
        confirmado: 'bg-green-100 text-green-800 border-green-200',
        pendiente: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        rechazado: 'bg-red-100 text-red-800 border-red-200',
    };
    return map[estado] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const puedePagar = computed(() => props.can?.upload_receipt && !props.can?.create_payments);
const puedeConfirmar = computed(() => props.can?.create_payments);
</script>

<template>
    <Head :title="`Pagos - ${torneo.nombre}`" />

    <AuthenticatedLayout>
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Pagos de Inscripción</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ torneo.nombre }}
                        <span v-if="torneo.pago_requerido" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                            ${{ torneo.precio_inscripcion }} {{ torneo.moneda }}
                        </span>
                        <span v-else class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500">
                            Sin costo
                        </span>
                    </p>
                </div>
                <Link :href="route('torneos.index')" class="text-sm text-primary hover:underline">&larr; Volver a torneos</Link>
            </div>

            <!-- Flash messages -->
            <div v-if="flash?.success"
                class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <span class="material-symbols-outlined !text-xl">check_circle</span>
                <span class="text-sm font-bold uppercase tracking-wide">{{ flash.success }}</span>
            </div>
            <div v-if="flash?.error"
                class="mb-4 bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <span class="material-symbols-outlined !text-xl">error</span>
                <span class="text-sm font-bold uppercase tracking-wide">{{ flash.error }}</span>
            </div>

            <!-- Inscripciones table -->
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Equipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Estado Pago</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Método</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Comprobante</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        <tr v-for="ins in inscripciones" :key="ins.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm font-medium text-slate-800 dark:text-white">{{ ins.equipo?.name || '—' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span v-if="ins.ultimoPago" :class="badgeClass(ins.ultimoPago.estado)"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                    {{ estadosPagoLabels[ins.ultimoPago.estado] || ins.ultimoPago.estado }}
                                </span>
                                <span v-else class="text-xs text-slate-400">Sin pago</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                {{ metodosPagoLabels[ins.ultimoPago?.metodo_pago] || ins.ultimoPago?.metodo_pago || '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a v-if="ins.ultimoPago?.comprobante_url && puedeConfirmar"
                                    :href="ins.ultimoPago.comprobante_url" target="_blank"
                                    class="inline-flex items-center gap-1 text-primary hover:text-primary-dark underline underline-offset-2">
                                    <span class="material-symbols-outlined !text-base">download</span>
                                    {{ ins.ultimoPago.comprobante_original }}
                                </a>
                                <span v-else-if="ins.ultimoPago?.comprobante_original" class="text-slate-600 dark:text-slate-400">
                                    {{ ins.ultimoPago.comprobante_original }}
                                </span>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Confirmar (admin) -->
                                    <button v-if="ins.tienePagoPendiente && puedeConfirmar"
                                        @click="confirmarPago(ins.ultimoPago.id)"
                                        class="text-xs px-2.5 py-1 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 transition-colors">
                                        Confirmar
                                    </button>
                                    <!-- Rechazar (admin) -->
                                    <button v-if="ins.tienePagoPendiente && puedeConfirmar"
                                        @click="rechazarPago(ins.ultimoPago.id)"
                                        class="text-xs px-2.5 py-1 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">
                                        Rechazar
                                    </button>
                                    <!-- Pagar (delegate) -->
                                    <button v-if="!ins.tienePagoPendiente && !ins.tienePagoConfirmado && puedePagar"
                                        @click="abrirModalPago(ins)"
                                        class="text-xs px-2.5 py-1 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 border border-primary/20 transition-colors">
                                        Pagar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="inscripciones.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">
                                No hay inscripciones en este torneo.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pago Modal -->
            <div v-if="showPagoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="cerrarModal">
                <div class="bg-white dark:bg-surface-dark rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Registrar pago</h3>
                    <p class="text-sm text-slate-500 mb-4">
                        Equipo: <strong>{{ selectedInscripcion?.equipo?.name }}</strong>
                        <span v-if="torneo.precio_inscripcion" class="ml-1">
                            — Monto: ${{ torneo.precio_inscripcion }} {{ torneo.moneda }}
                        </span>
                    </p>

                    <!-- Método de pago -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Método de pago</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-colors"
                                :class="pagoForm.metodo_pago === 'transferencia' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                                <input type="radio" v-model="pagoForm.metodo_pago" value="transferencia" class="sr-only" />
                                <span class="material-symbols-outlined text-lg">upload_file</span>
                                <span class="text-sm font-medium">Transferencia</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-colors"
                                :class="pagoForm.metodo_pago === 'efectivo' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                                <input type="radio" v-model="pagoForm.metodo_pago" value="efectivo" class="sr-only" />
                                <span class="material-symbols-outlined text-lg">payments</span>
                                <span class="text-sm font-medium">Efectivo</span>
                            </label>
                        </div>
                    </div>

                    <!-- Comprobante (solo transferencia) -->
                    <div v-if="pagoForm.metodo_pago === 'transferencia'" class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Comprobante</label>
                        <input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="handleFileChange"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG o PDF. Máximo 5MB.</p>
                    </div>

                    <!-- Referencia -->
                    <div v-if="pagoForm.metodo_pago === 'transferencia'" class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Referencia (opcional)</label>
                        <input v-model="pagoForm.referencia" type="text" maxlength="100"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800 text-sm" />
                    </div>

                    <!-- Notas -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notas (opcional)</label>
                        <textarea v-model="pagoForm.notas" rows="2" maxlength="500"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="cerrarModal">Cancelar</SecondaryButton>
                        <PrimaryButton @click="enviarPago" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Enviando...' : 'Registrar pago' }}
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

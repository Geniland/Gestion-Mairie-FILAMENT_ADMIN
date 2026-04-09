<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Ticket - Gestion Mairie</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div id="app" class="w-full max-w-md">
        <transition name="fade" mode="out-in">
            <!-- Loading -->
            <div v-if="loading" key="loading" class="flex flex-col items-center justify-center p-12">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-600"></div>
                <p class="mt-4 text-slate-600 font-medium text-lg">Vérification en cours...</p>
            </div>

            <!-- Ticket Valide -->
            <div v-else-if="status === 'valid'" key="valid" class="glass rounded-3xl shadow-2xl overflow-hidden border border-white">
                <div class="bg-green-500 p-8 text-center text-white">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fa-solid fa-check text-green-500 text-4xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Ticket Valide</h1>
                    <p class="opacity-90">Ce ticket est authentique et actif</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <span class="text-slate-500">N° Ticket</span>
                        <span class="font-semibold text-slate-800">[[ ticket.numero ]]</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <span class="text-slate-500">Contribuable</span>
                        <span class="font-semibold text-slate-800">[[ ticket.contribuable ]]</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <span class="text-slate-500">Taxe</span>
                        <span class="font-semibold text-indigo-600">[[ ticket.taxe ]]</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <span class="text-slate-500">Montant</span>
                        <span class="font-bold text-slate-800">[[ formatPrice(ticket.montant) ]] GNF</span>
                    </div>
                    <div class="bg-green-50 rounded-2xl p-4 mt-6 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fa-solid fa-calendar-check text-green-600 mr-3"></i>
                            <div>
                                <p class="text-xs text-green-600 font-medium">Expire le</p>
                                <p class="font-bold text-green-800">[[ ticket.date_expiration ]]</p>
                            </div>
                        </div>
                        <span class="bg-green-600 text-white text-xs px-3 py-1 rounded-full font-bold">
                            Expire dans [[ ticket.days_diff ]] jours
                        </span>
                    </div>
                </div>
                <div class="p-6 text-center text-xs text-slate-400">
                    Propulsé par le système de Gestion Mairie
                </div>
            </div>

            <!-- Ticket Expiré -->
            <div v-else-if="status === 'expired'" key="expired" class="glass rounded-3xl shadow-2xl overflow-hidden border border-white">
                <div class="bg-orange-500 p-8 text-center text-white">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fa-solid fa-clock text-orange-500 text-4xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Ticket Expiré</h1>
                    <p class="opacity-90">La date de validité est dépassée</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <span class="text-slate-500">N° Ticket</span>
                        <span class="font-semibold text-slate-800">[[ ticket.numero ]]</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <span class="text-slate-500">Contribuable</span>
                        <span class="font-semibold text-slate-800">[[ ticket.contribuable ]]</span>
                    </div>
                    <div class="bg-orange-50 rounded-2xl p-4 mt-6 flex items-center justify-between border border-orange-200">
                        <div class="flex items-center">
                            <i class="fa-solid fa-calendar-xmark text-orange-600 mr-3"></i>
                            <div>
                                <p class="text-xs text-orange-600 font-medium">Expiré le</p>
                                <p class="font-bold text-orange-800">[[ ticket.date_expiration ]]</p>
                            </div>
                        </div>
                        <span class="bg-orange-600 text-white text-xs px-3 py-1 rounded-full font-bold">
                            Il y a [[ Math.abs(ticket.days_diff) ]] jours
                        </span>
                    </div>
                </div>
                <div class="p-8">
                    <button @click="recharge" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 rounded-2xl shadow-lg transition-all active:scale-95">
                        RENOUVELER MAINTENANT
                    </button>
                </div>
            </div>

            <!-- Ticket Invalide / Fraude -->
            <div v-else key="error" class="glass rounded-3xl shadow-2xl overflow-hidden border border-white">
                <div class="bg-red-500 p-8 text-center text-white">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-4xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Ticket Invalide</h1>
                    <p class="opacity-90">Attention : ce ticket n'est pas reconnu</p>
                </div>
                <div class="p-12 text-center">
                    <div class="bg-red-50 p-6 rounded-2xl border border-red-100 mb-8">
                        <p class="text-red-700 font-medium">Ce ticket est introuvable ou a été marqué comme frauduleux dans notre base de données.</p>
                    </div>
                    <p class="text-slate-400 text-sm">Veuillez contacter les services de la mairie ou l'agent collecteur.</p>
                </div>
                <div class="p-6 border-t border-slate-50">
                    <button @click="close" class="w-full bg-slate-800 text-white font-bold py-4 rounded-2xl">
                        FERMER
                    </button>
                </div>
            </div>
        </transition>
    </div>

    <script>
        const { createApp, ref, onMounted } = Vue;

        createApp({
            delimiters: ['[[', ']]'],
            setup() {
                const loading = ref(true);
                const status = ref('invalid');
                const ticket = ref(null);
                const hash = '{{ $hash }}';

                const fetchTicket = async () => {
                    try {
                        const response = await fetch(`/api/v/${hash}`);
                        const data = await response.json();
                        
                        if (response.ok) {
                            status.value = data.status;
                            ticket.value = data.ticket;
                        } else {
                            status.value = 'invalid';
                        }
                    } catch (e) {
                        status.value = 'invalid';
                    } finally {
                        loading.value = false;
                    }
                };

                const formatPrice = (price) => {
                    return new Intl.NumberFormat('fr-FR').format(price);
                };

                const recharge = () => {
                    alert('Redirection vers le portail de paiement...');
                };

                const close = () => {
                    window.close();
                };

                onMounted(fetchTicket);

                return {
                    loading,
                    status,
                    ticket,
                    formatPrice,
                    recharge,
                    close
                };
            }
        }).mount('#app');
    </script>
</body>
</html>

<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

    <div class="rounded-xl p-6 text-white shadow-lg flex flex-col justify-between"
        style="background: linear-gradient(135deg,#43a047,#2e7d32)">
        
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium opacity-90">
                Recette du Jour
            </span>

            <x-heroicon-o-banknotes class="w-6 h-6 opacity-80"/>
        </div>

        <div class="text-3xl font-bold mt-4">
            {{ number_format($today,0,',',' ') }} FCFA
        </div>

    </div>


    <div class="rounded-xl p-6 text-white shadow-lg flex flex-col justify-between"
        style="background: linear-gradient(135deg,#0f766e,#115e59)">
        
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium opacity-90">
                Recette de la Semaine
            </span>

            <x-heroicon-o-banknotes class="w-6 h-6 opacity-80"/>
        </div>

        <div class="text-3xl font-bold mt-4">
            {{ number_format($week,0,',',' ') }} FCFA
        </div>

    </div>


    <div class="rounded-xl p-6 text-white shadow-lg flex flex-col justify-between"
        style="background: linear-gradient(135deg,#2b7bbb,#1d5f8f)">
        
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium opacity-90">
                Recette du Mois
            </span>

            <x-heroicon-o-banknotes class="w-6 h-6 opacity-80"/>
        </div>

        <div class="text-3xl font-bold mt-4">
            {{ number_format($month,0,',',' ') }} FCFA
        </div>

    </div>


    <div class="rounded-xl p-6 text-white shadow-lg flex flex-col justify-between"
        style="background: linear-gradient(135deg,#1f7a63,#155e4b)">
        
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium opacity-90">
                Recette de l’Année
            </span>

            <x-heroicon-o-banknotes class="w-6 h-6 opacity-80"/>
        </div>

        <div class="text-3xl font-bold mt-4">
            {{ number_format($year,0,',',' ') }} FCFA
        </div>

    </div>

</div>
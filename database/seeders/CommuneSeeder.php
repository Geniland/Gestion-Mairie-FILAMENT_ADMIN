<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommuneSeeder extends Seeder
{
    public function run()
    {

        $communes = [

            // GOLFE 1
            ['nom'=>'Golfe 1','region'=>'Maritime','quartier'=>'Bè'],
            ['nom'=>'Golfe 1','region'=>'Maritime','quartier'=>'Bè Kpota'],
            ['nom'=>'Golfe 1','region'=>'Maritime','quartier'=>'Bè Klikamé'],

            // GOLFE 2
            ['nom'=>'Golfe 2','region'=>'Maritime','quartier'=>'Tokoin'],
            ['nom'=>'Golfe 2','region'=>'Maritime','quartier'=>'Nyékonakpoè'],
            ['nom'=>'Golfe 2','region'=>'Maritime','quartier'=>'Kodjoviakopé'],

            // GOLFE 3
            ['nom'=>'Golfe 3','region'=>'Maritime','quartier'=>'Bè Château'],
            ['nom'=>'Golfe 3','region'=>'Maritime','quartier'=>'Akodessewa'],
            ['nom'=>'Golfe 3','region'=>'Maritime','quartier'=>'Adidogome'],

            // GOLFE 4
            ['nom'=>'Golfe 4','region'=>'Maritime','quartier'=>'Amoutiévé'],
            ['nom'=>'Golfe 4','region'=>'Maritime','quartier'=>'Adawlato'],
            ['nom'=>'Golfe 4','region'=>'Maritime','quartier'=>'Hanoukopé'],

            // GOLFE 5
            ['nom'=>'Golfe 5','region'=>'Maritime','quartier'=>'Aflao Gakli'],
            ['nom'=>'Golfe 5','region'=>'Maritime','quartier'=>'Sagbado'],
            ['nom'=>'Golfe 5','region'=>'Maritime','quartier'=>'Adidogomé'],

            // GOLFE 6
            ['nom'=>'Golfe 6','region'=>'Maritime','quartier'=>'Baguida'],
            ['nom'=>'Golfe 6','region'=>'Maritime','quartier'=>'Adakpamé'],
            ['nom'=>'Golfe 6','region'=>'Maritime','quartier'=>'Kpogan'],

            // AGOÈ
            ['nom'=>'Agoe Nyive 1','region'=>'Maritime','quartier'=>'Agoe'],
            ['nom'=>'Agoe Nyive 2','region'=>'Maritime','quartier'=>'Legbassito'],
            ['nom'=>'Agoe Nyive 3','region'=>'Maritime','quartier'=>'Vakpossito'],
            ['nom'=>'Agoe Nyive 4','region'=>'Maritime','quartier'=>'Logope'],
            ['nom'=>'Agoe Nyive 5','region'=>'Maritime','quartier'=>'Sanguera'],
            ['nom'=>'Agoe Nyive 6','region'=>'Maritime','quartier'=>'Adetikope']

        ];

        DB::table('communes')->insert($communes);
    }
}
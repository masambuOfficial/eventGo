<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// db/02-seed-reference.sql
//
// Starter set covering the Kampala pilot area plus major regional centres.
// Uganda creates and splits districts regularly. Before national launch,
// reconcile this list against the official UBOS district list and set
// effective_from where relevant. Do not treat this as authoritative.
class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $central = [
            'Kampala', 'Wakiso', 'Mukono', 'Mpigi', 'Butambala', 'Gomba', 'Kalangala', 'Kalungu',
            'Kayunga', 'Kiboga', 'Kyankwanzi', 'Luweero', 'Lwengo', 'Lyantonde', 'Masaka', 'Mityana',
            'Mubende', 'Nakaseke', 'Nakasongola', 'Rakai', 'Sembabule', 'Buikwe', 'Buvuma', 'Bukomansimbi',
            'Kyotera', 'Kassanda',
        ];

        $eastern = [
            'Jinja', 'Iganga', 'Kamuli', 'Mbale', 'Tororo', 'Busia', 'Bugiri', 'Mayuge',
            'Soroti', 'Kumi', 'Pallisa', 'Sironko', 'Kapchorwa', 'Bukwo', 'Budaka', 'Butaleja',
            'Namutumba', 'Kaliro', 'Buyende', 'Luuka', 'Namayingo', 'Bulambuli', 'Kween', 'Serere',
            'Ngora', 'Bukedea', 'Amuria', 'Katakwi', 'Kaberamaido', 'Manafwa', 'Bududa', 'Butebo',
            'Kibuku', 'Namisindwa', 'Bugweri', 'Kapelebyong',
        ];

        $northern = [
            'Gulu', 'Lira', 'Kitgum', 'Pader', 'Apac', 'Arua', 'Nebbi', 'Moyo',
            'Adjumani', 'Yumbe', 'Koboko', 'Maracha', 'Zombo', 'Amuru', 'Nwoya', 'Agago',
            'Lamwo', 'Otuke', 'Alebtong', 'Dokolo', 'Amolatar', 'Oyam', 'Kole', 'Kaabong',
            'Kotido', 'Abim', 'Moroto', 'Napak', 'Nakapiripirit', 'Amudat', 'Pakwach', 'Obongi',
            'Madi-Okollo', 'Kwania', 'Terego', 'Karenga', 'Nabilatuk',
        ];

        $western = [
            'Mbarara', 'Bushenyi', 'Ntungamo', 'Kabale', 'Kisoro', 'Rukungiri', 'Kanungu', 'Kasese',
            'Kabarole', 'Kamwenge', 'Kyenjojo', 'Hoima', 'Masindi', 'Buliisa', 'Kiryandongo', 'Kibaale',
            'Kyegegwa', 'Ibanda', 'Isingiro', 'Kiruhura', 'Mitooma', 'Rubirizi', 'Sheema', 'Buhweju',
            'Rukiga', 'Rubanda', 'Bunyangabu', 'Kagadi', 'Kakumiro', 'Kikuube', 'Rwampara', 'Kazo',
            'Kitagwenda', 'Ntoroko',
        ];

        $rows = [];

        foreach (['central' => $central, 'eastern' => $eastern, 'northern' => $northern, 'western' => $western] as $region => $names) {
            foreach ($names as $name) {
                $rows[] = ['name' => $name, 'region' => $region];
            }
        }

        DB::table('districts')->insert($rows);
    }
}

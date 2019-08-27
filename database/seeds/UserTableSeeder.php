<?php

use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect(self::$CENTRAL_WALTER_USER_MAP)->each(function ($user) {
            factory(User::class)->create([
                'central_id' => $user['central_id'],
                'walter_id' => $user['walter_id'],
                'email' => $user['email']
            ]);
        });
    }

    private static $CENTRAL_WALTER_USER_MAP = [
        ['central_id' => 2, 'walter_id' => 9, 'email' => 'charlie@kimmel.com'],
        ['central_id' => 3, 'walter_id' => 4, 'email' => 'josh@kimmel.com'],
        ['central_id' => 4, 'walter_id' => 285, 'email' => 'jacob@kimmel.com'],
        ['central_id' => 5, 'walter_id' => null, 'email' => 'aalsup@kimmel.com'],
        ['central_id' => 6, 'walter_id' => 49, 'email' => 'ahowse@kimmel.com'],
        ['central_id' => 7, 'walter_id' => 7, 'email' => 'alank@kimmel.com'],
        ['central_id' => 8, 'walter_id' => 51, 'email' => 'akimmel@kimmel.com'],
        ['central_id' => 9, 'walter_id' => null, 'email' => 'akling@kimmel.com'],
        ['central_id' => 10, 'walter_id' => 55,  'email' => 'alaibson@kimmel.com'],
        ['central_id' => 11, 'walter_id' => 59,  'email' => 'amacnair@kimmel.com'],
        ['central_id' => 12, 'walter_id' => 83,  'email' => 'awall@kimmel.com'],
        ['central_id' => 13, 'walter_id' => 19,  'email' => 'billyd@kimmel.com'],
        ['central_id' => 14, 'walter_id' => 44,  'email' => 'bfountain@kimmel.com'],
        ['central_id' => 15, 'walter_id' => null,  'email' => 'blaibson@kimmel.com'],
        ['central_id' => 16, 'walter_id' => 265, 'email' =>  'bsavage@kimmel.com'],
        ['central_id' => 17, 'walter_id' => 76,  'email' => 'bschlaefer@kimmel.com'],
        ['central_id' => 18, 'walter_id' => 89,  'email' => 'bwolfe@kimmel.com'],
        ['central_id' => 19, 'walter_id' => 289, 'email' =>  'cmccoy@kimmel.com'],
        ['central_id' => 20, 'walter_id' => 61,  'email' => 'cmitchell@kimmel.com'],
        ['central_id' => 21, 'walter_id' => 80,  'email' => 'cstewart@kimmel.com'],
        ['central_id' => 22, 'walter_id' => null,  'email' => 'csullins@kimmel.com'],
        ['central_id' => 23, 'walter_id' => 86,  'email' => 'cwhitt@kimmel.com'],
        ['central_id' => 24, 'walter_id' => 20,  'email' => 'deckart@kimmel.com'],
        ['central_id' => 25, 'walter_id' => 21,  'email' => 'dgoodrum@kimmel.com'],
        ['central_id' => 26, 'walter_id' => 48,  'email' => 'dholden@kimmel.com'],
        ['central_id' => 27, 'walter_id' => 77,  'email' => 'dsmoyer@kimmel.com'],
        ['central_id' => 28, 'walter_id' => 291, 'email' =>  'dwilliams@kimmel.com'],
        ['central_id' => 29, 'walter_id' => null,  'email' => 'fitness@kimmel.com'],
        ['central_id' => 30, 'walter_id' => 210, 'email' =>  'graxter@kimmel.com'],
        ['central_id' => 31, 'walter_id' =>  8,  'email' => 'guy@kimmel.com'],
        ['central_id' => 32, 'walter_id' =>  24, 'email' => 'jamie@kimmel.com'],
        ['central_id' => 33, 'walter_id' =>  30, 'email' => 'jashburn@kimmel.com'],
        ['central_id' => 34, 'walter_id' =>  35, 'email' => 'jcarver@kimmel.com'],
        ['central_id' => 35, 'walter_id' =>  38, 'email' => 'jcoddington@kimmel.com'],
        ['central_id' => 36, 'walter_id' =>  52, 'email' => 'jeremyk@kimmel.com'],
        ['central_id' => 37, 'walter_id' => null,  'email' => 'forbes@kimmel.com'],
        ['central_id' => 38, 'walter_id' =>  17, 'email' => 'jimc@kimmel.com'],
        ['central_id' => 39, 'walter_id' =>  64, 'email' => 'jmyers@kimmel.com'],
        ['central_id' => 40, 'walter_id' =>  65, 'email' => 'jnikolski@kimmel.com'],
        ['central_id' => 41, 'walter_id' =>  88, 'email' => 'justinw@kimmel.com'],
        ['central_id' => 42, 'walter_id' =>  87, 'email' => 'jwilkins@kimmel.com'],
        ['central_id' => 43, 'walter_id' => 284, 'email' =>  'kadcock@kimmel.com'],
        ['central_id' => 44, 'walter_id' => 162, 'email' =>  'khumes@kimmel.com'],
        ['central_id' => 45, 'walter_id' => 10,  'email' => 'kimmel@kimmel.com'],
        ['central_id' => 46, 'walter_id' => 11,  'email' => 'lfailing@kimmel.com'],
        ['central_id' => 47, 'walter_id' => 47,  'email' => 'lheamon@kimmel.com'],
        ['central_id' => 48, 'walter_id' => null,  'email' => 'maintenance@kimmel.com'],
        ['central_id' => 49, 'walter_id' => null,  'email' => 'mfield@kimmel.com'],
        ['central_id' => 50, 'walter_id' => 282, 'email' =>  'mfrosaker@kimmel.com'],
        ['central_id' => 51, 'walter_id' => 294, 'email' =>  'mhedden@kimmel.com'],
        ['central_id' => 52, 'walter_id' => 273, 'email' =>  'michaelj@kimmel.com'],
        ['central_id' => 53, 'walter_id' => 50,  'email' => 'mjones@kimmel.com'],
        ['central_id' => 54, 'walter_id' => 57,  'email' => 'mlove@kimmel.com'],
        ['central_id' => 55, 'walter_id' => 62,  'email' => 'mmurphy@kimmel.com'],
        ['central_id' => 56, 'walter_id' => 22,  'email' => 'msevier@kimmel.com'],
        ['central_id' => 57, 'walter_id' => 292, 'email' =>  'msternal@kimmel.com'],
        ['central_id' => 58, 'walter_id' => 81,  'email' => 'mthurman@kimmel.com'],
        ['central_id' => 59, 'walter_id' => 75,  'email' => 'psamuels@kimmel.com'],
        ['central_id' => 60, 'walter_id' => 23,  'email' => 'rcowan@kimmel.com'],
        ['central_id' => 61, 'walter_id' =>  56, 'email' => 'rlaibson@kimmel.com'],
        ['central_id' => 62, 'walter_id' =>  79, 'email' => 'rstein@kimmel.com'],
        ['central_id' => 63, 'walter_id' =>   null, 'email' => 'runner@kimmel.com'],
        ['central_id' => 64, 'walter_id' =>  36, 'email' => 'tchandler@kimmel.com'],
        ['central_id' => 65, 'walter_id' =>  6, 'email' => 'tim@kimmel.com'],
        ['central_id' => 66, 'walter_id' =>  121, 'email' => 'zcoddington@kimmel.com'],
        ['central_id' => 67, 'walter_id' =>  277, 'email' => 'zjaynes@kimmel.com'],
        ['central_id' => 68, 'walter_id' =>  322, 'email' => 'kripmaster@kimmel.com'],
        ['central_id' => 69, 'walter_id' =>   null, 'email' => 'barry@constructionjobs.com'],
        ['central_id' => 70, 'walter_id' =>   null, 'email' => 'dmanning@constructionjobs.com'],
        ['central_id' => 71, 'walter_id' =>   null, 'email' => 'dyel@constructionjobs.com'],
        ['central_id' => 72, 'walter_id' =>   null, 'email' => 'ekline@constructionjobs.com'],
        ['central_id' => 73, 'walter_id' =>  53, 'email' => 'jcalloway@kimmel.com'],
        ['central_id' => 74, 'walter_id' =>   null, 'email' => 'zsilveira@kimmel.com'],
        ['central_id' => 75, 'walter_id' =>  338, 'email' => 'ahuntsman@kimmel.com'],
        ['central_id' => 76, 'walter_id' =>  297, 'email' => 'clasher@kimmel.com'],
        ['central_id' => 77, 'walter_id' =>  null, 'email' => 'quintin@kimmel.com'],
        ['central_id' => 78, 'walter_id' =>   null, 'email' => 'vasbinder@kimmel.com'],
        ['central_id' => 79, 'walter_id' =>  312, 'email' => 'ajones@constructionjobs.com'],
        ['central_id' => 80, 'walter_id' =>  313, 'email' => 'wsnoddy@kimmel.com'],
        ['central_id' => 81, 'walter_id' =>  136, 'email' => 'jdubac@kimmel.com'],
        ['central_id' => 82, 'walter_id' =>  318, 'email' => 'ccowan@kimmel.com'],
        ['central_id' => 83, 'walter_id' =>  321, 'email' => 'smoreland@kimmel.com'],
        ['central_id' => 84, 'walter_id' =>  325, 'email' => 'teaton@kimmel.com'],
        ['central_id' => 85, 'walter_id' =>  327, 'email' => 'jhudson@kimmel.com'],
        ['central_id' => 86, 'walter_id' =>  329, 'email' => 'skerschen@kimmel.com'],
        ['central_id' => 87, 'walter_id' =>  347, 'email' => 'jkcalloway@kimmel.com'],
        ['central_id' => 88, 'walter_id' =>  330, 'email' => 'rstevenson@kimmel.com'],
        ['central_id' => 89, 'walter_id' =>  332, 'email' => 'pnarron@kimmel.com'],
        ['central_id' => 90, 'walter_id' =>  333, 'email' => 'adavis@kimmel.com'],
        ['central_id' => 91, 'walter_id' =>  null, 'email' => 'eknowlton@kimmel.com'],
        ['central_id' => 92, 'walter_id' =>  3, 'email' => 'josh@constructionjobs.com'],
        ['central_id' => 93, 'walter_id' => null, 'email' => 'rcastner@kimmel.com'],
        ['central_id' => 94, 'walter_id' =>  340, 'email' => 'wkelley@kimmel.com'],
        ['central_id' => 95, 'walter_id' =>  342, 'email' =>  'alayman@kimmel.com'],
        ['central_id' => 96, 'walter_id' =>  null,  'email' =>  'hkiehna@kimmel.com'],
        ['central_id' => 97, 'walter_id' =>  349, 'email' =>  'smanning@kimmel.com'],
        ['central_id' => 98, 'walter_id' =>  348, 'email' =>  'qmanning@kimmel.com'],
        ['central_id' => 99, 'walter_id' =>  346, 'email' =>  'dcalloway@kimmel.com'],
        ['central_id' => 100, 'walter_id' => 352, 'email' => 'aherman@kimmel.com'],
        ['central_id' => 101, 'walter_id' => 353, 'email' => 'cpeek@kimmel.com'],
        ['central_id' => 102, 'walter_id' => 354, 'email' => 'jgraham@kimmel.com'],
        ['central_id' => 103, 'walter_id' => 355, 'email' => 'kshope@kimmel.com'],
        ['central_id' => 104, 'walter_id' => 356, 'email' => 'meller@kimmel.com'],
        ['central_id' => 105, 'walter_id' => 357, 'email' => 'sward@kimmel.com'],
        ['central_id' => 106, 'walter_id' => 358, 'email' => 'wgodfrey@kimmel.com'],
        ['central_id' => 107, 'walter_id' => null,  'email' => 'software@kimmel.com'],
        ['central_id' => 108, 'walter_id' => null,  'email' => 'development@kimmel.com'],
        ['central_id' => 109, 'walter_id' => 360, 'email' =>  'jgreer@kimmel.com'],
        ['central_id' => 110, 'walter_id' => 362, 'email' =>  'sgray@kimmel.com'],
        ['central_id' => 111, 'walter_id' => 364, 'email' =>  'jslagle@kimmel.com'],
        ['central_id' => 112, 'walter_id' => 365, 'email' =>  'mgunther@kimmel.com'],
        ['central_id' => 113, 'walter_id' => 366, 'email' =>  'jsatterfield@kimmel.com'],
        ['central_id' => 114, 'walter_id' => 367, 'email' =>  'wosmers@kimmel.com'],
        ['central_id' => 115, 'walter_id' => 363, 'email' =>  'abarkley@kimmel.com'],
        ['central_id' => 116, 'walter_id' => 369, 'email' =>  'bowen@kimmel.com'],
        ['central_id' => 117, 'walter_id' => 370, 'email' =>  'cnichols@kimmel.com'],
        ['central_id' => 118, 'walter_id' => 371, 'email' =>  'ssafford@kimmel.com'],
        ['central_id' => 119, 'walter_id' => null,  'email' => 'daitra@kimmel.com'],
        ['central_id' => 120, 'walter_id' => 178, 'email' =>  'kchandler@kimmel.com'],
        ['central_id' => 121, 'walter_id' => null,  'email' => 'galverez@kimmel.com'],
    ];
}

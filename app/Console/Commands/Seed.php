<?php

namespace App\Console\Commands;

use App\Event;
use App\EventMembership;
use App\Repositories\EventRepository;
use App\User;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Seed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:seed {--eventId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a test data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$this->seedEvents();
        $this->seedMembers((int)$this->option('eventId'));

        return 0;
    }

    protected function seedMembers(int $eventId)
    {
        $event = Event::find($eventId);
        if (empty($event)) {
            $this->error("Event #$eventId not found!");
            return;
        }

        $users = User::where('status', User::STATUS_ACTIVE)
            ->inRandomOrder()
            ->take(100)
            ->get();

        $membership = new EventMembership();
        foreach ($users as $user) {
            $this->info("Creating member; event_id=$eventId, user_id={$user->id}");
            $newEntry = $membership->newInstance([
                'event_id' => $eventId,
                'user_id' => $user->id
            ]);
            $newEntry->save();
        }
    }

    protected function seedEvents()
    {
        $citiesDe = json_decode($this->citiesDe, true);

        $count = Event::all()->count();
        $this->info('Events count: ' . $count);
        if (Event::where('status', 'active')->count() > 10000){
            return 0;
        }

        $userIds = User::where('status', User::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();

        $eventRepository = new EventRepository();
        for ($i = 0; $i < 10000; $i++) {

            $city = Arr::random($citiesDe);
            $lat = (float)$city['coords']['lat'];
            $lng = (float)$city['coords']['lon'];

            $data = [
                'title' => Str::random(16),
                'time' => '12:00',
                'event_date' => Carbon::now()->addDays(random_int(0, 60)),
                'type' => Arr::random([Event::TYPE_FUN, Event::TYPE_FRIENDS, Event::TYPE_BANG]),
                'chemsfriendly' => random_int(0, 1),
                'address_type' => Arr::random(['full_address', 'city_only']),
                'description' => 'description',
                'location' => 'location',
                'lat' => $lat,
                'lng' => $lng,
                'address' => 'address',
                'locality' => $city['name'],
                'state' => $city['state'],
                'is_profile_linked' => random_int(0, 1),
                'country' => 'Germany',
                'country_code' => 'DE',
                'user_id' => Arr::random($userIds),
                'gps_geom' => Helper::getGpsGeom($lng, $lat),
                'location' => new Point($lat, $lng, 4326),	// (lat, lng)
                'photo' => 'c/cN/cNbJUXmTDT4swUNWp9BN7CW8yLOA7sBitaqIgrjj'
            ];

            $event = $eventRepository->createEvent($data);
            $this->info("Created {$event->id}, {$city['name']}, $lat/$lng");
        }
    }

    protected $citiesDe = '[
  {
    "area": "160.85",
    "coords": {
      "lat": "50.77556",
      "lon": "6.08361"
    },
    "district": "Aachen",
    "name": "Aachen",
    "population": "247380",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "10.69",
    "coords": {
      "lat": "47.84556",
      "lon": "8.85167"
    },
    "district": "Konstanz",
    "name": "Aach",
    "population": "2296",
    "state": "Baden-Württemberg"
  },
  {
    "area": "146.63",
    "coords": {
      "lat": "48.833",
      "lon": "10.100"
    },
    "district": "Ostalbkreis",
    "name": "Aalen",
    "population": "68456",
    "state": "Baden-Württemberg"
  },
  {
    "area": "48.39",
    "coords": {
      "lat": "49.250",
      "lon": "10.967"
    },
    "district": "Roth",
    "name": "Abenberg",
    "population": "5511",
    "state": "Bavaria"
  },
  {
    "area": "60.29",
    "coords": {
      "lat": "48.800",
      "lon": "11.850"
    },
    "district": "Kelheim",
    "name": "Abensberg",
    "population": "13946",
    "state": "Bavaria"
  },
  {
    "area": "65.24",
    "coords": {
      "lat": "48.633",
      "lon": "8.067"
    },
    "district": "Ortenaukreis",
    "name": "Achern",
    "population": "25630",
    "state": "Baden-Württemberg"
  },
  {
    "area": "68.01",
    "coords": {
      "lat": "53.06528",
      "lon": "9.03417"
    },
    "district": "Verden",
    "name": "Achim",
    "population": "31911",
    "state": "Lower Saxony"
  },
  {
    "area": "43.84",
    "coords": {
      "lat": "49.40472",
      "lon": "9.38917"
    },
    "district": "Neckar-Odenwald-Kreis",
    "name": "Adelsheim",
    "population": "4973",
    "state": "Baden-Württemberg"
  },
  {
    "area": "18.56",
    "coords": {
      "lat": "50.383",
      "lon": "6.933"
    },
    "district": "Ahrweiler",
    "name": "Adenau",
    "population": "2962",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "42.92",
    "coords": {
      "lat": "50.317",
      "lon": "12.267"
    },
    "district": "Vogtlandkreis",
    "name": "Adorf",
    "population": "4919",
    "state": "Saxony"
  },
  {
    "area": "151.22",
    "coords": {
      "lat": "52.067",
      "lon": "7.000"
    },
    "district": "Borken",
    "name": "Ahaus",
    "population": "39223",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "123.14",
    "coords": {
      "lat": "51.76333",
      "lon": "7.89111"
    },
    "district": "Warendorf",
    "name": "Ahlen",
    "population": "52582",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "35.3",
    "coords": {
      "lat": "53.67472",
      "lon": "10.24111"
    },
    "district": "Stormarn",
    "name": "Ahrensburg",
    "population": "33472",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "92.97",
    "coords": {
      "lat": "48.45000",
      "lon": "11.13333"
    },
    "district": "Aichach-Friedberg",
    "name": "Aichach",
    "population": "21434",
    "state": "Bavaria"
  },
  {
    "area": "23.64",
    "coords": {
      "lat": "48.62278",
      "lon": "9.23722"
    },
    "district": "Esslingen",
    "name": "Aichtal",
    "population": "9901",
    "state": "Baden-Württemberg"
  },
  {
    "area": "59.91",
    "coords": {
      "lat": "51.85000",
      "lon": "12.05000"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Aken",
    "population": "7567",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "134.41",
    "coords": {
      "lat": "48.21194",
      "lon": "9.02389"
    },
    "district": "Zollernalbkreis",
    "name": "Albstadt",
    "population": "45327",
    "state": "Baden-Württemberg"
  },
  {
    "area": "72.86",
    "coords": {
      "lat": "51.98861",
      "lon": "9.82694"
    },
    "district": "Hildesheim",
    "name": "Alfeld",
    "population": "18626",
    "state": "Lower Saxony"
  },
  {
    "area": "22.01",
    "coords": {
      "lat": "50.67889",
      "lon": "8.82444"
    },
    "district": "Giessen",
    "name": "Allendorf",
    "population": "4090",
    "state": "Hesse"
  },
  {
    "area": "149.77",
    "coords": {
      "lat": "51.400",
      "lon": "11.383"
    },
    "district": "Mansfeld-Südharz",
    "name": "Allstedt",
    "population": "7745",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "64.55",
    "coords": {
      "lat": "48.34611",
      "lon": "8.40389"
    },
    "district": "Freudenstadt",
    "name": "Alpirsbach",
    "population": "6304",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.658",
    "coords": {
      "lat": "50.883",
      "lon": "6.167"
    },
    "district": "Aachen",
    "name": "Alsdorf",
    "population": "47018",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "129.69",
    "coords": {
      "lat": "50.75111",
      "lon": "9.27111"
    },
    "district": "Vogelsbergkreis",
    "name": "Alsfeld",
    "population": "15989",
    "state": "Hesse"
  },
  {
    "area": "23.640",
    "coords": {
      "lat": "51.700",
      "lon": "11.667"
    },
    "district": "Salzlandkreis",
    "name": "Alsleben",
    "population": "2526",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "46.59",
    "coords": {
      "lat": "49.38750",
      "lon": "11.35694"
    },
    "district": "Nürnberger Land",
    "name": "Altdorf bei Nürnberg",
    "population": "15245",
    "state": "Bavaria"
  },
  {
    "area": "44.29",
    "coords": {
      "lat": "51.30000",
      "lon": "7.66667"
    },
    "district": "Märkischer Kreis",
    "name": "Altena",
    "population": "16922",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "145.81",
    "coords": {
      "lat": "50.76444",
      "lon": "13.75778"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Altenberg",
    "population": "7937",
    "state": "Saxony"
  },
  {
    "area": "45.6",
    "coords": {
      "lat": "50.98500",
      "lon": "12.43333"
    },
    "district": "Altenburger Land",
    "name": "Altenburg",
    "population": "32074",
    "state": "Thuringia"
  },
  {
    "area": "11.00",
    "coords": {
      "lat": "50.68722",
      "lon": "7.64556"
    },
    "district": "Altenkirchen",
    "name": "Altenkirchen",
    "population": "6263",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "53.21",
    "coords": {
      "lat": "48.58639",
      "lon": "8.60472"
    },
    "district": "Calw",
    "name": "Altensteig",
    "population": "10799",
    "state": "Baden-Württemberg"
  },
  {
    "area": "52.83",
    "coords": {
      "lat": "53.667",
      "lon": "13.250"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Altentreptow",
    "population": "5307",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "106.21",
    "coords": {
      "lat": "52.56667",
      "lon": "13.73306"
    },
    "district": "Märkisch-Oderland",
    "name": "Altlandsberg",
    "population": "9490",
    "state": "Brandenburg"
  },
  {
    "area": "23.43",
    "coords": {
      "lat": "48.22667",
      "lon": "12.67833"
    },
    "district": "Altötting",
    "name": "Altötting",
    "population": "12969",
    "state": "Bavaria"
  },
  {
    "area": "59.33",
    "coords": {
      "lat": "50.067",
      "lon": "9.067"
    },
    "district": "Aschaffenburg",
    "name": "Alzenau",
    "population": "18469",
    "state": "Bavaria"
  },
  {
    "area": "35.21",
    "coords": {
      "lat": "49.74583",
      "lon": "8.11528"
    },
    "district": "Alzey-Worms",
    "name": "Alzey",
    "population": "18535",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "50.04",
    "coords": {
      "lat": "49.44444",
      "lon": "11.84833"
    },
    "district": "Urban district",
    "name": "Amberg",
    "population": "41970",
    "state": "Bavaria"
  },
  {
    "area": "50.92",
    "coords": {
      "lat": "49.633",
      "lon": "9.217"
    },
    "district": "Miltenberg",
    "name": "Amorbach",
    "population": "3990",
    "state": "Bavaria"
  },
  {
    "area": "69.73",
    "coords": {
      "lat": "51.05139",
      "lon": "10.24472"
    },
    "district": "Wartburgkreis",
    "name": "Amt Creuzburg",
    "population": "4732",
    "state": "Thuringia"
  },
  {
    "area": "43.95",
    "coords": {
      "lat": "50.79778",
      "lon": "8.92306"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Amöneburg",
    "population": "5096",
    "state": "Hesse"
  },
  {
    "area": "95.29",
    "coords": {
      "lat": "51.300",
      "lon": "11.217"
    },
    "district": "Kyffhäuserkreis",
    "name": "An der Schmücke",
    "population": "6030",
    "state": "Thuringia"
  },
  {
    "area": "53.23",
    "coords": {
      "lat": "50.43972",
      "lon": "7.40167"
    },
    "district": "Mayen-Koblenz",
    "name": "Andernach",
    "population": "29966",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "326.44",
    "coords": {
      "lat": "53.03333",
      "lon": "14.00000"
    },
    "district": "Uckermark",
    "name": "Angermünde",
    "population": "13744",
    "state": "Brandenburg"
  },
  {
    "area": "56.57",
    "coords": {
      "lat": "53.850",
      "lon": "13.683"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Anklam",
    "population": "12385",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "27.70",
    "coords": {
      "lat": "50.58000",
      "lon": "13.00222"
    },
    "district": "Erzgebirgskreis",
    "name": "Annaberg-Buchholz",
    "population": "19769",
    "state": "Saxony"
  },
  {
    "area": "224.19",
    "coords": {
      "lat": "51.73278",
      "lon": "13.04556"
    },
    "district": "Wittenberg",
    "name": "Annaburg",
    "population": "6731",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "39.87",
    "coords": {
      "lat": "49.200",
      "lon": "7.967"
    },
    "district": "Südliche Weinstraße",
    "name": "Annweiler am Trifels",
    "population": "7081",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "99.92",
    "coords": {
      "lat": "49.30000",
      "lon": "10.58333"
    },
    "district": "Urban district",
    "name": "Ansbach",
    "population": "41847",
    "state": "Bavaria"
  },
  {
    "area": "46.15",
    "coords": {
      "lat": "51.017",
      "lon": "11.517"
    },
    "district": "Weimarer Land",
    "name": "Apolda",
    "population": "22012",
    "state": "Thuringia"
  },
  {
    "area": "269.68",
    "coords": {
      "lat": "52.8767",
      "lon": "11.4867"
    },
    "district": "Altmarkkreis Salzwedel",
    "name": "Arendsee",
    "population": "6750",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "30.72",
    "coords": {
      "lat": "52.667",
      "lon": "12.000"
    },
    "district": "Stendal",
    "name": "Arneburg",
    "population": "1500",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "0.45",
    "coords": {
      "lat": "54.63000",
      "lon": "9.93139"
    },
    "district": "Schleswig-Flensburg",
    "name": "ArnisArnæs",
    "population": "284",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "193.45",
    "coords": {
      "lat": "51.383",
      "lon": "8.083"
    },
    "district": "Hochsauerland",
    "name": "Arnsberg",
    "population": "73628",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "105.11",
    "coords": {
      "lat": "50.83417",
      "lon": "10.94639"
    },
    "district": "Ilm-Kreis",
    "name": "Arnstadt",
    "population": "27304",
    "state": "Thuringia"
  },
  {
    "area": "112.12",
    "coords": {
      "lat": "49.967",
      "lon": "9.983"
    },
    "district": "Main-Spessart",
    "name": "Arnstein",
    "population": "8125",
    "state": "Bavaria"
  },
  {
    "area": "121.71",
    "coords": {
      "lat": "51.683",
      "lon": "11.467"
    },
    "district": "Mansfeld-Südharz",
    "name": "Arnstein",
    "population": "6616",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "45.05",
    "coords": {
      "lat": "51.36667",
      "lon": "11.30000"
    },
    "district": "Kyffhäuserkreis",
    "name": "Artern",
    "population": "6799",
    "state": "Thuringia"
  },
  {
    "area": "43.22",
    "coords": {
      "lat": "50.067",
      "lon": "12.183"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Arzberg",
    "population": "5152",
    "state": "Bavaria"
  },
  {
    "area": "62.45",
    "coords": {
      "lat": "49.96667",
      "lon": "9.15000"
    },
    "district": "Urban district",
    "name": "Aschaffenburg",
    "population": "70527",
    "state": "Bavaria"
  },
  {
    "area": "156.31",
    "coords": {
      "lat": "51.750",
      "lon": "11.467"
    },
    "district": "Salzlandkreis",
    "name": "Aschersleben",
    "population": "27220",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "5.8",
    "coords": {
      "lat": "48.90000",
      "lon": "9.13333"
    },
    "district": "Ludwigsburg",
    "name": "Asperg",
    "population": "13480",
    "state": "Baden-Württemberg"
  },
  {
    "area": "97.86",
    "coords": {
      "lat": "51.117",
      "lon": "7.900"
    },
    "district": "Olpe",
    "name": "Attendorn",
    "population": "24367",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "17.54",
    "coords": {
      "lat": "49.533",
      "lon": "10.050"
    },
    "district": "Würzburg",
    "name": "Aub",
    "population": "1466",
    "state": "Bavaria"
  },
  {
    "area": "36.43",
    "coords": {
      "lat": "50.600",
      "lon": "12.683"
    },
    "district": "Erzgebirgskreis",
    "name": "Aue-Bad Schlema",
    "population": "20519",
    "state": "Saxony"
  },
  {
    "area": "70.33",
    "coords": {
      "lat": "49.683",
      "lon": "11.617"
    },
    "district": "Amberg-Sulzbach",
    "name": "Auerbach in der Oberpfalz",
    "population": "8818",
    "state": "Bavaria"
  },
  {
    "area": "55.52",
    "coords": {
      "lat": "50.50944",
      "lon": "12.40000"
    },
    "district": "Vogtlandkreis",
    "name": "Auerbach",
    "population": "18357",
    "state": "Saxony"
  },
  {
    "area": "146.84",
    "coords": {
      "lat": "48.367",
      "lon": "10.900"
    },
    "district": "Urban district",
    "name": "Augsburg",
    "population": "295135",
    "state": "Bavaria"
  },
  {
    "area": "23.42",
    "coords": {
      "lat": "50.81444",
      "lon": "13.10000"
    },
    "district": "Mittelsachsen",
    "name": "Augustusburg",
    "population": "4513",
    "state": "Saxony"
  },
  {
    "area": "52.36",
    "coords": {
      "lat": "47.95417",
      "lon": "9.63889"
    },
    "district": "Ravensburg",
    "name": "Aulendorf",
    "population": "10180",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.75",
    "coords": {
      "lat": "50.700",
      "lon": "11.900"
    },
    "district": "Greiz",
    "name": "Auma-Weidatal",
    "population": "3491",
    "state": "Thuringia"
  },
  {
    "area": "197.21",
    "coords": {
      "lat": "53.47139",
      "lon": "7.48361"
    },
    "district": "Aurich",
    "name": "Aurich",
    "population": "41991",
    "state": "Lower Saxony"
  },
  {
    "area": "43.57",
    "coords": {
      "lat": "50.583",
      "lon": "8.467"
    },
    "district": "Lahn-Dill",
    "name": "Aßlar",
    "population": "13656",
    "state": "Hesse"
  },
  {
    "area": "66.87",
    "coords": {
      "lat": "49.96667",
      "lon": "8.95000"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Babenhausen",
    "population": "16834",
    "state": "Hesse"
  },
  {
    "area": "23.65",
    "coords": {
      "lat": "50.06667",
      "lon": "7.76667"
    },
    "district": "Mainz-Bingen",
    "name": "Bacharach",
    "population": "1868",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "39.37",
    "coords": {
      "lat": "48.94639",
      "lon": "9.43056"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Backnang",
    "population": "37253",
    "state": "Baden-Württemberg"
  },
  {
    "area": "41.55",
    "coords": {
      "lat": "47.867",
      "lon": "12.017"
    },
    "district": "Rosenheim",
    "name": "Bad Aibling",
    "population": "19100",
    "state": "Bavaria"
  },
  {
    "area": "126.32",
    "coords": {
      "lat": "51.367",
      "lon": "9.017"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Bad Arolsen",
    "population": "15470",
    "state": "Hesse"
  },
  {
    "area": "234.82",
    "coords": {
      "lat": "52.14222",
      "lon": "12.59556"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Bad Belzig",
    "population": "11144",
    "state": "Brandenburg"
  },
  {
    "area": "100.16",
    "coords": {
      "lat": "52.30306",
      "lon": "7.15972"
    },
    "district": "Grafschaft Bentheim",
    "name": "Bad Bentheim",
    "population": "15486",
    "state": "Lower Saxony"
  },
  {
    "area": "10.71",
    "coords": {
      "lat": "49.10280",
      "lon": "7.99913"
    },
    "district": "Südliche Weinstraße",
    "name": "Bad Bergzabern",
    "population": "8124",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "55.27",
    "coords": {
      "lat": "50.90000",
      "lon": "11.28083"
    },
    "district": "Weimarer Land",
    "name": "Bad Berka",
    "population": "7503",
    "state": "Thuringia"
  },
  {
    "area": "275.33",
    "coords": {
      "lat": "51.04972",
      "lon": "8.40000"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Bad Berleburg",
    "population": "19446",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "38.25",
    "coords": {
      "lat": "50.04778",
      "lon": "11.67222"
    },
    "district": "Bayreuth",
    "name": "Bad Berneck i.Fichtelgebirge",
    "population": "4371",
    "state": "Bavaria"
  },
  {
    "area": "48.01",
    "coords": {
      "lat": "53.07917",
      "lon": "10.58333"
    },
    "district": "Uelzen",
    "name": "Bad Bevensen",
    "population": "9122",
    "state": "Lower Saxony"
  },
  {
    "area": "49.76",
    "coords": {
      "lat": "51.200",
      "lon": "11.567"
    },
    "district": "Burgenlandkreis",
    "name": "Bad Bibra",
    "population": "2718",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "35.56",
    "coords": {
      "lat": "50.68333",
      "lon": "11.26667"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Bad Blankenburg",
    "population": "6407",
    "state": "Thuringia"
  },
  {
    "area": "24.14",
    "coords": {
      "lat": "53.91861",
      "lon": "9.88444"
    },
    "district": "Segeberg",
    "name": "Bad Bramstedt",
    "population": "14420",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "19.94",
    "coords": {
      "lat": "50.50917",
      "lon": "7.29639"
    },
    "district": "Ahrweiler",
    "name": "Bad Breisig",
    "population": "9460",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "23.73",
    "coords": {
      "lat": "50.30944",
      "lon": "9.79056"
    },
    "district": "Bad Kissingen",
    "name": "Bad Brückenau",
    "population": "6449",
    "state": "Bavaria"
  },
  {
    "area": "23.77",
    "coords": {
      "lat": "48.06611",
      "lon": "9.61000"
    },
    "district": "Biberach",
    "name": "Bad Buchau",
    "population": "4294",
    "state": "Baden-Württemberg"
  },
  {
    "area": "54.64",
    "coords": {
      "lat": "50.300",
      "lon": "8.267"
    },
    "district": "Limburg-Weilburg",
    "name": "Bad Camberg",
    "population": "14263",
    "state": "Hesse"
  },
  {
    "area": "32.74",
    "coords": {
      "lat": "54.10694",
      "lon": "11.90528"
    },
    "district": "Rostock",
    "name": "Bad Doberan",
    "population": "12491",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "115.07",
    "coords": {
      "lat": "51.733",
      "lon": "9.017"
    },
    "district": "Höxter",
    "name": "Bad Driburg",
    "population": "19002",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "45.45",
    "coords": {
      "lat": "51.59194",
      "lon": "12.58528"
    },
    "district": "Nordsachsen",
    "name": "Bad Düben",
    "population": "7865",
    "state": "Saxony"
  },
  {
    "area": "102.67",
    "coords": {
      "lat": "49.45944",
      "lon": "8.16806"
    },
    "district": "Bad Dürkheim",
    "name": "Bad Dürkheim",
    "population": "18476",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "36.13",
    "coords": {
      "lat": "51.283",
      "lon": "12.067"
    },
    "district": "Saalekreis",
    "name": "Bad Dürrenberg",
    "population": "11643",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "62.09",
    "coords": {
      "lat": "48.017",
      "lon": "8.533"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Bad Dürrheim",
    "population": "13260",
    "state": "Baden-Württemberg"
  },
  {
    "area": "19.77",
    "coords": {
      "lat": "50.28194",
      "lon": "12.23472"
    },
    "district": "Vogtlandkreis",
    "name": "Bad Elster",
    "population": "3654",
    "state": "Saxony"
  },
  {
    "area": "15.36",
    "coords": {
      "lat": "50.33806",
      "lon": "7.71056"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Bad Ems",
    "population": "9681",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "63.15",
    "coords": {
      "lat": "52.86750",
      "lon": "9.69667"
    },
    "district": "Heidekreis",
    "name": "Bad Fallingbostel",
    "population": "11852",
    "state": "Lower Saxony"
  },
  {
    "area": "91.07",
    "coords": {
      "lat": "51.35583",
      "lon": "11.10111"
    },
    "district": "Kyffhäuserkreis",
    "name": "Bad Frankenhausen",
    "population": "10230",
    "state": "Thuringia"
  },
  {
    "area": "131.73",
    "coords": {
      "lat": "52.78556",
      "lon": "14.03250"
    },
    "district": "Märkisch-Oderland",
    "name": "Bad Freienwalde",
    "population": "12365",
    "state": "Brandenburg"
  },
  {
    "area": "24.70",
    "coords": {
      "lat": "49.233",
      "lon": "9.217"
    },
    "district": "Heilbronn",
    "name": "Bad Friedrichshall",
    "population": "19264",
    "state": "Baden-Württemberg"
  },
  {
    "area": "9.049",
    "coords": {
      "lat": "51.87194",
      "lon": "10.02528"
    },
    "district": "Northeim",
    "name": "Bad Gandersheim",
    "population": "9823",
    "state": "Lower Saxony"
  },
  {
    "area": "88.75",
    "coords": {
      "lat": "50.85833",
      "lon": "13.95000"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Bad Gottleuba-Berggießhübel",
    "population": "5633",
    "state": "Saxony"
  },
  {
    "area": "70.18",
    "coords": {
      "lat": "48.450",
      "lon": "13.200"
    },
    "district": "Passau",
    "name": "Bad Griesbach i.Rottal",
    "population": "9055",
    "state": "Bavaria"
  },
  {
    "area": "65.42",
    "coords": {
      "lat": "51.88111",
      "lon": "10.56222"
    },
    "district": "Goslar",
    "name": "Bad Harzburg",
    "population": "21945",
    "state": "Lower Saxony"
  },
  {
    "area": "33.03",
    "coords": {
      "lat": "48.80056",
      "lon": "8.44083"
    },
    "district": "Calw",
    "name": "Bad Herrenalb",
    "population": "7948",
    "state": "Baden-Württemberg"
  },
  {
    "area": "73.82",
    "coords": {
      "lat": "50.86833",
      "lon": "9.70750"
    },
    "district": "Hersfeld-Rotenburg",
    "name": "Bad Hersfeld",
    "population": "29800",
    "state": "Hesse"
  },
  {
    "area": "51.17",
    "coords": {
      "lat": "50.21667",
      "lon": "8.60000"
    },
    "district": "Hochtaunuskreis",
    "name": "Bad Homburg v. d. Höhe",
    "population": "54248",
    "state": "Hesse"
  },
  {
    "area": "48.3",
    "coords": {
      "lat": "50.64500",
      "lon": "7.22694"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Bad Honnef",
    "population": "25816",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "20.09",
    "coords": {
      "lat": "50.51778",
      "lon": "7.30861"
    },
    "district": "Neuwied",
    "name": "Bad Hönningen",
    "population": "5920",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "36.44",
    "coords": {
      "lat": "52.15917",
      "lon": "8.04722"
    },
    "district": "Osnabrück",
    "name": "Bad Iburg",
    "population": "10661",
    "state": "Lower Saxony"
  },
  {
    "area": "14.85",
    "coords": {
      "lat": "51.633",
      "lon": "9.450"
    },
    "district": "Kassel",
    "name": "Bad Karlshafen",
    "population": "3650",
    "state": "Hesse"
  },
  {
    "area": "69.42",
    "coords": {
      "lat": "50.200",
      "lon": "10.067"
    },
    "district": "Bad Kissingen",
    "name": "Bad Kissingen",
    "population": "22444",
    "state": "Bavaria"
  },
  {
    "area": "55.63",
    "coords": {
      "lat": "49.850",
      "lon": "7.867"
    },
    "district": "Bad Kreuznach",
    "name": "Bad Kreuznach",
    "population": "50948",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "35.66",
    "coords": {
      "lat": "47.917",
      "lon": "7.700"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Bad Krozingen",
    "population": "19644",
    "state": "Baden-Württemberg"
  },
  {
    "area": "69.52",
    "coords": {
      "lat": "50.300",
      "lon": "10.417"
    },
    "district": "Rhön-Grabfeld",
    "name": "Bad Königshofen",
    "population": "5984",
    "state": "Bavaria"
  },
  {
    "area": "46.73",
    "coords": {
      "lat": "49.750",
      "lon": "9.017"
    },
    "district": "Odenwaldkreis",
    "name": "Bad König",
    "population": "9762",
    "state": "Hesse"
  },
  {
    "area": "16.85",
    "coords": {
      "lat": "50.93056",
      "lon": "12.00972"
    },
    "district": "Greiz",
    "name": "Bad Köstritz",
    "population": "3513",
    "state": "Thuringia"
  },
  {
    "area": "62.17",
    "coords": {
      "lat": "49.17694",
      "lon": "12.85500"
    },
    "district": "Cham",
    "name": "Bad Kötzting",
    "population": "7498",
    "state": "Bavaria"
  },
  {
    "area": "135.76",
    "coords": {
      "lat": "50.93028",
      "lon": "8.41667"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Bad Laasphe",
    "population": "13565",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "129.37",
    "coords": {
      "lat": "51.10806",
      "lon": "10.64667"
    },
    "district": "Unstrut-Hainich-Kreis",
    "name": "Bad Langensalza",
    "population": "17441",
    "state": "Thuringia"
  },
  {
    "area": "85.36",
    "coords": {
      "lat": "51.367",
      "lon": "11.833"
    },
    "district": "Saalekreis",
    "name": "Bad Lauchstädt",
    "population": "8783",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "69.79",
    "coords": {
      "lat": "51.14472",
      "lon": "12.64528"
    },
    "district": "Leipzig",
    "name": "Bad Lausick",
    "population": "8005",
    "state": "Saxony"
  },
  {
    "area": "41.54",
    "coords": {
      "lat": "51.63167",
      "lon": "10.47056"
    },
    "district": "Göttingen",
    "name": "Bad Lauterberg",
    "population": "10269",
    "state": "Lower Saxony"
  },
  {
    "area": "48.81",
    "coords": {
      "lat": "50.81444",
      "lon": "10.35417"
    },
    "district": "Wartburgkreis",
    "name": "Bad Liebenstein",
    "population": "7786",
    "state": "Thuringia"
  },
  {
    "area": "138.41",
    "coords": {
      "lat": "51.51667",
      "lon": "13.40000"
    },
    "district": "Elbe-Elster",
    "name": "Bad Liebenwerda",
    "population": "9188",
    "state": "Brandenburg"
  },
  {
    "area": "33.80",
    "coords": {
      "lat": "48.77417",
      "lon": "8.73139"
    },
    "district": "Calw",
    "name": "Bad Liebenzell",
    "population": "9573",
    "state": "Baden-Württemberg"
  },
  {
    "area": "50.99",
    "coords": {
      "lat": "51.78333",
      "lon": "8.81667"
    },
    "district": "Paderborn",
    "name": "Bad Lippspringe",
    "population": "16089",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "48.94",
    "coords": {
      "lat": "50.45000",
      "lon": "11.65000"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Bad Lobenstein",
    "population": "5931",
    "state": "Thuringia"
  },
  {
    "area": "9.96",
    "coords": {
      "lat": "50.65194",
      "lon": "7.95222"
    },
    "district": "Westerwaldkreis",
    "name": "Bad Marienberg",
    "population": "5961",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "129.97",
    "coords": {
      "lat": "49.50000",
      "lon": "9.76667"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Bad Mergentheim",
    "population": "23704",
    "state": "Baden-Württemberg"
  },
  {
    "area": "15.35",
    "coords": {
      "lat": "51.55000",
      "lon": "14.71667"
    },
    "district": "Görlitz",
    "name": "Bad Muskau",
    "population": "3716",
    "state": "Saxony"
  },
  {
    "area": "107.69",
    "coords": {
      "lat": "52.19917",
      "lon": "9.46528"
    },
    "district": "Hameln-Pyrmont",
    "name": "Bad Münder",
    "population": "17465",
    "state": "Lower Saxony"
  },
  {
    "area": "150.84",
    "coords": {
      "lat": "50.55306",
      "lon": "6.76611"
    },
    "district": "Euskirchen",
    "name": "Bad Münstereifel",
    "population": "17299",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "32.55",
    "coords": {
      "lat": "50.367",
      "lon": "8.750"
    },
    "district": "Wetteraukreis",
    "name": "Bad Nauheim",
    "population": "32163",
    "state": "Hesse"
  },
  {
    "area": "23",
    "coords": {
      "lat": "52.33694",
      "lon": "9.37861"
    },
    "district": "Schaumburg",
    "name": "Bad Nenndorf",
    "population": "11144",
    "state": "Lower Saxony"
  },
  {
    "area": "63.4",
    "coords": {
      "lat": "50.54472",
      "lon": "7.11333"
    },
    "district": "Ahrweiler",
    "name": "Bad Neuenahr-Ahrweiler",
    "population": "28251",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "36.79",
    "coords": {
      "lat": "50.32194",
      "lon": "10.21611"
    },
    "district": "Rhön-Grabfeld",
    "name": "Bad Neustadt an der Saale",
    "population": "15411",
    "state": "Bavaria"
  },
  {
    "area": "64.8",
    "coords": {
      "lat": "52.200",
      "lon": "8.800"
    },
    "district": "Minden-Lübbecke",
    "name": "Bad Oeynhausen",
    "population": "48702",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "52.6",
    "coords": {
      "lat": "53.81167",
      "lon": "10.37417"
    },
    "district": "Stormarn",
    "name": "Bad Oldesloe",
    "population": "24744",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "47.78",
    "coords": {
      "lat": "50.217",
      "lon": "9.350"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Bad Orb",
    "population": "10020",
    "state": "Hesse"
  },
  {
    "area": "61.96",
    "coords": {
      "lat": "51.98667",
      "lon": "9.26361"
    },
    "district": "Hameln-Pyrmont",
    "name": "Bad Pyrmont",
    "population": "19090",
    "state": "Lower Saxony"
  },
  {
    "area": "73.55",
    "coords": {
      "lat": "49.233",
      "lon": "9.100"
    },
    "district": "Heilbronn",
    "name": "Bad Rappenau",
    "population": "21398",
    "state": "Baden-Württemberg"
  },
  {
    "area": "39.44",
    "coords": {
      "lat": "47.72472",
      "lon": "12.87694"
    },
    "district": "Berchtesgadener Land",
    "name": "Bad Reichenhall",
    "population": "18278",
    "state": "Bavaria"
  },
  {
    "area": "77.65",
    "coords": {
      "lat": "50.333",
      "lon": "10.783"
    },
    "district": "Coburg",
    "name": "Bad Rodach",
    "population": "6394",
    "state": "Bavaria"
  },
  {
    "area": "33.13",
    "coords": {
      "lat": "51.59694",
      "lon": "10.55222"
    },
    "district": "Göttingen",
    "name": "Bad Sachsa",
    "population": "7346",
    "state": "Lower Saxony"
  },
  {
    "area": "67.11",
    "coords": {
      "lat": "52.06528",
      "lon": "10.00917"
    },
    "district": "Hildesheim",
    "name": "Bad Salzdetfurth",
    "population": "13145",
    "state": "Lower Saxony"
  },
  {
    "area": "100.05",
    "coords": {
      "lat": "52.08750",
      "lon": "8.75056"
    },
    "district": "Lippe",
    "name": "Bad Salzuflen",
    "population": "54127",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "98.5",
    "coords": {
      "lat": "50.81167",
      "lon": "10.23333"
    },
    "district": "Wartburgkreis",
    "name": "Bad Salzungen",
    "population": "20244",
    "state": "Thuringia"
  },
  {
    "area": "97.34",
    "coords": {
      "lat": "48.01750",
      "lon": "9.50028"
    },
    "district": "Sigmaringen",
    "name": "Bad Saulgau",
    "population": "17509",
    "state": "Baden-Württemberg"
  },
  {
    "area": "46.77",
    "coords": {
      "lat": "50.917",
      "lon": "14.150"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Bad Schandau",
    "population": "3622",
    "state": "Saxony"
  },
  {
    "area": "159.99",
    "coords": {
      "lat": "51.68806",
      "lon": "12.73750"
    },
    "district": "Wittenberg",
    "name": "Bad Schmiedeberg",
    "population": "8222",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "55.02",
    "coords": {
      "lat": "48.00667",
      "lon": "9.65861"
    },
    "district": "Biberach",
    "name": "Bad Schussenried",
    "population": "8734",
    "state": "Baden-Württemberg"
  },
  {
    "area": "40.27",
    "coords": {
      "lat": "50.13333",
      "lon": "8.06667"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Bad Schwalbach",
    "population": "11187",
    "state": "Hesse"
  },
  {
    "area": "18.39",
    "coords": {
      "lat": "53.91944",
      "lon": "10.69750"
    },
    "district": "Ostholstein",
    "name": "Bad Schwartau",
    "population": "20036",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "18.87",
    "coords": {
      "lat": "53.917",
      "lon": "10.317"
    },
    "district": "Segeberg",
    "name": "Bad Segeberg",
    "population": "17267",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "54.06",
    "coords": {
      "lat": "49.78722",
      "lon": "7.65278"
    },
    "district": "Bad Kreuznach",
    "name": "Bad Sobernheim",
    "population": "6573",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "58.62",
    "coords": {
      "lat": "50.267",
      "lon": "9.367"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Bad Soden-Salmünster",
    "population": "13370",
    "state": "Hesse"
  },
  {
    "area": "12.55",
    "coords": {
      "lat": "50.133",
      "lon": "8.500"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Bad Soden",
    "population": "22645",
    "state": "Hesse"
  },
  {
    "area": "73.53",
    "coords": {
      "lat": "51.283",
      "lon": "9.983"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Bad Sooden-Allendorf",
    "population": "8675",
    "state": "Hesse"
  },
  {
    "area": "99.39",
    "coords": {
      "lat": "50.100",
      "lon": "10.967"
    },
    "district": "Lichtenfels",
    "name": "Bad Staffelstein",
    "population": "10389",
    "state": "Bavaria"
  },
  {
    "area": "91.14",
    "coords": {
      "lat": "51.08750",
      "lon": "11.62222"
    },
    "district": "Weimarer Land",
    "name": "Bad Sulza",
    "population": "7651",
    "state": "Thuringia"
  },
  {
    "area": "25.34",
    "coords": {
      "lat": "47.550",
      "lon": "7.950"
    },
    "district": "Waldshut",
    "name": "Bad Säckingen",
    "population": "17144",
    "state": "Baden-Württemberg"
  },
  {
    "area": "26.37",
    "coords": {
      "lat": "54.133",
      "lon": "12.667"
    },
    "district": "Vorpommern-Rügen",
    "name": "Bad Sülze",
    "population": "1710",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "25.1",
    "coords": {
      "lat": "48.68833",
      "lon": "8.68806"
    },
    "district": "Calw",
    "name": "Bad Teinach-Zavelstein",
    "population": "3094",
    "state": "Baden-Württemberg"
  },
  {
    "area": "27.27",
    "coords": {
      "lat": "51.15389",
      "lon": "10.83722"
    },
    "district": "Unstrut-Hainich-Kreis",
    "name": "Bad Tennstedt",
    "population": "2482",
    "state": "Thuringia"
  },
  {
    "area": "30.80",
    "coords": {
      "lat": "47.76028",
      "lon": "11.55667"
    },
    "district": "Bad Tölz-Wolfratshausen",
    "name": "Bad Tölz",
    "population": "18802",
    "state": "Bavaria"
  },
  {
    "area": "55.50",
    "coords": {
      "lat": "48.49306",
      "lon": "9.39861"
    },
    "district": "Reutlingen",
    "name": "Bad Urach",
    "population": "12472",
    "state": "Baden-Württemberg"
  },
  {
    "area": "25.65",
    "coords": {
      "lat": "50.17806",
      "lon": "8.73611"
    },
    "district": "Wetteraukreis",
    "name": "Bad Vilbel",
    "population": "33990",
    "state": "Hesse"
  },
  {
    "area": "108.54",
    "coords": {
      "lat": "47.92111",
      "lon": "9.75194"
    },
    "district": "Ravensburg",
    "name": "Bad Waldsee",
    "population": "20308",
    "state": "Baden-Württemberg"
  },
  {
    "area": "105.26",
    "coords": {
      "lat": "48.75028",
      "lon": "8.55056"
    },
    "district": "Calw",
    "name": "Bad Wildbad",
    "population": "10130",
    "state": "Baden-Württemberg"
  },
  {
    "area": "120.10",
    "coords": {
      "lat": "51.117",
      "lon": "9.117"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Bad Wildungen",
    "population": "17137",
    "state": "Hesse"
  },
  {
    "area": "79.21",
    "coords": {
      "lat": "52.95000",
      "lon": "11.94972"
    },
    "district": "Prignitz",
    "name": "Bad Wilsnack",
    "population": "2532",
    "state": "Brandenburg"
  },
  {
    "area": "19.38",
    "coords": {
      "lat": "49.233",
      "lon": "9.167"
    },
    "district": "Heilbronn",
    "name": "Bad Wimpfen",
    "population": "7359",
    "state": "Baden-Württemberg"
  },
  {
    "area": "78.26",
    "coords": {
      "lat": "49.500",
      "lon": "10.417"
    },
    "district": "Neustadt a.d.Aisch-Bad Windsheim",
    "name": "Bad Windsheim",
    "population": "12382",
    "state": "Bavaria"
  },
  {
    "area": "182.26",
    "coords": {
      "lat": "47.90944",
      "lon": "9.89944"
    },
    "district": "Ravensburg",
    "name": "Bad Wurzach",
    "population": "14651",
    "state": "Baden-Württemberg"
  },
  {
    "area": "57.80",
    "coords": {
      "lat": "48.00583",
      "lon": "10.59694"
    },
    "district": "Unterallgäu",
    "name": "Bad Wörishofen",
    "population": "15963",
    "state": "Bavaria"
  },
  {
    "area": "161.04",
    "coords": {
      "lat": "51.517",
      "lon": "8.700"
    },
    "district": "Paderborn",
    "name": "Bad Wünnenberg",
    "population": "12177",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "140.18",
    "coords": {
      "lat": "48.76278",
      "lon": "8.24083"
    },
    "district": "Urban district",
    "name": "Baden-Baden",
    "population": "55123",
    "state": "Baden-Württemberg"
  },
  {
    "area": "27.82",
    "coords": {
      "lat": "50.900",
      "lon": "6.183"
    },
    "district": "Aachen",
    "name": "Baesweiler",
    "population": "27033",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "11.79",
    "coords": {
      "lat": "49.650",
      "lon": "11.017"
    },
    "district": "Erlangen-Höchstadt",
    "name": "Baiersdorf",
    "population": "7794",
    "state": "Bavaria"
  },
  {
    "area": "90.34",
    "coords": {
      "lat": "48.27306",
      "lon": "8.85056"
    },
    "district": "Zollernalbkreis",
    "name": "Balingen",
    "population": "34217",
    "state": "Baden-Württemberg"
  },
  {
    "area": "86.61",
    "coords": {
      "lat": "51.72000",
      "lon": "11.23750"
    },
    "district": "Harz",
    "name": "Ballenstedt",
    "population": "8940",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "74.76",
    "coords": {
      "lat": "51.33333",
      "lon": "7.86667"
    },
    "district": "Märkischer Kreis",
    "name": "Balve",
    "population": "11361",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "54.62",
    "coords": {
      "lat": "49.900",
      "lon": "10.900"
    },
    "district": "Urban districts of Germany",
    "name": "Bamberg",
    "population": "77592",
    "state": "Bavaria"
  },
  {
    "area": "152.61",
    "coords": {
      "lat": "51.967",
      "lon": "11.867"
    },
    "district": "Salzlandkreis",
    "name": "Barby",
    "population": "8394",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "15.83",
    "coords": {
      "lat": "53.71667",
      "lon": "10.26667"
    },
    "district": "Stormarn",
    "name": "Bargteheide",
    "population": "16109",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "17.17",
    "coords": {
      "lat": "53.783",
      "lon": "9.767"
    },
    "district": "Pinneberg",
    "name": "Barmstedt",
    "population": "10368",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "59.46",
    "coords": {
      "lat": "51.98306",
      "lon": "9.11667"
    },
    "district": "Lippe",
    "name": "Barntrup",
    "population": "8587",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "102.65",
    "coords": {
      "lat": "52.30000",
      "lon": "9.48111"
    },
    "district": "Hanover",
    "name": "Barsinghausen",
    "population": "34234",
    "state": "Lower Saxony"
  },
  {
    "area": "40.83",
    "coords": {
      "lat": "54.367",
      "lon": "12.717"
    },
    "district": "Vorpommern-Rügen",
    "name": "Barth",
    "population": "8658",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "233.62",
    "coords": {
      "lat": "52.050",
      "lon": "13.500"
    },
    "district": "Teltow-Fläming",
    "name": "Baruth/Mark",
    "population": "4200",
    "state": "Brandenburg"
  },
  {
    "area": "169",
    "coords": {
      "lat": "52.84944",
      "lon": "8.72667"
    },
    "district": "Diepholz",
    "name": "Bassum",
    "population": "15955",
    "state": "Lower Saxony"
  },
  {
    "area": "64.73",
    "coords": {
      "lat": "51.017",
      "lon": "8.650"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Battenberg",
    "population": "5323",
    "state": "Hesse"
  },
  {
    "area": "69.47",
    "coords": {
      "lat": "49.61250",
      "lon": "7.33472"
    },
    "district": "Birkenfeld",
    "name": "Baumholder",
    "population": "4203",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "30.91",
    "coords": {
      "lat": "49.967",
      "lon": "10.833"
    },
    "district": "Bamberg",
    "name": "Baunach",
    "population": "4019",
    "state": "Bavaria"
  },
  {
    "area": "38.27",
    "coords": {
      "lat": "51.25889",
      "lon": "9.41833"
    },
    "district": "Kassel",
    "name": "Baunatal",
    "population": "27750",
    "state": "Hesse"
  },
  {
    "area": "66.62",
    "coords": {
      "lat": "51.18139",
      "lon": "14.42417"
    },
    "district": "Bautzen",
    "name": "Bautzen",
    "population": "39087",
    "state": "Saxony"
  },
  {
    "area": "66.92",
    "coords": {
      "lat": "49.94806",
      "lon": "11.57833"
    },
    "district": "Urban district",
    "name": "Bayreuth",
    "population": "74657",
    "state": "Bavaria"
  },
  {
    "area": "93.63",
    "coords": {
      "lat": "50.97111",
      "lon": "9.79028"
    },
    "district": "Hersfeld-Rotenburg",
    "name": "Bebra",
    "population": "13962",
    "state": "Hesse"
  },
  {
    "area": "111.46",
    "coords": {
      "lat": "51.75500",
      "lon": "8.04028"
    },
    "district": "Warendorf",
    "name": "Beckum",
    "population": "36646",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "80.21",
    "coords": {
      "lat": "51.00000",
      "lon": "6.56250"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Bedburg",
    "population": "23531",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "180.08",
    "coords": {
      "lat": "52.233",
      "lon": "12.967"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Beelitz",
    "population": "12448",
    "state": "Brandenburg"
  },
  {
    "area": "77.15",
    "coords": {
      "lat": "52.167",
      "lon": "14.250"
    },
    "district": "Oder-Spree",
    "name": "Beeskow",
    "population": "8042",
    "state": "Brandenburg"
  },
  {
    "area": "100.13",
    "coords": {
      "lat": "49.033",
      "lon": "11.467"
    },
    "district": "Eichstätt",
    "name": "Beilngries",
    "population": "9768",
    "state": "Bavaria"
  },
  {
    "area": "25.25",
    "coords": {
      "lat": "49.033",
      "lon": "9.317"
    },
    "district": "Heilbronn",
    "name": "Beilstein",
    "population": "6195",
    "state": "Baden-Württemberg"
  },
  {
    "area": "158.3",
    "coords": {
      "lat": "51.467",
      "lon": "13.033"
    },
    "district": "Nordsachsen",
    "name": "Belgern-Schildau",
    "population": "7701",
    "state": "Saxony"
  },
  {
    "area": "24.07",
    "coords": {
      "lat": "50.42972",
      "lon": "7.57028"
    },
    "district": "Mayen-Koblenz",
    "name": "Bendorf",
    "population": "16940",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "57.83",
    "coords": {
      "lat": "49.66667",
      "lon": "8.61667"
    },
    "district": "Bergstraße",
    "name": "Bensheim",
    "population": "40456",
    "state": "Hesse"
  },
  {
    "area": "131.18",
    "coords": {
      "lat": "49.100",
      "lon": "11.433"
    },
    "district": "Neumarkt in der Oberpfalz",
    "name": "Berching",
    "population": "8702",
    "state": "Bavaria"
  },
  {
    "area": "43.49",
    "coords": {
      "lat": "50.750",
      "lon": "12.167"
    },
    "district": "Greiz",
    "name": "Berga",
    "population": "3297",
    "state": "Thuringia"
  },
  {
    "area": "51.42",
    "coords": {
      "lat": "54.417",
      "lon": "13.433"
    },
    "district": "Vorpommern-Rügen",
    "name": "Bergen auf Rügen",
    "population": "13460",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "163.77",
    "coords": {
      "lat": "52.81028",
      "lon": "9.96111"
    },
    "district": "Celle",
    "name": "Bergen",
    "population": "13556",
    "state": "Lower Saxony"
  },
  {
    "area": "96.33",
    "coords": {
      "lat": "50.967",
      "lon": "6.650"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Bergheim",
    "population": "61612",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "83.12",
    "coords": {
      "lat": "51.100",
      "lon": "7.117"
    },
    "district": "Rheinisch-Bergischer Kreis",
    "name": "Bergisch Gladbach",
    "population": "111966",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "44.8",
    "coords": {
      "lat": "51.617",
      "lon": "7.633"
    },
    "district": "Unna",
    "name": "Bergkamen",
    "population": "48725",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "37.86",
    "coords": {
      "lat": "51.033",
      "lon": "7.650"
    },
    "district": "Oberbergischer Kreis",
    "name": "Bergneustadt",
    "population": "18865",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "891.1",
    "coords": {
      "lat": "52.52000",
      "lon": "13.40500"
    },
    "name": "Berlin",
    "population": "3769495",
    "state": "Berlin"
  },
  {
    "area": "103.73",
    "coords": {
      "lat": "52.66667",
      "lon": "13.58306"
    },
    "district": "Barnim",
    "name": "Bernau bei Berlin",
    "population": "38825",
    "state": "Brandenburg"
  },
  {
    "area": "113.45",
    "coords": {
      "lat": "51.800",
      "lon": "11.733"
    },
    "district": "Salzlandkreis",
    "name": "Bernburg",
    "population": "32674",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "23.66",
    "coords": {
      "lat": "49.91611",
      "lon": "7.06944"
    },
    "district": "Bernkastel-Wittlich",
    "name": "Bernkastel-Kues",
    "population": "7134",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "59.65",
    "coords": {
      "lat": "51.367",
      "lon": "14.067"
    },
    "district": "Bautzen",
    "name": "Bernsdorf",
    "population": "6466",
    "state": "Saxony"
  },
  {
    "area": "51.89",
    "coords": {
      "lat": "51.04583",
      "lon": "14.82639"
    },
    "district": "Görlitz",
    "name": "Bernstadt auf dem Eigen",
    "population": "3362",
    "state": "Saxony"
  },
  {
    "area": "42.54",
    "coords": {
      "lat": "52.533",
      "lon": "7.917"
    },
    "district": "Osnabrück",
    "name": "Bersenbrück",
    "population": "8501",
    "state": "Lower Saxony"
  },
  {
    "coords": {
      "lat": "48.9983",
      "lon": "9.1417"
    },
    "district": "Ludwigsburg",
    "name": "Besigheim",
    "population": "12627",
    "state": "Baden-Württemberg"
  },
  {
    "area": "9.57",
    "coords": {
      "lat": "50.78556",
      "lon": "7.87278"
    },
    "district": "Altenkirchen (Westerwald)",
    "name": "Betzdorf",
    "population": "10141",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "51.84",
    "coords": {
      "lat": "49.6802722",
      "lon": "11.4176361"
    },
    "district": "Bayreuth",
    "name": "Betzenstein",
    "population": "2475",
    "state": "Bavaria"
  },
  {
    "area": "97.84",
    "coords": {
      "lat": "51.66278",
      "lon": "9.37250"
    },
    "district": "Höxter",
    "name": "Beverungen",
    "population": "13115",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "31.08",
    "coords": {
      "lat": "49.34944",
      "lon": "7.25944"
    },
    "district": "Saarpfalz",
    "name": "Bexbach",
    "population": "17577",
    "state": "Saarland"
  },
  {
    "area": "72.16",
    "coords": {
      "lat": "48.100",
      "lon": "9.783"
    },
    "district": "Biberach",
    "name": "Biberach an der Riss",
    "population": "32938",
    "state": "Baden-Württemberg"
  },
  {
    "area": "90.33",
    "coords": {
      "lat": "50.91278",
      "lon": "8.53222"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Biedenkopf",
    "population": "13614",
    "state": "Hesse"
  },
  {
    "area": "257.8",
    "coords": {
      "lat": "52.02111",
      "lon": "8.53472"
    },
    "district": "Urban district",
    "name": "Bielefeld",
    "population": "333786",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "60.48",
    "coords": {
      "lat": "52.76667",
      "lon": "13.63306"
    },
    "district": "Barnim",
    "name": "Biesenthal",
    "population": "5791",
    "state": "Brandenburg"
  },
  {
    "area": "31.29",
    "coords": {
      "lat": "48.967",
      "lon": "9.133"
    },
    "district": "Ludwigsburg",
    "name": "Bietigheim-Bissingen",
    "population": "43093",
    "state": "Baden-Württemberg"
  },
  {
    "area": "90.93",
    "coords": {
      "lat": "51.97917",
      "lon": "7.29500"
    },
    "district": "Coesfeld",
    "name": "Billerbeck",
    "population": "11566",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "37.74",
    "coords": {
      "lat": "49.967",
      "lon": "7.900"
    },
    "district": "Mainz-Bingen",
    "name": "Bingen am Rhein",
    "population": "25659",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "13.58",
    "coords": {
      "lat": "49.650",
      "lon": "7.183"
    },
    "district": "Birkenfeld",
    "name": "Birkenfeld",
    "population": "6984",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "67.72",
    "coords": {
      "lat": "50.400",
      "lon": "10.017"
    },
    "district": "Rhön-Grabfeld",
    "name": "Bischofsheim i.d.Rhön",
    "population": "4825",
    "state": "Bavaria"
  },
  {
    "area": "46.26",
    "coords": {
      "lat": "51.12750",
      "lon": "14.17972"
    },
    "district": "Bautzen",
    "name": "Bischofswerda",
    "population": "10972",
    "state": "Saxony"
  },
  {
    "area": "289.43",
    "coords": {
      "lat": "52.66667",
      "lon": "11.55000"
    },
    "district": "Stendal",
    "name": "Bismark",
    "population": "8256",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "47.54",
    "coords": {
      "lat": "49.967",
      "lon": "6.533"
    },
    "district": "Eifelkreis Bitburg-Prüm",
    "name": "Bitburg",
    "population": "14904",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "87.55",
    "coords": {
      "lat": "51.617",
      "lon": "12.317"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Bitterfeld-Wolfen",
    "population": "38475",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "148.91",
    "coords": {
      "lat": "51.79528",
      "lon": "10.96222"
    },
    "district": "Harz",
    "name": "Blankenburg",
    "population": "19817",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "113.53",
    "coords": {
      "lat": "50.86000",
      "lon": "11.34389"
    },
    "district": "Weimarer Land",
    "name": "Blankenhain",
    "population": "6455",
    "state": "Thuringia"
  },
  {
    "area": "79.15",
    "coords": {
      "lat": "48.41194",
      "lon": "9.78500"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Blaubeuren",
    "population": "12521",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.61",
    "coords": {
      "lat": "48.41833",
      "lon": "9.90806"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Blaustein",
    "population": "16161",
    "state": "Baden-Württemberg"
  },
  {
    "area": "139.9",
    "coords": {
      "lat": "53.300",
      "lon": "10.733"
    },
    "district": "Lüneburg",
    "name": "Bleckede",
    "population": "9457",
    "state": "Lower Saxony"
  },
  {
    "area": "108.20",
    "coords": {
      "lat": "51.41667",
      "lon": "10.56667"
    },
    "district": "Nordhausen",
    "name": "Bleicherode",
    "population": "10419",
    "state": "Thuringia"
  },
  {
    "area": "108.27",
    "coords": {
      "lat": "49.23306",
      "lon": "7.25000"
    },
    "district": "Saarpfalz",
    "name": "Blieskastel",
    "population": "20656",
    "state": "Saarland"
  },
  {
    "area": "99.1",
    "coords": {
      "lat": "51.93306",
      "lon": "9.08306"
    },
    "district": "Lippe",
    "name": "Blomberg",
    "population": "15154",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "98.68",
    "coords": {
      "lat": "47.83917",
      "lon": "8.53417"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Blumberg",
    "population": "10127",
    "state": "Baden-Württemberg"
  },
  {
    "area": "50.45",
    "coords": {
      "lat": "48.267",
      "lon": "10.817"
    },
    "district": "Augsburg",
    "name": "Bobingen",
    "population": "17199",
    "state": "Bavaria"
  },
  {
    "area": "119.37",
    "coords": {
      "lat": "51.833",
      "lon": "6.617"
    },
    "district": "Borken",
    "name": "Bocholt",
    "population": "71099",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "145.4",
    "coords": {
      "lat": "51.48194",
      "lon": "7.21583"
    },
    "district": "Urban districts of Germany",
    "name": "Bochum",
    "population": "364628",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "109.04",
    "coords": {
      "lat": "52.01167",
      "lon": "10.13194"
    },
    "district": "Hildesheim",
    "name": "Bockenem",
    "population": "9795",
    "state": "Lower Saxony"
  },
  {
    "area": "28.92",
    "coords": {
      "lat": "51.967",
      "lon": "9.517"
    },
    "district": "Holzminden",
    "name": "Bodenwerder",
    "population": "5561",
    "state": "Lower Saxony"
  },
  {
    "area": "49.74",
    "coords": {
      "lat": "48.917",
      "lon": "12.683"
    },
    "district": "Straubing-Bogen",
    "name": "Bogen",
    "population": "10263",
    "state": "Bavaria"
  },
  {
    "area": "47.26",
    "coords": {
      "lat": "53.367",
      "lon": "10.717"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Boizenburg",
    "population": "10724",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "76.03",
    "coords": {
      "lat": "47.81944",
      "lon": "8.34306"
    },
    "district": "Waldshut",
    "name": "Bonndorf im Schwarzwald",
    "population": "6922",
    "state": "Baden-Württemberg"
  },
  {
    "area": "141.06",
    "coords": {
      "lat": "50.733",
      "lon": "7.100"
    },
    "district": "Urban district",
    "name": "Bonn",
    "population": "327258",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "76.98",
    "coords": {
      "lat": "48.85694",
      "lon": "10.35222"
    },
    "district": "Ostalbkreis",
    "name": "Bopfingen",
    "population": "11727",
    "state": "Baden-Württemberg"
  },
  {
    "area": "75.13",
    "coords": {
      "lat": "50.23139",
      "lon": "7.59083"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Boppard",
    "population": "15325",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "138.76",
    "coords": {
      "lat": "51.567",
      "lon": "9.250"
    },
    "district": "Höxter",
    "name": "Borgentreich",
    "population": "8523",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "55.84",
    "coords": {
      "lat": "52.10000",
      "lon": "8.30000"
    },
    "district": "Gütersloh",
    "name": "Borgholzhausen",
    "population": "8973",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "82.3",
    "coords": {
      "lat": "51.04583",
      "lon": "9.26722"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Borken",
    "population": "12649",
    "state": "Hesse"
  },
  {
    "area": "152.6",
    "coords": {
      "lat": "51.833",
      "lon": "6.867"
    },
    "district": "Borken",
    "name": "Borken",
    "population": "42530",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "30.74",
    "coords": {
      "lat": "53.58806",
      "lon": "6.66972"
    },
    "district": "Leer",
    "name": "Borkum",
    "population": "5125",
    "state": "Lower Saxony"
  },
  {
    "area": "62.35",
    "coords": {
      "lat": "51.117",
      "lon": "12.500"
    },
    "district": "Leipzig",
    "name": "Borna",
    "population": "19229",
    "state": "Saxony"
  },
  {
    "area": "82.72",
    "coords": {
      "lat": "50.75917",
      "lon": "7.00500"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Bornheim",
    "population": "48326",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "100.7",
    "coords": {
      "lat": "51.52472",
      "lon": "6.92278"
    },
    "district": "Urban districts of Germany",
    "name": "Bottrop",
    "population": "117383",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "101.81",
    "coords": {
      "lat": "49.48139",
      "lon": "9.64167"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Boxberg",
    "population": "6664",
    "state": "Baden-Württemberg"
  },
  {
    "area": "45.74",
    "coords": {
      "lat": "49.083",
      "lon": "9.067"
    },
    "district": "Heilbronn",
    "name": "Brackenheim",
    "population": "16106",
    "state": "Baden-Württemberg"
  },
  {
    "area": "173.74",
    "coords": {
      "lat": "51.717",
      "lon": "9.183"
    },
    "district": "Höxter",
    "name": "Brakel",
    "population": "16270",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "38.18",
    "coords": {
      "lat": "53.333",
      "lon": "8.483"
    },
    "district": "Wesermarsch",
    "name": "Brake",
    "population": "14965",
    "state": "Lower Saxony"
  },
  {
    "area": "183.32",
    "coords": {
      "lat": "52.400",
      "lon": "7.983"
    },
    "district": "Osnabrück",
    "name": "Bramsche",
    "population": "30952",
    "state": "Lower Saxony"
  },
  {
    "area": "46.24",
    "coords": {
      "lat": "50.86889",
      "lon": "13.32194"
    },
    "district": "Mittelsachsen",
    "name": "Brand-Erbisdorf",
    "population": "9452",
    "state": "Saxony"
  },
  {
    "area": "228.80",
    "coords": {
      "lat": "52.41667",
      "lon": "12.53333"
    },
    "district": "Urban district",
    "name": "Brandenburg an der Havel",
    "population": "72124",
    "state": "Brandenburg"
  },
  {
    "area": "34.81",
    "coords": {
      "lat": "51.33472",
      "lon": "12.60889"
    },
    "district": "Leipzig",
    "name": "Brandis",
    "population": "9613",
    "state": "Saxony"
  },
  {
    "area": "20.26",
    "coords": {
      "lat": "50.27472",
      "lon": "7.64611"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Braubach",
    "population": "3038",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "47.29",
    "coords": {
      "lat": "50.517",
      "lon": "8.383"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Braunfels",
    "population": "10976",
    "state": "Hesse"
  },
  {
    "area": "31.55",
    "coords": {
      "lat": "51.72694",
      "lon": "10.61194"
    },
    "district": "Goslar",
    "name": "Braunlage",
    "population": "5854",
    "state": "Lower Saxony"
  },
  {
    "area": "74.34",
    "coords": {
      "lat": "51.283",
      "lon": "11.900"
    },
    "district": "Saalekreis",
    "name": "Braunsbedra",
    "population": "10678",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "59",
    "coords": {
      "lat": "51.267",
      "lon": "7.467"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Breckerfeld",
    "population": "8938",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "9.75",
    "coords": {
      "lat": "54.62000",
      "lon": "8.96444"
    },
    "district": "Nordfriesland",
    "name": "BredstedtBräist / Bredsted",
    "population": "5429",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "54.58",
    "coords": {
      "lat": "48.033",
      "lon": "7.583"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Breisach",
    "population": "15606",
    "state": "Baden-Württemberg"
  },
  {
    "area": "326.73",
    "coords": {
      "lat": "53.083",
      "lon": "8.800"
    },
    "name": "Bremen",
    "population": "569352",
    "state": "Bremen"
  },
  {
    "area": "93.82",
    "coords": {
      "lat": "53.55000",
      "lon": "8.58333"
    },
    "name": "Bremerhaven",
    "population": "113634",
    "state": "Bremen"
  },
  {
    "area": "150.18",
    "coords": {
      "lat": "53.483",
      "lon": "9.133"
    },
    "district": "Rotenburg (Wümme)",
    "name": "Bremervörde",
    "population": "18528",
    "state": "Lower Saxony"
  },
  {
    "area": "71.12",
    "coords": {
      "lat": "49.03639",
      "lon": "8.70611"
    },
    "district": "Karlsruhe",
    "name": "Bretten",
    "population": "29412",
    "state": "Baden-Württemberg"
  },
  {
    "area": "30.76",
    "coords": {
      "lat": "49.81722",
      "lon": "9.03500"
    },
    "district": "Odenwaldkreis",
    "name": "Breuberg",
    "population": "7415",
    "state": "Hesse"
  },
  {
    "area": "228.95",
    "coords": {
      "lat": "51.39556",
      "lon": "8.56778"
    },
    "district": "Hochsauerland",
    "name": "Brilon",
    "population": "25417",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "49.69",
    "coords": {
      "lat": "50.8249417",
      "lon": "10.4456306"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Brotterode-Trusetal",
    "population": "6021",
    "state": "Thuringia"
  },
  {
    "area": "29.67",
    "coords": {
      "lat": "50.183",
      "lon": "8.917"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Bruchköbel",
    "population": "20427",
    "state": "Hesse"
  },
  {
    "area": "93.02",
    "coords": {
      "lat": "49.133",
      "lon": "8.600"
    },
    "district": "Karlsruhe",
    "name": "Bruchsal",
    "population": "44644",
    "state": "Baden-Württemberg"
  },
  {
    "area": "65.24",
    "coords": {
      "lat": "53.89639",
      "lon": "9.13861"
    },
    "district": "Dithmarschen",
    "name": "Brunsbüttel",
    "population": "12554",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "192.13",
    "coords": {
      "lat": "52.267",
      "lon": "10.517"
    },
    "district": "Urban district",
    "name": "Brunswick",
    "population": "248292",
    "state": "Lower Saxony"
  },
  {
    "area": "62.10",
    "coords": {
      "lat": "47.92972",
      "lon": "8.44806"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Bräunlingen",
    "population": "5828",
    "state": "Baden-Württemberg"
  },
  {
    "area": "85.71",
    "coords": {
      "lat": "52.200",
      "lon": "12.767"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Brück",
    "population": "4113",
    "state": "Brandenburg"
  },
  {
    "area": "27.30",
    "coords": {
      "lat": "53.717",
      "lon": "11.717"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Brüel",
    "population": "2554",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "36.12",
    "coords": {
      "lat": "50.833",
      "lon": "6.900"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Brühl",
    "population": "44397",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "101.02",
    "coords": {
      "lat": "53.40000",
      "lon": "14.13333"
    },
    "district": "Uckermark",
    "name": "Brüssow",
    "population": "1831",
    "state": "Brandenburg"
  },
  {
    "area": "138.99",
    "coords": {
      "lat": "49.52167",
      "lon": "9.32333"
    },
    "district": "Neckar-Odenwald-Kreis",
    "name": "Buchen",
    "population": "17796",
    "state": "Baden-Württemberg"
  },
  {
    "area": "74.62",
    "coords": {
      "lat": "53.317",
      "lon": "9.867"
    },
    "district": "Harburg",
    "name": "Buchholz in der Nordheide",
    "population": "39272",
    "state": "Lower Saxony"
  },
  {
    "area": "36.16",
    "coords": {
      "lat": "48.03750",
      "lon": "10.72500"
    },
    "district": "Ostallgäu",
    "name": "Buchloe",
    "population": "13132",
    "state": "Bavaria"
  },
  {
    "area": "14.31",
    "coords": {
      "lat": "52.567",
      "lon": "14.083"
    },
    "district": "Märkisch-Oderland",
    "name": "Buckow",
    "population": "1486",
    "state": "Brandenburg"
  },
  {
    "area": "76.55",
    "coords": {
      "lat": "53.483",
      "lon": "13.300"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Burg Stargard",
    "population": "5402",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "25.92",
    "coords": {
      "lat": "48.43222",
      "lon": "10.40694"
    },
    "district": "Günzburg",
    "name": "Burgau",
    "population": "9923",
    "state": "Bavaria"
  },
  {
    "area": "42.32",
    "coords": {
      "lat": "49.450",
      "lon": "10.317"
    },
    "district": "Neustadt a.d.Aisch-Bad Windsheim",
    "name": "Burgbernheim",
    "population": "3297",
    "state": "Bavaria"
  },
  {
    "area": "112.26",
    "coords": {
      "lat": "52.45000",
      "lon": "10.00833"
    },
    "district": "Hanover",
    "name": "Burgdorf",
    "population": "30699",
    "state": "Lower Saxony"
  },
  {
    "area": "19.85",
    "coords": {
      "lat": "48.167",
      "lon": "12.833"
    },
    "district": "Altötting",
    "name": "Burghausen",
    "population": "18701",
    "state": "Bavaria"
  },
  {
    "area": "40.59",
    "coords": {
      "lat": "50.117",
      "lon": "11.250"
    },
    "district": "Lichtenfels",
    "name": "Burgkunstadt",
    "population": "6451",
    "state": "Bavaria"
  },
  {
    "area": "93.28",
    "coords": {
      "lat": "49.200",
      "lon": "12.033"
    },
    "district": "Schwandorf",
    "name": "Burglengenfeld",
    "population": "13554",
    "state": "Bavaria"
  },
  {
    "area": "25.76",
    "coords": {
      "lat": "50.917",
      "lon": "12.817"
    },
    "district": "Mittelsachsen",
    "name": "Burgstädt",
    "population": "10672",
    "state": "Saxony"
  },
  {
    "area": "151.96",
    "coords": {
      "lat": "52.49333",
      "lon": "9.85861"
    },
    "district": "Hanover",
    "name": "Burgwedel",
    "population": "20369",
    "state": "Lower Saxony"
  },
  {
    "area": "164.02",
    "coords": {
      "lat": "52.27250",
      "lon": "11.85500"
    },
    "district": "Jerichower Land",
    "name": "Burg",
    "population": "22478",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "123.33",
    "coords": {
      "lat": "48.29028",
      "lon": "9.10944"
    },
    "district": "Zollernalbkreis",
    "name": "Burladingen",
    "population": "12146",
    "state": "Baden-Württemberg"
  },
  {
    "area": "27.38",
    "coords": {
      "lat": "51.10000",
      "lon": "7.11667"
    },
    "district": "Rheinisch-Bergischer Kreis",
    "name": "Burscheid",
    "population": "18172",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "106.60",
    "coords": {
      "lat": "50.43667",
      "lon": "8.66222"
    },
    "district": "Wetteraukreis",
    "name": "Butzbach",
    "population": "26197",
    "state": "Hesse"
  },
  {
    "area": "74.42",
    "coords": {
      "lat": "49.81028",
      "lon": "12.43611"
    },
    "district": "Tirschenreuth",
    "name": "Bärnau",
    "population": "3153",
    "state": "Bavaria"
  },
  {
    "area": "39.04",
    "coords": {
      "lat": "48.68556",
      "lon": "9.01528"
    },
    "district": "Böblingen",
    "name": "Böblingen",
    "population": "50155",
    "state": "Baden-Württemberg"
  },
  {
    "area": "24.55",
    "coords": {
      "lat": "51.20250",
      "lon": "12.38583"
    },
    "district": "Leipzig",
    "name": "Böhlen",
    "population": "6687",
    "state": "Saxony"
  },
  {
    "area": "20.14",
    "coords": {
      "lat": "49.0410",
      "lon": "9.0950"
    },
    "district": "Ludwigsburg",
    "name": "Bönnigheim",
    "population": "8015",
    "state": "Baden-Württemberg"
  },
  {
    "area": "68.84",
    "coords": {
      "lat": "52.26083",
      "lon": "9.04917"
    },
    "district": "Schaumburg",
    "name": "Bückeburg",
    "population": "19245",
    "state": "Lower Saxony"
  },
  {
    "area": "6.24",
    "coords": {
      "lat": "54.317",
      "lon": "9.683"
    },
    "district": "Rendsburg-Eckernförde",
    "name": "Büdelsdorf",
    "population": "10297",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "122.87",
    "coords": {
      "lat": "50.29083",
      "lon": "9.11250"
    },
    "district": "Wetteraukreis",
    "name": "Büdingen",
    "population": "21959",
    "state": "Hesse"
  },
  {
    "area": "73.21",
    "coords": {
      "lat": "48.69528",
      "lon": "8.13500"
    },
    "district": "Rastatt",
    "name": "Bühl",
    "population": "28900",
    "state": "Baden-Württemberg"
  },
  {
    "area": "59.30",
    "coords": {
      "lat": "52.200",
      "lon": "8.600"
    },
    "district": "Herford",
    "name": "Bünde",
    "population": "45521",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "170.97",
    "coords": {
      "lat": "51.550",
      "lon": "8.567"
    },
    "district": "Paderborn",
    "name": "Büren",
    "population": "21556",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "27",
    "coords": {
      "lat": "50.94139",
      "lon": "11.75389"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Bürgel",
    "population": "3132",
    "state": "Thuringia"
  },
  {
    "area": "34.46",
    "coords": {
      "lat": "49.63333",
      "lon": "8.45000"
    },
    "district": "Bergstraße",
    "name": "Bürstadt",
    "population": "16398",
    "state": "Hesse"
  },
  {
    "area": "39.70",
    "coords": {
      "lat": "53.850",
      "lon": "11.983"
    },
    "district": "Rostock",
    "name": "Bützow",
    "population": "7799",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "162.59",
    "coords": {
      "lat": "51.74583",
      "lon": "13.95083"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Calau/Kalawa",
    "population": "7769",
    "state": "Brandenburg"
  },
  {
    "area": "56.62",
    "coords": {
      "lat": "51.90333",
      "lon": "11.77583"
    },
    "district": "Salzlandkreis",
    "name": "Calbe",
    "population": "8609",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "59.88",
    "coords": {
      "lat": "48.71667",
      "lon": "8.73333"
    },
    "district": "Calw",
    "name": "Calw",
    "population": "23590",
    "state": "Baden-Württemberg"
  },
  {
    "area": "51.66",
    "coords": {
      "lat": "51.550",
      "lon": "7.317"
    },
    "district": "Recklinghausen",
    "name": "Castrop-Rauxel",
    "population": "73425",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "176.01",
    "coords": {
      "lat": "52.62556",
      "lon": "10.08250"
    },
    "district": "Celle",
    "name": "Celle",
    "population": "69602",
    "state": "Lower Saxony"
  },
  {
    "area": "80.67",
    "coords": {
      "lat": "49.217",
      "lon": "12.650"
    },
    "district": "Cham",
    "name": "Cham",
    "population": "16907",
    "state": "Bavaria"
  },
  {
    "area": "220.85",
    "coords": {
      "lat": "50.833",
      "lon": "12.917"
    },
    "district": "Urban districts of Germany",
    "name": "Chemnitz",
    "population": "247237",
    "state": "Saxony"
  },
  {
    "area": "43.71",
    "coords": {
      "lat": "51.80500",
      "lon": "10.33556"
    },
    "district": "Goslar",
    "name": "Clausthal-Zellerfeld",
    "population": "15888",
    "state": "Lower Saxony"
  },
  {
    "area": "97.79",
    "coords": {
      "lat": "51.79000",
      "lon": "6.14000"
    },
    "district": "Kleve",
    "name": "Cleves",
    "population": "51845",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "10.75",
    "coords": {
      "lat": "51.21667",
      "lon": "10.93333"
    },
    "district": "Kyffhäuserkreis",
    "name": "Clingen",
    "population": "1063",
    "state": "Thuringia"
  },
  {
    "area": "70.62",
    "coords": {
      "lat": "52.850",
      "lon": "8.050"
    },
    "district": "Cloppenburg",
    "name": "Cloppenburg",
    "population": "34913",
    "state": "Lower Saxony"
  },
  {
    "area": "48.30",
    "coords": {
      "lat": "50.267",
      "lon": "10.967"
    },
    "district": "Urban district",
    "name": "Coburg",
    "population": "41249",
    "state": "Bavaria"
  },
  {
    "area": "21.21",
    "coords": {
      "lat": "50.14694",
      "lon": "7.16667"
    },
    "district": "Cochem-Zell",
    "name": "Cochem",
    "population": "5312",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "141.05",
    "coords": {
      "lat": "51.950",
      "lon": "7.167"
    },
    "district": "Coesfeld",
    "name": "Coesfeld",
    "population": "36217",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "83.55",
    "coords": {
      "lat": "51.12917",
      "lon": "12.80694"
    },
    "district": "Leipzig",
    "name": "Colditz",
    "population": "8472",
    "state": "Saxony"
  },
  {
    "area": "405.15",
    "coords": {
      "lat": "50.93639",
      "lon": "6.95278"
    },
    "district": "Urban districts of Germany",
    "name": "Cologne",
    "population": "1085664",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "55.65",
    "coords": {
      "lat": "47.667",
      "lon": "9.183"
    },
    "district": "Konstanz",
    "name": "Constance",
    "population": "84760",
    "state": "Baden-Württemberg"
  },
  {
    "area": "25.85",
    "coords": {
      "lat": "51.133",
      "lon": "13.583"
    },
    "district": "Meißen",
    "name": "Coswig",
    "population": "20817",
    "state": "Saxony"
  },
  {
    "area": "295.73",
    "coords": {
      "lat": "51.883",
      "lon": "12.433"
    },
    "district": "Wittenberg",
    "name": "Coswig",
    "population": "11824",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "164.28",
    "coords": {
      "lat": "51.76056",
      "lon": "14.33417"
    },
    "district": "Urban district",
    "name": "Cottbus/Chóśebuz",
    "population": "100219",
    "state": "Brandenburg"
  },
  {
    "area": "109.08",
    "coords": {
      "lat": "49.13472",
      "lon": "10.07056"
    },
    "district": "Schwäbisch Hall",
    "name": "Crailsheim",
    "population": "34400",
    "state": "Baden-Württemberg"
  },
  {
    "area": "117.22",
    "coords": {
      "lat": "49.467",
      "lon": "10.033"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Creglingen",
    "population": "4694",
    "state": "Baden-Württemberg"
  },
  {
    "area": "64.89",
    "coords": {
      "lat": "49.8439861",
      "lon": "11.6226750"
    },
    "district": "Bayreuth",
    "name": "Creußen",
    "population": "4941",
    "state": "Bavaria"
  },
  {
    "area": "61.04",
    "coords": {
      "lat": "50.81806",
      "lon": "12.38750"
    },
    "district": "Zwickau",
    "name": "Crimmitschau",
    "population": "18536",
    "state": "Saxony"
  },
  {
    "area": "75.48",
    "coords": {
      "lat": "53.583",
      "lon": "11.650"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Crivitz",
    "population": "4892",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "161.91",
    "coords": {
      "lat": "53.86111",
      "lon": "8.69444"
    },
    "district": "Cuxhaven",
    "name": "Cuxhaven",
    "population": "48371",
    "state": "Lower Saxony"
  },
  {
    "area": "19.56",
    "coords": {
      "lat": "50.73972",
      "lon": "7.96833"
    },
    "district": "Altenkirchen",
    "name": "Daaden",
    "population": "4244",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "34.85",
    "coords": {
      "lat": "48.26028",
      "lon": "11.43417"
    },
    "district": "Dachau",
    "name": "Dachau, Bavaria",
    "population": "47400",
    "state": "Bavaria"
  },
  {
    "area": "71.68",
    "coords": {
      "lat": "51.367",
      "lon": "13.000"
    },
    "district": "Nordsachsen",
    "name": "Dahlen",
    "population": "4278",
    "state": "Saxony"
  },
  {
    "area": "162.02",
    "coords": {
      "lat": "51.86667",
      "lon": "13.43306"
    },
    "district": "Teltow-Fläming",
    "name": "Dahme",
    "population": "4897",
    "state": "Brandenburg"
  },
  {
    "area": "40.75",
    "coords": {
      "lat": "49.150",
      "lon": "7.783"
    },
    "district": "Südwestpfalz",
    "name": "Dahn",
    "population": "4605",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "104.45",
    "coords": {
      "lat": "52.52083",
      "lon": "8.19861"
    },
    "district": "Vechta",
    "name": "Damme",
    "population": "17127",
    "state": "Lower Saxony"
  },
  {
    "area": "76.31",
    "coords": {
      "lat": "53.083",
      "lon": "11.083"
    },
    "district": "Lüchow-Dannenberg",
    "name": "Dannenberg",
    "population": "8200",
    "state": "Lower Saxony"
  },
  {
    "area": "117.15",
    "coords": {
      "lat": "53.883",
      "lon": "12.833"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Dargun",
    "population": "4365",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "122.23",
    "coords": {
      "lat": "49.87222",
      "lon": "8.65278"
    },
    "district": "Urban district",
    "name": "Darmstadt",
    "population": "159207",
    "state": "Hesse"
  },
  {
    "area": "113.02",
    "coords": {
      "lat": "51.80333",
      "lon": "9.69028"
    },
    "district": "Northeim",
    "name": "Dassel",
    "population": "9604",
    "state": "Lower Saxony"
  },
  {
    "area": "66.54",
    "coords": {
      "lat": "53.91056",
      "lon": "10.97222"
    },
    "district": "Nordwestmecklenburg",
    "name": "Dassow",
    "population": "4042",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "66.08",
    "coords": {
      "lat": "51.65389",
      "lon": "7.34167"
    },
    "district": "Recklinghausen",
    "name": "Datteln",
    "population": "34614",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "48.97",
    "coords": {
      "lat": "50.19861",
      "lon": "6.83194"
    },
    "district": "Vulkaneifel",
    "name": "Daun",
    "population": "7974",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "77.21",
    "coords": {
      "lat": "48.833",
      "lon": "12.967"
    },
    "district": "Deggendorf",
    "name": "Deggendorf",
    "population": "33585",
    "state": "Bavaria"
  },
  {
    "area": "26.53",
    "coords": {
      "lat": "49.40750",
      "lon": "8.18639"
    },
    "district": "Bad Dürkheim",
    "name": "Deidesheim",
    "population": "3760",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "157.26",
    "coords": {
      "lat": "51.76667",
      "lon": "8.56667"
    },
    "district": "Paderborn",
    "name": "Delbrück",
    "population": "31949",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "83.57",
    "coords": {
      "lat": "51.52639",
      "lon": "12.34250"
    },
    "district": "Nordsachsen",
    "name": "Delitzsch",
    "population": "24868",
    "state": "Saxony"
  },
  {
    "area": "62.36",
    "coords": {
      "lat": "53.05056",
      "lon": "8.63167"
    },
    "district": "Urban district",
    "name": "Delmenhorst",
    "population": "77607",
    "state": "Lower Saxony"
  },
  {
    "area": "80.653",
    "coords": {
      "lat": "53.90500",
      "lon": "13.04389"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Demmin",
    "population": "10657",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "244.62",
    "coords": {
      "lat": "51.833",
      "lon": "12.250"
    },
    "district": "Urban district",
    "name": "Dessau-Roßlau",
    "population": "81237",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "129.39",
    "coords": {
      "lat": "51.93778",
      "lon": "8.88333"
    },
    "district": "Lippe",
    "name": "Detmold",
    "population": "74388",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "60.94",
    "coords": {
      "lat": "49.800",
      "lon": "10.183"
    },
    "district": "Kitzingen",
    "name": "Dettelbach",
    "population": "7240",
    "state": "Bavaria"
  },
  {
    "area": "23.11",
    "coords": {
      "lat": "49.90000",
      "lon": "8.85000"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Dieburg",
    "population": "15679",
    "state": "Hesse"
  },
  {
    "area": "82.58",
    "coords": {
      "lat": "51.46000",
      "lon": "8.98000"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Diemelstadt",
    "population": "5208",
    "state": "Hesse"
  },
  {
    "area": "104.45",
    "coords": {
      "lat": "52.60722",
      "lon": "8.37111"
    },
    "district": "Diepholz",
    "name": "Diepholz",
    "population": "16882",
    "state": "Lower Saxony"
  },
  {
    "area": "31.90",
    "coords": {
      "lat": "50.54889",
      "lon": "7.65944"
    },
    "district": "Neuwied",
    "name": "Dierdorf",
    "population": "5700",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "18.76",
    "coords": {
      "lat": "48.21194",
      "lon": "10.07333"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Dietenheim",
    "population": "6715",
    "state": "Baden-Württemberg"
  },
  {
    "area": "78.84",
    "coords": {
      "lat": "49.033",
      "lon": "11.583"
    },
    "district": "Neumarkt in der Oberpfalz",
    "name": "Dietfurt a.d. Altmühl",
    "population": "6139",
    "state": "Bavaria"
  },
  {
    "area": "21.67",
    "coords": {
      "lat": "50.017",
      "lon": "8.783"
    },
    "district": "Offenbach",
    "name": "Dietzenbach",
    "population": "34019",
    "state": "Hesse"
  },
  {
    "area": "12.41",
    "coords": {
      "lat": "50.37083",
      "lon": "8.01583"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Diez, Germany",
    "population": "11074",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "83.88",
    "coords": {
      "lat": "50.733",
      "lon": "8.283"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Dillenburg",
    "population": "23365",
    "state": "Hesse"
  },
  {
    "area": "75.59",
    "coords": {
      "lat": "48.567",
      "lon": "10.467"
    },
    "district": "Dillingen",
    "name": "Dillingen an der Donau",
    "population": "18978",
    "state": "Bavaria"
  },
  {
    "area": "22.07",
    "coords": {
      "lat": "49.350",
      "lon": "6.733"
    },
    "district": "Saarlouis",
    "name": "Dillingen",
    "population": "20048",
    "state": "Saarland"
  },
  {
    "area": "59.40",
    "coords": {
      "lat": "51.31556",
      "lon": "10.31944"
    },
    "district": "Eichsfeld",
    "name": "Dingelstädt",
    "population": "6893",
    "state": "Thuringia"
  },
  {
    "area": "44.04",
    "coords": {
      "lat": "48.633",
      "lon": "12.500"
    },
    "district": "Dingolfing-Landau",
    "name": "Dingolfing",
    "population": "19839",
    "state": "Bavaria"
  },
  {
    "area": "75.19",
    "coords": {
      "lat": "49.07083",
      "lon": "10.31944"
    },
    "district": "Ansbach",
    "name": "Dinkelsbühl",
    "population": "11825",
    "state": "Bavaria"
  },
  {
    "area": "72.65",
    "coords": {
      "lat": "52.667",
      "lon": "8.133"
    },
    "district": "Vechta",
    "name": "Dinklage",
    "population": "13150",
    "state": "Lower Saxony"
  },
  {
    "area": "47.67",
    "coords": {
      "lat": "51.567",
      "lon": "6.733"
    },
    "district": "Wesel",
    "name": "Dinslaken",
    "population": "67525",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "104.13",
    "coords": {
      "lat": "50.89333",
      "lon": "13.66667"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Dippoldiswalde",
    "population": "14432",
    "state": "Saxony"
  },
  {
    "area": "31.9",
    "coords": {
      "lat": "52.117",
      "lon": "8.200"
    },
    "district": "Osnabrück",
    "name": "Dissen, Lower Saxony",
    "population": "9882",
    "state": "Lower Saxony"
  },
  {
    "area": "30.40",
    "coords": {
      "lat": "48.82639",
      "lon": "9.06667"
    },
    "district": "Ludwigsburg",
    "name": "Ditzingen",
    "population": "24883",
    "state": "Baden-Württemberg"
  },
  {
    "area": "148.93",
    "coords": {
      "lat": "51.617",
      "lon": "13.567"
    },
    "district": "Elbe-Elster",
    "name": "Doberlug-Kirchhain",
    "population": "9062",
    "state": "Brandenburg"
  },
  {
    "area": "28.57",
    "coords": {
      "lat": "50.95472",
      "lon": "13.85750"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Dohna",
    "population": "6220",
    "state": "Saxony"
  },
  {
    "area": "30.20",
    "coords": {
      "lat": "51.650",
      "lon": "12.883"
    },
    "district": "Nordsachsen",
    "name": "Dommitzsch",
    "population": "2458",
    "state": "Saxony"
  },
  {
    "area": "104.63",
    "coords": {
      "lat": "47.95306",
      "lon": "8.50333"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Donaueschingen",
    "population": "22526",
    "state": "Baden-Württemberg"
  },
  {
    "area": "77.02",
    "coords": {
      "lat": "48.700",
      "lon": "10.800"
    },
    "district": "Donau-Ries",
    "name": "Donauwörth",
    "population": "20080",
    "state": "Bavaria"
  },
  {
    "area": "39.82",
    "coords": {
      "lat": "48.683",
      "lon": "9.817"
    },
    "district": "Göppingen",
    "name": "Donzdorf",
    "population": "10682",
    "state": "Baden-Württemberg"
  },
  {
    "area": "99.60",
    "coords": {
      "lat": "48.267",
      "lon": "12.150"
    },
    "district": "Erding",
    "name": "Dorfen",
    "population": "14650",
    "state": "Bavaria"
  },
  {
    "area": "85.4",
    "coords": {
      "lat": "51.100",
      "lon": "6.817"
    },
    "district": "Rhein-Kreis Neuss",
    "name": "Dormagen",
    "population": "64335",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "30.72",
    "coords": {
      "lat": "51.05389",
      "lon": "11.70750"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Dornburg-Camburg",
    "population": "5426",
    "state": "Thuringia"
  },
  {
    "area": "44.93",
    "coords": {
      "lat": "48.34944",
      "lon": "8.51222"
    },
    "district": "Rottweil",
    "name": "Dornhan",
    "population": "6006",
    "state": "Baden-Württemberg"
  },
  {
    "area": "24.21",
    "coords": {
      "lat": "48.467",
      "lon": "8.500"
    },
    "district": "Freudenstadt",
    "name": "Dornstetten",
    "population": "8061",
    "state": "Baden-Württemberg"
  },
  {
    "area": "171",
    "coords": {
      "lat": "51.66000",
      "lon": "6.96417"
    },
    "district": "Recklinghausen",
    "name": "Dorsten",
    "population": "74736",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "280.71",
    "coords": {
      "lat": "51.517",
      "lon": "7.467"
    },
    "district": "Urban district",
    "name": "Dortmund",
    "population": "587010",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "28",
    "coords": {
      "lat": "51.500",
      "lon": "9.767"
    },
    "district": "Göttingen",
    "name": "Dransfeld",
    "population": "4367",
    "state": "Lower Saxony"
  },
  {
    "area": "142.94",
    "coords": {
      "lat": "51.650",
      "lon": "14.217"
    },
    "district": "Spree-Neiße",
    "name": "Drebkau/Drjowk",
    "population": "5538",
    "state": "Brandenburg"
  },
  {
    "area": "53.328",
    "coords": {
      "lat": "50.000",
      "lon": "8.700"
    },
    "district": "Offenbach",
    "name": "Dreieich",
    "population": "42091",
    "state": "Hesse"
  },
  {
    "area": "106.42",
    "coords": {
      "lat": "51.79444",
      "lon": "7.73917"
    },
    "district": "Warendorf",
    "name": "Drensteinfurt",
    "population": "15542",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "328.8",
    "coords": {
      "lat": "51.033",
      "lon": "13.733"
    },
    "district": "Urban district",
    "name": "Dresden",
    "population": "554649",
    "state": "Saxony"
  },
  {
    "area": "67.12",
    "coords": {
      "lat": "51.033",
      "lon": "7.767"
    },
    "district": "Olpe",
    "name": "Drolshagen",
    "population": "11779",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "95.61",
    "coords": {
      "lat": "51.51250",
      "lon": "10.25972"
    },
    "district": "Göttingen",
    "name": "Duderstadt",
    "population": "20466",
    "state": "Lower Saxony"
  },
  {
    "area": "232.82",
    "coords": {
      "lat": "51.43472",
      "lon": "6.76250"
    },
    "district": "Urban district",
    "name": "Duisburg",
    "population": "498590",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "84.55",
    "coords": {
      "lat": "51.11944",
      "lon": "13.11278"
    },
    "district": "Mittelsachsen",
    "name": "Döbeln",
    "population": "23829",
    "state": "Saxony"
  },
  {
    "area": "15.73",
    "coords": {
      "lat": "51.617",
      "lon": "14.600"
    },
    "district": "Spree-Neiße",
    "name": "Döbern",
    "population": "3194",
    "state": "Brandenburg"
  },
  {
    "area": "60.38",
    "coords": {
      "lat": "53.13833",
      "lon": "11.26333"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Dömitz",
    "population": "3009",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "184.49",
    "coords": {
      "lat": "51.83083",
      "lon": "7.27833"
    },
    "district": "Coesfeld",
    "name": "Dülmen",
    "population": "46590",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "85.02",
    "coords": {
      "lat": "50.800",
      "lon": "6.483"
    },
    "district": "Düren",
    "name": "Düren",
    "population": "90733",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "217.41",
    "coords": {
      "lat": "51.233",
      "lon": "6.783"
    },
    "district": "Urban district",
    "name": "Düsseldorf",
    "population": "619294",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "44.54",
    "coords": {
      "lat": "51.28333",
      "lon": "10.73333"
    },
    "district": "Kyffhäuserkreis",
    "name": "Ebeleben",
    "population": "2717",
    "state": "Thuringia"
  },
  {
    "area": "81.16",
    "coords": {
      "lat": "49.467",
      "lon": "8.983"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Eberbach",
    "population": "14460",
    "state": "Baden-Württemberg"
  },
  {
    "area": "49.97",
    "coords": {
      "lat": "49.767",
      "lon": "11.167"
    },
    "district": "Forchheim",
    "name": "Ebermannstadt",
    "population": "6971",
    "state": "Bavaria"
  },
  {
    "area": "95.02",
    "coords": {
      "lat": "50.10000",
      "lon": "10.79361"
    },
    "district": "Haßberge",
    "name": "Ebern",
    "population": "7270",
    "state": "Bavaria"
  },
  {
    "area": "26.27",
    "coords": {
      "lat": "48.71472",
      "lon": "9.52361"
    },
    "district": "Göppingen",
    "name": "Ebersbach an der Fils",
    "population": "15535",
    "state": "Baden-Württemberg"
  },
  {
    "area": "20.42",
    "coords": {
      "lat": "51.000",
      "lon": "14.600"
    },
    "district": "Görlitz",
    "name": "Ebersbach-Neugersdorf",
    "population": "11994",
    "state": "Saxony"
  },
  {
    "area": "40.84",
    "coords": {
      "lat": "48.083",
      "lon": "11.967"
    },
    "district": "Ebersberg",
    "name": "Ebersberg",
    "population": "12239",
    "state": "Bavaria"
  },
  {
    "area": "58.17",
    "coords": {
      "lat": "52.833",
      "lon": "13.833"
    },
    "district": "Barnim",
    "name": "Eberswalde",
    "population": "40387",
    "state": "Brandenburg"
  },
  {
    "area": "35.95",
    "coords": {
      "lat": "51.117",
      "lon": "11.550"
    },
    "district": "Burgenlandkreis",
    "name": "Eckartsberga",
    "population": "2399",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "17.97",
    "coords": {
      "lat": "54.47417",
      "lon": "9.83778"
    },
    "district": "Rendsburg-Eckernförde",
    "name": "EckernfördeEckernföör, Egernførde",
    "population": "21902",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "17.90",
    "coords": {
      "lat": "49.283",
      "lon": "8.133"
    },
    "district": "Südliche Weinstraße",
    "name": "Edenkoben",
    "population": "6690",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "29.13",
    "coords": {
      "lat": "51.950",
      "lon": "11.433"
    },
    "district": "Salzlandkreis",
    "name": "Egeln",
    "population": "3269",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "44.50",
    "coords": {
      "lat": "48.40389",
      "lon": "12.76417"
    },
    "district": "Rottal-Inn",
    "name": "Eggenfelden",
    "population": "13736",
    "state": "Bavaria"
  },
  {
    "area": "88.01",
    "coords": {
      "lat": "53.683",
      "lon": "14.083"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Eggesin",
    "population": "4695",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "178.40",
    "coords": {
      "lat": "48.28333",
      "lon": "9.72361"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Ehingen",
    "population": "26259",
    "state": "Baden-Württemberg"
  },
  {
    "area": "15.86",
    "coords": {
      "lat": "50.64611",
      "lon": "12.96972"
    },
    "district": "Erzgebirgskreis",
    "name": "Ehrenfriedersdorf",
    "population": "4761",
    "state": "Saxony"
  },
  {
    "area": "7.07",
    "coords": {
      "lat": "49.7238806",
      "lon": "10.0009306"
    },
    "district": "Würzburg",
    "name": "Eibelstadt",
    "population": "3046",
    "state": "Bavaria"
  },
  {
    "area": "112.35",
    "coords": {
      "lat": "50.49556",
      "lon": "12.59750"
    },
    "district": "Erzgebirgskreis",
    "name": "Eibenstock",
    "population": "7370",
    "state": "Saxony"
  },
  {
    "area": "47.78",
    "coords": {
      "lat": "48.89194",
      "lon": "11.18389"
    },
    "district": "Eichstätt",
    "name": "Eichstätt",
    "population": "13525",
    "state": "Bavaria"
  },
  {
    "area": "46.84",
    "coords": {
      "lat": "51.46083",
      "lon": "12.63583"
    },
    "district": "Nordsachsen",
    "name": "Eilenburg",
    "population": "15583",
    "state": "Saxony"
  },
  {
    "area": "231.31",
    "coords": {
      "lat": "51.817",
      "lon": "9.867"
    },
    "district": "Northeim",
    "name": "Einbeck",
    "population": "30826",
    "state": "Lower Saxony"
  },
  {
    "area": "104.17",
    "coords": {
      "lat": "50.97611",
      "lon": "10.32056"
    },
    "district": "Urban district",
    "name": "Eisenach",
    "population": "42370",
    "state": "Thuringia"
  },
  {
    "area": "18.73",
    "coords": {
      "lat": "49.56139",
      "lon": "8.07250"
    },
    "district": "Donnersbergkreis",
    "name": "Eisenberg",
    "population": "9264",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "24.85",
    "coords": {
      "lat": "50.96667",
      "lon": "11.90000"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Eisenberg",
    "population": "10885",
    "state": "Thuringia"
  },
  {
    "area": "63.40",
    "coords": {
      "lat": "52.14500",
      "lon": "14.67278"
    },
    "district": "Oder-Spree",
    "name": "Eisenhüttenstadt",
    "population": "24633",
    "state": "Brandenburg"
  },
  {
    "area": "86.53",
    "coords": {
      "lat": "50.417",
      "lon": "10.917"
    },
    "district": "Hildburghausen",
    "name": "Eisfeld",
    "population": "7646",
    "state": "Thuringia"
  },
  {
    "area": "143.81",
    "coords": {
      "lat": "51.517",
      "lon": "11.550"
    },
    "district": "Mansfeld-Südharz",
    "name": "Eisleben",
    "population": "23373",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "16.41",
    "coords": {
      "lat": "48.69333",
      "lon": "9.70667"
    },
    "district": "Göppingen",
    "name": "Eislingen",
    "population": "20885",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.25",
    "coords": {
      "lat": "49.017",
      "lon": "10.967"
    },
    "district": "Weißenburg-Gunzenhausen",
    "name": "Ellingen",
    "population": "3820",
    "state": "Bavaria"
  },
  {
    "area": "69.42",
    "coords": {
      "lat": "51.58556",
      "lon": "10.66806"
    },
    "district": "Nordhausen",
    "name": "Ellrich",
    "population": "5543",
    "state": "Thuringia"
  },
  {
    "area": "127.4",
    "coords": {
      "lat": "48.96111",
      "lon": "10.13056"
    },
    "district": "Ostalbkreis",
    "name": "Ellwangen",
    "population": "24549",
    "state": "Baden-Württemberg"
  },
  {
    "area": "21.36",
    "coords": {
      "lat": "53.75194",
      "lon": "9.65111"
    },
    "district": "Pinneberg",
    "name": "Elmshorn",
    "population": "49883",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "66.00",
    "coords": {
      "lat": "50.933",
      "lon": "6.567"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Elsdorf",
    "population": "21663",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "115.15",
    "coords": {
      "lat": "53.233",
      "lon": "8.467"
    },
    "district": "Wesermarsch",
    "name": "Elsfleth",
    "population": "9105",
    "state": "Lower Saxony"
  },
  {
    "area": "25.1",
    "coords": {
      "lat": "50.600",
      "lon": "12.167"
    },
    "district": "Vogtlandkreis",
    "name": "Elsterberg",
    "population": "3937",
    "state": "Saxony"
  },
  {
    "area": "40.55",
    "coords": {
      "lat": "51.45778",
      "lon": "13.52389"
    },
    "district": "Elbe-Elster",
    "name": "Elsterwerda",
    "population": "7856",
    "state": "Brandenburg"
  },
  {
    "area": "32.65",
    "coords": {
      "lat": "51.22083",
      "lon": "14.13083"
    },
    "district": "Bautzen",
    "name": "Elstra",
    "population": "2729",
    "state": "Saxony"
  },
  {
    "area": "45.87",
    "coords": {
      "lat": "50.57694",
      "lon": "12.86722"
    },
    "district": "Erzgebirgskreis",
    "name": "Elterlein",
    "population": "2871",
    "state": "Saxony"
  },
  {
    "area": "39.97",
    "coords": {
      "lat": "49.967",
      "lon": "10.667"
    },
    "district": "Haßberge",
    "name": "Eltmann",
    "population": "5299",
    "state": "Bavaria"
  },
  {
    "area": "46.77",
    "coords": {
      "lat": "50.02556",
      "lon": "8.11917"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Eltville",
    "population": "17077",
    "state": "Hesse"
  },
  {
    "area": "75.28",
    "coords": {
      "lat": "48.17472",
      "lon": "8.07167"
    },
    "district": "Emmendingen",
    "name": "Elzach",
    "population": "7242",
    "state": "Baden-Württemberg"
  },
  {
    "area": "47.71",
    "coords": {
      "lat": "52.117",
      "lon": "9.733"
    },
    "district": "Hildesheim",
    "name": "Elze",
    "population": "8939",
    "state": "Lower Saxony"
  },
  {
    "area": "112.33",
    "coords": {
      "lat": "53.36694",
      "lon": "7.20611"
    },
    "district": "Urban district",
    "name": "Emden",
    "population": "50195",
    "state": "Lower Saxony"
  },
  {
    "area": "7.91",
    "coords": {
      "lat": "50.15528",
      "lon": "7.55167"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Emmelshausen",
    "population": "4831",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "33.80",
    "coords": {
      "lat": "48.12139",
      "lon": "7.84917"
    },
    "district": "Emmendingen",
    "name": "Emmendingen",
    "population": "27882",
    "state": "Baden-Württemberg"
  },
  {
    "area": "80.11",
    "coords": {
      "lat": "51.83500",
      "lon": "6.24528"
    },
    "district": "Kleve",
    "name": "Emmerich",
    "population": "30748",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "72.06",
    "coords": {
      "lat": "52.17278",
      "lon": "7.53444"
    },
    "district": "Steinfurt",
    "name": "Emsdetten",
    "population": "36012",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "26.72",
    "coords": {
      "lat": "48.14083",
      "lon": "7.70639"
    },
    "district": "Emmendingen",
    "name": "Endingen",
    "population": "9868",
    "state": "Baden-Württemberg"
  },
  {
    "area": "70.53",
    "coords": {
      "lat": "47.85278",
      "lon": "8.77139"
    },
    "district": "Konstanz",
    "name": "Engen",
    "population": "10796",
    "state": "Baden-Württemberg"
  },
  {
    "area": "41.21",
    "coords": {
      "lat": "52.13333",
      "lon": "8.56667"
    },
    "district": "Herford",
    "name": "Enger",
    "population": "20461",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "57.42",
    "coords": {
      "lat": "51.283",
      "lon": "7.333"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Ennepetal",
    "population": "30075",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "125.15",
    "coords": {
      "lat": "51.83667",
      "lon": "8.02556"
    },
    "district": "Warendorf",
    "name": "Ennigerloh",
    "population": "19829",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "5.7",
    "coords": {
      "lat": "49.400",
      "lon": "8.633"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Eppelheim",
    "population": "15177",
    "state": "Baden-Württemberg"
  },
  {
    "area": "88.59",
    "coords": {
      "lat": "49.133",
      "lon": "8.917"
    },
    "district": "Heilbronn",
    "name": "Eppingen",
    "population": "21819",
    "state": "Baden-Württemberg"
  },
  {
    "area": "24.21",
    "coords": {
      "lat": "50.133",
      "lon": "8.400"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Eppstein",
    "population": "13655",
    "state": "Hesse"
  },
  {
    "area": "63.92",
    "coords": {
      "lat": "48.32806",
      "lon": "9.88778"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Erbach",
    "population": "13453",
    "state": "Baden-Württemberg"
  },
  {
    "area": "62.67",
    "coords": {
      "lat": "49.65833",
      "lon": "8.99583"
    },
    "district": "Odenwaldkreis",
    "name": "Erbach",
    "population": "13666",
    "state": "Hesse"
  },
  {
    "area": "67.55",
    "coords": {
      "lat": "49.833",
      "lon": "12.050"
    },
    "district": "Tirschenreuth",
    "name": "Erbendorf",
    "population": "5085",
    "state": "Bavaria"
  },
  {
    "area": "54.64",
    "coords": {
      "lat": "48.283",
      "lon": "11.900"
    },
    "district": "Erding",
    "name": "Erding",
    "population": "36469",
    "state": "Bavaria"
  },
  {
    "area": "119.88",
    "coords": {
      "lat": "50.817",
      "lon": "6.767"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Erftstadt",
    "population": "49801",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "269.17",
    "coords": {
      "lat": "50.98333",
      "lon": "11.03333"
    },
    "district": "Urban district",
    "name": "Erfurt",
    "population": "213699",
    "state": "Thuringia"
  },
  {
    "area": "117.35",
    "coords": {
      "lat": "51.083",
      "lon": "6.317"
    },
    "district": "Heinsberg",
    "name": "Erkelenz",
    "population": "43364",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "16.60",
    "coords": {
      "lat": "52.417",
      "lon": "13.750"
    },
    "district": "Oder-Spree",
    "name": "Erkner",
    "population": "11815",
    "state": "Brandenburg"
  },
  {
    "area": "26.89",
    "coords": {
      "lat": "51.22389",
      "lon": "6.91472"
    },
    "district": "Mettmann",
    "name": "Erkrath",
    "population": "44384",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "76.95",
    "coords": {
      "lat": "49.583",
      "lon": "11.017"
    },
    "district": "Urban district",
    "name": "Erlangen",
    "population": "111962",
    "state": "Bavaria"
  },
  {
    "area": "16.33",
    "coords": {
      "lat": "49.80389",
      "lon": "9.16389"
    },
    "district": "Miltenberg",
    "name": "Erlenbach a.Main",
    "population": "10227",
    "state": "Bavaria"
  },
  {
    "area": "18.59",
    "coords": {
      "lat": "50.133",
      "lon": "8.933"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Erlensee",
    "population": "14899",
    "state": "Hesse"
  },
  {
    "area": "73.79",
    "coords": {
      "lat": "51.617",
      "lon": "8.350"
    },
    "district": "Soest",
    "name": "Erwitte",
    "population": "16045",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "12.14",
    "coords": {
      "lat": "50.14361",
      "lon": "8.57000"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Eschborn",
    "population": "21488",
    "state": "Hesse"
  },
  {
    "area": "35.16",
    "coords": {
      "lat": "49.750",
      "lon": "11.817"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Eschenbach in der Oberpfalz",
    "population": "4056",
    "state": "Bavaria"
  },
  {
    "area": "23.87",
    "coords": {
      "lat": "51.917",
      "lon": "9.650"
    },
    "district": "Holzminden",
    "name": "Eschershausen",
    "population": "3490",
    "state": "Lower Saxony"
  },
  {
    "area": "63.27",
    "coords": {
      "lat": "51.18806",
      "lon": "10.05278"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Eschwege",
    "population": "19606",
    "state": "Hesse"
  },
  {
    "area": "76.559",
    "coords": {
      "lat": "50.817",
      "lon": "6.283"
    },
    "district": "Aachen",
    "name": "Eschweiler",
    "population": "56385",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "26.83",
    "coords": {
      "lat": "53.64694",
      "lon": "7.61278"
    },
    "district": "Wittmund",
    "name": "Esens",
    "population": "7286",
    "state": "Lower Saxony"
  },
  {
    "area": "84.1",
    "coords": {
      "lat": "52.37722",
      "lon": "8.63278"
    },
    "district": "Minden-Lübbecke",
    "name": "Espelkamp",
    "population": "24685",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "210.32",
    "coords": {
      "lat": "51.45083",
      "lon": "7.01306"
    },
    "district": "Urban district",
    "name": "Essen",
    "population": "583109",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "46.43",
    "coords": {
      "lat": "48.73333",
      "lon": "9.31667"
    },
    "district": "Esslingen",
    "name": "Esslingen am Neckar",
    "population": "93542",
    "state": "Baden-Württemberg"
  },
  {
    "area": "48.90",
    "coords": {
      "lat": "48.25556",
      "lon": "7.81194"
    },
    "district": "Ortenaukreis",
    "name": "Ettenheim",
    "population": "13316",
    "state": "Baden-Württemberg"
  },
  {
    "area": "56.74",
    "coords": {
      "lat": "48.933",
      "lon": "8.400"
    },
    "district": "Karlsruhe",
    "name": "Ettlingen",
    "population": "39339",
    "state": "Baden-Württemberg"
  },
  {
    "area": "139.63",
    "coords": {
      "lat": "50.65972",
      "lon": "6.79167"
    },
    "district": "Euskirchen",
    "name": "Euskirchen",
    "population": "57975",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "41.4",
    "coords": {
      "lat": "54.13778",
      "lon": "10.61806"
    },
    "district": "Ostholstein",
    "name": "Eutin",
    "population": "16971",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "81.80",
    "coords": {
      "lat": "51.58306",
      "lon": "13.23222"
    },
    "district": "Elbe-Elster",
    "name": "Falkenberg/Elster",
    "population": "6368",
    "state": "Brandenburg"
  },
  {
    "area": "43.30",
    "coords": {
      "lat": "52.55833",
      "lon": "13.09167"
    },
    "district": "Havelland",
    "name": "Falkensee",
    "population": "43844",
    "state": "Brandenburg"
  },
  {
    "area": "102.98",
    "coords": {
      "lat": "51.733",
      "lon": "11.333"
    },
    "district": "Harz",
    "name": "Falkenstein/Harz",
    "population": "5274",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "31.06",
    "coords": {
      "lat": "50.467",
      "lon": "12.367"
    },
    "district": "Vogtlandkreis",
    "name": "Falkenstein",
    "population": "8061",
    "state": "Saxony"
  },
  {
    "area": "185.45",
    "coords": {
      "lat": "54.4454",
      "lon": "11.1702"
    },
    "district": "Ostholstein",
    "name": "Fehmarn",
    "population": "12592",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "27.70",
    "coords": {
      "lat": "48.80861",
      "lon": "9.27583"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Fellbach",
    "population": "45671",
    "state": "Baden-Württemberg"
  },
  {
    "area": "83.27",
    "coords": {
      "lat": "51.133",
      "lon": "9.417"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Felsberg",
    "population": "10627",
    "state": "Hesse"
  },
  {
    "area": "137.40",
    "coords": {
      "lat": "49.167",
      "lon": "10.317"
    },
    "district": "Ansbach",
    "name": "Feuchtwangen",
    "population": "12452",
    "state": "Bavaria"
  },
  {
    "area": "38.54",
    "coords": {
      "lat": "48.68028",
      "lon": "9.21833"
    },
    "district": "Esslingen",
    "name": "Filderstadt",
    "population": "45813",
    "state": "Baden-Württemberg"
  },
  {
    "area": "76.91",
    "coords": {
      "lat": "51.63306",
      "lon": "13.71667"
    },
    "district": "Elbe-Elster",
    "name": "Finsterwalde",
    "population": "16220",
    "state": "Brandenburg"
  },
  {
    "area": "46.37",
    "coords": {
      "lat": "50.52111",
      "lon": "10.14528"
    },
    "district": "Rhön-Grabfeld",
    "name": "Fladungen",
    "population": "2248",
    "state": "Bavaria"
  },
  {
    "area": "56.38",
    "coords": {
      "lat": "54.78194",
      "lon": "9.43667"
    },
    "district": "Urban district",
    "name": "Flensburg",
    "population": "89504",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "39.60",
    "coords": {
      "lat": "50.31583",
      "lon": "8.86306"
    },
    "district": "Wetteraukreis",
    "name": "Florstadt",
    "population": "8753",
    "state": "Hesse"
  },
  {
    "area": "27.61",
    "coords": {
      "lat": "50.85583",
      "lon": "13.07139"
    },
    "district": "Mittelsachsen",
    "name": "Flöha",
    "population": "10762",
    "state": "Saxony"
  },
  {
    "area": "22.95",
    "coords": {
      "lat": "50.017",
      "lon": "8.433"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Flörsheim am Main",
    "population": "21572",
    "state": "Hesse"
  },
  {
    "area": "44.95",
    "coords": {
      "lat": "49.71972",
      "lon": "11.05806"
    },
    "district": "Forchheim",
    "name": "Forchheim",
    "population": "32171",
    "state": "Bavaria"
  },
  {
    "area": "38.07",
    "coords": {
      "lat": "49.283",
      "lon": "9.567"
    },
    "district": "Hohenlohekreis",
    "name": "Forchtenberg",
    "population": "5057",
    "state": "Baden-Württemberg"
  },
  {
    "area": "109.91",
    "coords": {
      "lat": "51.733",
      "lon": "14.633"
    },
    "district": "Spree-Neiße",
    "name": "Forst (Lausitz)",
    "population": "18164",
    "state": "Brandenburg"
  },
  {
    "area": "57.29",
    "coords": {
      "lat": "51.100",
      "lon": "8.933"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Frankenau",
    "population": "2904",
    "state": "Hesse"
  },
  {
    "area": "124.87",
    "coords": {
      "lat": "51.05889",
      "lon": "8.79667"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Frankenberg",
    "population": "17808",
    "state": "Hesse"
  },
  {
    "area": "65.42",
    "coords": {
      "lat": "50.91083",
      "lon": "13.03778"
    },
    "district": "Mittelsachsen",
    "name": "Frankenberg",
    "population": "14088",
    "state": "Saxony"
  },
  {
    "area": "43.78",
    "coords": {
      "lat": "49.533",
      "lon": "8.350"
    },
    "district": "urban district",
    "name": "Frankenthal (Pfalz)",
    "population": "48561",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "248.31",
    "coords": {
      "lat": "50.117",
      "lon": "8.683"
    },
    "district": "Urban district",
    "name": "Frankfurt am Main",
    "population": "753056",
    "state": "Hesse"
  },
  {
    "area": "147.61",
    "coords": {
      "lat": "52.350",
      "lon": "14.550"
    },
    "district": "Urban district",
    "name": "Frankfurt",
    "population": "57873",
    "state": "Brandenburg"
  },
  {
    "area": "15.19",
    "coords": {
      "lat": "54.167",
      "lon": "12.867"
    },
    "district": "Vorpommern-Rügen",
    "name": "Franzburg",
    "population": "1344",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "58.83",
    "coords": {
      "lat": "50.80194",
      "lon": "13.53806"
    },
    "district": "Mittelsachsen",
    "name": "Frauenstein",
    "population": "2829",
    "state": "Saxony"
  },
  {
    "area": "45.11",
    "coords": {
      "lat": "50.917",
      "lon": "6.817"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Frechen",
    "population": "52473",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "13.14",
    "coords": {
      "lat": "48.93333",
      "lon": "9.20000"
    },
    "district": "Ludwigsburg",
    "name": "Freiberg am Neckar",
    "population": "15968",
    "state": "Baden-Württemberg"
  },
  {
    "area": "48.05",
    "coords": {
      "lat": "50.91194",
      "lon": "13.34278"
    },
    "district": "Mittelsachsen",
    "name": "Freiberg",
    "population": "40885",
    "state": "Saxony"
  },
  {
    "area": "153.07",
    "coords": {
      "lat": "47.983",
      "lon": "7.850"
    },
    "district": "Stadtkreis",
    "name": "Freiburg im Breisgau",
    "population": "230241",
    "state": "Baden-Württemberg"
  },
  {
    "area": "14.79",
    "coords": {
      "lat": "47.83333",
      "lon": "12.96667"
    },
    "district": "Berchtesgadener Land",
    "name": "Freilassing",
    "population": "16939",
    "state": "Bavaria"
  },
  {
    "area": "13.61",
    "coords": {
      "lat": "49.50750",
      "lon": "8.20861"
    },
    "district": "Bad Dürkheim",
    "name": "Freinsheim",
    "population": "4916",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "88.45",
    "coords": {
      "lat": "48.40278",
      "lon": "11.74889"
    },
    "district": "Freising",
    "name": "Freising",
    "population": "48634",
    "state": "Bavaria"
  },
  {
    "area": "40.53",
    "coords": {
      "lat": "51.01667",
      "lon": "13.65000"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Freital",
    "population": "39562",
    "state": "Saxony"
  },
  {
    "area": "48.81",
    "coords": {
      "lat": "52.467",
      "lon": "7.533"
    },
    "district": "Emsland",
    "name": "Freren",
    "population": "5023",
    "state": "Lower Saxony"
  },
  {
    "area": "34.78",
    "coords": {
      "lat": "49.750",
      "lon": "9.333"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Freudenberg",
    "population": "3774",
    "state": "Baden-Württemberg"
  },
  {
    "area": "54.48",
    "coords": {
      "lat": "50.89972",
      "lon": "7.86667"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Freudenberg",
    "population": "17739",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "87.58",
    "coords": {
      "lat": "48.46333",
      "lon": "8.41111"
    },
    "district": "Freudenstadt",
    "name": "Freudenstadt",
    "population": "23442",
    "state": "Baden-Württemberg"
  },
  {
    "area": "46.57",
    "coords": {
      "lat": "51.21278",
      "lon": "11.76972"
    },
    "district": "Burgenlandkreis",
    "name": "Freyburg",
    "population": "4656",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "80.53",
    "coords": {
      "lat": "49.200",
      "lon": "11.317"
    },
    "district": "Neumarkt in der Oberpfalz",
    "name": "Freystadt",
    "population": "9013",
    "state": "Bavaria"
  },
  {
    "area": "48.64",
    "coords": {
      "lat": "48.800",
      "lon": "13.550"
    },
    "district": "Freyung-Grafenau",
    "name": "Freyung",
    "population": "7166",
    "state": "Bavaria"
  },
  {
    "area": "22.47",
    "coords": {
      "lat": "48.02056",
      "lon": "8.93278"
    },
    "district": "Tuttlingen",
    "name": "Fridingen",
    "population": "3174",
    "state": "Baden-Württemberg"
  },
  {
    "area": "81.20",
    "coords": {
      "lat": "48.350",
      "lon": "10.983"
    },
    "district": "Aichach-Friedberg",
    "name": "Friedberg",
    "population": "29810",
    "state": "Bavaria"
  },
  {
    "area": "50.17",
    "coords": {
      "lat": "50.333",
      "lon": "8.750"
    },
    "district": "Wetteraukreis",
    "name": "Friedberg",
    "population": "29180",
    "state": "Hesse"
  },
  {
    "area": "173.21",
    "coords": {
      "lat": "52.100",
      "lon": "14.267"
    },
    "district": "Oder-Spree",
    "name": "Friedland",
    "population": "2957",
    "state": "Brandenburg"
  },
  {
    "area": "141.73",
    "coords": {
      "lat": "53.650",
      "lon": "13.533"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Friedland",
    "population": "6472",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "36.91",
    "coords": {
      "lat": "50.86667",
      "lon": "10.56667"
    },
    "district": "Gotha",
    "name": "Friedrichroda",
    "population": "7275",
    "state": "Thuringia"
  },
  {
    "area": "30.12",
    "coords": {
      "lat": "50.25556",
      "lon": "8.63972"
    },
    "district": "Hochtaunuskreis",
    "name": "Friedrichsdorf",
    "population": "25194",
    "state": "Hesse"
  },
  {
    "area": "69.91",
    "coords": {
      "lat": "47.650",
      "lon": "9.483"
    },
    "district": "Bodenseekreis",
    "name": "Friedrichshafen",
    "population": "60865",
    "state": "Baden-Württemberg"
  },
  {
    "area": "4.03",
    "coords": {
      "lat": "54.367",
      "lon": "9.067"
    },
    "district": "Nordfriesland",
    "name": "FriedrichstadtFrederiksstad",
    "population": "2578",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "9.07",
    "coords": {
      "lat": "49.32556",
      "lon": "7.09611"
    },
    "district": "Saarbrücken",
    "name": "Friedrichsthal",
    "population": "10133",
    "state": "Saarland"
  },
  {
    "area": "83.67",
    "coords": {
      "lat": "52.73306",
      "lon": "12.58306"
    },
    "district": "Havelland",
    "name": "Friesack",
    "population": "2538",
    "state": "Brandenburg"
  },
  {
    "area": "247.14",
    "coords": {
      "lat": "53.02056",
      "lon": "7.85861"
    },
    "district": "Cloppenburg",
    "name": "Friesoythe",
    "population": "22456",
    "state": "Lower Saxony"
  },
  {
    "area": "88.79",
    "coords": {
      "lat": "51.13333",
      "lon": "9.28333"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Fritzlar",
    "population": "14744",
    "state": "Hesse"
  },
  {
    "area": "145.31",
    "coords": {
      "lat": "51.05611",
      "lon": "12.55500"
    },
    "district": "Leipzig",
    "name": "Frohburg",
    "population": "12470",
    "state": "Saxony"
  },
  {
    "area": "56.21",
    "coords": {
      "lat": "51.47194",
      "lon": "7.76583"
    },
    "district": "Unna",
    "name": "Fröndenberg",
    "population": "20766",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "104.04",
    "coords": {
      "lat": "50.55083",
      "lon": "9.67528"
    },
    "district": "Fulda",
    "name": "Fulda",
    "population": "68586",
    "state": "Hesse"
  },
  {
    "area": "67.00",
    "coords": {
      "lat": "49.30972",
      "lon": "12.84000"
    },
    "district": "Cham",
    "name": "Furth im Wald",
    "population": "9093",
    "state": "Bavaria"
  },
  {
    "area": "82.57",
    "coords": {
      "lat": "48.05028",
      "lon": "8.20917"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Furtwangen im Schwarzwald",
    "population": "9091",
    "state": "Baden-Württemberg"
  },
  {
    "area": "78.62",
    "coords": {
      "lat": "52.517",
      "lon": "7.667"
    },
    "district": "Osnabrück",
    "name": "Fürstenau",
    "population": "9439",
    "state": "Lower Saxony"
  },
  {
    "area": "212.61",
    "coords": {
      "lat": "53.18528",
      "lon": "13.14556"
    },
    "district": "Oberhavel",
    "name": "Fürstenberg",
    "population": "5838",
    "state": "Brandenburg"
  },
  {
    "area": "32.53",
    "coords": {
      "lat": "48.17778",
      "lon": "11.25556"
    },
    "district": "Fürstenfeldbruck",
    "name": "Fürstenfeldbruck",
    "population": "37677",
    "state": "Bavaria"
  },
  {
    "area": "70.55",
    "coords": {
      "lat": "52.367",
      "lon": "14.067"
    },
    "district": "Oder-Spree",
    "name": "Fürstenwalde/Spree",
    "population": "31941",
    "state": "Brandenburg"
  },
  {
    "area": "63.35",
    "coords": {
      "lat": "49.467",
      "lon": "11.000"
    },
    "district": "Urban district",
    "name": "Fürth",
    "population": "127748",
    "state": "Bavaria"
  },
  {
    "area": "43.52",
    "coords": {
      "lat": "47.567",
      "lon": "10.700"
    },
    "district": "Ostallgäu",
    "name": "Füssen",
    "population": "15608",
    "state": "Bavaria"
  },
  {
    "area": "47.65",
    "coords": {
      "lat": "53.700",
      "lon": "11.117"
    },
    "district": "Nordwestmecklenburg",
    "name": "Gadebusch",
    "population": "5530",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "65.05",
    "coords": {
      "lat": "48.80389",
      "lon": "8.31944"
    },
    "district": "Rastatt",
    "name": "Gaggenau",
    "population": "29777",
    "state": "Baden-Württemberg"
  },
  {
    "area": "62.56",
    "coords": {
      "lat": "49.000",
      "lon": "9.767"
    },
    "district": "Schwäbisch Hall",
    "name": "Gaildorf",
    "population": "12080",
    "state": "Baden-Württemberg"
  },
  {
    "area": "52.97",
    "coords": {
      "lat": "48.24944",
      "lon": "9.21750"
    },
    "district": "Sigmaringen",
    "name": "Gammertingen",
    "population": "6320",
    "state": "Baden-Württemberg"
  },
  {
    "area": "79.31",
    "coords": {
      "lat": "52.41833",
      "lon": "9.59806"
    },
    "district": "Hanover",
    "name": "Garbsen",
    "population": "60754",
    "state": "Lower Saxony"
  },
  {
    "area": "28.16",
    "coords": {
      "lat": "48.250",
      "lon": "11.650"
    },
    "district": "Munich",
    "name": "Garching bei München",
    "population": "17711",
    "state": "Bavaria"
  },
  {
    "area": "632.43",
    "coords": {
      "lat": "52.52639",
      "lon": "11.39250"
    },
    "district": "Altmarkkreis Salzwedel",
    "name": "Gardelegen",
    "population": "22402",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "3.06",
    "coords": {
      "lat": "54.33056",
      "lon": "8.78056"
    },
    "district": "Nordfriesland",
    "name": "Garding",
    "population": "2764",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "61.69",
    "coords": {
      "lat": "53.20000",
      "lon": "14.38333"
    },
    "district": "Uckermark",
    "name": "Gartz",
    "population": "2550",
    "state": "Brandenburg"
  },
  {
    "area": "65.44",
    "coords": {
      "lat": "54.317",
      "lon": "13.350"
    },
    "district": "Vorpommern-Rügen",
    "name": "Garz",
    "population": "2194",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "13.99",
    "coords": {
      "lat": "49.950",
      "lon": "8.017"
    },
    "district": "Mainz-Bingen",
    "name": "Gau-Algesheim",
    "population": "6827",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "24.04",
    "coords": {
      "lat": "51.11389",
      "lon": "10.93472"
    },
    "district": "Sömmerda",
    "name": "Gebesee",
    "population": "2141",
    "state": "Thuringia"
  },
  {
    "area": "75.24",
    "coords": {
      "lat": "50.42444",
      "lon": "9.19972"
    },
    "district": "Wetteraukreis",
    "name": "Gedern",
    "population": "7342",
    "state": "Hesse"
  },
  {
    "area": "33.18",
    "coords": {
      "lat": "53.433",
      "lon": "10.367"
    },
    "district": "Lauenburg",
    "name": "Geesthacht",
    "population": "30551",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "356.20",
    "coords": {
      "lat": "53.633",
      "lon": "8.750"
    },
    "district": "Cuxhaven",
    "name": "Geestland",
    "population": "30866",
    "state": "Lower Saxony"
  },
  {
    "area": "45.21",
    "coords": {
      "lat": "50.43333",
      "lon": "11.85000"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Gefell",
    "population": "2468",
    "state": "Thuringia"
  },
  {
    "area": "50.08",
    "coords": {
      "lat": "50.0951389",
      "lon": "11.7398361"
    },
    "district": "Bayreuth",
    "name": "Gefrees",
    "population": "4332",
    "state": "Bavaria"
  },
  {
    "area": "42.97",
    "coords": {
      "lat": "52.31167",
      "lon": "9.60028"
    },
    "district": "Hanover",
    "name": "Gehrden",
    "population": "14864",
    "state": "Lower Saxony"
  },
  {
    "area": "83",
    "coords": {
      "lat": "50.96528",
      "lon": "6.11944"
    },
    "district": "Heinsberg",
    "name": "Geilenkirchen",
    "population": "27214",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "71.75",
    "coords": {
      "lat": "50.717",
      "lon": "9.967"
    },
    "district": "Wartburgkreis",
    "name": "Geisa",
    "population": "4754",
    "state": "Thuringia"
  },
  {
    "area": "99.96",
    "coords": {
      "lat": "48.82583",
      "lon": "12.39250"
    },
    "district": "Straubing-Bogen",
    "name": "Geiselhöring",
    "population": "6860",
    "state": "Bavaria"
  },
  {
    "area": "88.33",
    "coords": {
      "lat": "48.667",
      "lon": "11.600"
    },
    "district": "Pfaffenhofen an der Ilm",
    "name": "Geisenfeld",
    "population": "11363",
    "state": "Bavaria"
  },
  {
    "area": "40.34",
    "coords": {
      "lat": "49.98444",
      "lon": "7.96722"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Geisenheim",
    "population": "11704",
    "state": "Hesse"
  },
  {
    "area": "73.74",
    "coords": {
      "lat": "47.92222",
      "lon": "8.64639"
    },
    "district": "Tuttlingen",
    "name": "Geisingen",
    "population": "6202",
    "state": "Baden-Württemberg"
  },
  {
    "area": "75.83",
    "coords": {
      "lat": "48.62444",
      "lon": "9.83056"
    },
    "district": "Göppingen",
    "name": "Geislingen an der Steige",
    "population": "28122",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.95",
    "coords": {
      "lat": "48.28750",
      "lon": "8.81250"
    },
    "district": "Zollernalbkreis",
    "name": "Geislingen",
    "population": "5881",
    "state": "Baden-Württemberg"
  },
  {
    "area": "54.71",
    "coords": {
      "lat": "51.050",
      "lon": "12.683"
    },
    "district": "Leipzig",
    "name": "Geithain",
    "population": "6888",
    "state": "Saxony"
  },
  {
    "area": "96.91",
    "coords": {
      "lat": "51.51972",
      "lon": "6.33250"
    },
    "district": "Kleve",
    "name": "Geldern",
    "population": "33836",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "45.18",
    "coords": {
      "lat": "50.200",
      "lon": "9.167"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Gelnhausen",
    "population": "23073",
    "state": "Hesse"
  },
  {
    "area": "104.84",
    "coords": {
      "lat": "51.517",
      "lon": "7.100"
    },
    "district": "Urban district",
    "name": "Gelsenkirchen",
    "population": "260654",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "75.09",
    "coords": {
      "lat": "50.04972",
      "lon": "9.70556"
    },
    "district": "Main-Spessart",
    "name": "Gemünden am Main",
    "population": "10119",
    "state": "Bavaria"
  },
  {
    "area": "58.67",
    "coords": {
      "lat": "50.967",
      "lon": "8.967"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Gemünden",
    "population": "3801",
    "state": "Hesse"
  },
  {
    "area": "61.91",
    "coords": {
      "lat": "48.400",
      "lon": "8.017"
    },
    "district": "Ortenaukreis",
    "name": "Gengenbach",
    "population": "11023",
    "state": "Baden-Württemberg"
  },
  {
    "area": "230.72",
    "coords": {
      "lat": "52.400",
      "lon": "12.167"
    },
    "district": "Jerichower Land",
    "name": "Genthin",
    "population": "13985",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "55.41",
    "coords": {
      "lat": "52.200",
      "lon": "8.067"
    },
    "district": "Osnabrück",
    "name": "Georgsmarienhütte",
    "population": "31827",
    "state": "Lower Saxony"
  },
  {
    "area": "40.38",
    "coords": {
      "lat": "49.24944",
      "lon": "9.92028"
    },
    "district": "Schwäbisch Hall",
    "name": "Gerabronn",
    "population": "4270",
    "state": "Baden-Württemberg"
  },
  {
    "area": "152.19",
    "coords": {
      "lat": "50.88056",
      "lon": "12.08333"
    },
    "district": "Urban district",
    "name": "Gera",
    "population": "94152",
    "state": "Thuringia"
  },
  {
    "area": "102.28",
    "coords": {
      "lat": "51.63306",
      "lon": "11.61667"
    },
    "district": "Mansfeld-Südharz",
    "name": "Gerbstedt",
    "population": "7110",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "24.65",
    "coords": {
      "lat": "47.867",
      "lon": "11.467"
    },
    "district": "Bad Tölz-Wolfratshausen",
    "name": "Geretsried",
    "population": "25275",
    "state": "Bavaria"
  },
  {
    "area": "29.93",
    "coords": {
      "lat": "51.07639",
      "lon": "12.90417"
    },
    "district": "Mittelsachsen",
    "name": "Geringswalde",
    "population": "4156",
    "state": "Saxony"
  },
  {
    "area": "17.54",
    "coords": {
      "lat": "48.80000",
      "lon": "9.06528"
    },
    "district": "Ludwigsburg",
    "name": "Gerlingen",
    "population": "19745",
    "state": "Baden-Württemberg"
  },
  {
    "area": "21.61",
    "coords": {
      "lat": "48.133",
      "lon": "11.367"
    },
    "district": "Fürstenfeldbruck",
    "name": "Germering",
    "population": "40389",
    "state": "Bavaria"
  },
  {
    "area": "21.40",
    "coords": {
      "lat": "49.21667",
      "lon": "8.36667"
    },
    "district": "Germersheim",
    "name": "Germersheim",
    "population": "20779",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "82.09",
    "coords": {
      "lat": "48.76333",
      "lon": "8.33417"
    },
    "district": "Rastatt",
    "name": "Gernsbach",
    "population": "14296",
    "state": "Baden-Württemberg"
  },
  {
    "area": "40.11",
    "coords": {
      "lat": "49.750",
      "lon": "8.483"
    },
    "district": "Groß-Gerau",
    "name": "Gernsheim",
    "population": "10423",
    "state": "Hesse"
  },
  {
    "area": "64.33",
    "coords": {
      "lat": "50.22389",
      "lon": "6.66139"
    },
    "district": "Vulkaneifel",
    "name": "Gerolstein",
    "population": "7676",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "18.35",
    "coords": {
      "lat": "49.900",
      "lon": "10.350"
    },
    "district": "Schweinfurt",
    "name": "Gerolzhofen",
    "population": "6889",
    "state": "Bavaria"
  },
  {
    "area": "89.37",
    "coords": {
      "lat": "50.450",
      "lon": "9.917"
    },
    "district": "Fulda",
    "name": "Gersfeld",
    "population": "5458",
    "state": "Hesse"
  },
  {
    "area": "33.95",
    "coords": {
      "lat": "48.417",
      "lon": "10.867"
    },
    "district": "Augsburg",
    "name": "Gersthofen",
    "population": "22473",
    "state": "Bavaria"
  },
  {
    "area": "80.78",
    "coords": {
      "lat": "51.95694",
      "lon": "7.00556"
    },
    "district": "Borken",
    "name": "Gescher",
    "population": "17205",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "97.48",
    "coords": {
      "lat": "51.650",
      "lon": "8.517"
    },
    "district": "Soest",
    "name": "Geseke",
    "population": "21343",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "26.27",
    "coords": {
      "lat": "51.317",
      "lon": "7.333"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Gevelsberg",
    "population": "30695",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "18.76",
    "coords": {
      "lat": "50.62361",
      "lon": "12.92333"
    },
    "district": "Erzgebirgskreis",
    "name": "Geyer",
    "population": "3456",
    "state": "Saxony"
  },
  {
    "area": "44.05",
    "coords": {
      "lat": "48.62167",
      "lon": "10.24500"
    },
    "district": "Heidenheim",
    "name": "Giengen",
    "population": "19666",
    "state": "Baden-Württemberg"
  },
  {
    "area": "72.56",
    "coords": {
      "lat": "50.58333",
      "lon": "8.66667"
    },
    "district": "Giessen",
    "name": "Giessen",
    "population": "88546",
    "state": "Hesse"
  },
  {
    "area": "104.86",
    "coords": {
      "lat": "52.48861",
      "lon": "10.54639"
    },
    "district": "Gifhorn",
    "name": "Gifhorn",
    "population": "42519",
    "state": "Lower Saxony"
  },
  {
    "area": "13.94",
    "coords": {
      "lat": "49.983",
      "lon": "8.333"
    },
    "district": "Groß-Gerau",
    "name": "Ginsheim-Gustavsburg",
    "population": "16807",
    "state": "Hesse"
  },
  {
    "area": "35.91",
    "coords": {
      "lat": "51.567",
      "lon": "6.967"
    },
    "district": "Recklinghausen",
    "name": "Gladbeck",
    "population": "75687",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "72.28",
    "coords": {
      "lat": "50.76806",
      "lon": "8.58278"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Gladenbach",
    "population": "12262",
    "state": "Hesse"
  },
  {
    "area": "95.57",
    "coords": {
      "lat": "50.85000",
      "lon": "13.78333"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Glashütte",
    "population": "6705",
    "state": "Saxony"
  },
  {
    "area": "51.49",
    "coords": {
      "lat": "50.82333",
      "lon": "12.54444"
    },
    "district": "Zwickau",
    "name": "Glauchau",
    "population": "22440",
    "state": "Saxony"
  },
  {
    "area": "11.22",
    "coords": {
      "lat": "53.54056",
      "lon": "10.21111"
    },
    "district": "Stormarn",
    "name": "Glinde",
    "population": "18443",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "39.70",
    "coords": {
      "lat": "54.83361",
      "lon": "9.55000"
    },
    "district": "Schleswig-Flensburg",
    "name": "GlücksburgLyksborg",
    "population": "6124",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "22.76",
    "coords": {
      "lat": "53.79167",
      "lon": "9.42194"
    },
    "district": "Steinburg",
    "name": "Glückstadt",
    "population": "11069",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "41.08",
    "coords": {
      "lat": "53.967",
      "lon": "12.717"
    },
    "district": "Rostock",
    "name": "Gnoien",
    "population": "2880",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "115.38",
    "coords": {
      "lat": "51.68389",
      "lon": "6.16194"
    },
    "district": "Kleve",
    "name": "Goch",
    "population": "33825",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "64.85",
    "coords": {
      "lat": "53.567",
      "lon": "12.067"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Goldberg",
    "population": "3448",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "30.68",
    "coords": {
      "lat": "50.0115000",
      "lon": "11.6873139"
    },
    "district": "Bayreuth",
    "name": "Goldkronach",
    "population": "3487",
    "state": "Bavaria"
  },
  {
    "area": "63.29",
    "coords": {
      "lat": "51.967",
      "lon": "13.600"
    },
    "district": "Dahme-Spreewald",
    "name": "Golßen",
    "population": "2542",
    "state": "Brandenburg"
  },
  {
    "area": "159.96",
    "coords": {
      "lat": "52.067",
      "lon": "11.833"
    },
    "district": "Jerichower Land",
    "name": "Gommern",
    "population": "10543",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "163.71",
    "coords": {
      "lat": "51.90722",
      "lon": "10.43000"
    },
    "district": "Goslar",
    "name": "Goslar",
    "population": "50753",
    "state": "Lower Saxony"
  },
  {
    "area": "69.58",
    "coords": {
      "lat": "50.94889",
      "lon": "10.71833"
    },
    "district": "Gotha",
    "name": "Gotha",
    "population": "45733",
    "state": "Thuringia"
  },
  {
    "area": "72.08",
    "coords": {
      "lat": "53.267",
      "lon": "11.567"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Grabow",
    "population": "5633",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "63.79",
    "coords": {
      "lat": "48.850",
      "lon": "13.383"
    },
    "district": "Freyung-Grafenau",
    "name": "Grafenau",
    "population": "8256",
    "state": "Bavaria"
  },
  {
    "area": "216.24",
    "coords": {
      "lat": "49.717",
      "lon": "11.900"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Grafenwöhr",
    "population": "6363",
    "state": "Bavaria"
  },
  {
    "area": "29.57",
    "coords": {
      "lat": "48.050",
      "lon": "11.967"
    },
    "district": "Ebersberg",
    "name": "Grafing bei München",
    "population": "13660",
    "state": "Bavaria"
  },
  {
    "area": "121.15",
    "coords": {
      "lat": "53.00694",
      "lon": "13.15861"
    },
    "district": "Oberhavel",
    "name": "Gransee",
    "population": "5871",
    "state": "Brandenburg"
  },
  {
    "area": "55.37",
    "coords": {
      "lat": "50.750",
      "lon": "9.467"
    },
    "district": "Vogelsbergkreis",
    "name": "Grebenau",
    "population": "2431",
    "state": "Hesse"
  },
  {
    "area": "49.85",
    "coords": {
      "lat": "51.450",
      "lon": "9.417"
    },
    "district": "Kassel",
    "name": "Grebenstein",
    "population": "5745",
    "state": "Hesse"
  },
  {
    "area": "103.80",
    "coords": {
      "lat": "49.05194",
      "lon": "11.36056"
    },
    "district": "Roth",
    "name": "Greding",
    "population": "7126",
    "state": "Bavaria"
  },
  {
    "area": "50.50",
    "coords": {
      "lat": "54.083",
      "lon": "13.383"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Greifswald",
    "population": "59382",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "84.87",
    "coords": {
      "lat": "50.65472",
      "lon": "12.19972"
    },
    "district": "Greiz",
    "name": "Greiz",
    "population": "20524",
    "state": "Thuringia"
  },
  {
    "area": "19.15",
    "coords": {
      "lat": "51.22917",
      "lon": "10.94750"
    },
    "district": "Kyffhäuserkreis",
    "name": "Greußen",
    "population": "3480",
    "state": "Thuringia"
  },
  {
    "area": "102.6",
    "coords": {
      "lat": "51.08833",
      "lon": "6.58750"
    },
    "district": "Rhein-Kreis Neuss",
    "name": "Grevenbroich",
    "population": "63620",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "140.26",
    "coords": {
      "lat": "52.09167",
      "lon": "7.60833"
    },
    "district": "Steinfurt",
    "name": "Greven",
    "population": "37692",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "52.32",
    "coords": {
      "lat": "53.867",
      "lon": "11.167"
    },
    "district": "Nordwestmecklenburg",
    "name": "Grevesmühlen",
    "population": "10354",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "21.4083",
    "coords": {
      "lat": "49.86389",
      "lon": "8.56389"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Griesheim, Hesse",
    "population": "27435",
    "state": "Hesse"
  },
  {
    "area": "217.38",
    "coords": {
      "lat": "51.23861",
      "lon": "12.72528"
    },
    "district": "Leipzig",
    "name": "Grimma",
    "population": "28180",
    "state": "Saxony"
  },
  {
    "area": "50.29",
    "coords": {
      "lat": "54.11000",
      "lon": "13.04139"
    },
    "district": "Vorpommern-Rügen",
    "name": "Grimmen",
    "population": "9572",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "70.06",
    "coords": {
      "lat": "51.15556",
      "lon": "12.28056"
    },
    "district": "Leipzig",
    "name": "Groitzsch",
    "population": "7550",
    "state": "Saxony"
  },
  {
    "area": "88.13",
    "coords": {
      "lat": "52.067",
      "lon": "9.783"
    },
    "district": "Hildesheim",
    "name": "Gronau",
    "population": "10858",
    "state": "Lower Saxony"
  },
  {
    "area": "78.63",
    "coords": {
      "lat": "52.21250",
      "lon": "7.04167"
    },
    "district": "Borken",
    "name": "Gronau",
    "population": "48072",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "18.27",
    "coords": {
      "lat": "49.800",
      "lon": "8.833"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Groß-Bieberau",
    "population": "4665",
    "state": "Hesse"
  },
  {
    "area": "54.47",
    "coords": {
      "lat": "49.91917",
      "lon": "8.48500"
    },
    "district": "Groß-Gerau",
    "name": "Groß-Gerau",
    "population": "25302",
    "state": "Hesse"
  },
  {
    "area": "86.84",
    "coords": {
      "lat": "49.867",
      "lon": "8.933"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Groß-Umstadt",
    "population": "21162",
    "state": "Hesse"
  },
  {
    "area": "37.62",
    "coords": {
      "lat": "51.25750",
      "lon": "9.78444"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Großalmerode",
    "population": "6402",
    "state": "Hesse"
  },
  {
    "area": "25.51",
    "coords": {
      "lat": "49.00139",
      "lon": "9.29306"
    },
    "district": "Ludwigsburg",
    "name": "Großbottwar",
    "population": "8512",
    "state": "Baden-Württemberg"
  },
  {
    "area": "80.76",
    "coords": {
      "lat": "50.58278",
      "lon": "11.01056"
    },
    "district": "Ilm-Kreis",
    "name": "Großbreitenbach",
    "population": "6352",
    "state": "Thuringia"
  },
  {
    "area": "63.26",
    "coords": {
      "lat": "51.24944",
      "lon": "10.83222"
    },
    "district": "Kyffhäuserkreis",
    "name": "Großenehrich",
    "population": "2325",
    "state": "Thuringia"
  },
  {
    "area": "96.79",
    "coords": {
      "lat": "51.283",
      "lon": "13.550"
    },
    "district": "Meißen",
    "name": "Großenhain",
    "population": "18218",
    "state": "Saxony"
  },
  {
    "area": "81.29",
    "coords": {
      "lat": "51.58306",
      "lon": "14.00000"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Großräschen",
    "population": "8572",
    "state": "Brandenburg"
  },
  {
    "area": "40.86",
    "coords": {
      "lat": "51.14444",
      "lon": "14.01667"
    },
    "district": "Bautzen",
    "name": "Großröhrsdorf",
    "population": "9510",
    "state": "Saxony"
  },
  {
    "area": "61.44",
    "coords": {
      "lat": "50.96639",
      "lon": "13.27806"
    },
    "district": "Mittelsachsen",
    "name": "Großschirma",
    "population": "5665",
    "state": "Saxony"
  },
  {
    "area": "37.88",
    "coords": {
      "lat": "49.633",
      "lon": "11.250"
    },
    "district": "Forchheim",
    "name": "Gräfenberg, Bavaria",
    "population": "4106",
    "state": "Bavaria"
  },
  {
    "area": "158.90",
    "coords": {
      "lat": "51.717",
      "lon": "12.433"
    },
    "district": "Wittenberg",
    "name": "Gräfenhainichen",
    "population": "11654",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "36.45",
    "coords": {
      "lat": "50.533",
      "lon": "11.300"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Gräfenthal",
    "population": "1963",
    "state": "Thuringia"
  },
  {
    "area": "28.78",
    "coords": {
      "lat": "51.41667",
      "lon": "13.46639"
    },
    "district": "Meißen",
    "name": "Gröditz",
    "population": "7125",
    "state": "Saxony"
  },
  {
    "area": "59.67",
    "coords": {
      "lat": "51.93333",
      "lon": "11.21667"
    },
    "district": "Börde",
    "name": "Gröningen",
    "population": "3603",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "89.25",
    "coords": {
      "lat": "50.600",
      "lon": "8.950"
    },
    "district": "Gießen",
    "name": "Grünberg",
    "population": "13598",
    "state": "Hesse"
  },
  {
    "area": "22.26",
    "coords": {
      "lat": "50.567",
      "lon": "12.800"
    },
    "district": "Erzgebirgskreis",
    "name": "Grünhain-Beierfeld",
    "population": "5898",
    "state": "Saxony"
  },
  {
    "area": "44.72",
    "coords": {
      "lat": "49.600",
      "lon": "9.750"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Grünsfeld",
    "population": "3651",
    "state": "Baden-Württemberg"
  },
  {
    "area": "18.09",
    "coords": {
      "lat": "49.56917",
      "lon": "8.16806"
    },
    "district": "Bad Dürkheim",
    "name": "Grünstadt",
    "population": "13422",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "43.75",
    "coords": {
      "lat": "51.95333",
      "lon": "14.71667"
    },
    "district": "Spree-Neiße",
    "name": "Guben",
    "population": "16933",
    "state": "Brandenburg"
  },
  {
    "area": "46.5",
    "coords": {
      "lat": "51.183",
      "lon": "9.367"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Gudensberg",
    "population": "9657",
    "state": "Hesse"
  },
  {
    "area": "95.3",
    "coords": {
      "lat": "51.033",
      "lon": "7.567"
    },
    "district": "Oberbergischer Kreis",
    "name": "Gummersbach",
    "population": "50688",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "53.97",
    "coords": {
      "lat": "48.550",
      "lon": "10.367"
    },
    "district": "Dillingen",
    "name": "Gundelfingen an der Donau",
    "population": "7796",
    "state": "Bavaria"
  },
  {
    "area": "38.45",
    "coords": {
      "lat": "49.283",
      "lon": "9.167"
    },
    "district": "Heilbronn",
    "name": "Gundelsheim",
    "population": "7254",
    "state": "Baden-Württemberg"
  },
  {
    "area": "82.73",
    "coords": {
      "lat": "49.11472",
      "lon": "10.75417"
    },
    "district": "Weißenburg-Gunzenhausen",
    "name": "Gunzenhausen",
    "population": "16614",
    "state": "Bavaria"
  },
  {
    "area": "59.22",
    "coords": {
      "lat": "48.70250",
      "lon": "9.65278"
    },
    "district": "Göppingen (district)",
    "name": "Göppingen",
    "population": "57558",
    "state": "Baden-Württemberg"
  },
  {
    "area": "67.22",
    "coords": {
      "lat": "51.15278",
      "lon": "14.98722"
    },
    "district": "Görlitz",
    "name": "Görlitz",
    "population": "56324",
    "state": "Saxony"
  },
  {
    "area": "116.89",
    "coords": {
      "lat": "51.53389",
      "lon": "9.93556"
    },
    "district": "Göttingen",
    "name": "Göttingen",
    "population": "119801",
    "state": "Lower Saxony"
  },
  {
    "area": "14.04",
    "coords": {
      "lat": "50.89028",
      "lon": "12.43278"
    },
    "district": "Altenburger Land",
    "name": "Gößnitz",
    "population": "3398",
    "state": "Thuringia"
  },
  {
    "area": "16.26",
    "coords": {
      "lat": "49.067",
      "lon": "9.000"
    },
    "district": "Heilbronn",
    "name": "Güglingen",
    "population": "6353",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.40",
    "coords": {
      "lat": "48.45267",
      "lon": "10.27133"
    },
    "district": "Günzburg",
    "name": "Günzburg",
    "population": "20707",
    "state": "Bavaria"
  },
  {
    "area": "36.16",
    "coords": {
      "lat": "51.79722",
      "lon": "11.61000"
    },
    "district": "Salzlandkreis",
    "name": "Güsten",
    "population": "4144",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "70.86",
    "coords": {
      "lat": "53.79389",
      "lon": "12.17639"
    },
    "district": "Rostock",
    "name": "Güstrow",
    "population": "29241",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "111.99",
    "coords": {
      "lat": "51.900",
      "lon": "8.383"
    },
    "district": "Gütersloh",
    "name": "Gütersloh",
    "population": "100194",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "57.29",
    "coords": {
      "lat": "53.950",
      "lon": "13.417"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Gützkow",
    "population": "2965",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "24.22",
    "coords": {
      "lat": "51.167",
      "lon": "7.000"
    },
    "district": "Mettmann",
    "name": "Haan",
    "population": "30484",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "21.43",
    "coords": {
      "lat": "50.66139",
      "lon": "7.82028"
    },
    "district": "Westerwaldkreis",
    "name": "Hachenburg",
    "population": "6059",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "40.99",
    "coords": {
      "lat": "50.450",
      "lon": "8.050"
    },
    "district": "Limburg-Weilburg",
    "name": "Hadamar",
    "population": "12480",
    "state": "Hesse"
  },
  {
    "area": "15.85",
    "coords": {
      "lat": "49.02056",
      "lon": "8.24833"
    },
    "district": "Germersheim",
    "name": "Hagenbach",
    "population": "5523",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "67.44",
    "coords": {
      "lat": "53.417",
      "lon": "11.183"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Hagenow",
    "population": "12137",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "160.4",
    "coords": {
      "lat": "51.367",
      "lon": "7.483"
    },
    "district": "Urban districts of Germany",
    "name": "Hagen",
    "population": "188814",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "76.46",
    "coords": {
      "lat": "48.36472",
      "lon": "8.80500"
    },
    "district": "Zollernalbkreis",
    "name": "Haigerloch",
    "population": "10669",
    "state": "Baden-Württemberg"
  },
  {
    "area": "106.67",
    "coords": {
      "lat": "50.74222",
      "lon": "8.20389"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Haiger",
    "population": "19378",
    "state": "Hesse"
  },
  {
    "area": "51.57",
    "coords": {
      "lat": "50.96972",
      "lon": "13.12528"
    },
    "district": "Mittelsachsen",
    "name": "Hainichen",
    "population": "8588",
    "state": "Saxony"
  },
  {
    "area": "28.92",
    "coords": {
      "lat": "48.52444",
      "lon": "8.65028"
    },
    "district": "Calw",
    "name": "Haiterbach",
    "population": "5761",
    "state": "Baden-Württemberg"
  },
  {
    "area": "142.97",
    "coords": {
      "lat": "51.89583",
      "lon": "11.04667"
    },
    "district": "Harz",
    "name": "Halberstadt",
    "population": "40256",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "156.15",
    "coords": {
      "lat": "52.283",
      "lon": "11.417"
    },
    "district": "Börde",
    "name": "Haldensleben",
    "population": "19247",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "135.01",
    "coords": {
      "lat": "51.48278",
      "lon": "11.96972"
    },
    "district": "Urban district",
    "name": "Halle (Saale)",
    "population": "239257",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "65.36",
    "coords": {
      "lat": "51.11167",
      "lon": "8.62306"
    },
    "district": "Hochsauerlandkreis",
    "name": "Hallenberg",
    "population": "4486",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "69.21",
    "coords": {
      "lat": "52.067",
      "lon": "8.367"
    },
    "district": "Gütersloh",
    "name": "Halle",
    "population": "21640",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "14.54",
    "coords": {
      "lat": "49.933",
      "lon": "10.883"
    },
    "district": "Bamberg",
    "name": "Hallstadt",
    "population": "8575",
    "state": "Bavaria"
  },
  {
    "area": "158.34",
    "coords": {
      "lat": "51.750",
      "lon": "7.183"
    },
    "district": "Recklinghausen",
    "name": "Haltern am See",
    "population": "38013",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "77.37",
    "coords": {
      "lat": "51.183",
      "lon": "7.467"
    },
    "district": "Märkischer Kreis",
    "name": "Halver",
    "population": "16106",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "755.22",
    "coords": {
      "lat": "53.56528",
      "lon": "10.00139"
    },
    "name": "Hamburg",
    "population": "1899160",
    "state": "Hamburg"
  },
  {
    "area": "102.30",
    "coords": {
      "lat": "52.100",
      "lon": "9.367"
    },
    "district": "Hamelin-Pyrmont",
    "name": "Hamelin",
    "population": "57510",
    "state": "Lower Saxony"
  },
  {
    "area": "128.89",
    "coords": {
      "lat": "50.117",
      "lon": "9.900"
    },
    "district": "Bad Kissingen",
    "name": "Hammelburg",
    "population": "11037",
    "state": "Bavaria"
  },
  {
    "area": "164.44",
    "coords": {
      "lat": "51.73194",
      "lon": "6.59083"
    },
    "district": "Wesel",
    "name": "Hamminkeln",
    "population": "26739",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "226.26",
    "coords": {
      "lat": "51.683",
      "lon": "7.817"
    },
    "district": "Urban District",
    "name": "Hamm",
    "population": "179111",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "76.49",
    "coords": {
      "lat": "50.13278",
      "lon": "8.91694"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Hanau",
    "population": "96023",
    "state": "Hesse"
  },
  {
    "area": "121.12",
    "coords": {
      "lat": "51.417",
      "lon": "9.650"
    },
    "district": "Göttingen",
    "name": "Hann. Münden",
    "population": "23805",
    "state": "Lower Saxony"
  },
  {
    "area": "204.01",
    "coords": {
      "lat": "52.367",
      "lon": "9.717"
    },
    "district": "Hannover",
    "name": "Hanover",
    "population": "538068",
    "state": "Lower Saxony"
  },
  {
    "area": "76.49",
    "coords": {
      "lat": "53.47694",
      "lon": "9.70111"
    },
    "district": "Stade",
    "name": "Hanseatic City of Buxtehude",
    "population": "40150",
    "state": "Lower Saxony"
  },
  {
    "area": "123.98",
    "coords": {
      "lat": "51.283",
      "lon": "8.867"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Hanseatic City of Korbach",
    "population": "23581",
    "state": "Hesse"
  },
  {
    "area": "110.03",
    "coords": {
      "lat": "53.60083",
      "lon": "9.47639"
    },
    "district": "Stade",
    "name": "Hanseatic City of Stade",
    "population": "47533",
    "state": "Lower Saxony"
  },
  {
    "area": "73.17",
    "coords": {
      "lat": "48.767",
      "lon": "10.667"
    },
    "district": "Donau-Ries",
    "name": "Harburg",
    "population": "5535",
    "state": "Bavaria"
  },
  {
    "area": "83.87",
    "coords": {
      "lat": "51.65222",
      "lon": "9.82944"
    },
    "district": "Northeim",
    "name": "Hardegsen",
    "population": "7587",
    "state": "Lower Saxony"
  },
  {
    "area": "208.76",
    "coords": {
      "lat": "52.767",
      "lon": "7.217"
    },
    "district": "Emsland",
    "name": "Haren, Germany",
    "population": "23829",
    "state": "Lower Saxony"
  },
  {
    "area": "100.12",
    "coords": {
      "lat": "51.96667",
      "lon": "8.23306"
    },
    "district": "Gütersloh",
    "name": "Harsewinkel",
    "population": "25147",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "36.72",
    "coords": {
      "lat": "50.66833",
      "lon": "12.67139"
    },
    "district": "Zwickau",
    "name": "Hartenstein",
    "population": "4563",
    "state": "Saxony"
  },
  {
    "area": "54.29",
    "coords": {
      "lat": "51.097800",
      "lon": "12.977300"
    },
    "district": "Mittelsachsen",
    "name": "Hartha",
    "population": "7034",
    "state": "Saxony"
  },
  {
    "area": "164.57",
    "coords": {
      "lat": "51.64222",
      "lon": "11.14417"
    },
    "district": "Harz",
    "name": "Harzgerode",
    "population": "7745",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "159.2",
    "coords": {
      "lat": "52.667",
      "lon": "7.467"
    },
    "district": "Emsland",
    "name": "Haselünne",
    "population": "12914",
    "state": "Lower Saxony"
  },
  {
    "area": "18.71",
    "coords": {
      "lat": "48.27778",
      "lon": "8.08694"
    },
    "district": "Ortenaukreis",
    "name": "Haslach im Kinzigtal",
    "population": "7114",
    "state": "Baden-Württemberg"
  },
  {
    "area": "15.82",
    "coords": {
      "lat": "50.067",
      "lon": "8.467"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Hattersheim am Main",
    "population": "27590",
    "state": "Hesse"
  },
  {
    "area": "71.40",
    "coords": {
      "lat": "51.39917",
      "lon": "7.18583"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Hattingen",
    "population": "54562",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "58.51",
    "coords": {
      "lat": "51.000",
      "lon": "8.550"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Hatzfeld",
    "population": "2939",
    "state": "Hesse"
  },
  {
    "area": "36.07",
    "coords": {
      "lat": "48.28528",
      "lon": "8.17972"
    },
    "district": "Ortenaukreis",
    "name": "Hausach",
    "population": "5768",
    "state": "Baden-Württemberg"
  },
  {
    "area": "82.82",
    "coords": {
      "lat": "48.650",
      "lon": "13.633"
    },
    "district": "Passau",
    "name": "Hauzenberg",
    "population": "11649",
    "state": "Bavaria"
  },
  {
    "area": "149.13",
    "coords": {
      "lat": "52.82500",
      "lon": "12.07444"
    },
    "district": "Stendal",
    "name": "Havelberg",
    "population": "6537",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "66.60",
    "coords": {
      "lat": "52.500",
      "lon": "12.467"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Havelsee",
    "population": "3242",
    "state": "Brandenburg"
  },
  {
    "area": "63.31",
    "coords": {
      "lat": "48.27528",
      "lon": "9.47806"
    },
    "district": "Reutlingen",
    "name": "Hayingen",
    "population": "2203",
    "state": "Baden-Württemberg"
  },
  {
    "area": "52.77",
    "coords": {
      "lat": "50.017",
      "lon": "10.500"
    },
    "district": "Haßberge",
    "name": "Haßfurt",
    "population": "13609",
    "state": "Bavaria"
  },
  {
    "area": "66.44",
    "coords": {
      "lat": "48.35167",
      "lon": "8.96333"
    },
    "district": "Zollernalbkreis",
    "name": "Hechingen",
    "population": "19324",
    "state": "Baden-Württemberg"
  },
  {
    "area": "95.34",
    "coords": {
      "lat": "51.850",
      "lon": "11.517"
    },
    "district": "Salzlandkreis",
    "name": "Hecklingen",
    "population": "6970",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "58.64",
    "coords": {
      "lat": "49.117",
      "lon": "11.117"
    },
    "district": "Roth",
    "name": "Heideck",
    "population": "4653",
    "state": "Bavaria"
  },
  {
    "area": "108.83",
    "coords": {
      "lat": "49.417",
      "lon": "8.717"
    },
    "district": "Urban district",
    "name": "Heidelberg",
    "population": "160355",
    "state": "Baden-Württemberg"
  },
  {
    "area": "11.00",
    "coords": {
      "lat": "50.983",
      "lon": "13.867"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Heidenau",
    "population": "16649",
    "state": "Saxony"
  },
  {
    "area": "107.10",
    "coords": {
      "lat": "48.67611",
      "lon": "10.15444"
    },
    "district": "Heidenheim",
    "name": "Heidenheim an der Brenz",
    "population": "49526",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.8938",
    "coords": {
      "lat": "54.19611",
      "lon": "9.09333"
    },
    "district": "Dithmarschen",
    "name": "Heide",
    "population": "21684",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "70.88",
    "coords": {
      "lat": "51.37889",
      "lon": "10.13861"
    },
    "district": "Eichsfeld",
    "name": "Heilbad Heiligenstadt",
    "population": "17105",
    "state": "Thuringia"
  },
  {
    "area": "99.88",
    "coords": {
      "lat": "49.150",
      "lon": "9.217"
    },
    "district": "Stadtkreis",
    "name": "Heilbronn",
    "population": "125960",
    "state": "Baden-Württemberg"
  },
  {
    "area": "18.12",
    "coords": {
      "lat": "54.37389",
      "lon": "10.97972"
    },
    "district": "Ostholstein",
    "name": "Heiligenhafen",
    "population": "9211",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "27.47",
    "coords": {
      "lat": "51.31667",
      "lon": "6.96667"
    },
    "district": "Mettmann",
    "name": "Heiligenhaus",
    "population": "26335",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "62.23",
    "coords": {
      "lat": "49.317",
      "lon": "10.800"
    },
    "district": "Ansbach",
    "name": "Heilsbronn",
    "population": "9670",
    "state": "Bavaria"
  },
  {
    "area": "64.8",
    "coords": {
      "lat": "50.63306",
      "lon": "6.48306"
    },
    "district": "Düren",
    "name": "Heimbach",
    "population": "4333",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "14.32",
    "coords": {
      "lat": "48.80556",
      "lon": "8.86194"
    },
    "district": "Enzkreis",
    "name": "Heimsheim",
    "population": "5035",
    "state": "Baden-Württemberg"
  },
  {
    "area": "92.14",
    "coords": {
      "lat": "51.06306",
      "lon": "6.09639"
    },
    "district": "Heinsberg",
    "name": "Heinsberg",
    "population": "41946",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "11.71",
    "coords": {
      "lat": "47.87528",
      "lon": "7.65472"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Heitersheim",
    "population": "6257",
    "state": "Baden-Württemberg"
  },
  {
    "area": "112.75",
    "coords": {
      "lat": "50.283",
      "lon": "10.733"
    },
    "district": "Hildburghausen",
    "name": "Heldburg",
    "population": "3448",
    "state": "Thuringia"
  },
  {
    "area": "58.66",
    "coords": {
      "lat": "50.217",
      "lon": "11.683"
    },
    "district": "Hof",
    "name": "Helmbrechts",
    "population": "8413",
    "state": "Bavaria"
  },
  {
    "area": "66.54",
    "coords": {
      "lat": "52.22806",
      "lon": "11.01056"
    },
    "district": "Helmstedt",
    "name": "Helmstedt",
    "population": "25728",
    "state": "Lower Saxony"
  },
  {
    "area": "122.46",
    "coords": {
      "lat": "49.05194",
      "lon": "11.78278"
    },
    "district": "Regensburg",
    "name": "Hemau",
    "population": "9224",
    "state": "Bavaria"
  },
  {
    "area": "67.56",
    "coords": {
      "lat": "51.38333",
      "lon": "7.76667"
    },
    "district": "Märkischer Kreis",
    "name": "Hemer",
    "population": "34080",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "31.65",
    "coords": {
      "lat": "52.32361",
      "lon": "9.72556"
    },
    "district": "Hanover",
    "name": "Hemmingen",
    "population": "18998",
    "state": "Lower Saxony"
  },
  {
    "area": "45.08",
    "coords": {
      "lat": "53.70250",
      "lon": "9.13944"
    },
    "district": "Cuxhaven",
    "name": "Hemmoor",
    "population": "8673",
    "state": "Lower Saxony"
  },
  {
    "area": "12.86",
    "coords": {
      "lat": "49.59028",
      "lon": "8.65639"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Hemsbach",
    "population": "11968",
    "state": "Baden-Württemberg"
  },
  {
    "area": "105.79",
    "coords": {
      "lat": "50.783",
      "lon": "7.283"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Hennef",
    "population": "47339",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "31.29",
    "coords": {
      "lat": "52.63778",
      "lon": "13.20361"
    },
    "district": "Oberhavel",
    "name": "Hennigsdorf",
    "population": "26272",
    "state": "Brandenburg"
  },
  {
    "area": "52.14",
    "coords": {
      "lat": "49.64306",
      "lon": "8.63889"
    },
    "district": "Bergstraße",
    "name": "Heppenheim",
    "population": "26023",
    "state": "Hesse"
  },
  {
    "area": "35.48",
    "coords": {
      "lat": "48.22194",
      "lon": "7.77750"
    },
    "district": "Emmendingen",
    "name": "Herbolzheim",
    "population": "11065",
    "state": "Baden-Württemberg"
  },
  {
    "area": "63.82",
    "coords": {
      "lat": "50.68250",
      "lon": "8.30611"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Herborn, Hesse",
    "population": "20603",
    "state": "Hesse"
  },
  {
    "area": "58.63",
    "coords": {
      "lat": "48.62528",
      "lon": "10.17389"
    },
    "district": "Heidenheim",
    "name": "Herbrechtingen",
    "population": "13051",
    "state": "Baden-Württemberg"
  },
  {
    "area": "79.98",
    "coords": {
      "lat": "50.550",
      "lon": "9.350"
    },
    "district": "Vogelsbergkreis",
    "name": "Herbstein",
    "population": "4788",
    "state": "Hesse"
  },
  {
    "area": "22.4",
    "coords": {
      "lat": "51.400",
      "lon": "7.433"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Herdecke",
    "population": "22733",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "18.00",
    "coords": {
      "lat": "50.77750",
      "lon": "7.95472"
    },
    "district": "Altenkirchen",
    "name": "Herdorf",
    "population": "6498",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "78.95",
    "coords": {
      "lat": "52.13333",
      "lon": "8.68333"
    },
    "district": "Herford",
    "name": "Herford",
    "population": "66608",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "61.18",
    "coords": {
      "lat": "50.88722",
      "lon": "10.00556"
    },
    "district": "Hersfeld-Rotenburg",
    "name": "Heringen",
    "population": "7187",
    "state": "Hesse"
  },
  {
    "area": "66.73",
    "coords": {
      "lat": "51.44722",
      "lon": "10.88083"
    },
    "district": "Nordhausen",
    "name": "Heringen",
    "population": "4737",
    "state": "Thuringia"
  },
  {
    "area": "30.85",
    "coords": {
      "lat": "49.65722",
      "lon": "6.94889"
    },
    "district": "Trier-Saarburg",
    "name": "Hermeskeil",
    "population": "6492",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "7.51",
    "coords": {
      "lat": "50.89806",
      "lon": "11.85667"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Hermsdorf",
    "population": "7893",
    "state": "Thuringia"
  },
  {
    "area": "51.41",
    "coords": {
      "lat": "51.55000",
      "lon": "7.21667"
    },
    "district": "Urban district",
    "name": "Herne",
    "population": "156374",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "65.71",
    "coords": {
      "lat": "48.59667",
      "lon": "8.87083"
    },
    "district": "Böblingen",
    "name": "Herrenberg",
    "population": "31545",
    "state": "Baden-Württemberg"
  },
  {
    "area": "81.71",
    "coords": {
      "lat": "49.217",
      "lon": "10.517"
    },
    "district": "Ansbach",
    "name": "Herrieden",
    "population": "7999",
    "state": "Bavaria"
  },
  {
    "area": "73.94",
    "coords": {
      "lat": "51.01667",
      "lon": "14.74167"
    },
    "district": "Görlitz",
    "name": "Herrnhut",
    "population": "5922",
    "state": "Saxony"
  },
  {
    "area": "22.91",
    "coords": {
      "lat": "49.50806",
      "lon": "11.43278"
    },
    "district": "Nürnberger Land",
    "name": "Hersbruck",
    "population": "12512",
    "state": "Bavaria"
  },
  {
    "area": "37.31",
    "coords": {
      "lat": "51.600",
      "lon": "7.133"
    },
    "district": "Recklinghausen",
    "name": "Herten",
    "population": "61791",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "71.88",
    "coords": {
      "lat": "51.65750",
      "lon": "10.34111"
    },
    "district": "Göttingen",
    "name": "Herzberg am Harz",
    "population": "12889",
    "state": "Lower Saxony"
  },
  {
    "area": "148.48",
    "coords": {
      "lat": "51.68306",
      "lon": "13.23306"
    },
    "district": "Elbe-Elster",
    "name": "Herzberg",
    "population": "9027",
    "state": "Brandenburg"
  },
  {
    "area": "47.60",
    "coords": {
      "lat": "49.567",
      "lon": "10.883"
    },
    "district": "Erlangen-Höchstadt",
    "name": "Herzogenaurach",
    "population": "23126",
    "state": "Bavaria"
  },
  {
    "area": "33.401",
    "coords": {
      "lat": "50.867",
      "lon": "6.100"
    },
    "district": "Aachen",
    "name": "Herzogenrath",
    "population": "46402",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "105.87",
    "coords": {
      "lat": "51.200",
      "lon": "9.717"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Hessisch Lichtenau",
    "population": "12359",
    "state": "Hesse"
  },
  {
    "area": "120.39",
    "coords": {
      "lat": "52.167",
      "lon": "9.250"
    },
    "district": "Hameln-Pyrmont",
    "name": "Hessisch Oldendorf",
    "population": "18130",
    "state": "Lower Saxony"
  },
  {
    "area": "46.07",
    "coords": {
      "lat": "48.21611",
      "lon": "9.23139"
    },
    "district": "Sigmaringen",
    "name": "Hettingen",
    "population": "1783",
    "state": "Baden-Württemberg"
  },
  {
    "area": "36.92",
    "coords": {
      "lat": "51.633",
      "lon": "11.500"
    },
    "district": "Mansfeld-Südharz",
    "name": "Hettstedt",
    "population": "14023",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "25.81",
    "coords": {
      "lat": "48.78806",
      "lon": "9.93333"
    },
    "district": "Ostalbkreis",
    "name": "Heubach",
    "population": "9774",
    "state": "Baden-Württemberg"
  },
  {
    "area": "19.03",
    "coords": {
      "lat": "50.050",
      "lon": "8.800"
    },
    "district": "Offenbach",
    "name": "Heusenstamm",
    "population": "18973",
    "state": "Hesse"
  },
  {
    "area": "80.88",
    "coords": {
      "lat": "50.99833",
      "lon": "8.10944"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Hilchenbach",
    "population": "14906",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "72.94",
    "coords": {
      "lat": "50.41667",
      "lon": "10.75000"
    },
    "district": "Hildburghausen",
    "name": "Hildburghausen",
    "population": "11836",
    "state": "Thuringia"
  },
  {
    "coords": {
      "lat": "51.17139",
      "lon": "6.93944"
    },
    "name": "Hilden",
    "population": "0 2103",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "92.96",
    "coords": {
      "lat": "52.150",
      "lon": "9.950"
    },
    "district": "Hildesheim",
    "name": "Hildesheim",
    "population": "101990",
    "state": "Lower Saxony"
  },
  {
    "area": "20.62",
    "coords": {
      "lat": "50.29306",
      "lon": "6.67500"
    },
    "district": "Vulkaneifel",
    "name": "Hillesheim",
    "population": "3149",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "91.42",
    "coords": {
      "lat": "49.183",
      "lon": "11.183"
    },
    "district": "Roth",
    "name": "Hilpoltstein",
    "population": "13624",
    "state": "Bavaria"
  },
  {
    "area": "74.90",
    "coords": {
      "lat": "49.533",
      "lon": "11.950"
    },
    "district": "Amberg-Sulzbach",
    "name": "Hirschau",
    "population": "5629",
    "state": "Bavaria"
  },
  {
    "area": "24.12",
    "coords": {
      "lat": "50.40583",
      "lon": "11.82000"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Hirschberg",
    "population": "2123",
    "state": "Thuringia"
  },
  {
    "area": "30.86",
    "coords": {
      "lat": "49.45000",
      "lon": "8.90000"
    },
    "district": "Bergstraße",
    "name": "Hirschhorn",
    "population": "3460",
    "state": "Hesse"
  },
  {
    "area": "58.44",
    "coords": {
      "lat": "53.133",
      "lon": "11.050"
    },
    "district": "Lüchow-Dannenberg",
    "name": "Hitzacker",
    "population": "4951",
    "state": "Lower Saxony"
  },
  {
    "area": "19.43",
    "coords": {
      "lat": "50.01667",
      "lon": "8.35000"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Hochheim am Main",
    "population": "17743",
    "state": "Hesse"
  },
  {
    "area": "34.84",
    "coords": {
      "lat": "49.31806",
      "lon": "8.54722"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Hockenheim",
    "population": "21779",
    "state": "Baden-Württemberg"
  },
  {
    "area": "86.39",
    "coords": {
      "lat": "51.483",
      "lon": "9.400"
    },
    "district": "Kassel",
    "name": "Hofgeismar",
    "population": "15294",
    "state": "Hesse"
  },
  {
    "area": "56.35",
    "coords": {
      "lat": "50.133",
      "lon": "10.183"
    },
    "district": "Haßberge",
    "name": "Hofheim, Bavaria",
    "population": "5109",
    "state": "Bavaria"
  },
  {
    "area": "57.38",
    "coords": {
      "lat": "50.08333",
      "lon": "8.45000"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Hofheim, Hesse",
    "population": "39766",
    "state": "Hesse"
  },
  {
    "area": "58.02",
    "coords": {
      "lat": "50.31667",
      "lon": "11.91667"
    },
    "district": "Urban District",
    "name": "Hof",
    "population": "45930",
    "state": "Bavaria"
  },
  {
    "area": "48.06",
    "coords": {
      "lat": "52.667",
      "lon": "13.283"
    },
    "district": "Oberhavel",
    "name": "Hohen Neuendorf",
    "population": "26159",
    "state": "Brandenburg"
  },
  {
    "area": "8.19",
    "coords": {
      "lat": "50.100",
      "lon": "12.217"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Hohenberg a.d.Eger",
    "population": "1439",
    "state": "Bavaria"
  },
  {
    "area": "9.52",
    "coords": {
      "lat": "50.717",
      "lon": "12.050"
    },
    "district": "Greiz",
    "name": "Hohenleuben",
    "population": "1425",
    "state": "Thuringia"
  },
  {
    "area": "75.31",
    "coords": {
      "lat": "51.15639",
      "lon": "12.09806"
    },
    "district": "Burgenlandkreis",
    "name": "Hohenmölsen",
    "population": "9565",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "18.33",
    "coords": {
      "lat": "50.800",
      "lon": "12.717"
    },
    "district": "Zwickau",
    "name": "Hohenstein-Ernstthal",
    "population": "14607",
    "state": "Saxony"
  },
  {
    "area": "64.61",
    "coords": {
      "lat": "50.983",
      "lon": "14.117"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Hohnstein",
    "population": "3269",
    "state": "Saxony"
  },
  {
    "area": "81",
    "coords": {
      "lat": "49.933",
      "lon": "11.300"
    },
    "district": "Bayreuth",
    "name": "Hollfeld",
    "population": "5043",
    "state": "Bavaria"
  },
  {
    "area": "13.39",
    "coords": {
      "lat": "48.63917",
      "lon": "9.01083"
    },
    "district": "Böblingen",
    "name": "Holzgerlingen",
    "population": "13103",
    "state": "Baden-Württemberg"
  },
  {
    "area": "88.25",
    "coords": {
      "lat": "51.82972",
      "lon": "9.44833"
    },
    "district": "Holzminden",
    "name": "Holzminden",
    "population": "19998",
    "state": "Lower Saxony"
  },
  {
    "area": "99.99",
    "coords": {
      "lat": "51.03306",
      "lon": "9.40000"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Homberg",
    "population": "14035",
    "state": "Hesse"
  },
  {
    "area": "88",
    "coords": {
      "lat": "50.717",
      "lon": "9.000"
    },
    "district": "Vogelsbergkreis",
    "name": "Homberg",
    "population": "7400",
    "state": "Hesse"
  },
  {
    "area": "82.65",
    "coords": {
      "lat": "49.317",
      "lon": "7.333"
    },
    "district": "Saarpfalz",
    "name": "Homburg",
    "population": "41811",
    "state": "Saarland"
  },
  {
    "area": "119.84",
    "coords": {
      "lat": "48.44528",
      "lon": "8.69111"
    },
    "district": "Freudenstadt",
    "name": "Horb am Neckar",
    "population": "25135",
    "state": "Baden-Württemberg"
  },
  {
    "area": "90.15",
    "coords": {
      "lat": "51.88333",
      "lon": "8.96667"
    },
    "district": "Lippe",
    "name": "Horn-Bad Meinberg",
    "population": "17178",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "13.32",
    "coords": {
      "lat": "49.18806",
      "lon": "7.36917"
    },
    "district": "Südwestpfalz",
    "name": "Hornbach, Germany",
    "population": "1435",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "54.45",
    "coords": {
      "lat": "48.21333",
      "lon": "8.23083"
    },
    "district": "Ortenaukreis",
    "name": "Hornberg",
    "population": "4318",
    "state": "Baden-Württemberg"
  },
  {
    "area": "45",
    "coords": {
      "lat": "52.08056",
      "lon": "7.30833"
    },
    "district": "Steinfurt",
    "name": "Horstmar",
    "population": "6551",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "8.5",
    "coords": {
      "lat": "52.800",
      "lon": "9.133"
    },
    "district": "Nienburg/Weser",
    "name": "Hoya",
    "population": "3865",
    "state": "Lower Saxony"
  },
  {
    "area": "95.06",
    "coords": {
      "lat": "51.433",
      "lon": "14.250"
    },
    "district": "Bautzen",
    "name": "Hoyerswerda",
    "population": "32658",
    "state": "Saxony"
  },
  {
    "area": "86.75",
    "coords": {
      "lat": "50.467",
      "lon": "8.900"
    },
    "district": "Gießen",
    "name": "Hungen",
    "population": "12538",
    "state": "Hesse"
  },
  {
    "area": "25.82",
    "coords": {
      "lat": "54.46667",
      "lon": "9.05000"
    },
    "district": "Nordfriesland",
    "name": "Husum",
    "population": "23158",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "70.90",
    "coords": {
      "lat": "49.700",
      "lon": "10.800"
    },
    "district": "Erlangen-Höchstadt",
    "name": "Höchstadt",
    "population": "13422",
    "state": "Bavaria"
  },
  {
    "area": "37.45",
    "coords": {
      "lat": "48.600",
      "lon": "10.550"
    },
    "district": "Dillingen",
    "name": "Höchstädt an der Donau",
    "population": "6756",
    "state": "Bavaria"
  },
  {
    "area": "15.87",
    "coords": {
      "lat": "50.43500",
      "lon": "7.67111"
    },
    "district": "Westerwaldkreis",
    "name": "Höhr-Grenzhausen",
    "population": "9260",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "107.32",
    "coords": {
      "lat": "52.29722",
      "lon": "7.58611"
    },
    "district": "Steinfurt",
    "name": "Hörstel",
    "population": "20141",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "157.89",
    "coords": {
      "lat": "51.767",
      "lon": "9.367"
    },
    "district": "Höxter",
    "name": "Höxter",
    "population": "28824",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "61.27",
    "coords": {
      "lat": "51.06083",
      "lon": "6.21972"
    },
    "district": "Heinsberg",
    "name": "Hückelhoven",
    "population": "39931",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "50.46",
    "coords": {
      "lat": "51.14500",
      "lon": "7.34167"
    },
    "district": "Oberbergischer Kreis",
    "name": "Hückeswagen",
    "population": "15060",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "58.53",
    "coords": {
      "lat": "47.92611",
      "lon": "8.49000"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Hüfingen",
    "population": "7799",
    "state": "Baden-Württemberg"
  },
  {
    "area": "119.77",
    "coords": {
      "lat": "50.66667",
      "lon": "9.76667"
    },
    "district": "Fulda",
    "name": "Hünfeld",
    "population": "16512",
    "state": "Hesse"
  },
  {
    "area": "51.173",
    "coords": {
      "lat": "50.87750",
      "lon": "6.87611"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Hürth",
    "population": "60189",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "108.85",
    "coords": {
      "lat": "52.27778",
      "lon": "7.71667"
    },
    "district": "Steinfurt",
    "name": "Ibbenbüren",
    "population": "51904",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "34.22",
    "coords": {
      "lat": "48.367",
      "lon": "10.317"
    },
    "district": "Günzburg",
    "name": "Ichenhausen",
    "population": "9148",
    "state": "Bavaria"
  },
  {
    "area": "91.56",
    "coords": {
      "lat": "49.71139",
      "lon": "7.31306"
    },
    "district": "Birkenfeld",
    "name": "Idar-Oberstein",
    "population": "28323",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "79.6",
    "coords": {
      "lat": "50.22056",
      "lon": "8.27417"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Idstein",
    "population": "24897",
    "state": "Hesse"
  },
  {
    "area": "36.45",
    "coords": {
      "lat": "48.217",
      "lon": "10.083"
    },
    "district": "Neu-Ulm",
    "name": "Illertissen",
    "population": "17473",
    "state": "Bavaria"
  },
  {
    "area": "198.69",
    "coords": {
      "lat": "50.68389",
      "lon": "10.91944"
    },
    "district": "Ilm-Kreis",
    "name": "Ilmenau",
    "population": "39017",
    "state": "Thuringia"
  },
  {
    "area": "62.97",
    "coords": {
      "lat": "51.867",
      "lon": "10.683"
    },
    "district": "Harz",
    "name": "Ilsenburg",
    "population": "9526",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "54.90",
    "coords": {
      "lat": "49.17028",
      "lon": "9.92028"
    },
    "district": "Schwäbisch Hall",
    "name": "Ilshofen",
    "population": "6584",
    "state": "Baden-Württemberg"
  },
  {
    "area": "28.53",
    "coords": {
      "lat": "51.417",
      "lon": "9.500"
    },
    "district": "Kassel",
    "name": "Immenhausen",
    "population": "7068",
    "state": "Hesse"
  },
  {
    "area": "81.41",
    "coords": {
      "lat": "47.567",
      "lon": "10.217"
    },
    "district": "Oberallgäu",
    "name": "Immenstadt",
    "population": "14271",
    "state": "Bavaria"
  },
  {
    "area": "46.48",
    "coords": {
      "lat": "49.300",
      "lon": "9.650"
    },
    "district": "Hohenlohekreis",
    "name": "Ingelfingen",
    "population": "5480",
    "state": "Baden-Württemberg"
  },
  {
    "area": "73.33",
    "coords": {
      "lat": "49.97472",
      "lon": "8.05639"
    },
    "district": "Mainz-Bingen",
    "name": "Ingelheim am Rhein",
    "population": "35146",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "133.37",
    "coords": {
      "lat": "48.767",
      "lon": "11.433"
    },
    "district": "Urban district",
    "name": "Ingolstadt",
    "population": "136981",
    "state": "Bavaria"
  },
  {
    "area": "78.01",
    "coords": {
      "lat": "49.700",
      "lon": "10.267"
    },
    "district": "Kitzingen",
    "name": "Iphofen",
    "population": "4619",
    "state": "Bavaria"
  },
  {
    "area": "125.5",
    "coords": {
      "lat": "51.383",
      "lon": "7.667"
    },
    "district": "Märkischer Kreis",
    "name": "Iserlohn",
    "population": "92666",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "85.37",
    "coords": {
      "lat": "47.69194",
      "lon": "10.03944"
    },
    "district": "Ravensburg",
    "name": "Isny im Allgäu",
    "population": "14018",
    "state": "Baden-Württemberg"
  },
  {
    "area": "42.73",
    "coords": {
      "lat": "51.83306",
      "lon": "6.46667"
    },
    "district": "Borken",
    "name": "Isselburg",
    "population": "10692",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "28.03",
    "coords": {
      "lat": "53.92500",
      "lon": "9.51639"
    },
    "district": "Steinburg",
    "name": "Itzehoe",
    "population": "31879",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "30.64",
    "coords": {
      "lat": "53.917",
      "lon": "13.333"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Jarmen",
    "population": "2942",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "114.76",
    "coords": {
      "lat": "50.92722",
      "lon": "11.58611"
    },
    "district": "Urban district",
    "name": "Jena",
    "population": "111407",
    "state": "Thuringia"
  },
  {
    "area": "269.91",
    "coords": {
      "lat": "52.483",
      "lon": "12.017"
    },
    "district": "Jerichower Land",
    "name": "Jerichow",
    "population": "6858",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "351.94",
    "coords": {
      "lat": "51.79167",
      "lon": "12.95556"
    },
    "district": "Wittenberg",
    "name": "Jessen",
    "population": "14104",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "42.13",
    "coords": {
      "lat": "53.57444",
      "lon": "7.90083"
    },
    "district": "Friesland",
    "name": "Jever",
    "population": "14301",
    "state": "Lower Saxony"
  },
  {
    "area": "120.18",
    "coords": {
      "lat": "52.96667",
      "lon": "13.75000"
    },
    "district": "Barnim",
    "name": "Joachimsthal",
    "population": "3419",
    "state": "Brandenburg"
  },
  {
    "area": "29.59",
    "coords": {
      "lat": "50.43333",
      "lon": "12.71389"
    },
    "district": "Erzgebirgskreis",
    "name": "Johanngeorgenstadt",
    "population": "3973",
    "state": "Saxony"
  },
  {
    "area": "49.69",
    "coords": {
      "lat": "50.51444",
      "lon": "13.08861"
    },
    "district": "Erzgebirgskreis",
    "name": "Jöhstadt",
    "population": "2663",
    "state": "Saxony"
  },
  {
    "area": "71.84",
    "coords": {
      "lat": "51.10111",
      "lon": "6.50167"
    },
    "district": "Neuss",
    "name": "Jüchen",
    "population": "23337",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "90.4",
    "coords": {
      "lat": "50.92222",
      "lon": "6.35833"
    },
    "district": "Düren",
    "name": "Jülich",
    "population": "32632",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "175.68",
    "coords": {
      "lat": "51.99333",
      "lon": "13.07278"
    },
    "district": "Teltow-Fläming",
    "name": "Jüterbog",
    "population": "12311",
    "state": "Brandenburg"
  },
  {
    "area": "37.48",
    "coords": {
      "lat": "51.217",
      "lon": "6.617"
    },
    "district": "Rhein-Kreis Neuss",
    "name": "Kaarst",
    "population": "43433",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "7.97",
    "coords": {
      "lat": "50.80083",
      "lon": "11.58750"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Kahla",
    "population": "6822",
    "state": "Thuringia"
  },
  {
    "area": "8.18",
    "coords": {
      "lat": "50.23222",
      "lon": "7.13944"
    },
    "district": "Cochem-Zell",
    "name": "Kaisersesch",
    "population": "3158",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "139.74",
    "coords": {
      "lat": "49.44472",
      "lon": "7.76889"
    },
    "district": "Urban district",
    "name": "Kaiserslautern",
    "population": "99845",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "272.51",
    "coords": {
      "lat": "52.6333",
      "lon": "11.4000"
    },
    "district": "Altmarkkreis Salzwedel",
    "name": "Kalbe, Saxony-Anhalt",
    "population": "7594",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "88.2",
    "coords": {
      "lat": "51.73889",
      "lon": "6.29250"
    },
    "district": "Kleve",
    "name": "Kalkar",
    "population": "13902",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "23.1",
    "coords": {
      "lat": "53.83972",
      "lon": "9.96028"
    },
    "district": "Segeberg",
    "name": "Kaltenkirchen",
    "population": "21813",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "94.41",
    "coords": {
      "lat": "50.633",
      "lon": "10.167"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Kaltennordheim",
    "population": "5853",
    "state": "Thuringia"
  },
  {
    "area": "98.30",
    "coords": {
      "lat": "51.267",
      "lon": "14.100"
    },
    "district": "Bautzen",
    "name": "Kamenz",
    "population": "14742",
    "state": "Saxony"
  },
  {
    "area": "40.93",
    "coords": {
      "lat": "51.59167",
      "lon": "7.66528"
    },
    "district": "Unna",
    "name": "Kamen",
    "population": "42971",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "63.16",
    "coords": {
      "lat": "51.50000",
      "lon": "6.53333"
    },
    "district": "Wesel",
    "name": "Kamp-Lintfort",
    "population": "37391",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "26.64",
    "coords": {
      "lat": "49.083",
      "lon": "8.200"
    },
    "district": "Germersheim",
    "name": "Kandel",
    "population": "9061",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "62.27",
    "coords": {
      "lat": "47.717",
      "lon": "7.667"
    },
    "district": "Lörrach",
    "name": "Kandern",
    "population": "8249",
    "state": "Baden-Württemberg"
  },
  {
    "area": "43.32",
    "coords": {
      "lat": "54.66139",
      "lon": "9.93111"
    },
    "district": "Schleswig-Flensburg",
    "name": "KappelnKappel",
    "population": "8619",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "43.94",
    "coords": {
      "lat": "50.23222",
      "lon": "8.76806"
    },
    "district": "Wetteraukreis",
    "name": "Karben",
    "population": "22127",
    "state": "Hesse"
  },
  {
    "area": "173.46",
    "coords": {
      "lat": "49.00920970",
      "lon": "8.40395140"
    },
    "district": "Urban district",
    "name": "Karlsruhe",
    "population": "313092",
    "state": "Baden-Württemberg"
  },
  {
    "area": "98.11",
    "coords": {
      "lat": "49.96028",
      "lon": "9.77222"
    },
    "district": "Main-Spessart",
    "name": "Karlstadt am Main",
    "population": "15004",
    "state": "Bavaria"
  },
  {
    "area": "107",
    "coords": {
      "lat": "51.3158",
      "lon": "9.4979"
    },
    "district": "Urban district",
    "name": "Kassel",
    "population": "201585",
    "state": "Hesse"
  },
  {
    "area": "8.47",
    "coords": {
      "lat": "50.06944",
      "lon": "7.44306"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Kastellaun",
    "population": "5410",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "9.20",
    "coords": {
      "lat": "50.26667",
      "lon": "7.98333"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Katzenelnbogen",
    "population": "2230",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "12.98",
    "coords": {
      "lat": "50.08806",
      "lon": "7.76278"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Kaub",
    "population": "839",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "40.02",
    "coords": {
      "lat": "47.88000",
      "lon": "10.62250"
    },
    "district": "Urban district",
    "name": "Kaufbeuren",
    "population": "43893",
    "state": "Bavaria"
  },
  {
    "area": "75.07",
    "coords": {
      "lat": "48.567",
      "lon": "7.817"
    },
    "district": "Ortenaukreis",
    "name": "Kehl",
    "population": "36089",
    "state": "Baden-Württemberg"
  },
  {
    "area": "40.54",
    "coords": {
      "lat": "51.433",
      "lon": "11.033"
    },
    "district": "Mansfeld-Südharz",
    "name": "Kelbra",
    "population": "3407",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "76.76",
    "coords": {
      "lat": "48.917",
      "lon": "11.867"
    },
    "district": "Kelheim",
    "name": "Kelheim",
    "population": "16714",
    "state": "Bavaria"
  },
  {
    "area": "30.65",
    "coords": {
      "lat": "50.13778",
      "lon": "8.44972"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Kelkheim",
    "population": "29055",
    "state": "Hesse"
  },
  {
    "area": "18.81",
    "coords": {
      "lat": "53.950",
      "lon": "9.717"
    },
    "district": "Steinburg",
    "name": "Kellinghusen",
    "population": "8142",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "15.38",
    "coords": {
      "lat": "50.06167",
      "lon": "8.53111"
    },
    "district": "Groß-Gerau",
    "name": "Kelsterbach",
    "population": "16936",
    "state": "Hesse"
  },
  {
    "area": "235.11",
    "coords": {
      "lat": "51.783",
      "lon": "12.633"
    },
    "district": "Wittenberg",
    "name": "Kemberg",
    "population": "9737",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "54.13",
    "coords": {
      "lat": "49.867",
      "lon": "11.883"
    },
    "district": "Tirschenreuth",
    "name": "Kemnath",
    "population": "5508",
    "state": "Bavaria"
  },
  {
    "area": "68.79",
    "coords": {
      "lat": "51.36583",
      "lon": "6.41944"
    },
    "district": "Viersen",
    "name": "Kempen",
    "population": "34597",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "63.29",
    "coords": {
      "lat": "47.733",
      "lon": "10.317"
    },
    "district": "Urban district",
    "name": "Kempten",
    "population": "68907",
    "state": "Bavaria"
  },
  {
    "area": "36.93",
    "coords": {
      "lat": "48.19167",
      "lon": "7.76833"
    },
    "district": "Emmendingen",
    "name": "Kenzingen",
    "population": "10089",
    "state": "Baden-Württemberg"
  },
  {
    "area": "113.94",
    "coords": {
      "lat": "50.87194",
      "lon": "6.69611"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Kerpen",
    "population": "66206",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "92.79",
    "coords": {
      "lat": "52.46972",
      "lon": "12.84500"
    },
    "district": "Havelland",
    "name": "Ketzin",
    "population": "6498",
    "state": "Brandenburg"
  },
  {
    "area": "100.6",
    "coords": {
      "lat": "51.58333",
      "lon": "6.25000"
    },
    "district": "Kleve",
    "name": "Kevelaer",
    "population": "28021",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "118.6",
    "coords": {
      "lat": "54.32333",
      "lon": "10.13944"
    },
    "district": "Urban district",
    "name": "Kiel",
    "population": "247548",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "71.62",
    "coords": {
      "lat": "51.133",
      "lon": "7.567"
    },
    "district": "Märkischer Kreis",
    "name": "Kierspe",
    "population": "16137",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "40.93",
    "coords": {
      "lat": "49.20389",
      "lon": "9.98139"
    },
    "district": "Schwäbisch Hall",
    "name": "Kirchberg an der Jagst",
    "population": "4393",
    "state": "Baden-Württemberg"
  },
  {
    "area": "18.05",
    "coords": {
      "lat": "49.94500",
      "lon": "7.40722"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Kirchberg",
    "population": "3993",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "39.58",
    "coords": {
      "lat": "50.62222",
      "lon": "12.52556"
    },
    "district": "Zwickau",
    "name": "Kirchberg",
    "population": "8242",
    "state": "Saxony"
  },
  {
    "area": "48.51",
    "coords": {
      "lat": "50.150",
      "lon": "11.950"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Kirchenlamitz",
    "population": "3286",
    "state": "Bavaria"
  },
  {
    "area": "39.59",
    "coords": {
      "lat": "50.80861",
      "lon": "7.88333"
    },
    "district": "Altenkirchen",
    "name": "Kirchen",
    "population": "8498",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "90.92",
    "coords": {
      "lat": "50.817",
      "lon": "8.917"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Kirchhain",
    "population": "16298",
    "state": "Hesse"
  },
  {
    "area": "40.47",
    "coords": {
      "lat": "48.64833",
      "lon": "9.45111"
    },
    "district": "Esslingen",
    "name": "Kirchheim unter Teck",
    "population": "40523",
    "state": "Baden-Württemberg"
  },
  {
    "area": "26.36",
    "coords": {
      "lat": "49.66639",
      "lon": "8.01167"
    },
    "district": "Donnersbergkreis",
    "name": "Kirchheimbolanden",
    "population": "7802",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "16.53",
    "coords": {
      "lat": "49.78806",
      "lon": "7.45722"
    },
    "district": "Bad Kreuznach",
    "name": "Kirn",
    "population": "8193",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "79.88",
    "coords": {
      "lat": "50.767",
      "lon": "9.100"
    },
    "district": "Vogelsbergkreis",
    "name": "Kirtorf",
    "population": "3137",
    "state": "Hesse"
  },
  {
    "area": "46.99",
    "coords": {
      "lat": "49.733",
      "lon": "10.167"
    },
    "district": "Kitzingen",
    "name": "Kitzingen",
    "population": "21704",
    "state": "Bavaria"
  },
  {
    "area": "28.99",
    "coords": {
      "lat": "51.1644750",
      "lon": "12.5535000"
    },
    "district": "Leipzig",
    "name": "Kitzscher",
    "population": "4952",
    "state": "Saxony"
  },
  {
    "area": "21.14",
    "coords": {
      "lat": "49.783",
      "lon": "9.183"
    },
    "district": "Miltenberg",
    "name": "Klingenberg am Main",
    "population": "6160",
    "state": "Bavaria"
  },
  {
    "area": "50.44",
    "coords": {
      "lat": "50.36694",
      "lon": "12.46861"
    },
    "district": "Vogtlandkreis",
    "name": "Klingenthal",
    "population": "8365",
    "state": "Saxony"
  },
  {
    "area": "278.29",
    "coords": {
      "lat": "52.62629",
      "lon": "11.1616"
    },
    "district": "Altmarkkreis Salzwedel",
    "name": "Klötze",
    "population": "10077",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "44.12",
    "coords": {
      "lat": "53.967",
      "lon": "11.167"
    },
    "district": "Nordwestmecklenburg",
    "name": "Klütz",
    "population": "3114",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "26.33",
    "coords": {
      "lat": "49.02389",
      "lon": "8.75694"
    },
    "district": "Enzkreis",
    "name": "Knittlingen",
    "population": "8048",
    "state": "Baden-Württemberg"
  },
  {
    "area": "105.02",
    "coords": {
      "lat": "50.35972",
      "lon": "7.59778"
    },
    "district": "Urban district",
    "name": "Koblenz",
    "population": "114024",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "19.87",
    "coords": {
      "lat": "47.850",
      "lon": "12.067"
    },
    "district": "Rosenheim",
    "name": "Kolbermoor",
    "population": "18505",
    "state": "Bavaria"
  },
  {
    "area": "44.54",
    "coords": {
      "lat": "49.700",
      "lon": "6.583"
    },
    "district": "Trier-Saarburg",
    "name": "Konz",
    "population": "18348",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "20.71",
    "coords": {
      "lat": "48.83056",
      "lon": "9.12139"
    },
    "district": "Ludwigsburg",
    "name": "Korntal-Münchingen",
    "population": "19679",
    "state": "Baden-Württemberg"
  },
  {
    "area": "14.64",
    "coords": {
      "lat": "48.85980",
      "lon": "9.18520"
    },
    "district": "Ludwigsburg",
    "name": "Kornwestheim",
    "population": "33803",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.26",
    "coords": {
      "lat": "51.183",
      "lon": "6.517"
    },
    "district": "Rhein-Kreis Neuss",
    "name": "Korschenbroich",
    "population": "33066",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "80.56",
    "coords": {
      "lat": "49.12417",
      "lon": "8.71472"
    },
    "district": "Karlsruhe",
    "name": "Kraichtal",
    "population": "14627",
    "state": "Baden-Württemberg"
  },
  {
    "area": "87.07",
    "coords": {
      "lat": "53.650",
      "lon": "12.267"
    },
    "district": "Rostock",
    "name": "Krakow am See",
    "population": "3461",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "23.08",
    "coords": {
      "lat": "50.85000",
      "lon": "11.20000"
    },
    "district": "Weimarer Land",
    "name": "Kranichfeld",
    "population": "3355",
    "state": "Thuringia"
  },
  {
    "area": "52.91",
    "coords": {
      "lat": "49.383",
      "lon": "9.633"
    },
    "district": "Hohenlohekreis",
    "name": "Krautheim",
    "population": "4613",
    "state": "Baden-Württemberg"
  },
  {
    "area": "137.68",
    "coords": {
      "lat": "51.33333",
      "lon": "6.56667"
    },
    "district": "Urban districts of Germany",
    "name": "Krefeld",
    "population": "227020",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "208.43",
    "coords": {
      "lat": "52.76667",
      "lon": "13.03306"
    },
    "district": "Oberhavel",
    "name": "Kremmen",
    "population": "7657",
    "state": "Brandenburg"
  },
  {
    "area": "3.39",
    "coords": {
      "lat": "53.817",
      "lon": "9.467"
    },
    "district": "Steinburg",
    "name": "Krempe",
    "population": "2362",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "70.97",
    "coords": {
      "lat": "50.967",
      "lon": "7.967"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Kreuztal",
    "population": "31187",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "66.99",
    "coords": {
      "lat": "50.24111",
      "lon": "11.32806"
    },
    "district": "Kronach",
    "name": "Kronach",
    "population": "16874",
    "state": "Bavaria"
  },
  {
    "area": "18.62",
    "coords": {
      "lat": "50.183",
      "lon": "8.500"
    },
    "district": "Hochtaunuskreis",
    "name": "Kronberg im Taunus",
    "population": "18311",
    "state": "Hesse"
  },
  {
    "area": "38.65",
    "coords": {
      "lat": "51.9413000",
      "lon": "11.3084500"
    },
    "district": "Börde",
    "name": "Kroppenstedt",
    "population": "1399",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "44.75",
    "coords": {
      "lat": "48.250",
      "lon": "10.367"
    },
    "district": "Günzburg",
    "name": "Krumbach",
    "population": "13293",
    "state": "Bavaria"
  },
  {
    "area": "67.26",
    "coords": {
      "lat": "54.067",
      "lon": "11.783"
    },
    "district": "Rostock",
    "name": "Kröpelin",
    "population": "4784",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "92.77",
    "coords": {
      "lat": "50.100",
      "lon": "11.433"
    },
    "district": "Kulmbach",
    "name": "Kulmbach",
    "population": "25915",
    "state": "Bavaria"
  },
  {
    "area": "8.28",
    "coords": {
      "lat": "50.117",
      "lon": "11.567"
    },
    "district": "Kulmbach",
    "name": "Kupferberg",
    "population": "1049",
    "state": "Bavaria"
  },
  {
    "area": "18.08",
    "coords": {
      "lat": "48.82750",
      "lon": "8.25444"
    },
    "district": "Rastatt",
    "name": "Kuppenheim",
    "population": "8330",
    "state": "Baden-Württemberg"
  },
  {
    "area": "14.37",
    "coords": {
      "lat": "49.53472",
      "lon": "7.39806"
    },
    "district": "Kusel",
    "name": "Kusel",
    "population": "5405",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "4.62",
    "coords": {
      "lat": "50.04194",
      "lon": "6.59472"
    },
    "district": "Eifelkreis Bitburg-Prüm",
    "name": "Kyllburg",
    "population": "906",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "156.09",
    "coords": {
      "lat": "52.95000",
      "lon": "12.40000"
    },
    "district": "Ostprignitz-Ruppin",
    "name": "Kyritz",
    "population": "9303",
    "state": "Brandenburg"
  },
  {
    "area": "89.50",
    "coords": {
      "lat": "51.167",
      "lon": "11.217"
    },
    "district": "Sömmerda",
    "name": "Kölleda",
    "population": "6391",
    "state": "Thuringia"
  },
  {
    "area": "95.83",
    "coords": {
      "lat": "52.29167",
      "lon": "13.62500"
    },
    "district": "Dahme-Spreewald",
    "name": "Königs Wusterhausen",
    "population": "37190",
    "state": "Brandenburg"
  },
  {
    "area": "61.86",
    "coords": {
      "lat": "50.07778",
      "lon": "10.56667"
    },
    "district": "Haßberge",
    "name": "Königsberg in Bayern",
    "population": "3638",
    "state": "Bavaria"
  },
  {
    "area": "18.40",
    "coords": {
      "lat": "48.26889",
      "lon": "10.89083"
    },
    "district": "Augsburg",
    "name": "Königsbrunn",
    "population": "28076",
    "state": "Bavaria"
  },
  {
    "area": "77.83",
    "coords": {
      "lat": "51.250",
      "lon": "13.883"
    },
    "district": "Bautzen",
    "name": "Königsbrück",
    "population": "4486",
    "state": "Saxony"
  },
  {
    "area": "103.09",
    "coords": {
      "lat": "50.66139",
      "lon": "11.09722"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Königsee",
    "population": "7448",
    "state": "Thuringia"
  },
  {
    "area": "130.58",
    "coords": {
      "lat": "52.250",
      "lon": "10.817"
    },
    "district": "Helmstedt",
    "name": "Königslutter",
    "population": "15704",
    "state": "Lower Saxony"
  },
  {
    "area": "25.1",
    "coords": {
      "lat": "50.183",
      "lon": "8.467"
    },
    "district": "Hochtaunuskreis",
    "name": "Königstein im Taunus",
    "population": "16648",
    "state": "Hesse"
  },
  {
    "area": "26.93",
    "coords": {
      "lat": "50.91889",
      "lon": "14.07139"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Königstein",
    "population": "2089",
    "state": "Saxony"
  },
  {
    "area": "76.19",
    "coords": {
      "lat": "50.67361",
      "lon": "7.19472"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Königswinter",
    "population": "41243",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "125.11",
    "coords": {
      "lat": "51.66972",
      "lon": "11.77083"
    },
    "district": "Salzlandkreis",
    "name": "Könnern",
    "population": "8261",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "78.42",
    "coords": {
      "lat": "51.75000",
      "lon": "11.91667"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Köthen (Anhalt)",
    "population": "25911",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "16.16",
    "coords": {
      "lat": "54.133",
      "lon": "11.750"
    },
    "district": "Rostock",
    "name": "Kühlungsborn",
    "population": "7896",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "81.46",
    "coords": {
      "lat": "49.66944",
      "lon": "9.52056"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Külsheim",
    "population": "5122",
    "state": "Baden-Württemberg"
  },
  {
    "area": "75.17",
    "coords": {
      "lat": "49.283",
      "lon": "9.683"
    },
    "district": "Hohenlohekreis",
    "name": "Künzelsau",
    "population": "15391",
    "state": "Baden-Württemberg"
  },
  {
    "area": "114.78",
    "coords": {
      "lat": "53.93222",
      "lon": "12.34667"
    },
    "district": "Rostock",
    "name": "Laage",
    "population": "6396",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "34.05",
    "coords": {
      "lat": "52.317",
      "lon": "9.800"
    },
    "district": "Hanover",
    "name": "Laatzen",
    "population": "41422",
    "state": "Lower Saxony"
  },
  {
    "area": "19",
    "coords": {
      "lat": "49.467",
      "lon": "8.617"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Ladenburg",
    "population": "11537",
    "state": "Baden-Württemberg"
  },
  {
    "area": "76.04",
    "coords": {
      "lat": "51.96667",
      "lon": "8.80000"
    },
    "district": "Lippe",
    "name": "Lage",
    "population": "35047",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "36.85",
    "coords": {
      "lat": "50.30111",
      "lon": "7.60556"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Lahnstein",
    "population": "18067",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "69.86",
    "coords": {
      "lat": "48.333",
      "lon": "7.867"
    },
    "district": "Ortenau",
    "name": "Lahr",
    "population": "46797",
    "state": "Baden-Württemberg"
  },
  {
    "area": "69.84",
    "coords": {
      "lat": "48.48972",
      "lon": "9.68611"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Laichingen",
    "population": "11731",
    "state": "Baden-Württemberg"
  },
  {
    "area": "8.32",
    "coords": {
      "lat": "49.38028",
      "lon": "8.08611"
    },
    "district": "Bad Dürkheim",
    "name": "Lambrecht, Rhineland-Palatinate",
    "population": "4069",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "72.3",
    "coords": {
      "lat": "49.60000",
      "lon": "8.46667"
    },
    "district": "Bergstraße",
    "name": "Lampertheim",
    "population": "32537",
    "state": "Hesse"
  },
  {
    "area": "84.37",
    "coords": {
      "lat": "48.667",
      "lon": "12.667"
    },
    "district": "Dingolfing-Landau",
    "name": "Landau an der Isar",
    "population": "13390",
    "state": "Bavaria"
  },
  {
    "area": "82.94",
    "coords": {
      "lat": "49.200",
      "lon": "8.117"
    },
    "district": "Urban district",
    "name": "Landau",
    "population": "46677",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "57.89",
    "coords": {
      "lat": "48.04778",
      "lon": "10.89889"
    },
    "district": "Landsberg am Lech",
    "name": "Landsberg am Lech",
    "population": "29132",
    "state": "Bavaria"
  },
  {
    "area": "124.74",
    "coords": {
      "lat": "51.533",
      "lon": "12.167"
    },
    "district": "Saalekreis",
    "name": "Landsberg",
    "population": "15054",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "65.7",
    "coords": {
      "lat": "48.53972",
      "lon": "12.15083"
    },
    "district": "Urban district",
    "name": "Landshut",
    "population": "72404",
    "state": "Bavaria"
  },
  {
    "area": "15.34",
    "coords": {
      "lat": "49.41222",
      "lon": "7.57222"
    },
    "district": "Kaiserslautern",
    "name": "Landstuhl",
    "population": "8348",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "48.72",
    "coords": {
      "lat": "51.93806",
      "lon": "10.33500"
    },
    "district": "Goslar",
    "name": "Langelsheim",
    "population": "11361",
    "state": "Lower Saxony"
  },
  {
    "area": "29.12",
    "coords": {
      "lat": "49.983",
      "lon": "8.667"
    },
    "district": "Offenbach",
    "name": "Langen, Hesse",
    "population": "37902",
    "state": "Hesse"
  },
  {
    "area": "75.00",
    "coords": {
      "lat": "48.49667",
      "lon": "10.12000"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Langenau",
    "population": "15247",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.40",
    "coords": {
      "lat": "49.25333",
      "lon": "9.84861"
    },
    "district": "Schwäbisch Hall",
    "name": "Langenburg",
    "population": "1831",
    "state": "Baden-Württemberg"
  },
  {
    "area": "41.10",
    "coords": {
      "lat": "51.117",
      "lon": "6.950"
    },
    "district": "Mettmann",
    "name": "Langenfeld",
    "population": "58927",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "71.99",
    "coords": {
      "lat": "52.43944",
      "lon": "9.74000"
    },
    "district": "Hanover",
    "name": "Langenhagen",
    "population": "54244",
    "state": "Lower Saxony"
  },
  {
    "area": "26.25",
    "coords": {
      "lat": "50.183",
      "lon": "9.033"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Langenselbold",
    "population": "13979",
    "state": "Hesse"
  },
  {
    "area": "46.31",
    "coords": {
      "lat": "49.49444",
      "lon": "10.79472"
    },
    "district": "Fürth",
    "name": "Langenzenn",
    "population": "10665",
    "state": "Bavaria"
  },
  {
    "area": "27.98",
    "coords": {
      "lat": "53.933",
      "lon": "13.833"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Lassan",
    "population": "1510",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "97.01",
    "coords": {
      "lat": "50.533",
      "lon": "8.9900"
    },
    "district": "Gießen",
    "name": "Laubach",
    "population": "9583",
    "state": "Hesse"
  },
  {
    "area": "31.12",
    "coords": {
      "lat": "51.22361",
      "lon": "11.67972"
    },
    "district": "Burgenlandkreis",
    "name": "Laucha an der Unstrut",
    "population": "2845",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "88.43",
    "coords": {
      "lat": "51.50000",
      "lon": "13.80000"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Lauchhammer",
    "population": "14622",
    "state": "Brandenburg"
  },
  {
    "area": "40.86",
    "coords": {
      "lat": "48.87167",
      "lon": "10.24444"
    },
    "district": "Ostalbkreis",
    "name": "Lauchheim",
    "population": "4780",
    "state": "Baden-Württemberg"
  },
  {
    "area": "94.47",
    "coords": {
      "lat": "49.56861",
      "lon": "9.70389"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Lauda-Königshofen",
    "population": "14542",
    "state": "Baden-Württemberg"
  },
  {
    "area": "9.54",
    "coords": {
      "lat": "53.383",
      "lon": "10.567"
    },
    "district": "Lauenburg",
    "name": "Lauenburg",
    "population": "11444",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "59.80",
    "coords": {
      "lat": "49.51028",
      "lon": "11.27722"
    },
    "district": "Nürnberger Land",
    "name": "Lauf a.d.Pegnitz",
    "population": "26515",
    "state": "Bavaria"
  },
  {
    "area": "23.58",
    "coords": {
      "lat": "47.56556",
      "lon": "8.06472"
    },
    "district": "Waldshut",
    "name": "Laufenburg",
    "population": "9029",
    "state": "Baden-Württemberg"
  },
  {
    "area": "35.31",
    "coords": {
      "lat": "47.933",
      "lon": "12.933"
    },
    "district": "Berchtesgadener Land",
    "name": "Laufen",
    "population": "7192",
    "state": "Bavaria"
  },
  {
    "area": "22.63",
    "coords": {
      "lat": "49.08333",
      "lon": "9.15000"
    },
    "district": "Heilbronn",
    "name": "Lauffen",
    "population": "11640",
    "state": "Baden-Württemberg"
  },
  {
    "area": "44.39",
    "coords": {
      "lat": "48.567",
      "lon": "10.433"
    },
    "district": "Dillingen",
    "name": "Lauingen",
    "population": "11000",
    "state": "Bavaria"
  },
  {
    "area": "61.80",
    "coords": {
      "lat": "48.22889",
      "lon": "9.87972"
    },
    "district": "Biberach",
    "name": "Laupheim",
    "population": "22298",
    "state": "Baden-Württemberg"
  },
  {
    "area": "18.72",
    "coords": {
      "lat": "50.48139",
      "lon": "11.16028"
    },
    "district": "Sonneberg",
    "name": "Lauscha",
    "population": "3324",
    "state": "Thuringia"
  },
  {
    "area": "41.87",
    "coords": {
      "lat": "51.44806",
      "lon": "14.09972"
    },
    "district": "Bautzen",
    "name": "Lauta",
    "population": "8411",
    "state": "Saxony"
  },
  {
    "area": "30.3",
    "coords": {
      "lat": "50.567",
      "lon": "12.750"
    },
    "district": "Erzgebirgskreis",
    "name": "Lauter-Bernsbach",
    "population": "8678",
    "state": "Saxony"
  },
  {
    "area": "102",
    "coords": {
      "lat": "50.63778",
      "lon": "9.39444"
    },
    "district": "Vogelsbergkreis",
    "name": "Lauterbach",
    "population": "13664",
    "state": "Hesse"
  },
  {
    "area": "8.91",
    "coords": {
      "lat": "49.64944",
      "lon": "7.59194"
    },
    "district": "Kusel",
    "name": "Lauterecken",
    "population": "2071",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "23.32",
    "coords": {
      "lat": "48.71000",
      "lon": "9.86139"
    },
    "district": "Göppingen",
    "name": "Lauterstein",
    "population": "2552",
    "state": "Baden-Württemberg"
  },
  {
    "area": "64.15",
    "coords": {
      "lat": "49.41000",
      "lon": "6.91000"
    },
    "district": "Saarlouis",
    "name": "Lebach",
    "population": "19006",
    "state": "Saarland"
  },
  {
    "area": "54.23",
    "coords": {
      "lat": "52.41667",
      "lon": "14.53306"
    },
    "district": "Märkisch-Oderland",
    "name": "Lebus",
    "population": "3180",
    "state": "Brandenburg"
  },
  {
    "area": "70.30",
    "coords": {
      "lat": "53.23083",
      "lon": "7.45278"
    },
    "district": "Leer",
    "name": "Leer",
    "population": "34486",
    "state": "Lower Saxony"
  },
  {
    "area": "35.96",
    "coords": {
      "lat": "50.47556",
      "lon": "11.44806"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Lehesten",
    "population": "1691",
    "state": "Thuringia"
  },
  {
    "area": "127",
    "coords": {
      "lat": "52.367",
      "lon": "9.967"
    },
    "district": "Hanover",
    "name": "Lehrte",
    "population": "43999",
    "state": "Lower Saxony"
  },
  {
    "area": "37.33",
    "coords": {
      "lat": "51.117",
      "lon": "7.017"
    },
    "district": "Rheinisch-Bergischer Kreis",
    "name": "Leichlingen",
    "population": "28031",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "20.64",
    "coords": {
      "lat": "49.34806",
      "lon": "8.69111"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Leimen",
    "population": "26968",
    "state": "Baden-Württemberg"
  },
  {
    "area": "110.18",
    "coords": {
      "lat": "51.38333",
      "lon": "10.33333"
    },
    "district": "Eichsfeld",
    "name": "Leinefelde-Worbis",
    "population": "20124",
    "state": "Thuringia"
  },
  {
    "area": "29.90",
    "coords": {
      "lat": "48.69278",
      "lon": "9.14278"
    },
    "district": "Esslingen",
    "name": "Leinfelden-Echterdingen",
    "population": "40092",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.48",
    "coords": {
      "lat": "49.150",
      "lon": "9.117"
    },
    "district": "Heilbronn",
    "name": "Leingarten",
    "population": "11633",
    "state": "Baden-Württemberg"
  },
  {
    "area": "32.15",
    "coords": {
      "lat": "48.44889",
      "lon": "10.22083"
    },
    "district": "Günzburg",
    "name": "Leipheim",
    "population": "7209",
    "state": "Bavaria"
  },
  {
    "area": "297.36",
    "coords": {
      "lat": "51.333",
      "lon": "12.383"
    },
    "district": "Urban districts of Germany",
    "name": "Leipzig",
    "population": "587857",
    "state": "Saxony"
  },
  {
    "area": "78.01",
    "coords": {
      "lat": "51.167",
      "lon": "12.917"
    },
    "district": "Mittelsachsen",
    "name": "Leisnig",
    "population": "8257",
    "state": "Saxony"
  },
  {
    "area": "100.85",
    "coords": {
      "lat": "52.02722",
      "lon": "8.91167"
    },
    "district": "Lippe",
    "name": "Lemgo",
    "population": "40696",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "46.98",
    "coords": {
      "lat": "50.567",
      "lon": "12.367"
    },
    "district": "Vogtlandkreis",
    "name": "Lengenfeld",
    "population": "7118",
    "state": "Saxony"
  },
  {
    "area": "90.71",
    "coords": {
      "lat": "52.17500",
      "lon": "7.86667"
    },
    "district": "Steinfurt",
    "name": "Lengerich",
    "population": "22641",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "135.06",
    "coords": {
      "lat": "51.12361",
      "lon": "8.06806"
    },
    "district": "Olpe",
    "name": "Lennestadt",
    "population": "25503",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "95.75",
    "coords": {
      "lat": "53.09111",
      "lon": "11.47333"
    },
    "district": "Prignitz",
    "name": "Lenzen",
    "population": "2086",
    "state": "Brandenburg"
  },
  {
    "area": "48.73",
    "coords": {
      "lat": "48.80139",
      "lon": "9.01306"
    },
    "district": "Böblingen",
    "name": "Leonberg",
    "population": "48733",
    "state": "Baden-Württemberg"
  },
  {
    "area": "83.41",
    "coords": {
      "lat": "51.317",
      "lon": "12.017"
    },
    "district": "Saalekreis",
    "name": "Leuna",
    "population": "13969",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "28.66",
    "coords": {
      "lat": "50.550",
      "lon": "8.367"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Leun",
    "population": "5728",
    "state": "Hesse"
  },
  {
    "area": "57.17",
    "coords": {
      "lat": "50.54972",
      "lon": "11.44972"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Leutenberg",
    "population": "2084",
    "state": "Thuringia"
  },
  {
    "area": "84.11",
    "coords": {
      "lat": "49.283",
      "lon": "10.417"
    },
    "district": "Ansbach",
    "name": "Leutershausen",
    "population": "5615",
    "state": "Bavaria"
  },
  {
    "area": "174.95",
    "coords": {
      "lat": "47.82556",
      "lon": "10.02222"
    },
    "district": "Ravensburg",
    "name": "Leutkirch im Allgäu",
    "population": "22803",
    "state": "Baden-Württemberg"
  },
  {
    "area": "78.85",
    "coords": {
      "lat": "51.033",
      "lon": "6.983"
    },
    "district": "Urban district",
    "name": "Leverkusen",
    "population": "163838",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "27.62",
    "coords": {
      "lat": "48.72611",
      "lon": "8.00500"
    },
    "district": "Rastatt",
    "name": "Lichtenau",
    "population": "4975",
    "state": "Baden-Württemberg"
  },
  {
    "area": "192.17",
    "coords": {
      "lat": "51.60000",
      "lon": "8.88333"
    },
    "district": "Paderborn",
    "name": "Lichtenau",
    "population": "10632",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "9.47",
    "coords": {
      "lat": "50.367",
      "lon": "11.667"
    },
    "district": "Hof",
    "name": "Lichtenberg",
    "population": "1051",
    "state": "Bavaria"
  },
  {
    "area": "122.27",
    "coords": {
      "lat": "50.133",
      "lon": "11.033"
    },
    "district": "Lichtenfels",
    "name": "Lichtenfels",
    "population": "20133",
    "state": "Bavaria"
  },
  {
    "area": "96.73",
    "coords": {
      "lat": "51.150",
      "lon": "8.800"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Lichtenfels",
    "population": "4139",
    "state": "Hesse"
  },
  {
    "area": "15.48",
    "coords": {
      "lat": "50.75639",
      "lon": "12.63167"
    },
    "district": "Zwickau",
    "name": "Lichtenstein",
    "population": "11285",
    "state": "Saxony"
  },
  {
    "area": "77.64",
    "coords": {
      "lat": "50.52167",
      "lon": "8.82083"
    },
    "district": "Gießen",
    "name": "Lich",
    "population": "13650",
    "state": "Hesse"
  },
  {
    "area": "48.87",
    "coords": {
      "lat": "51.483",
      "lon": "9.283"
    },
    "district": "Kassel",
    "name": "Liebenau",
    "population": "3038",
    "state": "Hesse"
  },
  {
    "area": "138.84",
    "coords": {
      "lat": "52.867",
      "lon": "13.400"
    },
    "district": "Oberhavel",
    "name": "Liebenwalde",
    "population": "4296",
    "state": "Brandenburg"
  },
  {
    "area": "72.51",
    "coords": {
      "lat": "51.98306",
      "lon": "14.30000"
    },
    "district": "Dahme-Spreewald",
    "name": "Lieberose",
    "population": "1360",
    "state": "Brandenburg"
  },
  {
    "area": "37.41",
    "coords": {
      "lat": "50.86472",
      "lon": "13.85556"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Liebstadt",
    "population": "1291",
    "state": "Saxony"
  },
  {
    "area": "50.17",
    "coords": {
      "lat": "50.867",
      "lon": "12.750"
    },
    "district": "Zwickau",
    "name": "Limbach-Oberfrohna",
    "population": "24029",
    "state": "Saxony"
  },
  {
    "area": "45.15",
    "coords": {
      "lat": "50.383",
      "lon": "8.067"
    },
    "district": "Limburg-Weilburg",
    "name": "Limburg an der Lahn",
    "population": "35243",
    "state": "Hesse"
  },
  {
    "area": "33.18",
    "coords": {
      "lat": "47.54583",
      "lon": "9.68333"
    },
    "district": "Lindau",
    "name": "Lindau",
    "population": "25490",
    "state": "Bavaria"
  },
  {
    "area": "11.85",
    "coords": {
      "lat": "47.600",
      "lon": "9.900"
    },
    "district": "Lindau",
    "name": "Lindenberg im Allgäu",
    "population": "11546",
    "state": "Bavaria"
  },
  {
    "area": "21.09",
    "coords": {
      "lat": "49.68333",
      "lon": "8.78333"
    },
    "district": "Bergstraße",
    "name": "Lindenfels",
    "population": "5124",
    "state": "Hesse"
  },
  {
    "area": "22.77",
    "coords": {
      "lat": "50.533",
      "lon": "8.650"
    },
    "district": "Gießen",
    "name": "Linden",
    "population": "12967",
    "state": "Hesse"
  },
  {
    "area": "65.17",
    "coords": {
      "lat": "52.967",
      "lon": "12.983"
    },
    "district": "Ostprignitz-Ruppin",
    "name": "Lindow",
    "population": "3091",
    "state": "Brandenburg"
  },
  {
    "area": "176.15",
    "coords": {
      "lat": "52.52306",
      "lon": "7.32306"
    },
    "district": "Emsland",
    "name": "Lingen",
    "population": "54422",
    "state": "Lower Saxony"
  },
  {
    "area": "65.46",
    "coords": {
      "lat": "50.97889",
      "lon": "6.26778"
    },
    "district": "Düren",
    "name": "Linnich",
    "population": "12593",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "17.98",
    "coords": {
      "lat": "50.57028",
      "lon": "7.28472"
    },
    "district": "Neuwied",
    "name": "Linz am Rhein",
    "population": "6200",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "113.3",
    "coords": {
      "lat": "51.667",
      "lon": "8.350"
    },
    "district": "Soest",
    "name": "Lippstadt",
    "population": "67901",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "65.5",
    "coords": {
      "lat": "50.817",
      "lon": "7.217"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Lohmar",
    "population": "30363",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "90.78",
    "coords": {
      "lat": "52.66667",
      "lon": "8.23861"
    },
    "district": "Vechta",
    "name": "Lohne (Oldenburg)",
    "population": "26762",
    "state": "Lower Saxony"
  },
  {
    "area": "90.44",
    "coords": {
      "lat": "50.000",
      "lon": "9.583"
    },
    "district": "Main-Spessart",
    "name": "Lohr a. Main",
    "population": "15218",
    "state": "Bavaria"
  },
  {
    "area": "89.53",
    "coords": {
      "lat": "53.967",
      "lon": "13.150"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Loitz",
    "population": "4281",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "21.90",
    "coords": {
      "lat": "50.64972",
      "lon": "8.70444"
    },
    "district": "Gießen",
    "name": "Lollar",
    "population": "10395",
    "state": "Hesse"
  },
  {
    "area": "66.47",
    "coords": {
      "lat": "51.200",
      "lon": "13.300"
    },
    "district": "Meißen",
    "name": "Lommatzsch",
    "population": "4879",
    "state": "Saxony"
  },
  {
    "area": "34.28",
    "coords": {
      "lat": "48.79833",
      "lon": "9.68833"
    },
    "district": "Ostalbkreis",
    "name": "Lorch",
    "population": "10885",
    "state": "Baden-Württemberg"
  },
  {
    "area": "54",
    "coords": {
      "lat": "50.04417",
      "lon": "7.80333"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Lorch",
    "population": "3818",
    "state": "Hesse"
  },
  {
    "area": "25.24",
    "coords": {
      "lat": "49.65389",
      "lon": "8.56750"
    },
    "district": "Bergstraße",
    "name": "Lorsch",
    "population": "13643",
    "state": "Hesse"
  },
  {
    "area": "206.38",
    "coords": {
      "lat": "51.85000",
      "lon": "13.71667"
    },
    "district": "Dahme-Spreewald",
    "name": "Luckau",
    "population": "9582",
    "state": "Brandenburg"
  },
  {
    "area": "12.99",
    "coords": {
      "lat": "51.09500",
      "lon": "12.33528"
    },
    "district": "Altenburger Land",
    "name": "Lucka",
    "population": "3714",
    "state": "Thuringia"
  },
  {
    "area": "46.75",
    "coords": {
      "lat": "52.083",
      "lon": "13.167"
    },
    "district": "Teltow-Fläming",
    "name": "Luckenwalde",
    "population": "20522",
    "state": "Brandenburg"
  },
  {
    "area": "43.33",
    "coords": {
      "lat": "48.89750",
      "lon": "9.19222"
    },
    "district": "Ludwigsburg",
    "name": "Ludwigsburg",
    "population": "93499",
    "state": "Baden-Württemberg"
  },
  {
    "area": "109.30",
    "coords": {
      "lat": "52.29972",
      "lon": "13.26667"
    },
    "district": "Teltow-Fläming",
    "name": "Ludwigsfelde",
    "population": "26112",
    "state": "Brandenburg"
  },
  {
    "area": "77.68",
    "coords": {
      "lat": "49.48111",
      "lon": "8.43528"
    },
    "district": "Urban district",
    "name": "Ludwigshafen am Rhein",
    "population": "171061",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "78.30",
    "coords": {
      "lat": "53.32444",
      "lon": "11.49722"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Ludwigslust",
    "population": "12233",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "58.72",
    "coords": {
      "lat": "50.48583",
      "lon": "11.38750"
    },
    "district": "Kronach",
    "name": "Ludwigsstadt",
    "population": "3396",
    "state": "Bavaria"
  },
  {
    "area": "22.2",
    "coords": {
      "lat": "50.73833",
      "lon": "12.74639"
    },
    "district": "Erzgebirgskreis",
    "name": "Lugau",
    "population": "8013",
    "state": "Saxony"
  },
  {
    "area": "28.06",
    "coords": {
      "lat": "50.96306",
      "lon": "12.75306"
    },
    "district": "Mittelsachsen",
    "name": "Lunzenau",
    "population": "4235",
    "state": "Saxony"
  },
  {
    "area": "110.51",
    "coords": {
      "lat": "53.203235",
      "lon": "13.319617"
    },
    "district": "Uckermark",
    "name": "Lychen",
    "population": "3178",
    "state": "Brandenburg"
  },
  {
    "area": "78.74",
    "coords": {
      "lat": "51.09444",
      "lon": "14.66667"
    },
    "district": "Görlitz",
    "name": "Löbau",
    "population": "14643",
    "state": "Saxony"
  },
  {
    "area": "88.03",
    "coords": {
      "lat": "47.88389",
      "lon": "8.34361"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Löffingen",
    "population": "7676",
    "state": "Baden-Württemberg"
  },
  {
    "area": "59.41",
    "coords": {
      "lat": "52.200",
      "lon": "8.700"
    },
    "district": "Herford",
    "name": "Löhne",
    "population": "39697",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "143",
    "coords": {
      "lat": "52.717",
      "lon": "7.767"
    },
    "district": "Cloppenburg",
    "name": "Löningen",
    "population": "13441",
    "state": "Lower Saxony"
  },
  {
    "area": "39.43",
    "coords": {
      "lat": "47.617",
      "lon": "7.667"
    },
    "district": "Lörrach",
    "name": "Lörrach",
    "population": "49347",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.46",
    "coords": {
      "lat": "49.100",
      "lon": "9.383"
    },
    "district": "Heilbronn",
    "name": "Löwenstein",
    "population": "3394",
    "state": "Baden-Württemberg"
  },
  {
    "area": "30.54",
    "coords": {
      "lat": "50.62139",
      "lon": "12.73167"
    },
    "district": "Erzgebirgskreis",
    "name": "Lößnitz",
    "population": "8267",
    "state": "Saxony"
  },
  {
    "area": "65",
    "coords": {
      "lat": "52.30806",
      "lon": "8.62306"
    },
    "district": "Minden-Lübbecke",
    "name": "Lübbecke",
    "population": "25490",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "119.91",
    "coords": {
      "lat": "51.950",
      "lon": "13.900"
    },
    "district": "Dahme-Spreewald",
    "name": "Lübben/Lubin",
    "population": "14024",
    "state": "Brandenburg"
  },
  {
    "area": "138.78",
    "coords": {
      "lat": "51.867",
      "lon": "13.967"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Lübbenau/Lubnjow",
    "population": "16021",
    "state": "Brandenburg"
  },
  {
    "area": "214.13",
    "coords": {
      "lat": "53.86972",
      "lon": "10.68639"
    },
    "district": "Urban district",
    "name": "Lübeck",
    "population": "217198",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "119.69",
    "coords": {
      "lat": "53.300",
      "lon": "11.083"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Lübtheen",
    "population": "4766",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "74.49",
    "coords": {
      "lat": "53.46306",
      "lon": "12.02833"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Lübz",
    "population": "6342",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "89.01",
    "coords": {
      "lat": "52.967",
      "lon": "11.150"
    },
    "district": "Lüchow-Dannenberg",
    "name": "Lüchow",
    "population": "9388",
    "state": "Lower Saxony"
  },
  {
    "area": "86.73",
    "coords": {
      "lat": "51.217",
      "lon": "7.633"
    },
    "district": "Märkischer Kreis",
    "name": "Lüdenscheid",
    "population": "72611",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "140.31",
    "coords": {
      "lat": "51.767",
      "lon": "7.433"
    },
    "district": "Coesfeld",
    "name": "Lüdinghausen",
    "population": "24590",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "88.64",
    "coords": {
      "lat": "51.95000",
      "lon": "9.25000"
    },
    "district": "Lippe",
    "name": "Lügde",
    "population": "9448",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "70.34",
    "coords": {
      "lat": "53.25250",
      "lon": "10.41444"
    },
    "district": "Lüneburg",
    "name": "Lüneburg",
    "population": "75351",
    "state": "Lower Saxony"
  },
  {
    "area": "59.18",
    "coords": {
      "lat": "51.617",
      "lon": "7.517"
    },
    "district": "Unna",
    "name": "Lünen",
    "population": "86449",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "6.15",
    "coords": {
      "lat": "54.29472",
      "lon": "10.59139"
    },
    "district": "Plön",
    "name": "Lütjenburg",
    "population": "5322",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "108.28",
    "coords": {
      "lat": "51.250",
      "lon": "12.133"
    },
    "district": "Burgenlandkreis",
    "name": "Lützen",
    "population": "8546",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "20.53",
    "coords": {
      "lat": "50.90667",
      "lon": "11.44611"
    },
    "district": "Weimarer Land",
    "name": "Magdala",
    "population": "2005",
    "state": "Thuringia"
  },
  {
    "area": "200.95",
    "coords": {
      "lat": "52.13333",
      "lon": "11.61667"
    },
    "district": "Urban district",
    "name": "Magdeburg",
    "population": "238697",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "16.59",
    "coords": {
      "lat": "48.28694",
      "lon": "7.81139"
    },
    "district": "Ortenaukreis",
    "name": "Mahlberg",
    "population": "5061",
    "state": "Baden-Württemberg"
  },
  {
    "area": "12.00",
    "coords": {
      "lat": "49.700",
      "lon": "10.217"
    },
    "district": "Kitzingen",
    "name": "Mainbernheim",
    "population": "2182",
    "state": "Bavaria"
  },
  {
    "area": "61.59",
    "coords": {
      "lat": "48.650",
      "lon": "11.783"
    },
    "district": "Kelheim",
    "name": "Mainburg",
    "population": "15241",
    "state": "Bavaria"
  },
  {
    "area": "32.4",
    "coords": {
      "lat": "50.150",
      "lon": "8.833"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Maintal",
    "population": "39298",
    "state": "Hesse"
  },
  {
    "area": "97.75",
    "coords": {
      "lat": "50.000",
      "lon": "8.267"
    },
    "district": "Urban district",
    "name": "Mainz",
    "population": "217118",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "109.21",
    "coords": {
      "lat": "53.733",
      "lon": "12.783"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Malchin",
    "population": "7403",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "44.60",
    "coords": {
      "lat": "53.467",
      "lon": "12.417"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Malchow",
    "population": "6627",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "10.06",
    "coords": {
      "lat": "50.09194",
      "lon": "6.80917"
    },
    "district": "Bernkastel-Wittlich",
    "name": "Manderscheid",
    "population": "1415",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "144.96",
    "coords": {
      "lat": "49.48778",
      "lon": "8.46611"
    },
    "district": "urban district",
    "name": "Mannheim",
    "population": "309370",
    "state": "Baden-Württemberg"
  },
  {
    "area": "143.78",
    "coords": {
      "lat": "51.59417",
      "lon": "11.45472"
    },
    "district": "Mansfeld-Südharz",
    "name": "Mansfeld",
    "population": "8765",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "18.06",
    "coords": {
      "lat": "48.933",
      "lon": "9.250"
    },
    "district": "Ludwigsburg",
    "name": "Marbach am Neckar",
    "population": "16008",
    "state": "Baden-Württemberg"
  },
  {
    "area": "123.92",
    "coords": {
      "lat": "50.81000",
      "lon": "8.77083"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Marburg",
    "population": "76851",
    "state": "Hesse"
  },
  {
    "area": "133.47",
    "coords": {
      "lat": "50.633",
      "lon": "13.150"
    },
    "district": "Erzgebirgskreis",
    "name": "Marienberg",
    "population": "17097",
    "state": "Saxony"
  },
  {
    "area": "64.35",
    "coords": {
      "lat": "51.81667",
      "lon": "9.18306"
    },
    "district": "Höxter",
    "name": "Marienmünster",
    "population": "4962",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "40.92",
    "coords": {
      "lat": "47.72083",
      "lon": "9.39167"
    },
    "district": "Bodenseekreis",
    "name": "Markdorf",
    "population": "14031",
    "state": "Baden-Württemberg"
  },
  {
    "area": "28.16",
    "coords": {
      "lat": "48.90472",
      "lon": "9.08083"
    },
    "district": "Ludwigsburg",
    "name": "Markgröningen",
    "population": "14785",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.36",
    "coords": {
      "lat": "51.27778",
      "lon": "12.38333"
    },
    "district": "Leipzig",
    "name": "Markkleeberg",
    "population": "24679",
    "state": "Saxony"
  },
  {
    "area": "69.06",
    "coords": {
      "lat": "50.317",
      "lon": "12.317"
    },
    "district": "Vogtlandkreis",
    "name": "Markneukirchen",
    "population": "7583",
    "state": "Saxony"
  },
  {
    "area": "58.27",
    "coords": {
      "lat": "51.30167",
      "lon": "12.22111"
    },
    "district": "Leipzig",
    "name": "Markranstädt",
    "population": "15619",
    "state": "Saxony"
  },
  {
    "area": "20.15",
    "coords": {
      "lat": "49.66694",
      "lon": "10.14361"
    },
    "district": "Kitzingen",
    "name": "Marktbreit",
    "population": "3917",
    "state": "Bavaria"
  },
  {
    "area": "37.54",
    "coords": {
      "lat": "49.850",
      "lon": "9.600"
    },
    "district": "Main-Spessart",
    "name": "Marktheidenfeld",
    "population": "11194",
    "state": "Bavaria"
  },
  {
    "area": "35.49",
    "coords": {
      "lat": "50.117",
      "lon": "12.000"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Marktleuthen",
    "population": "3069",
    "state": "Bavaria"
  },
  {
    "area": "95.25",
    "coords": {
      "lat": "47.767",
      "lon": "10.617"
    },
    "district": "Ostallgäu",
    "name": "Marktoberdorf",
    "population": "18539",
    "state": "Bavaria"
  },
  {
    "area": "49.52",
    "coords": {
      "lat": "50.000",
      "lon": "12.067"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Marktredwitz",
    "population": "17217",
    "state": "Bavaria"
  },
  {
    "area": "10.51",
    "coords": {
      "lat": "49.700",
      "lon": "10.117"
    },
    "district": "Kitzingen",
    "name": "Marktsteft",
    "population": "1969",
    "state": "Bavaria"
  },
  {
    "area": "139.81",
    "coords": {
      "lat": "54.133",
      "lon": "12.567"
    },
    "district": "Vorpommern-Rügen",
    "name": "Marlow",
    "population": "4563",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "87.69",
    "coords": {
      "lat": "51.667",
      "lon": "7.117"
    },
    "district": "Recklinghausen",
    "name": "Marl",
    "population": "83941",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "4.83",
    "coords": {
      "lat": "53.950",
      "lon": "9.000"
    },
    "district": "Dithmarschen",
    "name": "Marne",
    "population": "5858",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "182.01",
    "coords": {
      "lat": "51.450",
      "lon": "8.833"
    },
    "district": "Hochsauerland",
    "name": "Marsberg",
    "population": "19640",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "25.44",
    "coords": {
      "lat": "49.00028",
      "lon": "8.81083"
    },
    "district": "Enzkreis",
    "name": "Maulbronn",
    "population": "6588",
    "state": "Baden-Württemberg"
  },
  {
    "area": "34.71",
    "coords": {
      "lat": "49.200",
      "lon": "12.100"
    },
    "district": "Schwandorf",
    "name": "Maxhütte-Haidhof",
    "population": "11575",
    "state": "Bavaria"
  },
  {
    "area": "58.04",
    "coords": {
      "lat": "50.33333",
      "lon": "7.21667"
    },
    "district": "Mayen-Koblenz",
    "name": "Mayen",
    "population": "19144",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "136.3",
    "coords": {
      "lat": "50.600",
      "lon": "6.650"
    },
    "district": "Euskirchen",
    "name": "Mechernich",
    "population": "27598",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "34.92",
    "coords": {
      "lat": "50.633",
      "lon": "7.017"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Meckenheim",
    "population": "24684",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "126.05",
    "coords": {
      "lat": "51.19722",
      "lon": "8.70694"
    },
    "district": "Hochsauerlandkreis",
    "name": "Medebach",
    "population": "8055",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "19.77",
    "coords": {
      "lat": "50.85194",
      "lon": "12.46361"
    },
    "district": "Zwickau",
    "name": "Meerane",
    "population": "14208",
    "state": "Saxony"
  },
  {
    "area": "64.37",
    "coords": {
      "lat": "51.267",
      "lon": "6.667"
    },
    "district": "Rhein-Kreis Neuss",
    "name": "Meerbusch",
    "population": "56189",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "12.08",
    "coords": {
      "lat": "47.700",
      "lon": "9.267"
    },
    "district": "Bodenseekreis",
    "name": "Meersburg",
    "population": "5944",
    "state": "Baden-Württemberg"
  },
  {
    "area": "115.18",
    "coords": {
      "lat": "51.117",
      "lon": "7.633"
    },
    "district": "Märkischer Kreis",
    "name": "Meinerzhagen",
    "population": "20397",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "105.65",
    "coords": {
      "lat": "50.550",
      "lon": "10.417"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Meiningen",
    "population": "24852",
    "state": "Thuringia"
  },
  {
    "area": "10.34",
    "coords": {
      "lat": "49.717",
      "lon": "7.667"
    },
    "district": "Bad Kreuznach",
    "name": "Meisenheim",
    "population": "2794",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "30.90",
    "coords": {
      "lat": "51.167",
      "lon": "13.483"
    },
    "district": "Meißen",
    "name": "Meissen",
    "population": "28044",
    "state": "Saxony"
  },
  {
    "area": "21.25",
    "coords": {
      "lat": "54.083",
      "lon": "9.067"
    },
    "district": "Dithmarschen",
    "name": "Meldorf",
    "population": "7204",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "254.00",
    "coords": {
      "lat": "52.20444",
      "lon": "8.33889"
    },
    "district": "Osnabrück",
    "name": "Melle",
    "population": "46493",
    "state": "Lower Saxony"
  },
  {
    "area": "55.78",
    "coords": {
      "lat": "50.417",
      "lon": "10.317"
    },
    "district": "Rhön-Grabfeld",
    "name": "Mellrichstadt",
    "population": "5525",
    "state": "Bavaria"
  },
  {
    "area": "63.1",
    "coords": {
      "lat": "51.133",
      "lon": "9.550"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Melsungen",
    "population": "13659",
    "state": "Hesse"
  },
  {
    "area": "70.17",
    "coords": {
      "lat": "47.98778",
      "lon": "10.18111"
    },
    "district": "Urban district",
    "name": "Memmingen",
    "population": "43837",
    "state": "Bavaria"
  },
  {
    "area": "86.08",
    "coords": {
      "lat": "51.43333",
      "lon": "7.80000"
    },
    "district": "Märkischer Kreis",
    "name": "Menden",
    "population": "52912",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "23.78",
    "coords": {
      "lat": "50.37444",
      "lon": "7.28083"
    },
    "district": "Mayen-Koblenz",
    "name": "Mendig",
    "population": "8895",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "49.80",
    "coords": {
      "lat": "48.04972",
      "lon": "9.33000"
    },
    "district": "Sigmaringen",
    "name": "Mengen",
    "population": "9896",
    "state": "Baden-Württemberg"
  },
  {
    "area": "188.48",
    "coords": {
      "lat": "52.69361",
      "lon": "7.29278"
    },
    "district": "Emsland",
    "name": "Meppen",
    "population": "35373",
    "state": "Lower Saxony"
  },
  {
    "area": "26.08",
    "coords": {
      "lat": "49.200",
      "lon": "10.683"
    },
    "district": "Ansbach",
    "name": "Merkendorf",
    "population": "3000",
    "state": "Bavaria"
  },
  {
    "area": "54.73",
    "coords": {
      "lat": "51.35444",
      "lon": "11.99278"
    },
    "district": "Saalekreis",
    "name": "Merseburg",
    "population": "34080",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "108.79",
    "coords": {
      "lat": "49.450",
      "lon": "6.617"
    },
    "district": "Merzig-Wadern",
    "name": "Merzig",
    "population": "29745",
    "state": "Saarland"
  },
  {
    "area": "218.5",
    "coords": {
      "lat": "51.350",
      "lon": "8.283"
    },
    "district": "Hochsauerlandkreis",
    "name": "Meschede",
    "population": "29921",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "42.52",
    "coords": {
      "lat": "51.250",
      "lon": "6.967"
    },
    "district": "Mettmann",
    "name": "Mettmann",
    "population": "38829",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "34.61",
    "coords": {
      "lat": "48.53667",
      "lon": "9.28583"
    },
    "district": "Reutlingen",
    "name": "Metzingen",
    "population": "22046",
    "state": "Baden-Württemberg"
  },
  {
    "area": "56.99",
    "coords": {
      "lat": "51.05000",
      "lon": "12.30000"
    },
    "district": "Altenburger Land",
    "name": "Meuselwitz",
    "population": "10065",
    "state": "Thuringia"
  },
  {
    "area": "50.62",
    "coords": {
      "lat": "53.31667",
      "lon": "12.23306"
    },
    "district": "Prignitz",
    "name": "Meyenburg",
    "population": "2083",
    "state": "Brandenburg"
  },
  {
    "area": "76.22",
    "coords": {
      "lat": "47.99278",
      "lon": "9.11250"
    },
    "district": "Sigmaringen",
    "name": "Meßkirch",
    "population": "8418",
    "state": "Baden-Württemberg"
  },
  {
    "area": "76.82",
    "coords": {
      "lat": "48.18056",
      "lon": "8.96250"
    },
    "district": "Zollernalbkreis",
    "name": "Meßstetten",
    "population": "10653",
    "state": "Baden-Württemberg"
  },
  {
    "area": "86.97",
    "coords": {
      "lat": "49.678591",
      "lon": "09.003859"
    },
    "district": "Odenwaldkreis",
    "name": "Michelstadt",
    "population": "16151",
    "state": "Hesse"
  },
  {
    "area": "32.35",
    "coords": {
      "lat": "47.783",
      "lon": "11.833"
    },
    "district": "Miesbach",
    "name": "Miesbach",
    "population": "11562",
    "state": "Bavaria"
  },
  {
    "area": "60.18",
    "coords": {
      "lat": "49.70389",
      "lon": "9.26444"
    },
    "district": "Miltenberg",
    "name": "Miltenberg",
    "population": "9355",
    "state": "Bavaria"
  },
  {
    "area": "56.44",
    "coords": {
      "lat": "48.033",
      "lon": "10.467"
    },
    "district": "Unterallgäu",
    "name": "Mindelheim",
    "population": "15002",
    "state": "Bavaria"
  },
  {
    "area": "101.08",
    "coords": {
      "lat": "52.28833",
      "lon": "8.91667"
    },
    "district": "Minden-Lübbecke",
    "name": "Minden",
    "population": "81682",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "156.37",
    "coords": {
      "lat": "53.267",
      "lon": "12.800"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Mirow",
    "population": "3933",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "98.48",
    "coords": {
      "lat": "52.267",
      "lon": "13.533"
    },
    "district": "Dahme-Spreewald",
    "name": "Mittenwalde",
    "population": "9140",
    "state": "Brandenburg"
  },
  {
    "area": "39.35",
    "coords": {
      "lat": "49.933",
      "lon": "12.233"
    },
    "district": "Tirschenreuth",
    "name": "Mitterteich",
    "population": "6596",
    "state": "Bavaria"
  },
  {
    "area": "41.24",
    "coords": {
      "lat": "50.98556",
      "lon": "12.98111"
    },
    "district": "Mittelsachsen",
    "name": "Mittweida",
    "population": "14645",
    "state": "Saxony"
  },
  {
    "area": "67.68",
    "coords": {
      "lat": "51.45917",
      "lon": "6.61972"
    },
    "district": "Wesel",
    "name": "Moers",
    "population": "103725",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "23.1",
    "coords": {
      "lat": "51.100",
      "lon": "6.900"
    },
    "district": "Mettmann",
    "name": "Monheim am Rhein",
    "population": "40645",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "69.35",
    "coords": {
      "lat": "48.833",
      "lon": "10.833"
    },
    "district": "Donau-Ries",
    "name": "Monheim",
    "population": "5093",
    "state": "Bavaria"
  },
  {
    "area": "94.62",
    "coords": {
      "lat": "50.56000",
      "lon": "6.25639"
    },
    "district": "Aachen",
    "name": "Monschau",
    "population": "11726",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "33.61",
    "coords": {
      "lat": "50.43750",
      "lon": "7.82583"
    },
    "district": "Westerwaldkreis",
    "name": "Montabaur",
    "population": "13691",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "43.86",
    "coords": {
      "lat": "48.467",
      "lon": "11.933"
    },
    "district": "Freising",
    "name": "Moosburg an der Isar",
    "population": "18510",
    "state": "Bavaria"
  },
  {
    "area": "82.25",
    "coords": {
      "lat": "51.700",
      "lon": "9.867"
    },
    "district": "Northeim",
    "name": "Moringen",
    "population": "6956",
    "state": "Lower Saxony"
  },
  {
    "area": "62.23",
    "coords": {
      "lat": "49.35222",
      "lon": "9.14667"
    },
    "district": "Neckar-Odenwald-Kreis",
    "name": "Mosbach",
    "population": "23398",
    "state": "Baden-Württemberg"
  },
  {
    "area": "13.08",
    "coords": {
      "lat": "48.23528",
      "lon": "9.64389"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Munderkingen",
    "population": "5292",
    "state": "Baden-Württemberg"
  },
  {
    "area": "310.43",
    "coords": {
      "lat": "48.133",
      "lon": "11.567"
    },
    "district": "Urban district",
    "name": "Munich",
    "population": "1471508",
    "state": "Bavaria"
  },
  {
    "area": "193.42",
    "coords": {
      "lat": "52.98861",
      "lon": "10.09111"
    },
    "district": "Heidekreis",
    "name": "Munster",
    "population": "15117",
    "state": "Lower Saxony"
  },
  {
    "area": "71.13",
    "coords": {
      "lat": "48.98000",
      "lon": "9.58139"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Murrhardt",
    "population": "14033",
    "state": "Baden-Württemberg"
  },
  {
    "area": "24.65",
    "coords": {
      "lat": "52.100",
      "lon": "13.750"
    },
    "district": "Dahme-Spreewald",
    "name": "Märkisch Buchholz",
    "population": "834",
    "state": "Brandenburg"
  },
  {
    "area": "530.19",
    "coords": {
      "lat": "52.14056",
      "lon": "11.95250"
    },
    "district": "Jerichower Land",
    "name": "Möckern",
    "population": "12874",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "49.61",
    "coords": {
      "lat": "49.317",
      "lon": "9.350"
    },
    "district": "Heilbronn",
    "name": "Möckmühl",
    "population": "8078",
    "state": "Baden-Württemberg"
  },
  {
    "area": "25.05",
    "coords": {
      "lat": "53.62694",
      "lon": "10.68472"
    },
    "district": "Lauenburg",
    "name": "Mölln",
    "population": "19031",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "170.43",
    "coords": {
      "lat": "51.20000",
      "lon": "6.43333"
    },
    "district": "Urban districts of Germany",
    "name": "Mönchengladbach",
    "population": "261454",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "44.16",
    "coords": {
      "lat": "50.000",
      "lon": "8.583"
    },
    "district": "Groß-Gerau",
    "name": "Mörfelden-Walldorf",
    "population": "34828",
    "state": "Hesse"
  },
  {
    "area": "50.05",
    "coords": {
      "lat": "48.40639",
      "lon": "9.05750"
    },
    "district": "Tübingen",
    "name": "Mössingen",
    "population": "20480",
    "state": "Baden-Württemberg"
  },
  {
    "area": "98.6",
    "coords": {
      "lat": "51.30000",
      "lon": "11.80000"
    },
    "district": "Saalekreis",
    "name": "Mücheln",
    "population": "8681",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "54.95",
    "coords": {
      "lat": "51.23333",
      "lon": "13.05000"
    },
    "district": "Nordsachsen",
    "name": "Mügeln",
    "population": "5913",
    "state": "Saxony"
  },
  {
    "area": "54.32",
    "coords": {
      "lat": "48.95000",
      "lon": "8.83944"
    },
    "district": "Enzkreis",
    "name": "Mühlacker",
    "population": "26076",
    "state": "Baden-Württemberg"
  },
  {
    "area": "88.55",
    "coords": {
      "lat": "51.43306",
      "lon": "13.21667"
    },
    "district": "Elbe-Elster",
    "name": "Mühlberg",
    "population": "3734",
    "state": "Brandenburg"
  },
  {
    "area": "29.42",
    "coords": {
      "lat": "48.24167",
      "lon": "12.52500"
    },
    "district": "Mühldorf am Inn",
    "name": "Mühldorf am Inn",
    "population": "20323",
    "state": "Bavaria"
  },
  {
    "area": "130.70",
    "coords": {
      "lat": "51.217",
      "lon": "10.450"
    },
    "district": "Unstrut-Hainich-Kreis",
    "name": "Mühlhausen",
    "population": "36200",
    "state": "Thuringia"
  },
  {
    "area": "20.67",
    "coords": {
      "lat": "50.117",
      "lon": "8.917"
    },
    "district": "Offenbach",
    "name": "Mühlheim am Main",
    "population": "28403",
    "state": "Hesse"
  },
  {
    "area": "21.73",
    "coords": {
      "lat": "48.03056",
      "lon": "8.88583"
    },
    "district": "Tuttlingen",
    "name": "Mühlheim an der Donau",
    "population": "3607",
    "state": "Baden-Württemberg"
  },
  {
    "area": "91.26",
    "coords": {
      "lat": "51.433",
      "lon": "6.883"
    },
    "district": "Urban districts of Germany",
    "name": "Mülheim an der Ruhr",
    "population": "170880",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "16.31",
    "coords": {
      "lat": "50.38694",
      "lon": "7.49528"
    },
    "district": "Mayen-Koblenz",
    "name": "Mülheim-Kärlich",
    "population": "11177",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "57.91",
    "coords": {
      "lat": "47.800",
      "lon": "7.633"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Müllheim",
    "population": "19127",
    "state": "Baden-Württemberg"
  },
  {
    "area": "68.54",
    "coords": {
      "lat": "52.250",
      "lon": "14.417"
    },
    "district": "Oder-Spree",
    "name": "Müllrose",
    "population": "4630",
    "state": "Brandenburg"
  },
  {
    "area": "68.78",
    "coords": {
      "lat": "50.200",
      "lon": "11.767"
    },
    "district": "Hof",
    "name": "Münchberg",
    "population": "10215",
    "state": "Bavaria"
  },
  {
    "area": "151.93",
    "coords": {
      "lat": "52.50361",
      "lon": "14.13972"
    },
    "district": "Märkisch-Oderland",
    "name": "Müncheberg",
    "population": "6870",
    "state": "Brandenburg"
  },
  {
    "area": "15.43",
    "coords": {
      "lat": "50.81667",
      "lon": "11.93333"
    },
    "district": "Greiz",
    "name": "Münchenbernsdorf",
    "population": "2943",
    "state": "Thuringia"
  },
  {
    "area": "93.11",
    "coords": {
      "lat": "50.250",
      "lon": "10.167"
    },
    "district": "Bad Kissingen",
    "name": "Münnerstadt",
    "population": "7606",
    "state": "Bavaria"
  },
  {
    "area": "116.05",
    "coords": {
      "lat": "48.41278",
      "lon": "9.49528"
    },
    "district": "Reutlingen",
    "name": "Münsingen",
    "population": "14335",
    "state": "Baden-Württemberg"
  },
  {
    "area": "27.78",
    "coords": {
      "lat": "50.24750",
      "lon": "7.36306"
    },
    "district": "Mayen-Koblenz",
    "name": "Münstermaifeld",
    "population": "3449",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "302.89",
    "coords": {
      "lat": "51.96250",
      "lon": "7.62556"
    },
    "district": "Urban district",
    "name": "Münster",
    "population": "314319",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "31.63",
    "coords": {
      "lat": "50.45333",
      "lon": "8.77611"
    },
    "district": "Wetteraukreis",
    "name": "Münzenberg",
    "population": "5769",
    "state": "Hesse"
  },
  {
    "area": "62.39",
    "coords": {
      "lat": "49.450",
      "lon": "12.167"
    },
    "district": "Schwandorf",
    "name": "Nabburg",
    "population": "6117",
    "state": "Bavaria"
  },
  {
    "area": "63.09",
    "coords": {
      "lat": "48.55194",
      "lon": "8.72556"
    },
    "district": "Calw",
    "name": "Nagold",
    "population": "22294",
    "state": "Baden-Württemberg"
  },
  {
    "area": "37.05",
    "coords": {
      "lat": "50.317",
      "lon": "11.683"
    },
    "district": "Hof",
    "name": "Naila",
    "population": "7684",
    "state": "Bavaria"
  },
  {
    "area": "17.51",
    "coords": {
      "lat": "50.31583",
      "lon": "7.80222"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Nassau",
    "population": "4513",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "13.02",
    "coords": {
      "lat": "50.19944",
      "lon": "7.85833"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Nastätten",
    "population": "4199",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "266.78",
    "coords": {
      "lat": "52.60000",
      "lon": "12.88306"
    },
    "district": "Havelland",
    "name": "Nauen",
    "population": "17967",
    "state": "Brandenburg"
  },
  {
    "area": "66.29",
    "coords": {
      "lat": "51.250",
      "lon": "9.167"
    },
    "district": "Kassel",
    "name": "Naumburg",
    "population": "5028",
    "state": "Hesse"
  },
  {
    "area": "129.88",
    "coords": {
      "lat": "51.150",
      "lon": "11.817"
    },
    "district": "Burgenlandkreis",
    "name": "Naumburg",
    "population": "32402",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "39.49",
    "coords": {
      "lat": "51.27778",
      "lon": "12.58833"
    },
    "district": "Leipzig",
    "name": "Naunhof",
    "population": "8735",
    "state": "Saxony"
  },
  {
    "area": "25.42",
    "coords": {
      "lat": "51.283",
      "lon": "11.567"
    },
    "district": "Burgenlandkreis",
    "name": "Nebra",
    "population": "3127",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "26.41",
    "coords": {
      "lat": "49.29250",
      "lon": "8.96056"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Neckarbischofsheim",
    "population": "4040",
    "state": "Baden-Württemberg"
  },
  {
    "area": "26.15",
    "coords": {
      "lat": "49.39389",
      "lon": "8.79750"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Neckargemünd",
    "population": "13290",
    "state": "Baden-Württemberg"
  },
  {
    "area": "17.22",
    "coords": {
      "lat": "49.40000",
      "lon": "8.83333"
    },
    "district": "Bergstraße",
    "name": "Neckarsteinach",
    "population": "3915",
    "state": "Hesse"
  },
  {
    "area": "24.94",
    "coords": {
      "lat": "49.191694",
      "lon": "9.224556"
    },
    "district": "Heilbronn",
    "name": "Neckarsulm",
    "population": "26492",
    "state": "Baden-Württemberg"
  },
  {
    "area": "118.52",
    "coords": {
      "lat": "48.75417",
      "lon": "10.33444"
    },
    "district": "Ostalbkreis",
    "name": "Neresheim",
    "population": "7945",
    "state": "Baden-Württemberg"
  },
  {
    "area": "137.39",
    "coords": {
      "lat": "50.91472",
      "lon": "8.10000"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Netphen",
    "population": "23130",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "84",
    "coords": {
      "lat": "51.317",
      "lon": "6.283"
    },
    "district": "Viersen",
    "name": "Nettetal",
    "population": "42493",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "12.51",
    "coords": {
      "lat": "50.617",
      "lon": "12.250"
    },
    "district": "Vogtlandkreis",
    "name": "Netzschkau",
    "population": "3930",
    "state": "Saxony"
  },
  {
    "area": "36.14",
    "coords": {
      "lat": "50.29306",
      "lon": "8.50889"
    },
    "district": "Hochtaunuskreis",
    "name": "Neu-Anspach",
    "population": "14618",
    "state": "Hesse"
  },
  {
    "area": "24.29",
    "coords": {
      "lat": "50.050",
      "lon": "8.700"
    },
    "district": "Offenbach",
    "name": "Neu-Isenburg",
    "population": "37668",
    "state": "Hesse"
  },
  {
    "area": "80.50",
    "coords": {
      "lat": "48.383",
      "lon": "10.000"
    },
    "district": "Neu-Ulm",
    "name": "Neu-Ulm",
    "population": "58707",
    "state": "Bavaria"
  },
  {
    "area": "85.65",
    "coords": {
      "lat": "53.55694",
      "lon": "13.26111"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Neubrandenburg",
    "population": "64086",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "24.99",
    "coords": {
      "lat": "54.017",
      "lon": "11.667"
    },
    "district": "Rostock",
    "name": "Neubukow",
    "population": "3918",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "24.69",
    "coords": {
      "lat": "48.66111",
      "lon": "8.69667"
    },
    "district": "Calw",
    "name": "Neubulach",
    "population": "5624",
    "state": "Baden-Württemberg"
  },
  {
    "area": "81.32",
    "coords": {
      "lat": "48.733",
      "lon": "11.183"
    },
    "district": "Neuburg-Schrobenhausen",
    "name": "Neuburg a.d. Donau",
    "population": "29682",
    "state": "Bavaria"
  },
  {
    "area": "32.93",
    "coords": {
      "lat": "49.283",
      "lon": "9.267"
    },
    "district": "Heilbronn",
    "name": "Neudenau",
    "population": "5266",
    "state": "Baden-Württemberg"
  },
  {
    "area": "44.12",
    "coords": {
      "lat": "47.81472",
      "lon": "7.56194"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Neuenburg am Rhein",
    "population": "12437",
    "state": "Baden-Württemberg"
  },
  {
    "area": "28.17",
    "coords": {
      "lat": "48.84611",
      "lon": "8.58889"
    },
    "district": "Enzkreis",
    "name": "Neuenbürg",
    "population": "8206",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.31",
    "coords": {
      "lat": "52.50000",
      "lon": "6.96667"
    },
    "district": "Grafschaft Bentheim",
    "name": "Neuenhaus",
    "population": "10025",
    "state": "Lower Saxony"
  },
  {
    "area": "54.12",
    "coords": {
      "lat": "51.28389",
      "lon": "7.78000"
    },
    "district": "Märkischer Kreis",
    "name": "Neuenrade",
    "population": "11982",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "41.18",
    "coords": {
      "lat": "49.233",
      "lon": "9.333"
    },
    "district": "Heilbronn",
    "name": "Neuenstadt am Kocher",
    "population": "10123",
    "state": "Baden-Württemberg"
  },
  {
    "area": "47.84",
    "coords": {
      "lat": "49.200",
      "lon": "9.583"
    },
    "district": "Hohenlohekreis",
    "name": "Neuenstein",
    "population": "6531",
    "state": "Baden-Württemberg"
  },
  {
    "area": "10.23",
    "coords": {
      "lat": "50.01056",
      "lon": "6.29583"
    },
    "district": "Eifelkreis Bitburg-Prüm",
    "name": "Neuerburg",
    "population": "1516",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "17.45",
    "coords": {
      "lat": "48.55444",
      "lon": "9.37556"
    },
    "district": "Esslingen",
    "name": "Neuffen",
    "population": "6299",
    "state": "Baden-Württemberg"
  },
  {
    "area": "108.22",
    "coords": {
      "lat": "50.517",
      "lon": "11.150"
    },
    "district": "Sonneberg",
    "name": "Neuhaus am Rennweg",
    "population": "9076",
    "state": "Thuringia"
  },
  {
    "area": "46.84",
    "coords": {
      "lat": "53.817",
      "lon": "12.783"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Neukalen",
    "population": "1736",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "43.48",
    "coords": {
      "lat": "51.44167",
      "lon": "6.55833"
    },
    "district": "Wesel",
    "name": "Neukirchen-Vluyn",
    "population": "26982",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "66.26",
    "coords": {
      "lat": "50.867",
      "lon": "9.333"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Neukirchen",
    "population": "6986",
    "state": "Hesse"
  },
  {
    "area": "27.49",
    "coords": {
      "lat": "53.867",
      "lon": "11.683"
    },
    "district": "Nordwestmecklenburg",
    "name": "Neukloster",
    "population": "3925",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "79.03",
    "coords": {
      "lat": "49.283",
      "lon": "11.467"
    },
    "district": "Neumarkt in der Oberpfalz",
    "name": "Neumarkt in der Oberpfalz",
    "population": "40002",
    "state": "Bavaria"
  },
  {
    "area": "61.06",
    "coords": {
      "lat": "48.367",
      "lon": "12.500"
    },
    "district": "Mühldorf am Inn",
    "name": "Neumarkt-Sankt Veit",
    "population": "6243",
    "state": "Bavaria"
  },
  {
    "area": "8.64",
    "coords": {
      "lat": "51.08000",
      "lon": "11.24778"
    },
    "district": "Weimarer Land",
    "name": "Neumark",
    "population": "494",
    "state": "Thuringia"
  },
  {
    "area": "71.57",
    "coords": {
      "lat": "54.07139",
      "lon": "9.99000"
    },
    "district": "Urban district",
    "name": "Neumünster",
    "population": "79487",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "110.16",
    "coords": {
      "lat": "49.333",
      "lon": "12.383"
    },
    "district": "Schwandorf",
    "name": "Neunburg vorm Wald",
    "population": "8338",
    "state": "Bavaria"
  },
  {
    "area": "75.08",
    "coords": {
      "lat": "49.350",
      "lon": "7.167"
    },
    "district": "Neunkirchen",
    "name": "Neunkirchen",
    "population": "46469",
    "state": "Saarland"
  },
  {
    "area": "303.32",
    "coords": {
      "lat": "52.93306",
      "lon": "12.80000"
    },
    "district": "Ostprignitz-Ruppin",
    "name": "Neuruppin",
    "population": "30846",
    "state": "Brandenburg"
  },
  {
    "area": "23.89",
    "coords": {
      "lat": "51.03889",
      "lon": "14.52944"
    },
    "district": "Görlitz",
    "name": "Neusalza-Spremberg",
    "population": "3337",
    "state": "Saxony"
  },
  {
    "area": "99.48",
    "coords": {
      "lat": "51.200",
      "lon": "6.700"
    },
    "district": "Rhein-Kreis Neuss",
    "name": "Neuss",
    "population": "153796",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "93.56",
    "coords": {
      "lat": "48.800",
      "lon": "11.767"
    },
    "district": "Kelheim",
    "name": "Neustadt a.d.Donau",
    "population": "14409",
    "state": "Bavaria"
  },
  {
    "area": "9.93",
    "coords": {
      "lat": "49.730820",
      "lon": "12.170700"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Neustadt a.d.Waldnaab",
    "population": "5727",
    "state": "Bavaria"
  },
  {
    "area": "20.30",
    "coords": {
      "lat": "49.817",
      "lon": "11.817"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Neustadt am Kulm",
    "population": "1105",
    "state": "Bavaria"
  },
  {
    "area": "357",
    "coords": {
      "lat": "52.500",
      "lon": "9.467"
    },
    "district": "Hanover",
    "name": "Neustadt am Rübenberge",
    "population": "44282",
    "state": "Lower Saxony"
  },
  {
    "coords": {
      "lat": "49.59667",
      "lon": "10.60889"
    },
    "district": "Neustadt (Aisch)-Bad Windsheim",
    "name": "Neustadt an der Aisch",
    "population": "13121",
    "state": "Bavaria"
  },
  {
    "area": "86.08",
    "coords": {
      "lat": "50.733",
      "lon": "11.750"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Neustadt an der Orla",
    "state": "Thuringia"
  },
  {
    "area": "117.10",
    "coords": {
      "lat": "49.350",
      "lon": "8.150"
    },
    "district": "Urban district",
    "name": "Neustadt an der Weinstraße",
    "population": "53148",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "61.90",
    "coords": {
      "lat": "50.32889",
      "lon": "11.12111"
    },
    "district": "Coburg",
    "name": "Neustadt b.Coburg",
    "population": "15257",
    "state": "Bavaria"
  },
  {
    "area": "19.74",
    "coords": {
      "lat": "54.10722",
      "lon": "10.81583"
    },
    "district": "Ostholstein",
    "name": "Neustadt in Holstein",
    "population": "15093",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "83.05",
    "coords": {
      "lat": "51.02389",
      "lon": "14.21667"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Neustadt in Sachsen",
    "population": "12137",
    "state": "Saxony"
  },
  {
    "area": "93.91",
    "coords": {
      "lat": "53.367",
      "lon": "11.583"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Neustadt-Glewe",
    "population": "7009",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "75.43",
    "coords": {
      "lat": "52.86667",
      "lon": "12.43306"
    },
    "district": "Ostprignitz-Ruppin",
    "name": "Neustadt",
    "population": "3452",
    "state": "Brandenburg"
  },
  {
    "area": "56.88",
    "coords": {
      "lat": "50.850",
      "lon": "9.117"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Neustadt",
    "population": "9586",
    "state": "Hesse"
  },
  {
    "area": "138.15",
    "coords": {
      "lat": "53.36472",
      "lon": "13.06361"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Neustrelitz",
    "population": "20140",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "25.14",
    "coords": {
      "lat": "48.400",
      "lon": "10.833"
    },
    "district": "Augsburg",
    "name": "Neusäß",
    "population": "22058",
    "state": "Bavaria"
  },
  {
    "area": "12.00",
    "coords": {
      "lat": "48.99361",
      "lon": "12.19528"
    },
    "district": "Regensburg",
    "name": "Neutraubling",
    "population": "13796",
    "state": "Bavaria"
  },
  {
    "area": "86.50",
    "coords": {
      "lat": "50.42861",
      "lon": "7.46139"
    },
    "district": "Neuwied",
    "name": "Neuwied",
    "population": "64574",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "36.60",
    "coords": {
      "lat": "48.217",
      "lon": "12.683"
    },
    "district": "Altötting",
    "name": "Neuötting",
    "population": "8932",
    "state": "Bavaria"
  },
  {
    "area": "40.21",
    "coords": {
      "lat": "50.29833",
      "lon": "8.81389"
    },
    "district": "Wetteraukreis",
    "name": "Niddatal",
    "population": "9786",
    "state": "Hesse"
  },
  {
    "area": "118.34",
    "coords": {
      "lat": "50.41278",
      "lon": "9.00917"
    },
    "district": "Wetteraukreis",
    "name": "Nidda",
    "population": "17285",
    "state": "Hesse"
  },
  {
    "area": "46.73",
    "coords": {
      "lat": "50.250",
      "lon": "8.900"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Nidderau",
    "population": "20333",
    "state": "Hesse"
  },
  {
    "area": "65.04",
    "coords": {
      "lat": "50.700",
      "lon": "6.483"
    },
    "district": "Düren",
    "name": "Nideggen",
    "population": "9945",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "30.63",
    "coords": {
      "lat": "54.7881000",
      "lon": "8.8296000"
    },
    "district": "Nordfriesland",
    "name": "NiebüllNaibel / Nibøl",
    "population": "9882",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "30.61",
    "coords": {
      "lat": "51.233",
      "lon": "9.317"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Niedenstein",
    "population": "5299",
    "state": "Hesse"
  },
  {
    "area": "17.09",
    "coords": {
      "lat": "49.90833",
      "lon": "8.20278"
    },
    "district": "Mainz-Bingen",
    "name": "Nieder-Olm",
    "population": "4482",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "35.79",
    "coords": {
      "lat": "50.817",
      "lon": "7.033"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Niederkassel",
    "population": "38218",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "17.71",
    "coords": {
      "lat": "49.300",
      "lon": "9.617"
    },
    "district": "Hohenlohekreis",
    "name": "Niedernhall",
    "population": "4106",
    "state": "Baden-Württemberg"
  },
  {
    "area": "104.06",
    "coords": {
      "lat": "49.40083",
      "lon": "9.91806"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Niederstetten",
    "population": "4834",
    "state": "Baden-Württemberg"
  },
  {
    "area": "29.80",
    "coords": {
      "lat": "48.54111",
      "lon": "10.23306"
    },
    "district": "Heidenheim",
    "name": "Niederstotzingen",
    "population": "4693",
    "state": "Baden-Württemberg"
  },
  {
    "area": "79.79",
    "coords": {
      "lat": "51.79972",
      "lon": "9.10972"
    },
    "district": "Höxter",
    "name": "Nieheim",
    "population": "6093",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "44.81",
    "coords": {
      "lat": "52.08306",
      "lon": "12.69972"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Niemegk",
    "population": "2037",
    "state": "Brandenburg"
  },
  {
    "area": "64.45",
    "coords": {
      "lat": "52.64111",
      "lon": "9.20694"
    },
    "district": "Nienburg",
    "name": "Nienburg",
    "population": "31550",
    "state": "Lower Saxony"
  },
  {
    "area": "79.10",
    "coords": {
      "lat": "51.817",
      "lon": "11.750"
    },
    "district": "Salzlandkreis",
    "name": "Nienburg",
    "population": "6193",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "19.34",
    "coords": {
      "lat": "49.86944",
      "lon": "8.33750"
    },
    "district": "Mainz-Bingen",
    "name": "Nierstein",
    "population": "8443",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "53.61",
    "coords": {
      "lat": "51.300",
      "lon": "14.817"
    },
    "district": "Görlitz",
    "name": "Niesky",
    "population": "9402",
    "state": "Saxony"
  },
  {
    "area": "93.15",
    "coords": {
      "lat": "49.200",
      "lon": "12.267"
    },
    "district": "Schwandorf",
    "name": "Nittenau",
    "population": "9019",
    "state": "Bavaria"
  },
  {
    "area": "87.32",
    "coords": {
      "lat": "53.500",
      "lon": "8.467"
    },
    "district": "Wesermarsch",
    "name": "Nordenham",
    "population": "26193",
    "state": "Lower Saxony"
  },
  {
    "area": "106.33",
    "coords": {
      "lat": "53.59667",
      "lon": "7.20556"
    },
    "district": "Aurich",
    "name": "Norden",
    "population": "25060",
    "state": "Lower Saxony"
  },
  {
    "area": "26.3",
    "coords": {
      "lat": "53.70722",
      "lon": "7.14694"
    },
    "district": "Aurich",
    "name": "Norderney",
    "population": "6089",
    "state": "Lower Saxony"
  },
  {
    "area": "58.1",
    "coords": {
      "lat": "53.70639",
      "lon": "10.01028"
    },
    "district": "Kreis Segeberg",
    "name": "Norderstedt",
    "population": "79159",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "110.86",
    "coords": {
      "lat": "51.50500",
      "lon": "10.79111"
    },
    "district": "Nordhausen",
    "name": "Nordhausen, Thuringia",
    "population": "41791",
    "state": "Thuringia"
  },
  {
    "area": "149.64",
    "coords": {
      "lat": "52.43194",
      "lon": "7.06778"
    },
    "district": "Grafschaft Bentheim",
    "name": "Nordhorn",
    "population": "53403",
    "state": "Lower Saxony"
  },
  {
    "area": "145.67",
    "coords": {
      "lat": "51.70667",
      "lon": "10.00111"
    },
    "district": "Northeim",
    "name": "Northeim",
    "population": "29107",
    "state": "Lower Saxony"
  },
  {
    "area": "12.77",
    "coords": {
      "lat": "54.167",
      "lon": "9.867"
    },
    "district": "Rendsburg-Eckernförde",
    "name": "Nortorf",
    "population": "6804",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "122.61",
    "coords": {
      "lat": "51.050",
      "lon": "13.300"
    },
    "district": "Meißen",
    "name": "Nossen",
    "population": "10598",
    "state": "Saxony"
  },
  {
    "area": "75.95",
    "coords": {
      "lat": "51.24694",
      "lon": "10.65639"
    },
    "district": "Unstrut-Hainich-Kreis",
    "name": "Nottertal-Heilinger Höhen",
    "population": "5963",
    "state": "Thuringia"
  },
  {
    "area": "186.46",
    "coords": {
      "lat": "49.450",
      "lon": "11.083"
    },
    "district": "Urban district",
    "name": "Nuremberg",
    "population": "518365",
    "state": "Bavaria"
  },
  {
    "area": "68.10",
    "coords": {
      "lat": "48.85111",
      "lon": "10.48833"
    },
    "district": "Donau-Ries",
    "name": "Nördlingen",
    "population": "20379",
    "state": "Bavaria"
  },
  {
    "area": "46.9",
    "coords": {
      "lat": "48.633",
      "lon": "9.333"
    },
    "district": "Esslingen",
    "name": "Nürtingen",
    "population": "41093",
    "state": "Baden-Württemberg"
  },
  {
    "area": "41.88",
    "coords": {
      "lat": "49.833",
      "lon": "8.750"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Ober-Ramstadt",
    "population": "15130",
    "state": "Hesse"
  },
  {
    "area": "12.11",
    "coords": {
      "lat": "49.42194",
      "lon": "10.95833"
    },
    "district": "Fürth",
    "name": "Oberasbach",
    "population": "17672",
    "state": "Bavaria"
  },
  {
    "area": "271.52",
    "coords": {
      "lat": "51.717",
      "lon": "10.817"
    },
    "district": "Harz",
    "name": "Oberharz am Brocken",
    "population": "10451",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "77.04",
    "coords": {
      "lat": "51.49667",
      "lon": "6.87056"
    },
    "district": "Urban districts of Germany",
    "name": "Oberhausen",
    "population": "210829",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "23.47",
    "coords": {
      "lat": "50.70528",
      "lon": "10.72583"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Oberhof",
    "population": "1608",
    "state": "Thuringia"
  },
  {
    "area": "69.13",
    "coords": {
      "lat": "48.533",
      "lon": "8.083"
    },
    "district": "Ortenaukreis",
    "name": "Oberkirch",
    "population": "20066",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.57",
    "coords": {
      "lat": "48.78389",
      "lon": "10.10528"
    },
    "district": "Ostalbkreis",
    "name": "Oberkochen",
    "population": "7895",
    "state": "Baden-Württemberg"
  },
  {
    "area": "14.67",
    "coords": {
      "lat": "50.783",
      "lon": "12.717"
    },
    "district": "Zwickau",
    "name": "Oberlungwitz",
    "population": "5881",
    "state": "Saxony"
  },
  {
    "area": "10.15",
    "coords": {
      "lat": "49.72694",
      "lon": "7.77278"
    },
    "district": "Donnersbergkreis",
    "name": "Obermoschel",
    "population": "1046",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "24.83",
    "coords": {
      "lat": "49.84000",
      "lon": "9.14139"
    },
    "district": "Miltenberg",
    "name": "Obernburg a.Main",
    "population": "8712",
    "state": "Bavaria"
  },
  {
    "area": "55.93",
    "coords": {
      "lat": "48.29139",
      "lon": "8.57250"
    },
    "district": "Rottweil",
    "name": "Oberndorf am Neckar",
    "population": "14073",
    "state": "Baden-Württemberg"
  },
  {
    "area": "32.55",
    "coords": {
      "lat": "52.26639",
      "lon": "9.11778"
    },
    "district": "Schaumburg",
    "name": "Obernkirchen",
    "population": "9246",
    "state": "Lower Saxony"
  },
  {
    "area": "8.17",
    "coords": {
      "lat": "48.92556",
      "lon": "9.02806"
    },
    "district": "Ludwigsburg",
    "name": "Oberriexingen",
    "population": "3319",
    "state": "Baden-Württemberg"
  },
  {
    "area": "13.62",
    "coords": {
      "lat": "50.067",
      "lon": "8.833"
    },
    "district": "Offenbach",
    "name": "Obertshausen",
    "population": "24943",
    "state": "Hesse"
  },
  {
    "area": "45.31",
    "coords": {
      "lat": "50.2032194",
      "lon": "8.5769250"
    },
    "district": "Hochtaunuskreis",
    "name": "Oberursel (Taunus)",
    "population": "46248",
    "state": "Hesse"
  },
  {
    "area": "62.41",
    "coords": {
      "lat": "49.450",
      "lon": "12.417"
    },
    "district": "Schwandorf",
    "name": "Oberviechtach",
    "population": "5030",
    "state": "Bavaria"
  },
  {
    "area": "18.11",
    "coords": {
      "lat": "50.11111",
      "lon": "7.72139"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Oberwesel",
    "population": "2813",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "39.98",
    "coords": {
      "lat": "50.41917",
      "lon": "12.97083"
    },
    "district": "Erzgebirgskreis",
    "name": "Oberwiesenthal",
    "population": "2075",
    "state": "Saxony"
  },
  {
    "area": "165.59",
    "coords": {
      "lat": "49.567",
      "lon": "8.967"
    },
    "district": "Odenwaldkreis",
    "name": "Oberzent",
    "population": "10180",
    "state": "Hesse"
  },
  {
    "area": "63.55",
    "coords": {
      "lat": "49.650",
      "lon": "10.067"
    },
    "district": "Würzburg",
    "name": "Ochsenfurt",
    "population": "11319",
    "state": "Bavaria"
  },
  {
    "area": "59.96",
    "coords": {
      "lat": "48.07222",
      "lon": "9.94806"
    },
    "district": "Biberach",
    "name": "Ochsenhausen",
    "population": "8856",
    "state": "Baden-Württemberg"
  },
  {
    "area": "105.54",
    "coords": {
      "lat": "52.20556",
      "lon": "7.19028"
    },
    "district": "Steinfurt",
    "name": "Ochtrup",
    "population": "19636",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "35.31",
    "coords": {
      "lat": "52.86667",
      "lon": "14.05000"
    },
    "district": "Barnim",
    "name": "Oderberg",
    "population": "2166",
    "state": "Brandenburg"
  },
  {
    "area": "249.22",
    "coords": {
      "lat": "52.433",
      "lon": "10.983"
    },
    "district": "Börde",
    "name": "Oebisfelde-Weferlingen",
    "population": "13701",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "77.35",
    "coords": {
      "lat": "50.86167",
      "lon": "13.16722"
    },
    "district": "Mittelsachsen",
    "name": "Oederan",
    "population": "8002",
    "state": "Saxony"
  },
  {
    "area": "102.63",
    "coords": {
      "lat": "51.833",
      "lon": "8.150"
    },
    "district": "Warendorf",
    "name": "Oelde",
    "population": "29326",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "53.67",
    "coords": {
      "lat": "50.41667",
      "lon": "12.16667"
    },
    "district": "Vogtlandkreis",
    "name": "Oelsnitz",
    "population": "10285",
    "state": "Saxony"
  },
  {
    "area": "26.28",
    "coords": {
      "lat": "50.72222",
      "lon": "12.69861"
    },
    "district": "Erzgebirgskreis",
    "name": "Oelsnitz",
    "population": "10957",
    "state": "Saxony"
  },
  {
    "area": "38.8",
    "coords": {
      "lat": "51.64222",
      "lon": "7.25083"
    },
    "district": "Recklinghausen",
    "name": "Oer-Erkenschwick",
    "population": "31442",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "32.69",
    "coords": {
      "lat": "51.96667",
      "lon": "8.66667"
    },
    "district": "Lippe",
    "name": "Oerlinghausen",
    "population": "17286",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "59.53",
    "coords": {
      "lat": "50.000",
      "lon": "8.000"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Oestrich-Winkel",
    "population": "11869",
    "state": "Hesse"
  },
  {
    "area": "34.21",
    "coords": {
      "lat": "48.950",
      "lon": "10.583"
    },
    "district": "Donau-Ries",
    "name": "Oettingen in Bayern",
    "population": "5142",
    "state": "Bavaria"
  },
  {
    "area": "44.90",
    "coords": {
      "lat": "50.10000",
      "lon": "8.80000"
    },
    "district": "Urban district",
    "name": "Offenbach am Main",
    "population": "128744",
    "state": "Hesse"
  },
  {
    "area": "78.38",
    "coords": {
      "lat": "48.467",
      "lon": "7.933"
    },
    "district": "Ortenaukreis",
    "name": "Offenburg",
    "population": "59646",
    "state": "Baden-Württemberg"
  },
  {
    "area": "113.41",
    "coords": {
      "lat": "50.82806",
      "lon": "10.73278"
    },
    "district": "Gotha",
    "name": "Ohrdruf, Thuringia",
    "population": "9784",
    "state": "Thuringia"
  },
  {
    "area": "125.36",
    "coords": {
      "lat": "50.667",
      "lon": "13.333"
    },
    "district": "Erzgebirgskreis",
    "name": "Olbernhau",
    "population": "10991",
    "state": "Saxony"
  },
  {
    "area": "29.91",
    "coords": {
      "lat": "48.200",
      "lon": "11.317"
    },
    "district": "Fürstenfeldbruck",
    "name": "Olching",
    "population": "27741",
    "state": "Bavaria"
  },
  {
    "area": "39.67",
    "coords": {
      "lat": "54.30000",
      "lon": "10.88333"
    },
    "district": "Ostholstein",
    "name": "Oldenburg in Holstein",
    "population": "9833",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "102.96",
    "coords": {
      "lat": "53.14389",
      "lon": "8.21389"
    },
    "district": "Urban district",
    "name": "Oldenburg",
    "population": "168210",
    "state": "Lower Saxony"
  },
  {
    "area": "52.43",
    "coords": {
      "lat": "51.717",
      "lon": "7.383"
    },
    "district": "Coesfeld",
    "name": "Olfen",
    "population": "12846",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "85.6",
    "coords": {
      "lat": "51.017",
      "lon": "7.833"
    },
    "district": "Olpe",
    "name": "Olpe",
    "population": "24688",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "117.97",
    "coords": {
      "lat": "51.350",
      "lon": "8.483"
    },
    "district": "Hochsauerland",
    "name": "Olsberg",
    "population": "21556",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "73.04",
    "coords": {
      "lat": "48.47361",
      "lon": "8.15944"
    },
    "district": "Ortenaukreis",
    "name": "Oppenau",
    "population": "4718",
    "state": "Baden-Württemberg"
  },
  {
    "area": "7.09",
    "coords": {
      "lat": "49.85556",
      "lon": "8.36028"
    },
    "district": "Mainz-Bingen",
    "name": "Oppenheim",
    "population": "7562",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "115.16",
    "coords": {
      "lat": "51.79917",
      "lon": "12.40694"
    },
    "district": "Wittenberg",
    "name": "Oranienbaum-Wörlitz",
    "population": "8344",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "162.37",
    "coords": {
      "lat": "52.75444",
      "lon": "13.23694"
    },
    "district": "Oberhavel",
    "name": "Oranienburg",
    "population": "44512",
    "state": "Brandenburg"
  },
  {
    "area": "7.58",
    "coords": {
      "lat": "50.77528",
      "lon": "11.53139"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Orlamünde",
    "population": "1113",
    "state": "Thuringia"
  },
  {
    "area": "15.16",
    "coords": {
      "lat": "49.17778",
      "lon": "10.65556"
    },
    "district": "Ansbach",
    "name": "Ornbau",
    "population": "1626",
    "state": "Bavaria"
  },
  {
    "area": "54.70",
    "coords": {
      "lat": "50.35583",
      "lon": "9.05528"
    },
    "district": "Wetteraukreis",
    "name": "Ortenberg",
    "population": "9001",
    "state": "Hesse"
  },
  {
    "area": "7.34",
    "coords": {
      "lat": "51.36667",
      "lon": "13.78306"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Ortrand",
    "population": "2067",
    "state": "Brandenburg"
  },
  {
    "area": "55.31",
    "coords": {
      "lat": "51.30028",
      "lon": "13.10722"
    },
    "district": "Nordsachsen",
    "name": "Oschatz",
    "population": "14349",
    "state": "Saxony"
  },
  {
    "area": "188.92",
    "coords": {
      "lat": "52.017",
      "lon": "11.250"
    },
    "district": "Börde",
    "name": "Oschersleben",
    "population": "19630",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "119.80",
    "coords": {
      "lat": "52.283",
      "lon": "8.050"
    },
    "district": "Urban district",
    "name": "Osnabrück",
    "population": "164748",
    "state": "Lower Saxony"
  },
  {
    "area": "229.74",
    "coords": {
      "lat": "52.783",
      "lon": "11.767"
    },
    "district": "Stendal",
    "name": "Osterburg",
    "population": "9782",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "47.32",
    "coords": {
      "lat": "49.43083",
      "lon": "9.42611"
    },
    "district": "Neckar-Odenwald-Kreis",
    "name": "Osterburken",
    "population": "6507",
    "state": "Baden-Württemberg"
  },
  {
    "area": "27.61",
    "coords": {
      "lat": "51.07667",
      "lon": "11.93306"
    },
    "district": "Burgenlandkreis",
    "name": "Osterfeld",
    "population": "2427",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "111.19",
    "coords": {
      "lat": "48.700",
      "lon": "13.017"
    },
    "district": "Deggendorf",
    "name": "Osterhofen",
    "population": "11798",
    "state": "Bavaria"
  },
  {
    "area": "147",
    "coords": {
      "lat": "53.217",
      "lon": "8.800"
    },
    "district": "Osterholz",
    "name": "Osterholz-Scharmbeck",
    "population": "30300",
    "state": "Lower Saxony"
  },
  {
    "area": "102.46",
    "coords": {
      "lat": "51.72861",
      "lon": "10.25222"
    },
    "district": "Göttingen",
    "name": "Osterode am Harz",
    "population": "21731",
    "state": "Lower Saxony"
  },
  {
    "area": "212.67",
    "coords": {
      "lat": "51.967",
      "lon": "10.717"
    },
    "district": "Harz",
    "name": "Osterwieck",
    "population": "11103",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "22.81",
    "coords": {
      "lat": "48.733",
      "lon": "9.250"
    },
    "district": "Esslingen",
    "name": "Ostfildern",
    "population": "39321",
    "state": "Baden-Württemberg"
  },
  {
    "area": "40.73",
    "coords": {
      "lat": "50.467",
      "lon": "10.217"
    },
    "district": "Rhön-Grabfeld",
    "name": "Ostheim",
    "population": "3319",
    "state": "Bavaria"
  },
  {
    "area": "27.11",
    "coords": {
      "lat": "49.70778",
      "lon": "8.32889"
    },
    "district": "Alzey-Worms",
    "name": "Osthofen",
    "population": "9402",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "23.39",
    "coords": {
      "lat": "51.01472",
      "lon": "14.93222"
    },
    "district": "Görlitz",
    "name": "Ostritz",
    "population": "2257",
    "state": "Saxony"
  },
  {
    "area": "32.09",
    "coords": {
      "lat": "49.50444",
      "lon": "7.77111"
    },
    "district": "Kaiserslautern",
    "name": "Otterberg",
    "population": "5389",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "33.54",
    "coords": {
      "lat": "53.800",
      "lon": "8.900"
    },
    "district": "Cuxhaven",
    "name": "Otterndorf",
    "population": "7238",
    "state": "Lower Saxony"
  },
  {
    "area": "45.51",
    "coords": {
      "lat": "49.367",
      "lon": "7.167"
    },
    "district": "Neunkirchen",
    "name": "Ottweiler",
    "population": "14358",
    "state": "Saarland"
  },
  {
    "area": "68.8",
    "coords": {
      "lat": "50.950",
      "lon": "7.300"
    },
    "district": "Rheinisch-Bergischer Kreis",
    "name": "Overath",
    "population": "27040",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "9.70",
    "coords": {
      "lat": "48.58833",
      "lon": "9.45139"
    },
    "district": "Esslingen",
    "name": "Owen",
    "population": "3392",
    "state": "Baden-Württemberg"
  },
  {
    "area": "179.38",
    "coords": {
      "lat": "51.71806",
      "lon": "8.75417"
    },
    "district": "Paderborn",
    "name": "Paderborn",
    "population": "150580",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "118.36",
    "coords": {
      "lat": "53.067",
      "lon": "7.400"
    },
    "district": "Emsland",
    "name": "Papenburg",
    "population": "37579",
    "state": "Lower Saxony"
  },
  {
    "area": "64.32",
    "coords": {
      "lat": "48.93472",
      "lon": "10.97444"
    },
    "district": "Weißenburg-Gunzenhausen",
    "name": "Pappenheim",
    "population": "4023",
    "state": "Bavaria"
  },
  {
    "area": "124.49",
    "coords": {
      "lat": "53.417",
      "lon": "11.833"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Parchim",
    "population": "18037",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "57.00",
    "coords": {
      "lat": "49.150",
      "lon": "11.717"
    },
    "district": "Neumarkt in der Oberpfalz",
    "name": "Parsberg",
    "population": "7213",
    "state": "Bavaria"
  },
  {
    "area": "54.99",
    "coords": {
      "lat": "53.500",
      "lon": "14.000"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Pasewalk",
    "population": "10213",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "69.58",
    "coords": {
      "lat": "48.56667",
      "lon": "13.46667"
    },
    "district": "Urban district",
    "name": "Passau",
    "population": "52469",
    "state": "Bavaria"
  },
  {
    "area": "67",
    "coords": {
      "lat": "52.267",
      "lon": "9.767"
    },
    "district": "Hanover",
    "name": "Pattensen",
    "population": "14636",
    "state": "Lower Saxony"
  },
  {
    "area": "64.13",
    "coords": {
      "lat": "50.567",
      "lon": "11.967"
    },
    "district": "Vogtlandkreis",
    "name": "Pausa-Mühltroff",
    "population": "4945",
    "state": "Saxony"
  },
  {
    "area": "48.61",
    "coords": {
      "lat": "51.16667",
      "lon": "12.25000"
    },
    "district": "Leipzig",
    "name": "Pegau",
    "population": "6288",
    "state": "Saxony"
  },
  {
    "area": "100.03",
    "coords": {
      "lat": "49.75639",
      "lon": "11.54500"
    },
    "district": "Bayreuth",
    "name": "Pegnitz",
    "population": "13244",
    "state": "Bavaria"
  },
  {
    "area": "119.51",
    "coords": {
      "lat": "52.32028",
      "lon": "10.23361"
    },
    "district": "Peine",
    "name": "Peine",
    "population": "49952",
    "state": "Lower Saxony"
  },
  {
    "area": "13.38",
    "coords": {
      "lat": "51.86667",
      "lon": "14.41667"
    },
    "district": "Spree-Neiße",
    "name": "Peitz/Picnjo",
    "population": "4383",
    "state": "Brandenburg"
  },
  {
    "area": "63.31",
    "coords": {
      "lat": "50.93361",
      "lon": "12.70583"
    },
    "district": "Mittelsachsen",
    "name": "Penig",
    "population": "8780",
    "state": "Saxony"
  },
  {
    "area": "78.64",
    "coords": {
      "lat": "53.283",
      "lon": "14.250"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Penkun",
    "population": "1785",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "25.73",
    "coords": {
      "lat": "47.750",
      "lon": "11.383"
    },
    "district": "Weilheim-Schongau",
    "name": "Penzberg",
    "population": "16586",
    "state": "Bavaria"
  },
  {
    "area": "115.47",
    "coords": {
      "lat": "53.50500",
      "lon": "13.08306"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Penzlin",
    "population": "4159",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "137.82",
    "coords": {
      "lat": "53.06667",
      "lon": "11.86667"
    },
    "district": "Prignitz",
    "name": "Perleberg",
    "population": "12141",
    "state": "Brandenburg"
  },
  {
    "area": "212",
    "coords": {
      "lat": "52.383",
      "lon": "8.967"
    },
    "district": "Minden-Lübbecke",
    "name": "Petershagen",
    "population": "25168",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "92.39",
    "coords": {
      "lat": "48.533",
      "lon": "11.517"
    },
    "district": "Pfaffenhofen a.d.Ilm",
    "name": "Pfaffenhofen a.d.Ilm",
    "population": "25917",
    "state": "Bavaria"
  },
  {
    "area": "52.33",
    "coords": {
      "lat": "48.417",
      "lon": "12.917"
    },
    "district": "Rottal-Inn",
    "name": "Pfarrkirchen",
    "population": "12677",
    "state": "Bavaria"
  },
  {
    "area": "98.03",
    "coords": {
      "lat": "48.89500",
      "lon": "8.70500"
    },
    "district": "Stadtkreis",
    "name": "Pforzheim",
    "population": "125542",
    "state": "Baden-Württemberg"
  },
  {
    "area": "51.43",
    "coords": {
      "lat": "49.500",
      "lon": "12.183"
    },
    "district": "Schwandorf",
    "name": "Pfreimd",
    "population": "5349",
    "state": "Bavaria"
  },
  {
    "area": "90.56",
    "coords": {
      "lat": "47.92417",
      "lon": "9.25667"
    },
    "district": "Sigmaringen",
    "name": "Pfullendorf",
    "population": "13437",
    "state": "Baden-Württemberg"
  },
  {
    "area": "30.12",
    "coords": {
      "lat": "48.46556",
      "lon": "9.22611"
    },
    "district": "Reutlingen",
    "name": "Pfullingen",
    "population": "18654",
    "state": "Baden-Württemberg"
  },
  {
    "area": "42.53",
    "coords": {
      "lat": "49.80556",
      "lon": "8.60444"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Pfungstadt",
    "population": "25151",
    "state": "Hesse"
  },
  {
    "area": "50.56",
    "coords": {
      "lat": "49.23333",
      "lon": "8.45000"
    },
    "district": "Karlsruhe",
    "name": "Philippsburg",
    "population": "13615",
    "state": "Baden-Württemberg"
  },
  {
    "area": "21.54",
    "coords": {
      "lat": "53.633",
      "lon": "9.800"
    },
    "district": "Pinneberg",
    "name": "Pinneberg",
    "population": "43280",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "61.37",
    "coords": {
      "lat": "49.200",
      "lon": "7.600"
    },
    "district": "Urban district",
    "name": "Pirmasens",
    "population": "40403",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "53.02",
    "coords": {
      "lat": "50.96222",
      "lon": "13.94028"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Pirna",
    "population": "38320",
    "state": "Saxony"
  },
  {
    "area": "35.90",
    "coords": {
      "lat": "48.767",
      "lon": "12.867"
    },
    "district": "Deggendorf",
    "name": "Plattling",
    "population": "13043",
    "state": "Bavaria"
  },
  {
    "area": "115.99",
    "coords": {
      "lat": "53.45806",
      "lon": "12.26250"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Plau am See",
    "population": "6037",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "102.11",
    "coords": {
      "lat": "50.483",
      "lon": "12.117"
    },
    "district": "Vogtlandkreis",
    "name": "Plauen",
    "population": "64931",
    "state": "Saxony"
  },
  {
    "area": "22.69",
    "coords": {
      "lat": "50.77944",
      "lon": "10.89889"
    },
    "district": "Ilm-Kreis",
    "name": "Plaue",
    "population": "1961",
    "state": "Thuringia"
  },
  {
    "area": "96.29",
    "coords": {
      "lat": "51.217",
      "lon": "7.883"
    },
    "district": "Märkischer Kreis",
    "name": "Plettenberg",
    "population": "25318",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "36.10",
    "coords": {
      "lat": "49.650",
      "lon": "12.417"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Pleystein",
    "population": "2367",
    "state": "Bavaria"
  },
  {
    "area": "10.65",
    "coords": {
      "lat": "48.71167",
      "lon": "9.41639"
    },
    "district": "Esslingen",
    "name": "Plochingen",
    "population": "14433",
    "state": "Baden-Württemberg"
  },
  {
    "area": "36.73",
    "coords": {
      "lat": "54.16222",
      "lon": "10.42139"
    },
    "district": "Plön",
    "name": "Plön",
    "population": "8914",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "83.57",
    "coords": {
      "lat": "50.717",
      "lon": "13.183"
    },
    "district": "Erzgebirgskreis",
    "name": "Pockau-Lengefeld",
    "population": "7634",
    "state": "Saxony"
  },
  {
    "area": "68.82",
    "coords": {
      "lat": "48.400",
      "lon": "13.317"
    },
    "district": "Passau",
    "name": "Pocking",
    "population": "15967",
    "state": "Bavaria"
  },
  {
    "area": "38",
    "coords": {
      "lat": "50.517",
      "lon": "8.700"
    },
    "district": "Gießen",
    "name": "Pohlheim",
    "population": "18143",
    "state": "Hesse"
  },
  {
    "area": "28.70",
    "coords": {
      "lat": "50.30111",
      "lon": "7.31667"
    },
    "district": "Mayen-Koblenz",
    "name": "Polch",
    "population": "6821",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "105",
    "coords": {
      "lat": "52.21667",
      "lon": "8.93333"
    },
    "district": "Minden-Lübbecke",
    "name": "Porta Westfalica",
    "population": "35671",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "187.28",
    "coords": {
      "lat": "52.400",
      "lon": "13.067"
    },
    "district": "Urban district",
    "name": "Potsdam",
    "population": "178089",
    "state": "Brandenburg"
  },
  {
    "area": "73.24",
    "coords": {
      "lat": "49.77222",
      "lon": "11.41139"
    },
    "district": "Bayreuth",
    "name": "Pottenstein",
    "population": "5226",
    "state": "Bavaria"
  },
  {
    "area": "14.4",
    "coords": {
      "lat": "54.23667",
      "lon": "10.28222"
    },
    "district": "Plön",
    "name": "Preetz",
    "population": "15958",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "45.42",
    "coords": {
      "lat": "52.53306",
      "lon": "12.33306"
    },
    "district": "Havelland",
    "name": "Premnitz",
    "population": "8453",
    "state": "Brandenburg"
  },
  {
    "area": "142.18",
    "coords": {
      "lat": "53.317",
      "lon": "13.867"
    },
    "district": "Uckermark",
    "name": "Prenzlau",
    "population": "19024",
    "state": "Brandenburg"
  },
  {
    "area": "66.31",
    "coords": {
      "lat": "49.767",
      "lon": "11.917"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Pressath",
    "population": "4349",
    "state": "Bavaria"
  },
  {
    "area": "68.78",
    "coords": {
      "lat": "52.28333",
      "lon": "8.50000"
    },
    "district": "Minden-Lübbecke",
    "name": "Preußisch Oldendorf",
    "population": "12289",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "48.87",
    "coords": {
      "lat": "49.817",
      "lon": "10.333"
    },
    "district": "Kitzingen",
    "name": "Prichsenstadt",
    "population": "3050",
    "state": "Bavaria"
  },
  {
    "area": "165.57",
    "coords": {
      "lat": "53.14972",
      "lon": "12.18306"
    },
    "district": "Prignitz",
    "name": "Pritzwalk",
    "population": "11924",
    "state": "Brandenburg"
  },
  {
    "area": "22.86",
    "coords": {
      "lat": "50.20806",
      "lon": "6.42444"
    },
    "district": "Eifelkreis Bitburg-Prüm",
    "name": "Prüm",
    "population": "5438",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "12.23",
    "coords": {
      "lat": "48.150",
      "lon": "11.350"
    },
    "district": "Fürstenfeldbruck",
    "name": "Puchheim",
    "population": "21531",
    "state": "Bavaria"
  },
  {
    "area": "72.14",
    "coords": {
      "lat": "51.000",
      "lon": "6.800"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Pulheim",
    "population": "54071",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "26.72",
    "coords": {
      "lat": "51.18167",
      "lon": "14.01306"
    },
    "district": "Bautzen",
    "name": "Pulsnitz",
    "population": "7467",
    "state": "Saxony"
  },
  {
    "area": "66.60",
    "coords": {
      "lat": "54.333",
      "lon": "13.483"
    },
    "district": "Vorpommern-Rügen",
    "name": "Putbus",
    "population": "4364",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "118.49",
    "coords": {
      "lat": "53.250",
      "lon": "12.050"
    },
    "district": "Prignitz",
    "name": "Putlitz",
    "population": "2681",
    "state": "Brandenburg"
  },
  {
    "area": "24.45",
    "coords": {
      "lat": "50.70000",
      "lon": "11.60000"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Pößneck",
    "population": "11924",
    "state": "Thuringia"
  },
  {
    "area": "23.94",
    "coords": {
      "lat": "49.283",
      "lon": "6.883"
    },
    "district": "Saarbrücken",
    "name": "Püttlingen",
    "population": "18510",
    "state": "Saarland"
  },
  {
    "area": "17.95",
    "coords": {
      "lat": "52.67722",
      "lon": "7.95750"
    },
    "district": "Osnabrück",
    "name": "Quakenbrück",
    "population": "13500",
    "state": "Lower Saxony"
  },
  {
    "area": "120.42",
    "coords": {
      "lat": "51.79167",
      "lon": "11.14722"
    },
    "district": "Harz",
    "name": "Quedlinburg",
    "population": "23989",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "155.23",
    "coords": {
      "lat": "51.383",
      "lon": "11.600"
    },
    "district": "Saalekreis",
    "name": "Querfurt",
    "population": "10593",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "43.16",
    "coords": {
      "lat": "53.73333",
      "lon": "9.89722"
    },
    "district": "Pinneberg",
    "name": "Quickborn",
    "population": "21296",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "30.72",
    "coords": {
      "lat": "50.967",
      "lon": "13.633"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Rabenau",
    "population": "4427",
    "state": "Saxony"
  },
  {
    "area": "30.00",
    "coords": {
      "lat": "51.117",
      "lon": "13.917"
    },
    "district": "Bautzen",
    "name": "Radeberg",
    "population": "18463",
    "state": "Saxony"
  },
  {
    "area": "26.06",
    "coords": {
      "lat": "51.100",
      "lon": "13.650"
    },
    "district": "Meißen",
    "name": "Radebeul",
    "population": "34008",
    "state": "Saxony"
  },
  {
    "area": "54.00",
    "coords": {
      "lat": "51.21250",
      "lon": "13.72556"
    },
    "district": "Meißen",
    "name": "Radeburg",
    "population": "7325",
    "state": "Saxony"
  },
  {
    "area": "53.77",
    "coords": {
      "lat": "51.200",
      "lon": "7.350"
    },
    "district": "Oberbergischer Kreis",
    "name": "Radevormwald",
    "population": "22107",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "58.58",
    "coords": {
      "lat": "47.73333",
      "lon": "8.96667"
    },
    "district": "Konstanz",
    "name": "Radolfzell",
    "population": "31203",
    "state": "Baden-Württemberg"
  },
  {
    "area": "97.09",
    "coords": {
      "lat": "51.667",
      "lon": "12.067"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Raguhn-Jeßnitz",
    "population": "9033",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "137.60",
    "coords": {
      "lat": "52.417",
      "lon": "8.617"
    },
    "district": "Minden-Lübbecke",
    "name": "Rahden",
    "population": "15441",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "77.13",
    "coords": {
      "lat": "48.683",
      "lon": "10.917"
    },
    "district": "Donau-Ries",
    "name": "Rain",
    "population": "8836",
    "state": "Bavaria"
  },
  {
    "area": "43.03",
    "coords": {
      "lat": "49.44611",
      "lon": "7.55472"
    },
    "district": "Kaiserslautern",
    "name": "Ramstein-Miesenbach",
    "population": "7876",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "10.55",
    "coords": {
      "lat": "50.66389",
      "lon": "11.56806"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Ranis",
    "population": "1708",
    "state": "Thuringia"
  },
  {
    "area": "12.14",
    "coords": {
      "lat": "50.46611",
      "lon": "7.72528"
    },
    "district": "Westerwaldkreis",
    "name": "Ransbach-Baumbach",
    "population": "7715",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "59.02",
    "coords": {
      "lat": "48.850",
      "lon": "8.200"
    },
    "district": "Rastatt",
    "name": "Rastatt",
    "population": "49783",
    "state": "Baden-Württemberg"
  },
  {
    "area": "35.42",
    "coords": {
      "lat": "51.17611",
      "lon": "11.41917"
    },
    "district": "Sömmerda",
    "name": "Rastenberg",
    "population": "2486",
    "state": "Thuringia"
  },
  {
    "area": "105.68",
    "coords": {
      "lat": "52.600",
      "lon": "12.333"
    },
    "district": "Havelland",
    "name": "Rathenow",
    "population": "24309",
    "state": "Brandenburg"
  },
  {
    "area": "88.72",
    "coords": {
      "lat": "51.30000",
      "lon": "6.85000"
    },
    "district": "Mettmann",
    "name": "Ratingen",
    "population": "87297",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "30.29",
    "coords": {
      "lat": "53.700",
      "lon": "10.750"
    },
    "district": "Herzogtum Lauenburg",
    "name": "Ratzeburg",
    "population": "14652",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "11.12",
    "coords": {
      "lat": "49.26778",
      "lon": "8.70361"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Rauenberg",
    "population": "8693",
    "state": "Baden-Württemberg"
  },
  {
    "area": "13.01",
    "coords": {
      "lat": "50.017",
      "lon": "8.450"
    },
    "district": "Groß-Gerau",
    "name": "Raunheim",
    "population": "16284",
    "state": "Hesse"
  },
  {
    "area": "67.33",
    "coords": {
      "lat": "50.86667",
      "lon": "8.91667"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Rauschenberg",
    "population": "4395",
    "state": "Hesse"
  },
  {
    "area": "92.04",
    "coords": {
      "lat": "47.78306",
      "lon": "9.61139"
    },
    "district": "Ravensburg",
    "name": "Ravensburg",
    "population": "50623",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.97",
    "coords": {
      "lat": "49.40111",
      "lon": "9.50778"
    },
    "district": "Neckar-Odenwald-Kreis",
    "name": "Ravenstein",
    "population": "2868",
    "state": "Baden-Württemberg"
  },
  {
    "area": "66.4",
    "coords": {
      "lat": "51.58500",
      "lon": "7.16194"
    },
    "district": "Recklinghausen",
    "name": "Recklinghausen",
    "population": "112267",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "109.66",
    "coords": {
      "lat": "51.76667",
      "lon": "6.40000"
    },
    "district": "Kleve",
    "name": "Rees",
    "population": "20972",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "80.76",
    "coords": {
      "lat": "49.017",
      "lon": "12.083"
    },
    "district": "Urban district",
    "name": "Regensburg",
    "population": "152610",
    "state": "Bavaria"
  },
  {
    "area": "65.15",
    "coords": {
      "lat": "48.967",
      "lon": "13.133"
    },
    "district": "Regen",
    "name": "Regen",
    "population": "11001",
    "state": "Bavaria"
  },
  {
    "area": "26.35",
    "coords": {
      "lat": "51.083",
      "lon": "12.450"
    },
    "district": "Leipzig",
    "name": "Regis-Breitingen",
    "population": "3888",
    "state": "Saxony"
  },
  {
    "area": "80.34",
    "coords": {
      "lat": "50.250",
      "lon": "12.017"
    },
    "district": "Hof",
    "name": "Rehau",
    "population": "9424",
    "state": "Bavaria"
  },
  {
    "area": "99.99",
    "coords": {
      "lat": "52.45083",
      "lon": "9.20778"
    },
    "district": "Nienburg",
    "name": "Rehburg-Loccum",
    "population": "10110",
    "state": "Lower Saxony"
  },
  {
    "area": "44.29",
    "coords": {
      "lat": "53.767",
      "lon": "11.033"
    },
    "district": "Nordwestmecklenburg",
    "name": "Rehna",
    "population": "3510",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "27.60",
    "coords": {
      "lat": "50.35694",
      "lon": "8.87250"
    },
    "district": "Wetteraukreis",
    "name": "Reichelsheim",
    "population": "6769",
    "state": "Hesse"
  },
  {
    "area": "34.46",
    "coords": {
      "lat": "50.617",
      "lon": "12.300"
    },
    "district": "Vogtlandkreis",
    "name": "Reichenbach im Vogtland",
    "population": "20625",
    "state": "Saxony"
  },
  {
    "area": "62.59",
    "coords": {
      "lat": "51.14167",
      "lon": "14.80000"
    },
    "district": "Görlitz",
    "name": "Reichenbach",
    "population": "4957",
    "state": "Saxony"
  },
  {
    "area": "31.23",
    "coords": {
      "lat": "53.50889",
      "lon": "10.24833"
    },
    "district": "Stormarn",
    "name": "Reinbek",
    "population": "27649",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "17.36",
    "coords": {
      "lat": "53.83333",
      "lon": "10.48333"
    },
    "district": "Stormarn",
    "name": "Reinfeld",
    "population": "9058",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "27.70",
    "coords": {
      "lat": "49.82694",
      "lon": "8.83083"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Reinheim",
    "population": "16346",
    "state": "Hesse"
  },
  {
    "area": "33.16",
    "coords": {
      "lat": "50.57861",
      "lon": "7.23056"
    },
    "district": "Ahrweiler",
    "name": "Remagen",
    "population": "17032",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "74.6",
    "coords": {
      "lat": "51.18333",
      "lon": "7.20000"
    },
    "district": "Urban districts of Germany",
    "name": "Remscheid",
    "population": "110994",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "22.82",
    "coords": {
      "lat": "48.86889",
      "lon": "9.27639"
    },
    "district": "Ludwigsburg",
    "name": "Remseck",
    "population": "26467",
    "state": "Baden-Württemberg"
  },
  {
    "area": "32.08",
    "coords": {
      "lat": "48.58583",
      "lon": "8.01056"
    },
    "district": "Ortenaukreis",
    "name": "Renchen",
    "population": "7361",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.72",
    "coords": {
      "lat": "54.30000",
      "lon": "9.66667"
    },
    "district": "Rendsburg-Eckernförde",
    "name": "Rendsburg",
    "population": "28470",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "18.14",
    "coords": {
      "lat": "50.60972",
      "lon": "8.06889"
    },
    "district": "Westerwaldkreis",
    "name": "Rennerod",
    "population": "4354",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "31.13",
    "coords": {
      "lat": "48.76611",
      "lon": "8.93472"
    },
    "district": "Böblingen",
    "name": "Renningen",
    "population": "18206",
    "state": "Baden-Württemberg"
  },
  {
    "area": "33.45",
    "coords": {
      "lat": "54.100",
      "lon": "11.617"
    },
    "district": "Rostock",
    "name": "Rerik",
    "population": "2142",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "33.76",
    "coords": {
      "lat": "52.78472",
      "lon": "9.37806"
    },
    "district": "Heidekreis",
    "name": "Rethem",
    "population": "2337",
    "state": "Lower Saxony"
  },
  {
    "area": "87.06",
    "coords": {
      "lat": "48.483",
      "lon": "9.217"
    },
    "district": "Reutlingen",
    "name": "Reutlingen",
    "population": "115966",
    "state": "Baden-Württemberg"
  },
  {
    "area": "86.66",
    "coords": {
      "lat": "51.84167",
      "lon": "8.30000"
    },
    "district": "Gütersloh",
    "name": "Rheda-Wiedenbrück",
    "population": "48505",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "78.65",
    "coords": {
      "lat": "51.83333",
      "lon": "6.70056"
    },
    "district": "Borken",
    "name": "Rhede",
    "population": "19328",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "73.51",
    "coords": {
      "lat": "48.66778",
      "lon": "7.93472"
    },
    "district": "Ortenaukreis",
    "name": "Rheinau",
    "population": "11395",
    "state": "Baden-Württemberg"
  },
  {
    "area": "69.74",
    "coords": {
      "lat": "50.633",
      "lon": "6.950"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Rheinbach",
    "population": "27063",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "75.15",
    "coords": {
      "lat": "51.54667",
      "lon": "6.60056"
    },
    "district": "Wesel",
    "name": "Rheinberg",
    "population": "31097",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "16.33",
    "coords": {
      "lat": "50.000",
      "lon": "7.667"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Rheinböllen",
    "population": "4123",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "145",
    "coords": {
      "lat": "52.283",
      "lon": "7.433"
    },
    "district": "Steinfurt",
    "name": "Rheine",
    "population": "76107",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "62.84",
    "coords": {
      "lat": "47.56111",
      "lon": "7.79167"
    },
    "district": "Lörrach",
    "name": "Rheinfelden",
    "population": "33074",
    "state": "Baden-Württemberg"
  },
  {
    "area": "324.83",
    "coords": {
      "lat": "53.09833",
      "lon": "12.89583"
    },
    "district": "Ostprignitz-Ruppin",
    "name": "Rheinsberg",
    "population": "8015",
    "state": "Brandenburg"
  },
  {
    "area": "32.31",
    "coords": {
      "lat": "48.96056",
      "lon": "8.28972"
    },
    "district": "Karlsruhe",
    "name": "Rheinstetten",
    "population": "20340",
    "state": "Baden-Württemberg"
  },
  {
    "area": "16.30",
    "coords": {
      "lat": "50.2804889",
      "lon": "7.618111"
    },
    "district": "Mayen-Koblenz",
    "name": "Rhens",
    "population": "2942",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "31.51",
    "coords": {
      "lat": "52.75000",
      "lon": "12.33306"
    },
    "district": "Havelland",
    "name": "Rhinow",
    "population": "1587",
    "state": "Brandenburg"
  },
  {
    "area": "122.20",
    "coords": {
      "lat": "54.25000",
      "lon": "12.46667"
    },
    "district": "Vorpommern-Rügen",
    "name": "Ribnitz-Damgarten",
    "population": "15167",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "15.63",
    "coords": {
      "lat": "54.200",
      "lon": "12.867"
    },
    "district": "Vorpommern-Rügen",
    "name": "Richtenberg",
    "population": "1288",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "100.45",
    "coords": {
      "lat": "48.967",
      "lon": "11.683"
    },
    "district": "Kelheim",
    "name": "Riedenburg",
    "population": "6030",
    "state": "Bavaria"
  },
  {
    "area": "64.97",
    "coords": {
      "lat": "48.15528",
      "lon": "9.47278"
    },
    "district": "Biberach",
    "name": "Riedlingen",
    "population": "10528",
    "state": "Baden-Württemberg"
  },
  {
    "area": "73.76",
    "coords": {
      "lat": "49.83722",
      "lon": "8.50444"
    },
    "district": "Groß-Gerau",
    "name": "Riedstadt",
    "population": "23785",
    "state": "Hesse"
  },
  {
    "area": "26.20",
    "coords": {
      "lat": "50.100",
      "lon": "9.633"
    },
    "district": "Main-Spessart",
    "name": "Rieneck",
    "population": "1959",
    "state": "Bavaria"
  },
  {
    "area": "58.91",
    "coords": {
      "lat": "51.30806",
      "lon": "13.29389"
    },
    "district": "Meißen",
    "name": "Riesa",
    "population": "30054",
    "state": "Saxony"
  },
  {
    "area": "110.37",
    "coords": {
      "lat": "51.800",
      "lon": "8.433"
    },
    "district": "Gütersloh",
    "name": "Rietberg",
    "population": "29466",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "109",
    "coords": {
      "lat": "52.19056",
      "lon": "9.08139"
    },
    "district": "Schaumburg",
    "name": "Rinteln",
    "population": "25484",
    "state": "Lower Saxony"
  },
  {
    "area": "23.71",
    "coords": {
      "lat": "51.04806",
      "lon": "12.79861"
    },
    "district": "Mittelsachsen",
    "name": "Rochlitz",
    "population": "5711",
    "state": "Saxony"
  },
  {
    "area": "36.83",
    "coords": {
      "lat": "49.6285861",
      "lon": "7.8205111"
    },
    "district": "Donnersbergkreis",
    "name": "Rockenhausen",
    "population": "5322",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "15.69",
    "coords": {
      "lat": "49.233",
      "lon": "7.650"
    },
    "district": "Südwestpfalz",
    "name": "Rodalben",
    "population": "6749",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "15.6",
    "coords": {
      "lat": "52.317",
      "lon": "9.350"
    },
    "district": "Schaumburg",
    "name": "Rodenberg",
    "population": "6505",
    "state": "Lower Saxony"
  },
  {
    "area": "26.88",
    "coords": {
      "lat": "50.517",
      "lon": "12.417"
    },
    "district": "Vogtlandkreis",
    "name": "Rodewisch",
    "population": "6359",
    "state": "Saxony"
  },
  {
    "area": "65.04",
    "coords": {
      "lat": "50.017",
      "lon": "8.883"
    },
    "district": "Offenbach",
    "name": "Rodgau",
    "population": "45202",
    "state": "Hesse"
  },
  {
    "area": "113.80",
    "coords": {
      "lat": "49.200",
      "lon": "12.517"
    },
    "district": "Cham",
    "name": "Roding",
    "population": "12081",
    "state": "Bavaria"
  },
  {
    "area": "54.43",
    "coords": {
      "lat": "50.717",
      "lon": "9.217"
    },
    "district": "Vogelsbergkreis",
    "name": "Romrod",
    "population": "2673",
    "state": "Hesse"
  },
  {
    "area": "19.18",
    "coords": {
      "lat": "50.86361",
      "lon": "12.18083"
    },
    "district": "Greiz",
    "name": "Ronneburg",
    "population": "5026",
    "state": "Thuringia"
  },
  {
    "area": "37.78",
    "coords": {
      "lat": "52.31944",
      "lon": "9.65556"
    },
    "district": "Hanover",
    "name": "Ronnenberg",
    "population": "24347",
    "state": "Lower Saxony"
  },
  {
    "area": "45.33",
    "coords": {
      "lat": "50.29861",
      "lon": "8.70056"
    },
    "district": "Wetteraukreis",
    "name": "Rosbach vor der Höhe",
    "population": "12307",
    "state": "Hesse"
  },
  {
    "area": "51.11",
    "coords": {
      "lat": "48.28639",
      "lon": "8.72417"
    },
    "district": "Zollernalbkreis",
    "name": "Rosenfeld",
    "population": "6347",
    "state": "Baden-Württemberg"
  },
  {
    "area": "37.22",
    "coords": {
      "lat": "47.85000",
      "lon": "12.13333"
    },
    "district": "urban district",
    "name": "Rosenheim",
    "population": "63324",
    "state": "Bavaria"
  },
  {
    "area": "51.54",
    "coords": {
      "lat": "50.967",
      "lon": "8.867"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Rosenthal",
    "population": "2177",
    "state": "Hesse"
  },
  {
    "area": "181.44",
    "coords": {
      "lat": "54.08333",
      "lon": "12.13333"
    },
    "district": "Urban district",
    "name": "Rostock",
    "population": "208886",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "79.84",
    "coords": {
      "lat": "50.99611",
      "lon": "9.72778"
    },
    "district": "Hersfeld-Rotenburg",
    "name": "Rotenburg a. d.  Fulda",
    "population": "14676",
    "state": "Hesse"
  },
  {
    "area": "98.81",
    "coords": {
      "lat": "53.10694",
      "lon": "9.39694"
    },
    "district": "Rotenburg (Wümme)",
    "name": "Rotenburg an der Wümme",
    "population": "21798",
    "state": "Lower Saxony"
  },
  {
    "area": "41.68",
    "coords": {
      "lat": "49.383",
      "lon": "10.183"
    },
    "district": "Ansbach",
    "name": "Rothenburg ob der Tauber",
    "population": "11243",
    "state": "Bavaria"
  },
  {
    "area": "72.28",
    "coords": {
      "lat": "51.33333",
      "lon": "14.96667"
    },
    "district": "Görlitz",
    "name": "Rothenburg",
    "population": "4510",
    "state": "Saxony"
  },
  {
    "area": "12.07",
    "coords": {
      "lat": "49.883",
      "lon": "9.583"
    },
    "district": "Main-Spessart",
    "name": "Rothenfels",
    "population": "1013",
    "state": "Bavaria"
  },
  {
    "area": "96.25",
    "coords": {
      "lat": "49.24611",
      "lon": "11.09111"
    },
    "district": "Roth",
    "name": "Roth",
    "population": "25593",
    "state": "Bavaria"
  },
  {
    "area": "90.15",
    "coords": {
      "lat": "48.70194",
      "lon": "12.02722"
    },
    "district": "Landshut",
    "name": "Rottenburg a.d.Laaber",
    "population": "8267",
    "state": "Bavaria"
  },
  {
    "area": "142.26",
    "coords": {
      "lat": "48.47722",
      "lon": "8.93444"
    },
    "district": "Tübingen",
    "name": "Rottenburg am Neckar",
    "population": "43723",
    "state": "Baden-Württemberg"
  },
  {
    "area": "71.76",
    "coords": {
      "lat": "48.16806",
      "lon": "8.62472"
    },
    "district": "Rottweil",
    "name": "Rottweil",
    "population": "25274",
    "state": "Baden-Württemberg"
  },
  {
    "area": "73.04",
    "coords": {
      "lat": "51.300",
      "lon": "11.433"
    },
    "district": "Kyffhäuserkreis",
    "name": "Roßleben-Wiehe",
    "population": "7595",
    "state": "Thuringia"
  },
  {
    "area": "43.94",
    "coords": {
      "lat": "51.067",
      "lon": "13.183"
    },
    "district": "Mittelsachsen",
    "name": "Roßwein",
    "population": "7564",
    "state": "Saxony"
  },
  {
    "area": "135.17",
    "coords": {
      "lat": "50.71694",
      "lon": "11.32750"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Rudolstadt",
    "population": "25115",
    "state": "Thuringia"
  },
  {
    "area": "37.12",
    "coords": {
      "lat": "51.46667",
      "lon": "13.86667"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Ruhland",
    "population": "3672",
    "state": "Brandenburg"
  },
  {
    "area": "38.51",
    "coords": {
      "lat": "50.89194",
      "lon": "10.36667"
    },
    "district": "Wartburgkreis",
    "name": "Ruhla",
    "population": "5540",
    "state": "Thuringia"
  },
  {
    "area": "43.69",
    "coords": {
      "lat": "50.40528",
      "lon": "8.15500"
    },
    "district": "Limburg-Weilburg",
    "name": "Runkel",
    "population": "9303",
    "state": "Hesse"
  },
  {
    "area": "16.24",
    "coords": {
      "lat": "48.80972",
      "lon": "8.94500"
    },
    "district": "Böblingen",
    "name": "Rutesheim",
    "population": "10916",
    "state": "Baden-Württemberg"
  },
  {
    "area": "30.17",
    "coords": {
      "lat": "53.37611",
      "lon": "12.60611"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Röbel",
    "population": "5044",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "49.96",
    "coords": {
      "lat": "50.283",
      "lon": "11.067"
    },
    "district": "Coburg",
    "name": "Rödental",
    "population": "13107",
    "state": "Bavaria"
  },
  {
    "area": "29.99",
    "coords": {
      "lat": "49.96667",
      "lon": "8.81667"
    },
    "district": "Offenbach",
    "name": "Rödermark",
    "population": "28071",
    "state": "Hesse"
  },
  {
    "area": "122.46",
    "coords": {
      "lat": "50.383",
      "lon": "10.550"
    },
    "district": "Hildburghausen",
    "name": "Römhild",
    "population": "6869",
    "state": "Thuringia"
  },
  {
    "area": "38.81",
    "coords": {
      "lat": "50.900",
      "lon": "7.183"
    },
    "district": "Rheinisch-Bergischer Kreis",
    "name": "Rösrath",
    "population": "28693",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "46.03",
    "coords": {
      "lat": "51.19722",
      "lon": "12.41722"
    },
    "district": "Leipzig",
    "name": "Rötha",
    "population": "6141",
    "state": "Saxony"
  },
  {
    "area": "14.27",
    "coords": {
      "lat": "49.48472",
      "lon": "11.24750"
    },
    "district": "Nürnberger Land",
    "name": "Röthenbach an der Pegnitz",
    "population": "12203",
    "state": "Bavaria"
  },
  {
    "area": "27.19",
    "coords": {
      "lat": "49.500",
      "lon": "9.967"
    },
    "district": "Würzburg",
    "name": "Röttingen",
    "population": "1673",
    "state": "Bavaria"
  },
  {
    "area": "66.68",
    "coords": {
      "lat": "49.350",
      "lon": "12.517"
    },
    "district": "Cham",
    "name": "Rötz",
    "population": "3383",
    "state": "Bavaria"
  },
  {
    "area": "51",
    "coords": {
      "lat": "49.98333",
      "lon": "7.93056"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Rüdesheim",
    "population": "9922",
    "state": "Hesse"
  },
  {
    "area": "58.3",
    "coords": {
      "lat": "50.000",
      "lon": "8.433"
    },
    "district": "Groß-Gerau",
    "name": "Rüsselsheim am Main",
    "population": "65440",
    "state": "Hesse"
  },
  {
    "area": "158.09",
    "coords": {
      "lat": "51.49333",
      "lon": "8.48333"
    },
    "district": "Soest",
    "name": "Rüthen",
    "population": "10957",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "71.87",
    "coords": {
      "lat": "50.49167",
      "lon": "11.70000"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Saalburg-Ebersdorf",
    "population": "3414",
    "state": "Thuringia"
  },
  {
    "area": "145.56",
    "coords": {
      "lat": "50.650",
      "lon": "11.367"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Saalfeld",
    "population": "29457",
    "state": "Thuringia"
  },
  {
    "area": "167.07",
    "coords": {
      "lat": "49.233",
      "lon": "7.000"
    },
    "district": "Saarbrücken",
    "name": "Saarbrücken",
    "population": "180741",
    "state": "Saarland"
  },
  {
    "area": "20.36",
    "coords": {
      "lat": "49.617",
      "lon": "6.550"
    },
    "district": "Trier-Saarburg",
    "name": "Saarburg",
    "population": "7427",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "43.27",
    "coords": {
      "lat": "49.31667",
      "lon": "6.75000"
    },
    "district": "Saarlouis",
    "name": "Saarlouis",
    "population": "34552",
    "state": "Saarland"
  },
  {
    "area": "15.53",
    "coords": {
      "lat": "52.400",
      "lon": "9.267"
    },
    "district": "Schaumburg",
    "name": "Sachsenhagen",
    "population": "1972",
    "state": "Lower Saxony"
  },
  {
    "area": "57.92",
    "coords": {
      "lat": "48.96000",
      "lon": "9.06472"
    },
    "district": "Ludwigsburg",
    "name": "Sachsenheim",
    "population": "18794",
    "state": "Baden-Württemberg"
  },
  {
    "area": "223.92",
    "coords": {
      "lat": "52.15000",
      "lon": "10.33333"
    },
    "district": "Urban districts of Germany",
    "name": "Salzgitter",
    "population": "104948",
    "state": "Lower Saxony"
  },
  {
    "area": "109.40",
    "coords": {
      "lat": "51.67083",
      "lon": "8.60472"
    },
    "district": "Paderborn",
    "name": "Salzkotten",
    "population": "25062",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "304.53",
    "coords": {
      "lat": "52.850",
      "lon": "11.150"
    },
    "district": "Altmarkkreis Salzwedel",
    "name": "Salzwedel",
    "population": "23655",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "18.58",
    "coords": {
      "lat": "52.78333",
      "lon": "12.05000"
    },
    "district": "Stendal",
    "name": "Sandau",
    "population": "849",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "81.71",
    "coords": {
      "lat": "51.617",
      "lon": "12.233"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Sandersdorf-Brehna",
    "population": "14398",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "207.64",
    "coords": {
      "lat": "51.46667",
      "lon": "11.30000"
    },
    "district": "Mansfeld-Südharz",
    "name": "Sangerhausen",
    "population": "26297",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "34.22",
    "coords": {
      "lat": "50.77000",
      "lon": "7.18667"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Sankt Augustin",
    "population": "55767",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "54.36",
    "coords": {
      "lat": "47.76194",
      "lon": "8.12833"
    },
    "district": "Waldshut",
    "name": "Sankt Blasien",
    "population": "4009",
    "state": "Baden-Württemberg"
  },
  {
    "area": "59.85",
    "coords": {
      "lat": "48.12472",
      "lon": "8.33083"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Sankt Georgen im Schwarzwald",
    "population": "12958",
    "state": "Baden-Württemberg"
  },
  {
    "area": "7.00",
    "coords": {
      "lat": "50.15472",
      "lon": "7.71528"
    },
    "district": "Rhein-Lahn-Kreis",
    "name": "Sankt Goarshausen",
    "population": "1280",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "22.93",
    "coords": {
      "lat": "50.150",
      "lon": "7.717"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Sankt Goar",
    "population": "2731",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "49.95",
    "coords": {
      "lat": "49.300",
      "lon": "7.117"
    },
    "district": "Saarpfalz-Kreis",
    "name": "Sankt Ingbert",
    "population": "35714",
    "state": "Saarland"
  },
  {
    "area": "113.54",
    "coords": {
      "lat": "49.467",
      "lon": "7.167"
    },
    "district": "Sankt Wendel",
    "name": "Sankt Wendel",
    "population": "25862",
    "state": "Saarland"
  },
  {
    "area": "42.94",
    "coords": {
      "lat": "52.23944",
      "lon": "9.86056"
    },
    "district": "Hildesheim",
    "name": "Sarstedt",
    "population": "19359",
    "state": "Lower Saxony"
  },
  {
    "area": "78.08",
    "coords": {
      "lat": "51.98972",
      "lon": "8.04083"
    },
    "district": "Warendorf",
    "name": "Sassenberg",
    "population": "14260",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "46.45",
    "coords": {
      "lat": "54.51639",
      "lon": "13.64111"
    },
    "district": "Vorpommern-Rügen",
    "name": "Sassnitz",
    "population": "9320",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "35.17",
    "coords": {
      "lat": "50.717",
      "lon": "13.417"
    },
    "district": "Mittelsachsen",
    "name": "Sayda, Saxony",
    "population": "1777",
    "state": "Saxony"
  },
  {
    "area": "44.03",
    "coords": {
      "lat": "50.39444",
      "lon": "11.00861"
    },
    "district": "Sonneberg",
    "name": "Schalkau",
    "population": "3353",
    "state": "Thuringia"
  },
  {
    "area": "26.66",
    "coords": {
      "lat": "50.267",
      "lon": "11.767"
    },
    "district": "Hof",
    "name": "Schauenstein",
    "population": "1922",
    "state": "Bavaria"
  },
  {
    "area": "18.72",
    "coords": {
      "lat": "48.07361",
      "lon": "9.29333"
    },
    "district": "Sigmaringen",
    "name": "Scheer",
    "population": "2490",
    "state": "Baden-Württemberg"
  },
  {
    "area": "9.01",
    "coords": {
      "lat": "50.54083",
      "lon": "12.91250"
    },
    "district": "Erzgebirgskreis",
    "name": "Scheibenberg",
    "population": "2086",
    "state": "Saxony"
  },
  {
    "area": "45.12",
    "coords": {
      "lat": "49.667",
      "lon": "10.467"
    },
    "district": "Neustadt a.d.Aisch-Bad Windsheim",
    "name": "Scheinfeld",
    "population": "4648",
    "state": "Bavaria"
  },
  {
    "area": "75.24",
    "coords": {
      "lat": "48.37556",
      "lon": "9.73250"
    },
    "district": "Alb-Donau-Kreis",
    "name": "Schelklingen",
    "population": "6883",
    "state": "Baden-Württemberg"
  },
  {
    "area": "9.99",
    "coords": {
      "lat": "53.60278",
      "lon": "9.82333"
    },
    "district": "Pinneberg",
    "name": "Schenefeld",
    "population": "19271",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "94.88",
    "coords": {
      "lat": "49.97500",
      "lon": "11.03333"
    },
    "district": "Bamberg",
    "name": "Scheßlitz",
    "population": "7259",
    "state": "Bavaria"
  },
  {
    "area": "60.04",
    "coords": {
      "lat": "51.88306",
      "lon": "9.18306"
    },
    "district": "Lippe",
    "name": "Schieder-Schwalenberg",
    "population": "8475",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "28.04",
    "coords": {
      "lat": "49.383",
      "lon": "8.367"
    },
    "district": "Rhein-Pfalz-Kreis",
    "name": "Schifferstadt",
    "population": "20193",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "27.52",
    "coords": {
      "lat": "49.267",
      "lon": "10.267"
    },
    "district": "Ansbach",
    "name": "Schillingsfürst",
    "population": "2835",
    "state": "Bavaria"
  },
  {
    "area": "34.22",
    "coords": {
      "lat": "48.29056",
      "lon": "8.34472"
    },
    "district": "Rottweil",
    "name": "Schiltach",
    "population": "3809",
    "state": "Baden-Württemberg"
  },
  {
    "area": "24.3",
    "coords": {
      "lat": "51.100",
      "lon": "14.433"
    },
    "district": "Bautzen",
    "name": "Schirgiswalde-Kirschau",
    "population": "6227",
    "state": "Saxony"
  },
  {
    "area": "79.36",
    "coords": {
      "lat": "51.40000",
      "lon": "12.21667"
    },
    "district": "Nordsachsen",
    "name": "Schkeuditz",
    "population": "18066",
    "state": "Saxony"
  },
  {
    "area": "53.3",
    "coords": {
      "lat": "51.03333",
      "lon": "11.81667"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Schkölen",
    "population": "2590",
    "state": "Thuringia"
  },
  {
    "area": "122.09",
    "coords": {
      "lat": "50.53306",
      "lon": "6.46667"
    },
    "district": "Euskirchen",
    "name": "Schleiden",
    "population": "13053",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "108.22",
    "coords": {
      "lat": "50.58333",
      "lon": "11.81667"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Schleiz",
    "population": "8936",
    "state": "Thuringia"
  },
  {
    "area": "24.3",
    "coords": {
      "lat": "54.51806",
      "lon": "9.57028"
    },
    "district": "Schleswig-Flensburg",
    "name": "Schleswig",
    "population": "25276",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "21.17",
    "coords": {
      "lat": "50.55861",
      "lon": "12.95111"
    },
    "district": "Erzgebirgskreis",
    "name": "Schlettau",
    "population": "2392",
    "state": "Saxony"
  },
  {
    "area": "125.56",
    "coords": {
      "lat": "50.51667",
      "lon": "10.75000"
    },
    "district": "Hildburghausen",
    "name": "Schleusingen",
    "population": "10960",
    "state": "Thuringia"
  },
  {
    "area": "78.22",
    "coords": {
      "lat": "51.71667",
      "lon": "13.38306"
    },
    "district": "Elbe-Elster",
    "name": "Schlieben",
    "population": "2422",
    "state": "Brandenburg"
  },
  {
    "area": "142.09",
    "coords": {
      "lat": "50.667",
      "lon": "9.567"
    },
    "district": "Vogelsbergkreis",
    "name": "Schlitz",
    "population": "9764",
    "state": "Hesse"
  },
  {
    "area": "67.42",
    "coords": {
      "lat": "51.883",
      "lon": "8.617"
    },
    "district": "Gütersloh",
    "name": "Schloß Holte-Stukenbrock",
    "population": "26776",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "113.30",
    "coords": {
      "lat": "50.350",
      "lon": "9.517"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Schlüchtern",
    "population": "15914",
    "state": "Hesse"
  },
  {
    "area": "70.22",
    "coords": {
      "lat": "49.767",
      "lon": "10.617"
    },
    "district": "Bamberg",
    "name": "Schlüsselfeld",
    "population": "5941",
    "state": "Bavaria"
  },
  {
    "area": "105.35",
    "coords": {
      "lat": "50.717",
      "lon": "10.450"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Schmalkalden",
    "population": "19732",
    "state": "Thuringia"
  },
  {
    "area": "303.00",
    "coords": {
      "lat": "51.148983",
      "lon": "8.284539"
    },
    "district": "Hochsauerland",
    "name": "Schmallenberg",
    "population": "24869",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "94.72",
    "coords": {
      "lat": "50.89500",
      "lon": "12.35639"
    },
    "district": "Altenburger Land",
    "name": "Schmölln",
    "population": "13741",
    "state": "Thuringia"
  },
  {
    "area": "23.70",
    "coords": {
      "lat": "53.03667",
      "lon": "11.56806"
    },
    "district": "Lüchow-Dannenberg",
    "name": "Schnackenburg",
    "population": "564",
    "state": "Lower Saxony"
  },
  {
    "area": "62.55",
    "coords": {
      "lat": "49.533",
      "lon": "12.017"
    },
    "district": "Amberg-Sulzbach",
    "name": "Schnaittenbach",
    "population": "4203",
    "state": "Bavaria"
  },
  {
    "area": "23.35",
    "coords": {
      "lat": "50.59417",
      "lon": "12.64556"
    },
    "district": "Erzgebirgskreis",
    "name": "Schneeberg",
    "population": "13894",
    "state": "Saxony"
  },
  {
    "area": "234.58",
    "coords": {
      "lat": "53.11667",
      "lon": "9.80000"
    },
    "district": "Heidekreis",
    "name": "Schneverdingen",
    "population": "18662",
    "state": "Lower Saxony"
  },
  {
    "area": "21.35",
    "coords": {
      "lat": "47.817",
      "lon": "10.900"
    },
    "district": "Weilheim-Schongau",
    "name": "Schongau",
    "population": "12396",
    "state": "Bavaria"
  },
  {
    "area": "68.01",
    "coords": {
      "lat": "47.650",
      "lon": "7.817"
    },
    "district": "Lörrach",
    "name": "Schopfheim",
    "population": "19645",
    "state": "Baden-Württemberg"
  },
  {
    "area": "56.86",
    "coords": {
      "lat": "48.800",
      "lon": "9.533"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Schorndorf",
    "population": "39634",
    "state": "Baden-Württemberg"
  },
  {
    "area": "68.67",
    "coords": {
      "lat": "53.533",
      "lon": "7.950"
    },
    "district": "Friesland",
    "name": "Schortens",
    "population": "20329",
    "state": "Lower Saxony"
  },
  {
    "area": "133.56",
    "coords": {
      "lat": "50.500",
      "lon": "9.117"
    },
    "district": "Vogelsbergkreis",
    "name": "Schotten",
    "population": "10059",
    "state": "Hesse"
  },
  {
    "area": "80.70",
    "coords": {
      "lat": "48.22694",
      "lon": "8.38417"
    },
    "district": "Rottweil",
    "name": "Schramberg",
    "population": "21189",
    "state": "Baden-Württemberg"
  },
  {
    "area": "7.05",
    "coords": {
      "lat": "51.43639",
      "lon": "11.66500"
    },
    "district": "Saalekreis",
    "name": "Schraplau",
    "population": "1073",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "31.64",
    "coords": {
      "lat": "49.47361",
      "lon": "8.65917"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Schriesheim",
    "population": "15081",
    "state": "Baden-Württemberg"
  },
  {
    "area": "75.31",
    "coords": {
      "lat": "48.533",
      "lon": "11.267"
    },
    "district": "Neuburg-Schrobenhausen",
    "name": "Schrobenhausen",
    "population": "17106",
    "state": "Bavaria"
  },
  {
    "area": "105.21",
    "coords": {
      "lat": "49.34444",
      "lon": "9.98056"
    },
    "district": "Schwäbisch Hall",
    "name": "Schrozberg",
    "population": "5741",
    "state": "Baden-Württemberg"
  },
  {
    "area": "38.28",
    "coords": {
      "lat": "53.93889",
      "lon": "12.10694"
    },
    "district": "Rostock",
    "name": "Schwaan",
    "population": "5022",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "40.71",
    "coords": {
      "lat": "49.32917",
      "lon": "11.02083"
    },
    "district": "Urban district",
    "name": "Schwabach",
    "population": "40792",
    "state": "Bavaria"
  },
  {
    "area": "55.52",
    "coords": {
      "lat": "48.17889",
      "lon": "10.75500"
    },
    "district": "Augsburg",
    "name": "Schwabmünchen",
    "population": "14075",
    "state": "Bavaria"
  },
  {
    "area": "49.50",
    "coords": {
      "lat": "49.133",
      "lon": "9.050"
    },
    "district": "Heilbronn",
    "name": "Schwaigern",
    "population": "11425",
    "state": "Baden-Württemberg"
  },
  {
    "area": "6.47",
    "coords": {
      "lat": "50.150",
      "lon": "8.533"
    },
    "district": "Main-Taunus-Kreis",
    "name": "Schwalbach am Taunus",
    "population": "15333",
    "state": "Hesse"
  },
  {
    "area": "84.74",
    "coords": {
      "lat": "50.933",
      "lon": "9.217"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Schwalmstadt",
    "population": "18122",
    "state": "Hesse"
  },
  {
    "area": "123.76",
    "coords": {
      "lat": "49.32833",
      "lon": "12.11000"
    },
    "district": "Schwandorf",
    "name": "Schwandorf",
    "population": "28828",
    "state": "Bavaria"
  },
  {
    "area": "32.61",
    "coords": {
      "lat": "51.96750",
      "lon": "11.12083"
    },
    "district": "Harz",
    "name": "Schwanebeck",
    "population": "2454",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "25.99",
    "coords": {
      "lat": "50.583",
      "lon": "11.150"
    },
    "district": "Saalfeld-Rudolstadt",
    "name": "Schwarzatal",
    "population": "3622",
    "state": "Thuringia"
  },
  {
    "area": "36.50",
    "coords": {
      "lat": "50.28333",
      "lon": "11.62083"
    },
    "district": "Hof",
    "name": "Schwarzenbach a.Wald",
    "population": "4395",
    "state": "Bavaria"
  },
  {
    "area": "55.10",
    "coords": {
      "lat": "50.22083",
      "lon": "11.93333"
    },
    "district": "Hof",
    "name": "Schwarzenbach a.d.Saale",
    "population": "7042",
    "state": "Bavaria"
  },
  {
    "area": "11.56",
    "coords": {
      "lat": "53.50417",
      "lon": "10.47917"
    },
    "district": "Herzogtum Lauenburg",
    "name": "Schwarzenbek",
    "population": "16447",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "46.25",
    "coords": {
      "lat": "50.54528",
      "lon": "12.77917"
    },
    "district": "Erzgebirgskreis",
    "name": "Schwarzenberg",
    "population": "16723",
    "state": "Saxony"
  },
  {
    "area": "26.90",
    "coords": {
      "lat": "50.917",
      "lon": "9.433"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Schwarzenborn",
    "population": "1203",
    "state": "Hesse"
  },
  {
    "area": "33.23",
    "coords": {
      "lat": "51.48306",
      "lon": "13.86667"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Schwarzheide",
    "population": "5652",
    "state": "Brandenburg"
  },
  {
    "area": "200.12",
    "coords": {
      "lat": "53.050",
      "lon": "14.267"
    },
    "district": "Uckermark",
    "name": "Schwedt",
    "population": "29920",
    "state": "Brandenburg"
  },
  {
    "area": "31.09",
    "coords": {
      "lat": "49.82000",
      "lon": "6.75222"
    },
    "district": "Trier-Saarburg",
    "name": "Schweich",
    "population": "7827",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "35.71",
    "coords": {
      "lat": "50.05000",
      "lon": "10.23333"
    },
    "district": "Urban district",
    "name": "Schweinfurt",
    "population": "54032",
    "state": "Bavaria"
  },
  {
    "area": "20.5",
    "coords": {
      "lat": "51.267",
      "lon": "7.267"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Schwelm",
    "population": "28542",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "17.81",
    "coords": {
      "lat": "54.267",
      "lon": "10.217"
    },
    "district": "Plön",
    "name": "Schwentinental",
    "population": "13723",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "130.46",
    "coords": {
      "lat": "53.63333",
      "lon": "11.41667"
    },
    "district": "Urban district",
    "name": "Schwerin",
    "population": "95818",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "56.2",
    "coords": {
      "lat": "51.44583",
      "lon": "7.56528"
    },
    "district": "Unna",
    "name": "Schwerte",
    "population": "46340",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "21.62",
    "coords": {
      "lat": "49.383",
      "lon": "8.567"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Schwetzingen",
    "population": "21433",
    "state": "Baden-Württemberg"
  },
  {
    "area": "113.78",
    "coords": {
      "lat": "48.800",
      "lon": "9.800"
    },
    "district": "Ostalbkreis",
    "name": "Schwäbisch Gmünd",
    "population": "61186",
    "state": "Baden-Württemberg"
  },
  {
    "area": "104.23",
    "coords": {
      "lat": "49.11222",
      "lon": "9.73750"
    },
    "district": "Schwäbisch Hall",
    "name": "Schwäbisch Hall",
    "population": "40440",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.27",
    "coords": {
      "lat": "48.21139",
      "lon": "8.76167"
    },
    "district": "Zollernalbkreis",
    "name": "Schömberg",
    "population": "4627",
    "state": "Baden-Württemberg"
  },
  {
    "area": "14.70",
    "coords": {
      "lat": "47.78667",
      "lon": "7.89417"
    },
    "district": "Lörrach",
    "name": "Schönau im Schwarzwald",
    "population": "2420",
    "state": "Baden-Württemberg"
  },
  {
    "area": "22.49",
    "coords": {
      "lat": "49.43556",
      "lon": "8.80917"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Schönau",
    "population": "4438",
    "state": "Baden-Württemberg"
  },
  {
    "area": "52.18",
    "coords": {
      "lat": "53.850",
      "lon": "10.917"
    },
    "district": "Nordwestmecklenburg",
    "name": "Schönberg",
    "population": "4751",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "85.77",
    "coords": {
      "lat": "52.017",
      "lon": "11.750"
    },
    "district": "Salzlandkreis",
    "name": "Schönebeck",
    "population": "30720",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "55.06",
    "coords": {
      "lat": "50.367",
      "lon": "12.317"
    },
    "district": "Vogtlandkreis",
    "name": "Schöneck",
    "population": "3173",
    "state": "Saxony"
  },
  {
    "area": "155.13",
    "coords": {
      "lat": "51.81250",
      "lon": "13.22278"
    },
    "district": "Elbe-Elster",
    "name": "Schönewalde",
    "population": "3040",
    "state": "Brandenburg"
  },
  {
    "area": "35.36",
    "coords": {
      "lat": "52.133",
      "lon": "10.950"
    },
    "district": "Helmstedt",
    "name": "Schöningen",
    "population": "11306",
    "state": "Lower Saxony"
  },
  {
    "area": "50.27",
    "coords": {
      "lat": "49.517",
      "lon": "12.550"
    },
    "district": "Schwandorf",
    "name": "Schönsee",
    "population": "2447",
    "state": "Bavaria"
  },
  {
    "area": "19.18",
    "coords": {
      "lat": "50.200",
      "lon": "12.083"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Schönwald",
    "population": "3219",
    "state": "Bavaria"
  },
  {
    "area": "39.65",
    "coords": {
      "lat": "52.13306",
      "lon": "10.77833"
    },
    "district": "Wolfenbüttel",
    "name": "Schöppenstedt",
    "population": "5474",
    "state": "Lower Saxony"
  },
  {
    "area": "19.43",
    "coords": {
      "lat": "52.317",
      "lon": "7.217"
    },
    "district": "Grafschaft Bentheim",
    "name": "Schüttorf",
    "population": "12839",
    "state": "Lower Saxony"
  },
  {
    "area": "88.09",
    "coords": {
      "lat": "50.967",
      "lon": "14.283"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Sebnitz",
    "population": "9552",
    "state": "Saxony"
  },
  {
    "area": "106.99",
    "coords": {
      "lat": "52.867",
      "lon": "11.750"
    },
    "district": "Stendal",
    "name": "Seehausen",
    "population": "4847",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "78.79",
    "coords": {
      "lat": "51.800",
      "lon": "11.333"
    },
    "district": "Salzlandkreis",
    "name": "Seeland",
    "population": "7961",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "25.28",
    "coords": {
      "lat": "52.51667",
      "lon": "14.38306"
    },
    "district": "Märkisch-Oderland",
    "name": "Seelow",
    "population": "5426",
    "state": "Brandenburg"
  },
  {
    "area": "54",
    "coords": {
      "lat": "52.39611",
      "lon": "9.59806"
    },
    "district": "Hanover",
    "name": "Seelze",
    "population": "34442",
    "state": "Lower Saxony"
  },
  {
    "area": "102.13",
    "coords": {
      "lat": "51.89306",
      "lon": "10.17833"
    },
    "district": "Goslar",
    "name": "Seesen",
    "population": "19340",
    "state": "Lower Saxony"
  },
  {
    "area": "103.44",
    "coords": {
      "lat": "52.31611",
      "lon": "9.96417"
    },
    "district": "Hanover",
    "name": "Sehnde",
    "population": "23389",
    "state": "Lower Saxony"
  },
  {
    "area": "19.13",
    "coords": {
      "lat": "50.933",
      "lon": "14.617"
    },
    "district": "Görlitz",
    "name": "Seifhennersdorf",
    "population": "3676",
    "state": "Saxony"
  },
  {
    "area": "27.70",
    "coords": {
      "lat": "50.317",
      "lon": "11.750"
    },
    "district": "Hof",
    "name": "Selbitz",
    "population": "4294",
    "state": "Bavaria"
  },
  {
    "area": "62.37",
    "coords": {
      "lat": "50.167",
      "lon": "12.133"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Selb",
    "population": "15128",
    "state": "Bavaria"
  },
  {
    "area": "30.85",
    "coords": {
      "lat": "50.033",
      "lon": "8.967"
    },
    "district": "Offenbach",
    "name": "Seligenstadt",
    "population": "21293",
    "state": "Hesse"
  },
  {
    "area": "60.34",
    "coords": {
      "lat": "51.683",
      "lon": "7.483"
    },
    "district": "Unna",
    "name": "Selm",
    "population": "26011",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "8.71",
    "coords": {
      "lat": "50.53222",
      "lon": "7.75722"
    },
    "district": "Westerwaldkreis",
    "name": "Selters",
    "population": "2773",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "96.66",
    "coords": {
      "lat": "51.84389",
      "lon": "7.82778"
    },
    "district": "Warendorf",
    "name": "Sendenhorst",
    "population": "13157",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "25.17",
    "coords": {
      "lat": "48.317",
      "lon": "10.067"
    },
    "district": "Neu-Ulm",
    "name": "Senden",
    "population": "22336",
    "state": "Bavaria"
  },
  {
    "area": "127.56",
    "coords": {
      "lat": "51.517",
      "lon": "14.017"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Senftenberg",
    "population": "24275",
    "state": "Brandenburg"
  },
  {
    "area": "72.51",
    "coords": {
      "lat": "50.167",
      "lon": "10.833"
    },
    "district": "Coburg",
    "name": "Seßlach",
    "population": "3934",
    "state": "Bavaria"
  },
  {
    "area": "23.47",
    "coords": {
      "lat": "50.80139",
      "lon": "7.20444"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Siegburg",
    "population": "41463",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "114.67",
    "coords": {
      "lat": "50.883",
      "lon": "8.017"
    },
    "district": "Siegen-Wittgenstein",
    "name": "Siegen",
    "population": "102836",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "92.85",
    "coords": {
      "lat": "48.08667",
      "lon": "9.21639"
    },
    "district": "Sigmaringen",
    "name": "Sigmaringen",
    "population": "17278",
    "state": "Baden-Württemberg"
  },
  {
    "area": "47.33",
    "coords": {
      "lat": "48.267",
      "lon": "13.017"
    },
    "district": "Rottal-Inn",
    "name": "Simbach am Inn",
    "population": "9923",
    "state": "Bavaria"
  },
  {
    "area": "11.96",
    "coords": {
      "lat": "49.98333",
      "lon": "7.51667"
    },
    "district": "Rhein-Hunsrück-Kreis",
    "name": "Simmern",
    "population": "7950",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "50.85",
    "coords": {
      "lat": "48.71333",
      "lon": "9.00278"
    },
    "district": "Böblingen",
    "name": "Sindelfingen",
    "population": "64858",
    "state": "Baden-Württemberg"
  },
  {
    "area": "61.75",
    "coords": {
      "lat": "47.76278",
      "lon": "8.84000"
    },
    "district": "Konstanz",
    "name": "Singen",
    "population": "47723",
    "state": "Baden-Württemberg"
  },
  {
    "area": "127.01",
    "coords": {
      "lat": "49.250",
      "lon": "8.883"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Sinsheim",
    "population": "35442",
    "state": "Baden-Württemberg"
  },
  {
    "area": "41.02",
    "coords": {
      "lat": "50.54528",
      "lon": "7.25194"
    },
    "district": "Ahrweiler",
    "name": "Sinzig",
    "population": "17614",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "85.81",
    "coords": {
      "lat": "51.57111",
      "lon": "8.10917"
    },
    "district": "Soest",
    "name": "Soest",
    "population": "47460",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "89.45",
    "coords": {
      "lat": "51.16667",
      "lon": "7.08333"
    },
    "district": "Independent city",
    "name": "Solingen",
    "population": "159360",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "34.05",
    "coords": {
      "lat": "50.53972",
      "lon": "8.40722"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Solms",
    "population": "13611",
    "state": "Hesse"
  },
  {
    "area": "203.25",
    "coords": {
      "lat": "52.983",
      "lon": "9.833"
    },
    "name": "Soltau",
    "population": "22044",
    "state": "Lower Saxony"
  },
  {
    "area": "114.36",
    "coords": {
      "lat": "51.367",
      "lon": "10.867"
    },
    "district": "Kyffhäuserkreis",
    "name": "Sondershausen",
    "population": "21513",
    "state": "Thuringia"
  },
  {
    "area": "84.69",
    "coords": {
      "lat": "50.350",
      "lon": "11.167"
    },
    "district": "Sonneberg",
    "name": "Sonneberg",
    "population": "23830",
    "state": "Thuringia"
  },
  {
    "area": "118.54",
    "coords": {
      "lat": "51.68306",
      "lon": "13.65000"
    },
    "district": "Elbe-Elster",
    "name": "Sonnewalde",
    "population": "3231",
    "state": "Brandenburg"
  },
  {
    "area": "20.982",
    "coords": {
      "lat": "47.51583",
      "lon": "10.28111"
    },
    "district": "Oberallgäu",
    "name": "Sonthofen",
    "population": "21541",
    "state": "Bavaria"
  },
  {
    "area": "111.29",
    "coords": {
      "lat": "51.067",
      "lon": "9.933"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Sontra",
    "population": "7839",
    "state": "Hesse"
  },
  {
    "area": "18.50",
    "coords": {
      "lat": "48.07583",
      "lon": "8.73778"
    },
    "district": "Tuttlingen",
    "name": "Spaichingen",
    "population": "13084",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.80",
    "coords": {
      "lat": "49.17389",
      "lon": "10.92750"
    },
    "district": "Roth",
    "name": "Spalt",
    "population": "5023",
    "state": "Bavaria"
  },
  {
    "area": "97.7",
    "coords": {
      "lat": "51.117",
      "lon": "9.667"
    },
    "district": "Schwalm-Eder-Kreis",
    "name": "Spangenberg",
    "population": "6183",
    "state": "Hesse"
  },
  {
    "area": "15.37",
    "coords": {
      "lat": "49.93722",
      "lon": "6.63806"
    },
    "district": "Eifelkreis Bitburg-Prüm",
    "name": "Speicher",
    "population": "3449",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "40.244",
    "coords": {
      "lat": "52.13306",
      "lon": "8.48306"
    },
    "district": "Herford",
    "name": "Spenge",
    "population": "14487",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "42.58",
    "coords": {
      "lat": "49.31944",
      "lon": "8.43111"
    },
    "district": "Urban district",
    "name": "Speyer",
    "population": "50378",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "202.31",
    "coords": {
      "lat": "51.57167",
      "lon": "14.37944"
    },
    "district": "Spree-Neiße",
    "name": "Spremberg/Grodk",
    "population": "22175",
    "state": "Brandenburg"
  },
  {
    "area": "159.78",
    "coords": {
      "lat": "52.217",
      "lon": "9.550"
    },
    "district": "Hannover",
    "name": "Springe",
    "population": "28951",
    "state": "Lower Saxony"
  },
  {
    "area": "47.78",
    "coords": {
      "lat": "51.367",
      "lon": "7.250"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Sprockhövel",
    "population": "24747",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "10.81",
    "coords": {
      "lat": "50.95694",
      "lon": "14.03222"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Stadt Wehlen",
    "population": "1579",
    "state": "Saxony"
  },
  {
    "area": "78.29",
    "coords": {
      "lat": "50.833",
      "lon": "9.017"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Stadtallendorf",
    "population": "21456",
    "state": "Hesse"
  },
  {
    "area": "11.50",
    "coords": {
      "lat": "48.367",
      "lon": "10.850"
    },
    "district": "Augsburg",
    "name": "Stadtbergen",
    "population": "15010",
    "state": "Bavaria"
  },
  {
    "area": "60.27",
    "coords": {
      "lat": "52.32472",
      "lon": "9.20694"
    },
    "district": "Schaumburg",
    "name": "Stadthagen",
    "population": "22247",
    "state": "Lower Saxony"
  },
  {
    "area": "120.26",
    "coords": {
      "lat": "50.77500",
      "lon": "11.08083"
    },
    "district": "Ilm-Kreis",
    "name": "Stadtilm",
    "population": "8420",
    "state": "Thuringia"
  },
  {
    "area": "79.06",
    "coords": {
      "lat": "51.99250",
      "lon": "6.91500"
    },
    "district": "Borken",
    "name": "Stadtlohn",
    "population": "20322",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "24.86",
    "coords": {
      "lat": "51.883",
      "lon": "9.617"
    },
    "district": "Holzminden",
    "name": "Stadtoldendorf",
    "population": "5757",
    "state": "Lower Saxony"
  },
  {
    "area": "10.84",
    "coords": {
      "lat": "49.783",
      "lon": "9.417"
    },
    "district": "Miltenberg",
    "name": "Stadtprozelten",
    "population": "1541",
    "state": "Bavaria"
  },
  {
    "area": "24.07",
    "coords": {
      "lat": "50.85000",
      "lon": "11.73333"
    },
    "district": "Saale-Holzland-Kreis",
    "name": "Stadtroda",
    "population": "6692",
    "state": "Thuringia"
  },
  {
    "area": "39.65",
    "coords": {
      "lat": "50.167",
      "lon": "11.500"
    },
    "district": "Kulmbach",
    "name": "Stadtsteinach",
    "population": "3138",
    "state": "Bavaria"
  },
  {
    "area": "61.77",
    "coords": {
      "lat": "47.99722",
      "lon": "11.34056"
    },
    "district": "Starnberg",
    "name": "Starnberg",
    "population": "23498",
    "state": "Bavaria"
  },
  {
    "area": "23.26",
    "coords": {
      "lat": "47.88139",
      "lon": "7.73139"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Staufen im Breisgau",
    "population": "8209",
    "state": "Baden-Württemberg"
  },
  {
    "area": "28.13",
    "coords": {
      "lat": "50.667",
      "lon": "8.717"
    },
    "district": "Gießen",
    "name": "Staufenberg",
    "population": "8423",
    "state": "Hesse"
  },
  {
    "area": "40.84",
    "coords": {
      "lat": "53.700",
      "lon": "12.900"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Stavenhagen",
    "population": "5741",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "146.53",
    "coords": {
      "lat": "51.867",
      "lon": "11.567"
    },
    "district": "Salzlandkreis",
    "name": "Staßfurt",
    "population": "25385",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "26.35",
    "coords": {
      "lat": "50.43333",
      "lon": "11.16667"
    },
    "district": "Sonneberg",
    "name": "Steinach",
    "population": "3856",
    "state": "Thuringia"
  },
  {
    "area": "104.88",
    "coords": {
      "lat": "50.317",
      "lon": "9.467"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Steinau an der Straße",
    "population": "10275",
    "state": "Hesse"
  },
  {
    "area": "76.72",
    "coords": {
      "lat": "50.70056",
      "lon": "10.56667"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Steinbach-Hallenberg",
    "population": "9238",
    "state": "Thuringia"
  },
  {
    "area": "4.4",
    "coords": {
      "lat": "50.167",
      "lon": "8.567"
    },
    "district": "Hochtaunuskreis",
    "name": "Steinbach",
    "population": "10682",
    "state": "Hesse"
  },
  {
    "area": "111.42",
    "coords": {
      "lat": "52.14750",
      "lon": "7.34417"
    },
    "district": "Steinfurt",
    "name": "Steinfurt",
    "population": "34084",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "23.19",
    "coords": {
      "lat": "48.96667",
      "lon": "9.28333"
    },
    "district": "Ludwigsburg",
    "name": "Steinheim an der Murr",
    "population": "12220",
    "state": "Baden-Württemberg"
  },
  {
    "area": "75.68",
    "coords": {
      "lat": "51.86583",
      "lon": "9.09444"
    },
    "district": "Höxter",
    "name": "Steinheim",
    "population": "12657",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "19.52",
    "coords": {
      "lat": "49.417",
      "lon": "11.017"
    },
    "district": "Fürth",
    "name": "Stein",
    "population": "13996",
    "state": "Bavaria"
  },
  {
    "area": "268.02",
    "coords": {
      "lat": "52.600",
      "lon": "11.850"
    },
    "district": "Stendal",
    "name": "Stendal",
    "population": "39439",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "67.67",
    "coords": {
      "lat": "53.700",
      "lon": "11.817"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Sternberg",
    "population": "4157",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "69.75",
    "coords": {
      "lat": "47.85139",
      "lon": "9.01139"
    },
    "district": "Konstanz",
    "name": "Stockach",
    "population": "17114",
    "state": "Baden-Württemberg"
  },
  {
    "area": "98.52",
    "coords": {
      "lat": "50.767",
      "lon": "6.233"
    },
    "district": "Aachen",
    "name": "Stolberg",
    "population": "56792",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "38.82",
    "coords": {
      "lat": "50.70833",
      "lon": "12.77833"
    },
    "district": "Erzgebirgskreis",
    "name": "Stollberg",
    "population": "11303",
    "state": "Saxony"
  },
  {
    "area": "60.85",
    "coords": {
      "lat": "51.04889",
      "lon": "14.08278"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Stolpen",
    "population": "5616",
    "state": "Saxony"
  },
  {
    "area": "179.96",
    "coords": {
      "lat": "52.250",
      "lon": "13.933"
    },
    "district": "Oder-Spree",
    "name": "Storkow",
    "population": "9180",
    "state": "Brandenburg"
  },
  {
    "area": "74",
    "coords": {
      "lat": "51.45000",
      "lon": "6.26667"
    },
    "district": "Kleve",
    "name": "Straelen",
    "population": "16114",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "38.97",
    "coords": {
      "lat": "54.300",
      "lon": "13.083"
    },
    "district": "Vorpommern-Rügen",
    "name": "Stralsund",
    "population": "59421",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "86.84",
    "coords": {
      "lat": "53.500",
      "lon": "13.750"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Strasburg",
    "population": "4721",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "67.58",
    "coords": {
      "lat": "48.883",
      "lon": "12.567"
    },
    "district": "Urban district",
    "name": "Straubing",
    "population": "47794",
    "state": "Bavaria"
  },
  {
    "area": "67.86",
    "coords": {
      "lat": "52.58333",
      "lon": "13.88333"
    },
    "district": "Märkisch-Oderland",
    "name": "Strausberg",
    "population": "26587",
    "state": "Brandenburg"
  },
  {
    "area": "30.07",
    "coords": {
      "lat": "51.35250",
      "lon": "13.22583"
    },
    "district": "Meißen",
    "name": "Strehla",
    "population": "3686",
    "state": "Saxony"
  },
  {
    "area": "9.02",
    "coords": {
      "lat": "49.94694",
      "lon": "7.77944"
    },
    "district": "Bad Kreuznach",
    "name": "Stromberg",
    "population": "3303",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "45.67",
    "coords": {
      "lat": "49.06472",
      "lon": "8.47167"
    },
    "district": "Karlsruhe",
    "name": "Stutensee",
    "population": "24541",
    "state": "Baden-Württemberg"
  },
  {
    "area": "207.36",
    "coords": {
      "lat": "48.783",
      "lon": "9.183"
    },
    "district": "Stadtkreis",
    "name": "Stuttgart",
    "population": "634830",
    "state": "Baden-Württemberg"
  },
  {
    "area": "7.29",
    "coords": {
      "lat": "51.10972",
      "lon": "11.93306"
    },
    "district": "Burgenlandkreis",
    "name": "Stößen",
    "population": "937",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "93.20",
    "coords": {
      "lat": "47.74528",
      "lon": "8.44583"
    },
    "district": "Waldshut",
    "name": "Stühlingen",
    "population": "5327",
    "state": "Baden-Württemberg"
  },
  {
    "area": "141.62",
    "coords": {
      "lat": "50.61056",
      "lon": "10.69306"
    },
    "district": "Urban district",
    "name": "Suhl",
    "population": "36955",
    "state": "Thuringia"
  },
  {
    "area": "110.61",
    "coords": {
      "lat": "52.667",
      "lon": "8.800"
    },
    "district": "Diepholz",
    "name": "Sulingen",
    "population": "12842",
    "state": "Lower Saxony"
  },
  {
    "area": "87.60",
    "coords": {
      "lat": "48.36278",
      "lon": "8.63167"
    },
    "district": "Rottweil",
    "name": "Sulz am Neckar",
    "population": "12336",
    "state": "Baden-Württemberg"
  },
  {
    "area": "53.19",
    "coords": {
      "lat": "49.500",
      "lon": "11.750"
    },
    "district": "Amberg-Sulzbach",
    "name": "Sulzbach-Rosenberg",
    "population": "19414",
    "state": "Bavaria"
  },
  {
    "area": "16.12",
    "coords": {
      "lat": "49.283",
      "lon": "7.067"
    },
    "district": "Saarbrücken",
    "name": "Sulzbach",
    "population": "16468",
    "state": "Saarland"
  },
  {
    "area": "22.73",
    "coords": {
      "lat": "47.84028",
      "lon": "7.70917"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Sulzburg",
    "population": "2771",
    "state": "Baden-Württemberg"
  },
  {
    "area": "192.86",
    "coords": {
      "lat": "51.317",
      "lon": "8.000"
    },
    "district": "Hochsauerlandkreis",
    "name": "Sundern",
    "population": "27802",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "127.93",
    "coords": {
      "lat": "52.91306",
      "lon": "8.82694"
    },
    "district": "Diepholz",
    "name": "Syke",
    "population": "24355",
    "state": "Lower Saxony"
  },
  {
    "area": "87.60",
    "coords": {
      "lat": "51.16167",
      "lon": "11.11694"
    },
    "district": "Sömmerda",
    "name": "Sömmerda",
    "population": "19034",
    "state": "Thuringia"
  },
  {
    "area": "191.60",
    "coords": {
      "lat": "51.667",
      "lon": "12.067"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Südliches Anhalt",
    "population": "13417",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "12.78",
    "coords": {
      "lat": "48.67972",
      "lon": "9.75750"
    },
    "district": "Göppingen",
    "name": "Süßen",
    "population": "10192",
    "state": "Baden-Württemberg"
  },
  {
    "area": "41.54",
    "coords": {
      "lat": "50.78972",
      "lon": "10.61667"
    },
    "district": "Gotha",
    "name": "Tambach-Dietharz",
    "population": "4276",
    "state": "Thuringia"
  },
  {
    "area": "294.73",
    "coords": {
      "lat": "52.433",
      "lon": "11.800"
    },
    "district": "Stendal",
    "name": "Tangerhütte",
    "population": "10718",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "89.87",
    "coords": {
      "lat": "52.54083",
      "lon": "11.96889"
    },
    "district": "Stendal",
    "name": "Tangermünde",
    "population": "10310",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "60.45",
    "coords": {
      "lat": "50.650",
      "lon": "10.017"
    },
    "district": "Fulda",
    "name": "Tann (Rhön)",
    "population": "4476",
    "state": "Hesse"
  },
  {
    "area": "87.18",
    "coords": {
      "lat": "50.49444",
      "lon": "11.86111"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Tanna",
    "population": "3548",
    "state": "Thuringia"
  },
  {
    "area": "69.31",
    "coords": {
      "lat": "49.62250",
      "lon": "9.66278"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Tauberbischofsheim",
    "population": "13231",
    "state": "Baden-Württemberg"
  },
  {
    "area": "33.70",
    "coords": {
      "lat": "51.38000",
      "lon": "12.49361"
    },
    "district": "Nordsachsen",
    "name": "Taucha",
    "population": "15673",
    "state": "Saxony"
  },
  {
    "area": "67.03",
    "coords": {
      "lat": "50.133",
      "lon": "8.150"
    },
    "district": "Rheingau-Taunus-Kreis",
    "name": "Taunusstein",
    "population": "30005",
    "state": "Hesse"
  },
  {
    "area": "70.37",
    "coords": {
      "lat": "52.21944",
      "lon": "7.81250"
    },
    "district": "Steinfurt",
    "name": "Tecklenburg",
    "population": "9145",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "22.77",
    "coords": {
      "lat": "47.700",
      "lon": "11.767"
    },
    "district": "Miesbach",
    "name": "Tegernsee",
    "population": "3669",
    "state": "Bavaria"
  },
  {
    "area": "90.6",
    "coords": {
      "lat": "51.98194",
      "lon": "7.78556"
    },
    "district": "Warendorf",
    "name": "Telgte",
    "population": "19925",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "21.54",
    "coords": {
      "lat": "52.40222",
      "lon": "13.27056"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Teltow",
    "population": "25825",
    "state": "Brandenburg"
  },
  {
    "area": "377.01",
    "coords": {
      "lat": "53.11667",
      "lon": "13.50000"
    },
    "district": "Uckermark",
    "name": "Templin",
    "population": "15798",
    "state": "Brandenburg"
  },
  {
    "area": "61.98",
    "coords": {
      "lat": "47.81444",
      "lon": "8.65917"
    },
    "district": "Konstanz",
    "name": "Tengen",
    "population": "4584",
    "state": "Baden-Württemberg"
  },
  {
    "area": "24.52",
    "coords": {
      "lat": "54.017",
      "lon": "12.467"
    },
    "district": "Rostock",
    "name": "Tessin",
    "population": "3872",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "47.17",
    "coords": {
      "lat": "53.767",
      "lon": "12.567"
    },
    "district": "Rostock",
    "name": "Teterow",
    "population": "8470",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "71.22",
    "coords": {
      "lat": "47.67083",
      "lon": "9.58750"
    },
    "district": "Bodenseekreis",
    "name": "Tettnang",
    "population": "19198",
    "state": "Baden-Württemberg"
  },
  {
    "area": "38.25",
    "coords": {
      "lat": "49.22083",
      "lon": "12.08528"
    },
    "district": "Schwandorf",
    "name": "Teublitz",
    "population": "7418",
    "state": "Bavaria"
  },
  {
    "area": "81.84",
    "coords": {
      "lat": "51.117",
      "lon": "12.017"
    },
    "district": "Burgenlandkreis",
    "name": "Teuchern",
    "population": "8077",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "48.00",
    "coords": {
      "lat": "52.13611",
      "lon": "13.61056"
    },
    "district": "Dahme-Spreewald",
    "name": "Teupitz",
    "population": "1917",
    "state": "Brandenburg"
  },
  {
    "area": "34.26",
    "coords": {
      "lat": "50.39583",
      "lon": "11.38056"
    },
    "district": "Kronach",
    "name": "Teuschnitz",
    "population": "2000",
    "state": "Bavaria"
  },
  {
    "area": "137.62",
    "coords": {
      "lat": "51.750",
      "lon": "11.050"
    },
    "district": "Harz",
    "name": "Thale",
    "population": "17442",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "10.92",
    "coords": {
      "lat": "50.70250",
      "lon": "12.85167"
    },
    "district": "Erzgebirgskreis",
    "name": "Thalheim",
    "population": "6051",
    "state": "Saxony"
  },
  {
    "area": "20.02",
    "coords": {
      "lat": "48.267",
      "lon": "10.467"
    },
    "district": "Günzburg",
    "name": "Thannhausen",
    "population": "6277",
    "state": "Bavaria"
  },
  {
    "area": "71.22",
    "coords": {
      "lat": "50.98333",
      "lon": "13.58083"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Tharandt",
    "population": "5423",
    "state": "Saxony"
  },
  {
    "area": "20.19",
    "coords": {
      "lat": "50.50417",
      "lon": "10.61583"
    },
    "district": "Hildburghausen",
    "name": "Themar",
    "population": "2851",
    "state": "Thuringia"
  },
  {
    "area": "18.89",
    "coords": {
      "lat": "50.67111",
      "lon": "12.95139"
    },
    "district": "Erzgebirgskreis",
    "name": "Thum",
    "population": "5146",
    "state": "Saxony"
  },
  {
    "area": "66.54",
    "coords": {
      "lat": "49.883",
      "lon": "12.333"
    },
    "district": "Tirschenreuth",
    "name": "Tirschenreuth",
    "population": "8707",
    "state": "Bavaria"
  },
  {
    "area": "89.66",
    "coords": {
      "lat": "47.91222",
      "lon": "8.21472"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Titisee-Neustadt",
    "population": "12269",
    "state": "Baden-Württemberg"
  },
  {
    "area": "72.04",
    "coords": {
      "lat": "48.06306",
      "lon": "12.76694"
    },
    "district": "Traunstein",
    "name": "Tittmoning",
    "population": "5798",
    "state": "Bavaria"
  },
  {
    "area": "69.60",
    "coords": {
      "lat": "47.833",
      "lon": "7.950"
    },
    "district": "Lörrach",
    "name": "Todtnau",
    "population": "4894",
    "state": "Baden-Württemberg"
  },
  {
    "area": "102.53",
    "coords": {
      "lat": "51.56028",
      "lon": "13.00556"
    },
    "district": "Nordsachsen",
    "name": "Torgau",
    "population": "20065",
    "state": "Saxony"
  },
  {
    "area": "72.22",
    "coords": {
      "lat": "53.617",
      "lon": "14.000"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Torgelow",
    "population": "9153",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "20.62",
    "coords": {
      "lat": "53.700",
      "lon": "9.717"
    },
    "district": "Pinneberg",
    "name": "Tornesch",
    "population": "13779",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "31.35",
    "coords": {
      "lat": "49.95111",
      "lon": "7.11667"
    },
    "district": "Bernkastel-Wittlich",
    "name": "Traben-Trarbach",
    "population": "5567",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "45.05",
    "coords": {
      "lat": "47.967",
      "lon": "12.583"
    },
    "district": "Traunstein",
    "name": "Traunreut",
    "population": "20977",
    "state": "Bavaria"
  },
  {
    "area": "48.53",
    "coords": {
      "lat": "47.867",
      "lon": "12.633"
    },
    "district": "Traunstein",
    "name": "Traunstein",
    "population": "20520",
    "state": "Bavaria"
  },
  {
    "area": "125.66",
    "coords": {
      "lat": "52.21667",
      "lon": "13.19972"
    },
    "district": "Teltow-Fläming",
    "name": "Trebbin",
    "population": "9541",
    "state": "Brandenburg"
  },
  {
    "area": "35.03",
    "coords": {
      "lat": "51.28306",
      "lon": "12.75000"
    },
    "district": "Leipzig",
    "name": "Trebsen",
    "population": "3813",
    "state": "Saxony"
  },
  {
    "area": "72.46",
    "coords": {
      "lat": "51.13667",
      "lon": "10.23750"
    },
    "district": "Wartburgkreis",
    "name": "Treffurt",
    "population": "6084",
    "state": "Thuringia"
  },
  {
    "area": "69.35",
    "coords": {
      "lat": "51.583",
      "lon": "9.417"
    },
    "district": "Kassel",
    "name": "Trendelburg",
    "population": "4911",
    "state": "Hesse"
  },
  {
    "area": "103.00",
    "coords": {
      "lat": "48.95528",
      "lon": "10.90944"
    },
    "district": "Weißenburg-Gunzenhausen",
    "name": "Treuchtlingen",
    "population": "12942",
    "state": "Bavaria"
  },
  {
    "area": "211.33",
    "coords": {
      "lat": "52.09722",
      "lon": "12.87111"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Treuenbrietzen",
    "population": "7405",
    "state": "Brandenburg"
  },
  {
    "area": "43.74",
    "coords": {
      "lat": "50.54250",
      "lon": "12.30222"
    },
    "district": "Vogtlandkreis",
    "name": "Treuen",
    "population": "7894",
    "state": "Saxony"
  },
  {
    "area": "33.32",
    "coords": {
      "lat": "48.13083",
      "lon": "8.23167"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Triberg im Schwarzwald",
    "population": "4794",
    "state": "Baden-Württemberg"
  },
  {
    "area": "54.75",
    "coords": {
      "lat": "54.083",
      "lon": "12.750"
    },
    "district": "Vorpommern-Rügen",
    "name": "Tribsees",
    "population": "2608",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "117.13",
    "coords": {
      "lat": "49.750",
      "lon": "6.633"
    },
    "district": "Urban district",
    "name": "Trier",
    "population": "110636",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "32.96",
    "coords": {
      "lat": "50.733",
      "lon": "11.850"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Triptis",
    "population": "3663",
    "state": "Thuringia"
  },
  {
    "area": "79.14",
    "coords": {
      "lat": "48.30806",
      "lon": "9.24444"
    },
    "district": "Reutlingen",
    "name": "Trochtelfingen",
    "population": "6366",
    "state": "Baden-Württemberg"
  },
  {
    "area": "62.17",
    "coords": {
      "lat": "50.81611",
      "lon": "7.15556"
    },
    "district": "Rhein-Sieg-Kreis",
    "name": "Troisdorf",
    "population": "74903",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "24.20",
    "coords": {
      "lat": "48.07556",
      "lon": "8.63611"
    },
    "district": "Tuttlingen",
    "name": "Trossingen",
    "population": "16829",
    "state": "Baden-Württemberg"
  },
  {
    "area": "51.36",
    "coords": {
      "lat": "48.017",
      "lon": "12.550"
    },
    "district": "Traunstein",
    "name": "Trostberg",
    "population": "11222",
    "state": "Bavaria"
  },
  {
    "area": "90.48",
    "coords": {
      "lat": "47.98500",
      "lon": "8.82333"
    },
    "district": "Tuttlingen",
    "name": "Tuttlingen",
    "population": "35730",
    "state": "Baden-Württemberg"
  },
  {
    "area": "114.22",
    "coords": {
      "lat": "52.800",
      "lon": "8.650"
    },
    "district": "Diepholz",
    "name": "Twistringen",
    "population": "12449",
    "state": "Lower Saxony"
  },
  {
    "area": "13.66",
    "coords": {
      "lat": "48.250",
      "lon": "12.567"
    },
    "district": "Altötting",
    "name": "Töging am Inn",
    "population": "9291",
    "state": "Bavaria"
  },
  {
    "area": "44.33",
    "coords": {
      "lat": "51.32083",
      "lon": "6.49306"
    },
    "district": "Viersen",
    "name": "Tönisvorst",
    "population": "29306",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "44.41",
    "coords": {
      "lat": "54.31722",
      "lon": "8.94278"
    },
    "district": "Nordfriesland",
    "name": "TönningTønning / Taning",
    "population": "4965",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "108.12",
    "coords": {
      "lat": "48.52000",
      "lon": "9.05556"
    },
    "district": "Tübingen",
    "name": "Tübingen",
    "population": "90546",
    "state": "Baden-Württemberg"
  },
  {
    "area": "134.91",
    "coords": {
      "lat": "51.550",
      "lon": "13.350"
    },
    "district": "Elbe-Elster",
    "name": "Uebigau-Wahrenbrück",
    "population": "5245",
    "state": "Brandenburg"
  },
  {
    "area": "84.64",
    "coords": {
      "lat": "53.73889",
      "lon": "14.04444"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Ueckermünde",
    "population": "8591",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "135.84",
    "coords": {
      "lat": "52.96472",
      "lon": "10.56583"
    },
    "district": "Uelzen",
    "name": "Uelzen",
    "population": "33614",
    "state": "Lower Saxony"
  },
  {
    "area": "11.43",
    "coords": {
      "lat": "53.68722",
      "lon": "9.66917"
    },
    "district": "Pinneberg",
    "name": "Uetersen",
    "population": "18496",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "59.47",
    "coords": {
      "lat": "49.517",
      "lon": "10.250"
    },
    "district": "Neustadt a.d.Aisch-Bad Windsheim",
    "name": "Uffenheim",
    "population": "6518",
    "state": "Bavaria"
  },
  {
    "area": "24.79",
    "coords": {
      "lat": "48.70583",
      "lon": "9.59194"
    },
    "district": "Göppingen",
    "name": "Uhingen",
    "population": "14422",
    "state": "Baden-Württemberg"
  },
  {
    "area": "28.62",
    "coords": {
      "lat": "50.2086889",
      "lon": "6.9795889"
    },
    "district": "Cochem-Zell",
    "name": "Ulmen",
    "population": "3335",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "118.69",
    "coords": {
      "lat": "48.400",
      "lon": "9.983"
    },
    "district": "Stadtkreis",
    "name": "Ulm",
    "population": "126329",
    "state": "Baden-Württemberg"
  },
  {
    "area": "65.61",
    "coords": {
      "lat": "50.583",
      "lon": "9.200"
    },
    "district": "Vogelsbergkreis",
    "name": "Ulrichstein",
    "population": "2985",
    "state": "Hesse"
  },
  {
    "area": "15.70",
    "coords": {
      "lat": "50.25833",
      "lon": "10.82778"
    },
    "district": "Hildburghausen",
    "name": "Ummerstadt",
    "population": "461",
    "state": "Thuringia"
  },
  {
    "area": "8.16",
    "coords": {
      "lat": "50.60083",
      "lon": "7.21500"
    },
    "district": "Neuwied",
    "name": "Unkel",
    "population": "5021",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "88.52",
    "coords": {
      "lat": "51.53472",
      "lon": "7.68889"
    },
    "district": "Unna",
    "name": "Unna",
    "population": "58633",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "14.93",
    "coords": {
      "lat": "48.28333",
      "lon": "11.56667"
    },
    "district": "Munich",
    "name": "Unterschleißheim",
    "population": "28907",
    "state": "Bavaria"
  },
  {
    "area": "38.54",
    "coords": {
      "lat": "53.867",
      "lon": "13.917"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Usedom",
    "population": "1747",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "55.83",
    "coords": {
      "lat": "50.33444",
      "lon": "8.53750"
    },
    "district": "Hochtaunuskreis",
    "name": "Usingen",
    "population": "14505",
    "state": "Hesse"
  },
  {
    "area": "113.4",
    "coords": {
      "lat": "51.65972",
      "lon": "9.63583"
    },
    "district": "Northeim",
    "name": "Uslar",
    "population": "14236",
    "state": "Lower Saxony"
  },
  {
    "area": "44.41",
    "coords": {
      "lat": "50.82889",
      "lon": "10.02139"
    },
    "district": "Wartburgkreis",
    "name": "Vacha",
    "population": "5173",
    "state": "Thuringia"
  },
  {
    "area": "73.41",
    "coords": {
      "lat": "48.93278",
      "lon": "8.95639"
    },
    "district": "Ludwigsburg",
    "name": "Vaihingen an der Enz",
    "population": "29467",
    "state": "Baden-Württemberg"
  },
  {
    "area": "13.22",
    "coords": {
      "lat": "50.3971361",
      "lon": "7.6220222"
    },
    "district": "Mayen-Koblenz",
    "name": "Vallendar",
    "population": "8680",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "113.53",
    "coords": {
      "lat": "53.39694",
      "lon": "8.13611"
    },
    "district": "Friesland",
    "name": "Varel",
    "population": "24001",
    "state": "Lower Saxony"
  },
  {
    "area": "87.8",
    "coords": {
      "lat": "52.73056",
      "lon": "8.28861"
    },
    "district": "Vechta",
    "name": "Vechta",
    "population": "32433",
    "state": "Lower Saxony"
  },
  {
    "area": "74.9",
    "coords": {
      "lat": "51.333",
      "lon": "7.050"
    },
    "district": "Mettmann",
    "name": "Velbert",
    "population": "81984",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "175.65",
    "coords": {
      "lat": "49.23278",
      "lon": "11.67194"
    },
    "district": "Neumarkt in der Oberpfalz",
    "name": "Velburg",
    "population": "5312",
    "state": "Bavaria"
  },
  {
    "area": "21.32",
    "coords": {
      "lat": "49.617",
      "lon": "11.517"
    },
    "district": "Nürnberger Land",
    "name": "Velden",
    "population": "1813",
    "state": "Bavaria"
  },
  {
    "area": "70.52",
    "coords": {
      "lat": "51.89389",
      "lon": "6.98972"
    },
    "district": "Borken",
    "name": "Velen",
    "population": "13130",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "31.89",
    "coords": {
      "lat": "49.08778",
      "lon": "9.88167"
    },
    "district": "Schwäbisch Hall",
    "name": "Vellberg",
    "population": "4446",
    "state": "Baden-Württemberg"
  },
  {
    "area": "13.97",
    "coords": {
      "lat": "51.350",
      "lon": "9.467"
    },
    "district": "Kassel",
    "name": "Vellmar",
    "population": "18134",
    "state": "Hesse"
  },
  {
    "area": "23.39",
    "coords": {
      "lat": "52.683",
      "lon": "13.183"
    },
    "district": "Oberhavel",
    "name": "Velten",
    "population": "11965",
    "state": "Brandenburg"
  },
  {
    "area": "71.59",
    "coords": {
      "lat": "52.92333",
      "lon": "9.23500"
    },
    "district": "Verden",
    "name": "Verden an der Aller",
    "population": "27661",
    "state": "Lower Saxony"
  },
  {
    "area": "31.24",
    "coords": {
      "lat": "48.17833",
      "lon": "9.21194"
    },
    "district": "Sigmaringen",
    "name": "Veringenstadt",
    "population": "2168",
    "state": "Baden-Württemberg"
  },
  {
    "area": "71.36",
    "coords": {
      "lat": "51.88306",
      "lon": "8.51667"
    },
    "district": "Gütersloh",
    "name": "Verl",
    "population": "25498",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "84.81",
    "coords": {
      "lat": "52.04361",
      "lon": "8.15000"
    },
    "district": "Gütersloh",
    "name": "Versmold",
    "population": "21468",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "110.22",
    "coords": {
      "lat": "51.78306",
      "lon": "14.06667"
    },
    "district": "Oberspreewald-Lausitz",
    "name": "Vetschau/Wětošow",
    "population": "8103",
    "state": "Brandenburg"
  },
  {
    "area": "62.48",
    "coords": {
      "lat": "49.07917",
      "lon": "12.88472"
    },
    "district": "Regen",
    "name": "Viechtach",
    "population": "8364",
    "state": "Bavaria"
  },
  {
    "area": "48.41",
    "coords": {
      "lat": "49.54167",
      "lon": "8.57861"
    },
    "district": "Bergstraße",
    "name": "Viernheim",
    "population": "34175",
    "state": "Hesse"
  },
  {
    "area": "91.07",
    "coords": {
      "lat": "51.25611",
      "lon": "6.39722"
    },
    "district": "Viersen",
    "name": "Viersen",
    "population": "76905",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "165.47",
    "coords": {
      "lat": "48.06028",
      "lon": "8.45861"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Villingen-Schwenningen",
    "population": "85181",
    "state": "Baden-Württemberg"
  },
  {
    "area": "68.85",
    "coords": {
      "lat": "48.450",
      "lon": "12.350"
    },
    "district": "Landshut",
    "name": "Vilsbiburg",
    "population": "12074",
    "state": "Bavaria"
  },
  {
    "area": "64.71",
    "coords": {
      "lat": "49.600",
      "lon": "11.800"
    },
    "district": "Amberg-Sulzbach",
    "name": "Vilseck",
    "population": "6093",
    "state": "Bavaria"
  },
  {
    "area": "86.36",
    "coords": {
      "lat": "48.63306",
      "lon": "13.18306"
    },
    "district": "Passau",
    "name": "Vilshofen an der Donau",
    "population": "16703",
    "state": "Bavaria"
  },
  {
    "area": "158.85",
    "coords": {
      "lat": "52.967",
      "lon": "9.583"
    },
    "district": "Rotenburg (Wümme)",
    "name": "Visselhövede",
    "population": "9629",
    "state": "Lower Saxony"
  },
  {
    "area": "76.92",
    "coords": {
      "lat": "52.16667",
      "lon": "8.84972"
    },
    "district": "Herford",
    "name": "Vlotho",
    "population": "18429",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "53.49",
    "coords": {
      "lat": "51.60000",
      "lon": "6.68333"
    },
    "district": "Wesel",
    "name": "Voerde",
    "population": "35999",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "37.40",
    "coords": {
      "lat": "48.08917",
      "lon": "7.63167"
    },
    "district": "Breisgau-Hochschwarzwald",
    "name": "Vogtsburg",
    "population": "6031",
    "state": "Baden-Württemberg"
  },
  {
    "area": "45.19",
    "coords": {
      "lat": "48.767",
      "lon": "11.617"
    },
    "district": "Pfaffenhofen an der Ilm",
    "name": "Vohburg a.d.Donau",
    "population": "8312",
    "state": "Bavaria"
  },
  {
    "area": "74.07",
    "coords": {
      "lat": "49.617",
      "lon": "12.333"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Vohenstrauß",
    "population": "7398",
    "state": "Bavaria"
  },
  {
    "area": "60.19",
    "coords": {
      "lat": "49.867",
      "lon": "10.217"
    },
    "district": "Kitzingen",
    "name": "Volkach",
    "population": "8857",
    "state": "Bavaria"
  },
  {
    "area": "67.47",
    "coords": {
      "lat": "51.383",
      "lon": "9.117"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Volkmarsen",
    "population": "6804",
    "state": "Hesse"
  },
  {
    "area": "135.53",
    "coords": {
      "lat": "52.03306",
      "lon": "6.83306"
    },
    "district": "Borken",
    "name": "Vreden",
    "population": "22641",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "70.47",
    "coords": {
      "lat": "48.04556",
      "lon": "8.30417"
    },
    "district": "Schwarzwald-Baar-Kreis",
    "name": "Vöhrenbach",
    "population": "3853",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.63",
    "coords": {
      "lat": "48.283",
      "lon": "10.083"
    },
    "district": "Neu-Ulm",
    "name": "Vöhringen",
    "population": "13557",
    "state": "Bavaria"
  },
  {
    "area": "67.06",
    "coords": {
      "lat": "49.250",
      "lon": "6.833"
    },
    "district": "Saarbrücken",
    "name": "Völklingen",
    "population": "39374",
    "state": "Saarland"
  },
  {
    "area": "24.97",
    "coords": {
      "lat": "49.44111",
      "lon": "8.18000"
    },
    "district": "Bad Dürkheim",
    "name": "Wachenheim",
    "population": "4644",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "111.17",
    "coords": {
      "lat": "49.517",
      "lon": "6.867"
    },
    "district": "Merzig-Wadern",
    "name": "Wadern",
    "population": "15727",
    "state": "Saarland"
  },
  {
    "area": "42.84",
    "coords": {
      "lat": "49.250",
      "lon": "8.517"
    },
    "district": "Karlsruhe",
    "name": "Waghäusel",
    "population": "20935",
    "state": "Baden-Württemberg"
  },
  {
    "area": "15.74",
    "coords": {
      "lat": "53.950",
      "lon": "10.217"
    },
    "district": "Segeberg",
    "name": "Wahlstedt",
    "population": "9596",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "42.76",
    "coords": {
      "lat": "48.83028",
      "lon": "9.31694"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Waiblingen",
    "population": "55449",
    "state": "Baden-Württemberg"
  },
  {
    "area": "25.57",
    "coords": {
      "lat": "49.29750",
      "lon": "8.92000"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Waibstadt",
    "population": "5682",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.65",
    "coords": {
      "lat": "49.850",
      "lon": "11.333"
    },
    "district": "Bayreuth",
    "name": "Waischenfeld",
    "population": "3083",
    "state": "Bavaria"
  },
  {
    "area": "63.02",
    "coords": {
      "lat": "50.87889",
      "lon": "7.61500"
    },
    "district": "Oberbergischer Kreis",
    "name": "Waldbröl",
    "population": "19543",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "115.73",
    "coords": {
      "lat": "51.200",
      "lon": "9.067"
    },
    "district": "Waldeck-Frankenberg",
    "name": "Waldeck",
    "population": "6761",
    "state": "Hesse"
  },
  {
    "area": "22.70",
    "coords": {
      "lat": "48.63722",
      "lon": "9.13167"
    },
    "district": "Böblingen",
    "name": "Waldenbuch",
    "population": "8717",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.55",
    "coords": {
      "lat": "49.183",
      "lon": "9.633"
    },
    "district": "Hohenlohekreis",
    "name": "Waldenburg",
    "population": "3094",
    "state": "Baden-Württemberg"
  },
  {
    "area": "25.07",
    "coords": {
      "lat": "50.87583",
      "lon": "12.59972"
    },
    "district": "Zwickau",
    "name": "Waldenburg",
    "population": "4012",
    "state": "Saxony"
  },
  {
    "area": "60.40",
    "coords": {
      "lat": "49.967",
      "lon": "12.067"
    },
    "district": "Tirschenreuth",
    "name": "Waldershof",
    "population": "4294",
    "state": "Bavaria"
  },
  {
    "area": "41.62",
    "coords": {
      "lat": "51.067",
      "lon": "13.017"
    },
    "district": "Mittelsachsen",
    "name": "Waldheim",
    "population": "8964",
    "state": "Saxony"
  },
  {
    "area": "96.48",
    "coords": {
      "lat": "51.150",
      "lon": "9.883"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Waldkappel",
    "population": "4249",
    "state": "Hesse"
  },
  {
    "area": "80.06",
    "coords": {
      "lat": "48.7304778",
      "lon": "13.601139"
    },
    "district": "Freyung-Grafenau",
    "name": "Waldkirchen",
    "population": "10534",
    "state": "Bavaria"
  },
  {
    "area": "48.47",
    "coords": {
      "lat": "48.100",
      "lon": "7.967"
    },
    "district": "Emmendingen",
    "name": "Waldkirch",
    "population": "21809",
    "state": "Baden-Württemberg"
  },
  {
    "area": "21.53",
    "coords": {
      "lat": "48.217",
      "lon": "12.400"
    },
    "district": "Mühldorf am Inn",
    "name": "Waldkraiburg",
    "population": "23442",
    "state": "Bavaria"
  },
  {
    "area": "101.16",
    "coords": {
      "lat": "49.367",
      "lon": "12.700"
    },
    "district": "Cham",
    "name": "Waldmünchen",
    "population": "6728",
    "state": "Bavaria"
  },
  {
    "area": "66.54",
    "coords": {
      "lat": "50.000",
      "lon": "12.300"
    },
    "district": "Tirschenreuth",
    "name": "Waldsassen",
    "population": "6694",
    "state": "Bavaria"
  },
  {
    "area": "77.98",
    "coords": {
      "lat": "47.62306",
      "lon": "8.21444"
    },
    "district": "Waldshut",
    "name": "Waldshut-Tiengen",
    "population": "24226",
    "state": "Baden-Württemberg"
  },
  {
    "area": "19.91",
    "coords": {
      "lat": "49.30000",
      "lon": "8.65000"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Walldorf",
    "population": "15534",
    "state": "Baden-Württemberg"
  },
  {
    "area": "105.88",
    "coords": {
      "lat": "49.58306",
      "lon": "9.36806"
    },
    "district": "Neckar-Odenwald-Kreis",
    "name": "Walldürn",
    "population": "11518",
    "state": "Baden-Württemberg"
  },
  {
    "area": "45.60",
    "coords": {
      "lat": "50.26750",
      "lon": "11.47333"
    },
    "district": "Kronach",
    "name": "Wallenfels",
    "population": "2657",
    "state": "Bavaria"
  },
  {
    "area": "334.8",
    "coords": {
      "lat": "52.867",
      "lon": "9.583"
    },
    "district": "Heidekreis",
    "name": "Walsrode",
    "population": "30038",
    "state": "Lower Saxony"
  },
  {
    "area": "71.20",
    "coords": {
      "lat": "50.89750",
      "lon": "10.55583"
    },
    "district": "Gotha",
    "name": "Waltershausen",
    "population": "12973",
    "state": "Thuringia"
  },
  {
    "area": "46.98",
    "coords": {
      "lat": "51.617",
      "lon": "7.383"
    },
    "district": "Recklinghausen",
    "name": "Waltrop",
    "population": "29345",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "46.9",
    "coords": {
      "lat": "51.183",
      "lon": "10.167"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Wanfried",
    "population": "4179",
    "state": "Hesse"
  },
  {
    "area": "101.28",
    "coords": {
      "lat": "47.68583",
      "lon": "9.83417"
    },
    "district": "Ravensburg",
    "name": "Wangen im Allgäu",
    "population": "26905",
    "state": "Baden-Württemberg"
  },
  {
    "area": "188.07",
    "coords": {
      "lat": "52.067",
      "lon": "11.433"
    },
    "district": "Börde",
    "name": "Wanzleben-Börde",
    "population": "13903",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "168.71",
    "coords": {
      "lat": "51.50000",
      "lon": "9.16972"
    },
    "district": "Höxter",
    "name": "Warburg",
    "population": "23079",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "176.75",
    "coords": {
      "lat": "51.95389",
      "lon": "7.99333"
    },
    "district": "Warendorf",
    "name": "Warendorf",
    "population": "37226",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "158.39",
    "coords": {
      "lat": "53.517",
      "lon": "12.683"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Waren",
    "population": "21061",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "44.26",
    "coords": {
      "lat": "53.783",
      "lon": "11.683"
    },
    "district": "Nordwestmecklenburg",
    "name": "Warin",
    "population": "3246",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "157.91",
    "coords": {
      "lat": "51.450",
      "lon": "8.350"
    },
    "district": "Soest",
    "name": "Warstein",
    "population": "24842",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "42.41",
    "coords": {
      "lat": "51.100",
      "lon": "6.150"
    },
    "district": "Heinsberg",
    "name": "Wassenberg",
    "population": "18292",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "18.80",
    "coords": {
      "lat": "48.0617",
      "lon": "12.2333"
    },
    "district": "Rosenheim",
    "name": "Wasserburg am Inn",
    "population": "12691",
    "state": "Bavaria"
  },
  {
    "area": "53.58",
    "coords": {
      "lat": "49.033",
      "lon": "10.600"
    },
    "district": "Ansbach",
    "name": "Wassertrüdingen",
    "population": "6041",
    "state": "Bavaria"
  },
  {
    "area": "89.09",
    "coords": {
      "lat": "50.66667",
      "lon": "10.36667"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Wasungen",
    "population": "5541",
    "state": "Thuringia"
  },
  {
    "area": "33.82",
    "coords": {
      "lat": "53.583",
      "lon": "9.700"
    },
    "district": "Pinneberg",
    "name": "Wedel",
    "population": "33547",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "81.24",
    "coords": {
      "lat": "53.16917",
      "lon": "7.35639"
    },
    "district": "Leer",
    "name": "Weener",
    "population": "15842",
    "state": "Lower Saxony"
  },
  {
    "area": "84",
    "coords": {
      "lat": "51.14167",
      "lon": "6.27917"
    },
    "district": "Heinsberg",
    "name": "Wegberg",
    "population": "28175",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "51.76",
    "coords": {
      "lat": "51.867",
      "lon": "11.167"
    },
    "district": "Harz",
    "name": "Wegeleben",
    "population": "2507",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "35.66",
    "coords": {
      "lat": "47.62972",
      "lon": "7.90444"
    },
    "district": "Waldshut",
    "name": "Wehr",
    "population": "13098",
    "state": "Baden-Württemberg"
  },
  {
    "area": "36.48",
    "coords": {
      "lat": "50.77500",
      "lon": "12.05694"
    },
    "district": "Greiz",
    "name": "Weida",
    "population": "8472",
    "state": "Thuringia"
  },
  {
    "area": "68.50",
    "coords": {
      "lat": "49.667",
      "lon": "12.150"
    },
    "district": "Urban district",
    "name": "Weiden in der Oberpfalz",
    "population": "42520",
    "state": "Bavaria"
  },
  {
    "area": "80.92",
    "coords": {
      "lat": "49.48167",
      "lon": "9.89917"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Weikersheim",
    "population": "7333",
    "state": "Baden-Württemberg"
  },
  {
    "area": "19.47",
    "coords": {
      "lat": "47.59472",
      "lon": "7.61083"
    },
    "district": "Lörrach",
    "name": "Weil am Rhein, Germany",
    "population": "30175",
    "state": "Baden-Württemberg"
  },
  {
    "area": "43.17",
    "coords": {
      "lat": "48.75083",
      "lon": "8.87056"
    },
    "district": "Böblingen",
    "name": "Weil der Stadt",
    "population": "19205",
    "state": "Baden-Württemberg"
  },
  {
    "area": "57.45",
    "coords": {
      "lat": "50.483",
      "lon": "8.250"
    },
    "district": "Limburg-Weilburg",
    "name": "Weilburg",
    "population": "12990",
    "state": "Hesse"
  },
  {
    "area": "26.51",
    "coords": {
      "lat": "48.61500",
      "lon": "9.53861"
    },
    "district": "Esslingen",
    "name": "Weilheim an der Teck",
    "population": "10275",
    "state": "Baden-Württemberg"
  },
  {
    "area": "55.44",
    "coords": {
      "lat": "47.833",
      "lon": "11.150"
    },
    "district": "Weilheim-Schongau",
    "name": "Weilheim in Oberbayern",
    "population": "22477",
    "state": "Bavaria"
  },
  {
    "area": "84.48",
    "coords": {
      "lat": "50.98333",
      "lon": "11.31667"
    },
    "district": "Urban district",
    "name": "Weimar",
    "population": "65090",
    "state": "Thuringia"
  },
  {
    "area": "12.17",
    "coords": {
      "lat": "47.80917",
      "lon": "9.64444"
    },
    "district": "Ravensburg",
    "name": "Weingarten",
    "population": "24943",
    "state": "Baden-Württemberg"
  },
  {
    "area": "58.11",
    "coords": {
      "lat": "49.550",
      "lon": "8.667"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Weinheim",
    "population": "45284",
    "state": "Baden-Württemberg"
  },
  {
    "area": "22.22",
    "coords": {
      "lat": "49.151806",
      "lon": "9.285694"
    },
    "district": "Heilbronn",
    "name": "Weinsberg",
    "population": "12336",
    "state": "Baden-Württemberg"
  },
  {
    "area": "31.71",
    "coords": {
      "lat": "48.81111",
      "lon": "9.36556"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Weinstadt",
    "population": "26987",
    "state": "Baden-Württemberg"
  },
  {
    "area": "90.14",
    "coords": {
      "lat": "50.067",
      "lon": "11.217"
    },
    "district": "Lichtenfels",
    "name": "Weismain",
    "population": "4783",
    "state": "Bavaria"
  },
  {
    "area": "34.4",
    "coords": {
      "lat": "49.900",
      "lon": "8.600"
    },
    "district": "Darmstadt-Dieburg",
    "name": "Weiterstadt",
    "population": "25975",
    "state": "Hesse"
  },
  {
    "area": "50.92",
    "coords": {
      "lat": "51.19694",
      "lon": "14.65944"
    },
    "district": "Bautzen",
    "name": "Weißenberg/Wóspork",
    "population": "3133",
    "state": "Saxony"
  },
  {
    "area": "97.55",
    "coords": {
      "lat": "49.03056",
      "lon": "10.97194"
    },
    "district": "Weißenburg-Gunzenhausen",
    "name": "Weißenburg in Bayern",
    "population": "18464",
    "state": "Bavaria"
  },
  {
    "area": "113.51",
    "coords": {
      "lat": "51.200",
      "lon": "11.967"
    },
    "district": "Burgenlandkreis",
    "name": "Weißenfels",
    "population": "40409",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "53.69",
    "coords": {
      "lat": "48.300",
      "lon": "10.167"
    },
    "district": "Neu-Ulm",
    "name": "Weißenhorn",
    "population": "13442",
    "state": "Bavaria"
  },
  {
    "area": "55.33",
    "coords": {
      "lat": "51.18333",
      "lon": "11.06667"
    },
    "district": "Sömmerda",
    "name": "Weißensee",
    "population": "3719",
    "state": "Thuringia"
  },
  {
    "area": "42.23",
    "coords": {
      "lat": "50.10083",
      "lon": "11.88472"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Weißenstadt",
    "population": "3113",
    "state": "Bavaria"
  },
  {
    "area": "3.99",
    "coords": {
      "lat": "50.41444",
      "lon": "7.46056"
    },
    "district": "Mayen-Koblenz",
    "name": "Weißenthurm",
    "population": "9115",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "63.60",
    "coords": {
      "lat": "51.500",
      "lon": "14.633"
    },
    "district": "Görlitz",
    "name": "Weißwasser",
    "population": "16130",
    "state": "Saxony"
  },
  {
    "area": "37.99",
    "coords": {
      "lat": "48.87472",
      "lon": "9.63444"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Welzheim",
    "population": "11176",
    "state": "Baden-Württemberg"
  },
  {
    "area": "23.91",
    "coords": {
      "lat": "51.56667",
      "lon": "14.18306"
    },
    "district": "Spree-Neiße",
    "name": "Welzow/Wjelcej",
    "population": "3418",
    "state": "Brandenburg"
  },
  {
    "area": "31.69",
    "coords": {
      "lat": "48.867",
      "lon": "10.717"
    },
    "district": "Donau-Ries",
    "name": "Wemding",
    "population": "5802",
    "state": "Bavaria"
  },
  {
    "area": "12.15",
    "coords": {
      "lat": "48.67472",
      "lon": "9.38167"
    },
    "district": "Esslingen",
    "name": "Wendlingen",
    "population": "16268",
    "state": "Baden-Württemberg"
  },
  {
    "area": "53.39",
    "coords": {
      "lat": "52.867",
      "lon": "11.967"
    },
    "district": "Stendal",
    "name": "Werben",
    "population": "1051",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "65.60",
    "coords": {
      "lat": "50.733",
      "lon": "12.383"
    },
    "district": "Zwickau",
    "name": "Werdau",
    "population": "20793",
    "state": "Saxony"
  },
  {
    "area": "115.99",
    "coords": {
      "lat": "52.37806",
      "lon": "12.93500"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Werder (Havel)",
    "population": "26184",
    "state": "Brandenburg"
  },
  {
    "area": "33.35",
    "coords": {
      "lat": "51.267",
      "lon": "7.767"
    },
    "district": "Märkischer Kreis",
    "name": "Werdohl",
    "population": "17737",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "63.75",
    "coords": {
      "lat": "52.850",
      "lon": "7.683"
    },
    "district": "Emsland",
    "name": "Werlte",
    "population": "10260",
    "state": "Lower Saxony"
  },
  {
    "area": "76.24",
    "coords": {
      "lat": "51.55000",
      "lon": "7.92000"
    },
    "district": "Soest",
    "name": "Werl",
    "population": "30772",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "74.66",
    "coords": {
      "lat": "51.150",
      "lon": "7.217"
    },
    "district": "Rheinisch-Bergischer Kreis",
    "name": "Wermelskirchen",
    "population": "34765",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "10.90",
    "coords": {
      "lat": "48.68861",
      "lon": "9.42222"
    },
    "district": "Esslingen",
    "name": "Wernau",
    "population": "12324",
    "state": "Baden-Württemberg"
  },
  {
    "area": "116.34",
    "coords": {
      "lat": "52.63306",
      "lon": "13.73306"
    },
    "district": "Barnim",
    "name": "Werneuchen",
    "population": "8994",
    "state": "Brandenburg"
  },
  {
    "area": "76.08",
    "coords": {
      "lat": "51.667",
      "lon": "7.617"
    },
    "district": "Unna",
    "name": "Werne",
    "population": "29662",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "170.03",
    "coords": {
      "lat": "51.83500",
      "lon": "10.78528"
    },
    "district": "Harz",
    "name": "Wernigerode",
    "population": "32733",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "77.67",
    "coords": {
      "lat": "50.933",
      "lon": "10.067"
    },
    "district": "Wartburgkreis",
    "name": "Werra-Suhl-Tal",
    "population": "6411",
    "state": "Thuringia"
  },
  {
    "area": "138.63",
    "coords": {
      "lat": "49.75889",
      "lon": "9.51750"
    },
    "district": "Main-Tauber-Kreis",
    "name": "Wertheim",
    "population": "22780",
    "state": "Baden-Württemberg"
  },
  {
    "area": "35.31",
    "coords": {
      "lat": "52.06667",
      "lon": "8.41667"
    },
    "district": "Gütersloh",
    "name": "Werther (Westf.)",
    "population": "11274",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "51.80",
    "coords": {
      "lat": "48.533",
      "lon": "10.667"
    },
    "district": "Dillingen",
    "name": "Wertingen",
    "population": "9294",
    "state": "Bavaria"
  },
  {
    "area": "122.617",
    "coords": {
      "lat": "51.65861",
      "lon": "6.61778"
    },
    "district": "Wesel",
    "name": "Wesel",
    "population": "60357",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "89.43",
    "coords": {
      "lat": "53.267",
      "lon": "12.967"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Wesenberg",
    "population": "3020",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "5.14",
    "coords": {
      "lat": "54.217",
      "lon": "8.917"
    },
    "district": "Dithmarschen",
    "name": "Wesselburen",
    "population": "3372",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "23.4",
    "coords": {
      "lat": "50.817",
      "lon": "6.967"
    },
    "district": "Rhein-Erft-Kreis",
    "name": "Wesseling",
    "population": "36146",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "18.48",
    "coords": {
      "lat": "50.56389",
      "lon": "7.97250"
    },
    "district": "Westerwaldkreis",
    "name": "Westerburg",
    "population": "5666",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "179",
    "coords": {
      "lat": "53.250",
      "lon": "7.917"
    },
    "district": "Ammerland",
    "name": "Westerstede",
    "population": "22778",
    "state": "Lower Saxony"
  },
  {
    "area": "104.56",
    "coords": {
      "lat": "50.88333",
      "lon": "8.71667"
    },
    "district": "Marburg-Biedenkopf",
    "name": "Wetter (Hessen)",
    "population": "8718",
    "state": "Hesse"
  },
  {
    "area": "31.47",
    "coords": {
      "lat": "51.38806",
      "lon": "7.39500"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Wetter",
    "population": "27441",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "126.94",
    "coords": {
      "lat": "51.633",
      "lon": "11.900"
    },
    "district": "Saalekreis",
    "name": "Wettin-Löbejün",
    "population": "9828",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "75.67",
    "coords": {
      "lat": "50.567",
      "lon": "8.500"
    },
    "district": "Lahn-Dill-Kreis",
    "name": "Wetzlar",
    "population": "52954",
    "state": "Hesse"
  },
  {
    "area": "25.23",
    "coords": {
      "lat": "49.317",
      "lon": "9.417"
    },
    "district": "Heilbronn",
    "name": "Widdern",
    "population": "1809",
    "state": "Baden-Württemberg"
  },
  {
    "area": "53.27",
    "coords": {
      "lat": "50.950",
      "lon": "7.533"
    },
    "district": "Oberbergischer Kreis",
    "name": "Wiehl",
    "population": "25135",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "203.9",
    "coords": {
      "lat": "50.08250",
      "lon": "8.24000"
    },
    "district": "Urban district",
    "name": "Wiesbaden",
    "population": "278342",
    "state": "Hesse"
  },
  {
    "area": "23.40",
    "coords": {
      "lat": "48.56167",
      "lon": "9.62528"
    },
    "district": "Göppingen",
    "name": "Wiesensteig",
    "population": "2065",
    "state": "Baden-Württemberg"
  },
  {
    "area": "30.262",
    "coords": {
      "lat": "49.29417",
      "lon": "8.69833"
    },
    "district": "Rhein-Neckar-Kreis",
    "name": "Wiesloch",
    "population": "26758",
    "state": "Baden-Württemberg"
  },
  {
    "area": "82.99",
    "coords": {
      "lat": "53.400",
      "lon": "7.733"
    },
    "district": "Aurich",
    "name": "Wiesmoor",
    "population": "13141",
    "state": "Lower Saxony"
  },
  {
    "area": "9.09",
    "coords": {
      "lat": "52.317",
      "lon": "13.633"
    },
    "district": "Dahme-Spreewald",
    "name": "Wildau",
    "population": "10303",
    "state": "Brandenburg"
  },
  {
    "area": "56.68",
    "coords": {
      "lat": "48.62389",
      "lon": "8.74722"
    },
    "district": "Calw",
    "name": "Wildberg",
    "population": "10069",
    "state": "Baden-Württemberg"
  },
  {
    "area": "20.57",
    "coords": {
      "lat": "50.65750",
      "lon": "12.58278"
    },
    "district": "Zwickau",
    "name": "Wildenfels",
    "population": "3583",
    "state": "Saxony"
  },
  {
    "area": "89.47",
    "coords": {
      "lat": "52.900",
      "lon": "8.433"
    },
    "district": "Oldenburg",
    "name": "Wildeshausen",
    "population": "19932",
    "state": "Lower Saxony"
  },
  {
    "area": "106.91",
    "coords": {
      "lat": "53.51667",
      "lon": "8.13333"
    },
    "district": "Urban district",
    "name": "Wilhelmshaven",
    "population": "76278",
    "state": "Lower Saxony"
  },
  {
    "area": "12.70",
    "coords": {
      "lat": "50.667",
      "lon": "12.517"
    },
    "district": "Zwickau",
    "name": "Wilkau-Haßlau",
    "population": "9784",
    "state": "Saxony"
  },
  {
    "area": "128.13",
    "coords": {
      "lat": "51.63306",
      "lon": "9.03306"
    },
    "district": "Höxter",
    "name": "Willebadessen",
    "population": "8142",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "67.77",
    "coords": {
      "lat": "51.26306",
      "lon": "6.54917"
    },
    "district": "Viersen",
    "name": "Willich",
    "population": "50592",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "81.69",
    "coords": {
      "lat": "51.05222",
      "lon": "13.53833"
    },
    "district": "Sächsische Schweiz-Osterzgebirge",
    "name": "Wilsdruff",
    "population": "14217",
    "state": "Saxony"
  },
  {
    "area": "2.71",
    "coords": {
      "lat": "53.92250",
      "lon": "9.37444"
    },
    "district": "Steinburg",
    "name": "Wilster",
    "population": "4308",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "17.06",
    "coords": {
      "lat": "51.100",
      "lon": "14.400"
    },
    "district": "Bautzen",
    "name": "Wilthen",
    "population": "4921",
    "state": "Saxony"
  },
  {
    "area": "36.38",
    "coords": {
      "lat": "49.800",
      "lon": "12.167"
    },
    "district": "Neustadt a.d.Waldnaab",
    "name": "Windischeschenbach",
    "population": "4960",
    "state": "Bavaria"
  },
  {
    "area": "68.17",
    "coords": {
      "lat": "49.250",
      "lon": "10.817"
    },
    "district": "Ansbach",
    "name": "Windsbach",
    "population": "6018",
    "state": "Bavaria"
  },
  {
    "area": "28.05",
    "coords": {
      "lat": "48.87639",
      "lon": "9.39778"
    },
    "district": "Rems-Murr-Kreis",
    "name": "Winnenden",
    "population": "28339",
    "state": "Baden-Württemberg"
  },
  {
    "area": "109.55",
    "coords": {
      "lat": "53.367",
      "lon": "10.217"
    },
    "district": "Harburg",
    "name": "Winsen",
    "population": "34896",
    "state": "Lower Saxony"
  },
  {
    "area": "147.86",
    "coords": {
      "lat": "51.200",
      "lon": "8.517"
    },
    "district": "Hochsauerlandkreis",
    "name": "Winterberg",
    "population": "12611",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "118.16",
    "coords": {
      "lat": "51.11667",
      "lon": "7.40000"
    },
    "district": "Oberbergischer Kreis",
    "name": "Wipperfürth",
    "population": "21003",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "10.13",
    "coords": {
      "lat": "50.47417",
      "lon": "7.79528"
    },
    "district": "Westerwaldkreis",
    "name": "Wirges",
    "population": "5341",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "41.36",
    "coords": {
      "lat": "53.900",
      "lon": "11.467"
    },
    "district": "Nordwestmecklenburg",
    "name": "Wismar",
    "population": "42550",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "34.88",
    "coords": {
      "lat": "50.78250",
      "lon": "7.73500"
    },
    "district": "Altenkirchen (Westerwald)",
    "name": "Wissen",
    "population": "8354",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "50.44",
    "coords": {
      "lat": "53.000",
      "lon": "11.750"
    },
    "district": "Prignitz",
    "name": "Wittenberge",
    "population": "17015",
    "state": "Brandenburg"
  },
  {
    "area": "240.32",
    "coords": {
      "lat": "51.8671",
      "lon": "12.6484"
    },
    "district": "Wittenberg",
    "name": "Wittenberg",
    "population": "46008",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "80.00",
    "coords": {
      "lat": "53.500",
      "lon": "11.067"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Wittenburg",
    "population": "6313",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "72.40",
    "coords": {
      "lat": "51.43333",
      "lon": "7.33333"
    },
    "district": "Ennepe-Ruhr-Kreis",
    "name": "Witten",
    "population": "96563",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "62.24",
    "coords": {
      "lat": "51.383",
      "lon": "14.250"
    },
    "district": "Bautzen",
    "name": "Wittichenau/Kulow",
    "population": "5715",
    "state": "Saxony"
  },
  {
    "area": "225.08",
    "coords": {
      "lat": "52.717",
      "lon": "10.733"
    },
    "district": "Gifhorn",
    "name": "Wittingen",
    "population": "11503",
    "state": "Lower Saxony"
  },
  {
    "area": "49.64",
    "coords": {
      "lat": "49.98694",
      "lon": "6.88972"
    },
    "district": "Bernkastel-Wittlich",
    "name": "Wittlich",
    "population": "18995",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "210",
    "coords": {
      "lat": "53.57500",
      "lon": "7.78083"
    },
    "district": "Wittmund",
    "name": "Wittmund",
    "population": "20321",
    "state": "Lower Saxony"
  },
  {
    "area": "417.20",
    "coords": {
      "lat": "53.16361",
      "lon": "12.48556"
    },
    "district": "Ostprignitz-Ruppin",
    "name": "Wittstock",
    "population": "14198",
    "state": "Brandenburg"
  },
  {
    "area": "126.69",
    "coords": {
      "lat": "51.34222",
      "lon": "9.85778"
    },
    "district": "Werra-Meißner-Kreis",
    "name": "Witzenhausen, Germany",
    "population": "15167",
    "state": "Hesse"
  },
  {
    "area": "164.14",
    "coords": {
      "lat": "53.45944",
      "lon": "13.58278"
    },
    "district": "Mecklenburgische Seenplatte",
    "name": "Woldegk",
    "population": "4392",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "67.99",
    "coords": {
      "lat": "48.300",
      "lon": "8.217"
    },
    "district": "Ortenaukreis",
    "name": "Wolfach",
    "population": "5850",
    "state": "Baden-Württemberg"
  },
  {
    "area": "78.46",
    "coords": {
      "lat": "52.16222",
      "lon": "10.53694"
    },
    "district": "Wolfenbüttel",
    "name": "Wolfenbüttel",
    "population": "52174",
    "state": "Lower Saxony"
  },
  {
    "area": "111.95",
    "coords": {
      "lat": "51.317",
      "lon": "9.167"
    },
    "district": "Kassel",
    "name": "Wolfhagen",
    "population": "13059",
    "state": "Hesse"
  },
  {
    "area": "25.47",
    "coords": {
      "lat": "49.233",
      "lon": "10.733"
    },
    "district": "Ansbach",
    "name": "Wolframs-Eschenbach",
    "population": "3116",
    "state": "Bavaria"
  },
  {
    "area": "9.13",
    "coords": {
      "lat": "47.91333",
      "lon": "11.42778"
    },
    "district": "Bad Tölz-Wolfratshausen",
    "name": "Wolfratshausen",
    "population": "18836",
    "state": "Bavaria"
  },
  {
    "area": "204.02",
    "coords": {
      "lat": "52.42306",
      "lon": "10.78722"
    },
    "district": "Urban district",
    "name": "Wolfsburg",
    "population": "124151",
    "state": "Lower Saxony"
  },
  {
    "area": "13.75",
    "coords": {
      "lat": "49.58417",
      "lon": "7.60611"
    },
    "district": "Kusel",
    "name": "Wolfstein",
    "population": "1919",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "61.53",
    "coords": {
      "lat": "54.050",
      "lon": "13.767"
    },
    "district": "Vorpommern-Greifswald",
    "name": "Wolgast",
    "population": "12028",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "30.51",
    "coords": {
      "lat": "50.65444",
      "lon": "13.07472"
    },
    "district": "Erzgebirgskreis",
    "name": "Wolkenstein",
    "population": "3907",
    "state": "Saxony"
  },
  {
    "area": "54.28",
    "coords": {
      "lat": "52.25194",
      "lon": "11.62972"
    },
    "district": "Börde",
    "name": "Wolmirstedt",
    "population": "11536",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "108.73",
    "coords": {
      "lat": "49.63194",
      "lon": "8.36528"
    },
    "district": "Urban district",
    "name": "Worms",
    "population": "83330",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "94.54",
    "coords": {
      "lat": "52.71667",
      "lon": "14.13306"
    },
    "district": "Märkisch-Oderland",
    "name": "Wriezen",
    "population": "7254",
    "state": "Brandenburg"
  },
  {
    "area": "54.91",
    "coords": {
      "lat": "50.017",
      "lon": "12.017"
    },
    "district": "Wunsiedel im Fichtelgebirge",
    "name": "Wunsiedel",
    "population": "9259",
    "state": "Bavaria"
  },
  {
    "area": "126.60",
    "coords": {
      "lat": "52.42750",
      "lon": "9.42944"
    },
    "district": "Hanover",
    "name": "Wunstorf",
    "population": "41594",
    "state": "Lower Saxony"
  },
  {
    "area": "168.41",
    "coords": {
      "lat": "51.26667",
      "lon": "7.18333"
    },
    "district": "Urban district",
    "name": "Wuppertal",
    "population": "354382",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "72.31",
    "coords": {
      "lat": "50.46389",
      "lon": "11.53667"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Wurzbach",
    "population": "3098",
    "state": "Thuringia"
  },
  {
    "area": "68.54",
    "coords": {
      "lat": "51.367",
      "lon": "12.717"
    },
    "district": "Leipzig",
    "name": "Wurzen",
    "population": "16154",
    "state": "Saxony"
  },
  {
    "area": "29.90",
    "coords": {
      "lat": "52.92194",
      "lon": "11.12167"
    },
    "district": "Lüchow-Dannenberg",
    "name": "Wustrow",
    "population": "2788",
    "state": "Lower Saxony"
  },
  {
    "area": "8",
    "coords": {
      "lat": "54.700",
      "lon": "8.567"
    },
    "district": "Nordfriesland",
    "name": "Wyk auf Föhra Wik / Vyk",
    "population": "4218",
    "state": "Schleswig-Holstein"
  },
  {
    "area": "50.79",
    "coords": {
      "lat": "50.26667",
      "lon": "9.30000"
    },
    "district": "Main-Kinzig-Kreis",
    "name": "Wächtersbach",
    "population": "12542",
    "state": "Hesse"
  },
  {
    "area": "16.75",
    "coords": {
      "lat": "49.84306",
      "lon": "8.11556"
    },
    "district": "Alzey-Worms",
    "name": "Wörrstadt",
    "population": "8027",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "15.89",
    "coords": {
      "lat": "49.79639",
      "lon": "9.15750"
    },
    "district": "Miltenberg",
    "name": "Wörth am Main",
    "population": "4683",
    "state": "Bavaria"
  },
  {
    "area": "131.64",
    "coords": {
      "lat": "49.05167",
      "lon": "8.26028"
    },
    "district": "Germersheim",
    "name": "Wörth am Rhein",
    "population": "18123",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "52.34",
    "coords": {
      "lat": "49.00083",
      "lon": "12.40111"
    },
    "district": "Regensburg",
    "name": "Wörth an der Donau",
    "population": "4918",
    "state": "Bavaria"
  },
  {
    "area": "32.23",
    "coords": {
      "lat": "51.28333",
      "lon": "7.03333"
    },
    "district": "Mettmann",
    "name": "Wülfrath",
    "population": "21035",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "34.385",
    "coords": {
      "lat": "50.817",
      "lon": "6.133"
    },
    "district": "Aachen",
    "name": "Würselen",
    "population": "38712",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "87.63",
    "coords": {
      "lat": "49.783",
      "lon": "9.933"
    },
    "district": "Urban district",
    "name": "Würzburg",
    "population": "127880",
    "state": "Bavaria"
  },
  {
    "area": "72.39",
    "coords": {
      "lat": "51.66222",
      "lon": "6.45389"
    },
    "district": "Wesel",
    "name": "Xanten",
    "population": "21690",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "148.49",
    "coords": {
      "lat": "51.79917",
      "lon": "12.40694"
    },
    "district": "Wittenberg",
    "name": "Zahna-Elster",
    "population": "9216",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "91.89",
    "coords": {
      "lat": "53.533",
      "lon": "10.917"
    },
    "district": "Ludwigslust-Parchim",
    "name": "Zarrentin am Schaalsee",
    "population": "5192",
    "state": "Mecklenburg-Western Pomerania"
  },
  {
    "area": "221.52",
    "coords": {
      "lat": "52.983",
      "lon": "13.333"
    },
    "district": "Oberhavel",
    "name": "Zehdenick",
    "population": "13437",
    "state": "Brandenburg"
  },
  {
    "area": "35.74",
    "coords": {
      "lat": "50.017",
      "lon": "10.600"
    },
    "district": "Haßberge",
    "name": "Zeil am Main",
    "population": "5561",
    "state": "Bavaria"
  },
  {
    "area": "87.15",
    "coords": {
      "lat": "51.04778",
      "lon": "12.13833"
    },
    "district": "Burgenlandkreis",
    "name": "Zeitz",
    "population": "27955",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "36.43",
    "coords": {
      "lat": "48.34667",
      "lon": "8.06389"
    },
    "district": "Ortenaukreis",
    "name": "Zell am Harmersbach",
    "population": "8112",
    "state": "Baden-Württemberg"
  },
  {
    "area": "36.13",
    "coords": {
      "lat": "47.70694",
      "lon": "7.85139"
    },
    "district": "Lörrach",
    "name": "Zell im Wiesental",
    "population": "6325",
    "state": "Baden-Württemberg"
  },
  {
    "area": "52.99",
    "coords": {
      "lat": "50.65972",
      "lon": "10.66694"
    },
    "district": "Schmalkalden-Meiningen",
    "name": "Zella-Mehlis",
    "population": "12863",
    "state": "Thuringia"
  },
  {
    "area": "44.98",
    "coords": {
      "lat": "50.02639",
      "lon": "7.18361"
    },
    "district": "Cochem-Zell",
    "name": "Zell",
    "population": "4099",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "467.65",
    "coords": {
      "lat": "51.96806",
      "lon": "12.08444"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Zerbst",
    "population": "21657",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "134.72",
    "coords": {
      "lat": "50.64861",
      "lon": "11.98056"
    },
    "district": "Greiz",
    "name": "Zeulenroda-Triebes",
    "population": "16487",
    "state": "Thuringia"
  },
  {
    "area": "73.9",
    "coords": {
      "lat": "53.30000",
      "lon": "9.28333"
    },
    "district": "Rotenburg",
    "name": "Zeven",
    "population": "13809",
    "state": "Lower Saxony"
  },
  {
    "area": "8.24",
    "coords": {
      "lat": "50.61667",
      "lon": "11.65000"
    },
    "district": "Saale-Orla-Kreis",
    "name": "Ziegenrück",
    "population": "657",
    "state": "Thuringia"
  },
  {
    "area": "86.53",
    "coords": {
      "lat": "51.317",
      "lon": "9.167"
    },
    "district": "Kassel",
    "name": "Zierenberg",
    "population": "6592",
    "state": "Hesse"
  },
  {
    "area": "67.46",
    "coords": {
      "lat": "52.26667",
      "lon": "12.28306"
    },
    "district": "Potsdam-Mittelmark",
    "name": "Ziesar",
    "population": "2443",
    "state": "Brandenburg"
  },
  {
    "area": "28.78",
    "coords": {
      "lat": "49.450",
      "lon": "10.950"
    },
    "district": "Fürth",
    "name": "Zirndorf",
    "population": "25596",
    "state": "Bavaria"
  },
  {
    "area": "66.74",
    "coords": {
      "lat": "50.89611",
      "lon": "14.80722"
    },
    "district": "Görlitz",
    "name": "Zittau",
    "population": "25381",
    "state": "Saxony"
  },
  {
    "area": "179.57",
    "coords": {
      "lat": "52.21667",
      "lon": "13.44972"
    },
    "district": "Teltow-Fläming",
    "name": "Zossen",
    "population": "19403",
    "state": "Brandenburg"
  },
  {
    "area": "22.88",
    "coords": {
      "lat": "50.750",
      "lon": "13.067"
    },
    "district": "Erzgebirgskreis",
    "name": "Zschopau",
    "population": "9214",
    "state": "Saxony"
  },
  {
    "area": "70.64",
    "coords": {
      "lat": "49.250",
      "lon": "7.367"
    },
    "district": "Urban district",
    "name": "Zweibrücken",
    "population": "34209",
    "state": "Rhineland-Palatinate"
  },
  {
    "area": "46.21",
    "coords": {
      "lat": "51.21750",
      "lon": "12.32417"
    },
    "district": "Leipzig",
    "name": "Zwenkau",
    "population": "9274",
    "state": "Saxony"
  },
  {
    "area": "102.54",
    "coords": {
      "lat": "50.717",
      "lon": "12.500"
    },
    "district": "Zwickau",
    "name": "Zwickau",
    "population": "89540",
    "state": "Saxony"
  },
  {
    "area": "41.14",
    "coords": {
      "lat": "49.017",
      "lon": "13.233"
    },
    "district": "Regen",
    "name": "Zwiesel",
    "population": "9421",
    "state": "Bavaria"
  },
  {
    "area": "5.66",
    "coords": {
      "lat": "49.71667",
      "lon": "8.61667"
    },
    "district": "Bergstraße",
    "name": "Zwingenberg",
    "population": "7171",
    "state": "Hesse"
  },
  {
    "area": "64.18",
    "coords": {
      "lat": "50.61667",
      "lon": "12.80000"
    },
    "district": "Erzgebirgskreis",
    "name": "Zwönitz",
    "population": "11993",
    "state": "Saxony"
  },
  {
    "area": "113.26",
    "coords": {
      "lat": "51.61667",
      "lon": "12.11667"
    },
    "district": "Anhalt-Bitterfeld",
    "name": "Zörbig",
    "population": "9216",
    "state": "Saxony-Anhalt"
  },
  {
    "area": "101",
    "coords": {
      "lat": "50.70000",
      "lon": "6.65000"
    },
    "district": "Euskirchen",
    "name": "Zülpich",
    "population": "20174",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "67.79",
    "coords": {
      "lat": "49.200",
      "lon": "9.500"
    },
    "district": "Hohenlohekreis",
    "name": "Öhringen",
    "population": "24374",
    "state": "Baden-Württemberg"
  },
  {
    "area": "53.23",
    "coords": {
      "lat": "49.21944",
      "lon": "8.71083"
    },
    "district": "Karlsruhe",
    "name": "Östringen",
    "population": "13015",
    "state": "Baden-Württemberg"
  },
  {
    "area": "26.11",
    "coords": {
      "lat": "50.91972",
      "lon": "6.11944"
    },
    "district": "Heinsberg",
    "name": "Übach-Palenberg",
    "population": "24081",
    "state": "North Rhine-Westphalia"
  },
  {
    "area": "58.67",
    "coords": {
      "lat": "47.76667",
      "lon": "9.15833"
    },
    "district": "Bodenseekreis",
    "name": "Überlingen",
    "population": "22554",
    "state": "Baden-Württemberg"
  }
]';
}

<?php

namespace App\Console\Commands;

use DB;
use App\User;
use App\Country;
use Illuminate\Console\Command;

class ImportCountriesFromJSON extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:countries_from_json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import countries from JSON';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = json_decode(file_get_contents(storage_path('app/countries.geojson')));
        foreach ($data->features as $row) {
            $country = Country::where([
                'name' => $row->properties->ADMIN,
            ])->first();

            if ($country) {
                DB::table('countries_polygons')->whereCountryId($country->id)->delete();
                if ($row->geometry->type == 'Polygon') {
                    DB::table('countries_polygons')->where('id', $country->id)->insert([
                        'country_id' => $country->id,
                        'polygon'    => DB::raw("ST_TRANSFORM(ST_GeomFromGeoJSON('" . json_encode($row->geometry) . "'), 4326)"),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($row->geometry->type == 'MultiPolygon') {
                    foreach ($row->geometry->coordinates as $group) {
                        try {
                            DB::table('countries_polygons')->where('id', $country->id)->insert([
                                'country_id' => $country->id,
                                'polygon'    => DB::raw("ST_TRANSFORM(ST_GeomFromGeoJSON('" . json_encode(['type' => 'Polygon', 'coordinates' => $group]) . "'), 4326)"),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } catch (\Exception $e) {
                            $this->warn('Error: ' . $e->getMessage());
                        }
                    }
                } else {
                    $this->error('Unknown geometry type: ' . $row->geometry->type);
                }
            }
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Services\UrlService;
use App\Models\Page;
use App\Models\Tune;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index($para = "") {

        shell_exec('pwd');

        $checker = App::make('App\Services\UrlService');
        $checked = $checker->checkUrl($para);

        if (!$checked) {
            return redirect('');
        }

        ini_set('max_post_size', 0);

        if (!file_exists('storage/data/'.$para)) {
            //make it self destruct
            mkdir('storage/data/'.$para);
            chmod('storage/data/'.$para, 0777);
        }

        $para_a = $para == "" ? "" : $para."/";

        $tunes_ordered = glob('storage/data/'.$para_a.'*.mp3');
        usort($tunes_ordered, function($a, $b) {
            return filemtime($a) < filemtime($b);
        });

        $tunes = [];
        foreach ($tunes_ordered as $tune_ordered) {
            $tunes[] = str_replace('storage/data/'.$para_a, '', $tune_ordered);
        }

        $para = $para == "" ? '-' : $para;

        $t_string = implode(' ', $tunes);

        return view('dashboard', compact('t_string', 'para'));
    }

    public function upload(Request $request, $para) {

        $o_path = $_SERVER['SERVER_NAME'] === 'localhost' ? 'local/' : '';

        if ($para === '-' || $para === '') {
            $subdir = '';
        } else {
            $subdir = $para."/";
        }

        $file = $request->file('song');
        $typ = $file->getMimeType();

        /*
            .wav = 'audio/x-wav'
            .mp3 = 'audio/mpeg'
            .aif = 'audio/x-aiff'
        */
        if ($typ === 'audio/mpeg' || $typ === 'audio/x-wav' || $typ === 'audio/x-aiff') {

            //removes spaces in name
            $song_name = str_replace(" ", "_", $file->getClientOriginalName());

            $path = $request->file('song')->store('upload');

            $file->move('storage/data/'.$subdir, $song_name);

            $path_bare = substr($path, 7);
            $path_full = '../storage/data/'.$subdir.$path_bare;

            $path = 'storage/data/';


            //mp3 compression
            if ($typ === 'audio/mpeg') {
                exec('/usr/'.$o_path.'bin/ffmpeg -i '.$path.$subdir.$song_name.' -ab 110k '.$path.$subdir.'_'.$song_name, $o, $r);

                //deletes raw if compression worked
                if ($r === 0) {
                    unlink($path.$subdir.$song_name);
                }

                //wav conversion
            } elseif ($typ === 'audio/x-wav') {
                exec('/usr/'.$o_path.'bin/ffmpeg -i '.$path.$subdir.$song_name.' -f mp3 '.$path.$subdir.'_'.str_replace('.wav', '', $song_name).'.mp3', $oc, $rc);

                //deletes raw if conversion worked
                if ($rc === 0) {
                    unlink($path.$subdir.$song_name);
                }
            } elseif ($typ === 'audio/x-aiff') {

                // ffmpeg -i sauce.aif -f mp3 -acodec libmp3lame -ab 192000 -ar 44100 sauce.mp3
                exec('/usr/'.$o_path.'bin/ffmpeg -i '.$path.$subdir.$song_name.' -f mp3 -acodec libmp3lame -ab 192000 -ar 44100 '.$path.$subdir.'_'.str_replace('.aif', '', $song_name).'.mp3', $ao, $ac);
                //deletes raw if conversion worked
                if ($ac === 0) {
                    unlink($path.$subdir.$song_name);
                }
            }



            //db record


            if(!Numbers::where('country',$country)->exists()){
                Numbers::Create([
                    'country'    => $country
                ]);
            }

            $tune = new Tune();
            $tune->name = $song_name;
            $tune->save();

            //how to do FKC?????



        } else {

            return redirect($para);
        }

        return redirect($para);
    }

    public function dl() {
        try {
            // return Storage::download(app_path('../public/storage/data/'.$_GET['song']));
            return Response()->download('storage/data/'.$_GET['song']);
        } catch (exception $e) {
            dump($e);
        }
    }

    public function delete() {

        $para = $_GET['para'] == '-' ? '' : $_GET['para'].'/';

        $markers = json_decode(file_get_contents('../public/data/markerData.json'), true);

        unset($markers[$_GET['song']]);

        try {
            unlink('storage/data/'.$para.$_GET['song']);
        } catch (exception $e) {
            //log exep
            return "";
        }
        return 'deleted';
    }

    public function getMarker() {

        if ($_GET['se'] === 's') {
            $which = 'start';
        } elseif ($_GET['se'] === 'e') {
            $which = 'end';
        } else {
            //throw exception
            $which = null;
        }

        $position = DB::table('tunes')->where([
            ['name', $_GET['name']],
        ])->get($which);

        return $position;
    }

    public function setMarker() {

        if ($_GET['se'] === 's') {
            $which = 'start';
        } elseif ($_GET['se'] === 'e') {
            $which = 'end';
        } else {
            $which = null;
        }

        $page_id = DB::table('pages')->where([
            ['name', isset($_GET['page']) ? $_GET['page'] : '/'],
        ])->get('id')->first()->id;

        DB::table('tunes')->where([
            ['name', $_GET['name']]
            ])->update(
            [
                $which => $_GET['value'],
                'page_id' => $page_id
            ]
        );
    }

    public function ctx() {

        $items = scandir('storage/data/');

        array_shift($items);
        array_shift($items);

        $tunes = [];
        foreach ($items as $item) {
            if (strpos($item, '.mp3') !== false) {
                $tunes[] = $item;
            }
        }

        $t_string = implode(' ', $tunes);

        return view('ctx', compact('t_string'));
    }

    public function phaser() {
        return view('phaser');
    }

    public function allpass() {
        return view('allpass');
    }


}

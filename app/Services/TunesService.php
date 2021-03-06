<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Tune;
use Illuminate\Support\Facades\Log;

class TunesService
{
    protected $appPath = '/home/u1050-a6xqjx3tg3dg/www/crackmaninoff.com';

    protected $songPath = '/public_html/public/storage/data/';

    protected $ffmpegPath = '/usr/bin/ffmpeg';

    /*
        .wav = 'audio/x-wav'
        .mp3 = 'audio/mpeg'
        .aif = 'audio/x-aiff'
    */

    public function upload($request, $para)
    {
        return $this->saveFile($request, $para);
    }

    public function logRecord($tune, $page)
    {
        try {
            Tune::create([
                'name' => $tune,
                'page_id' => Page::where('name', $page === '-' ? '/' : $page)->first()->id
            ])->save();

        } catch (exception $e) {
            Log::error($e->getMessage());
        }

        return true;
    }

    public function compressConvertFile($para, $song_name, $typ)
    {
        if ($para === '-' || $para === '') {
            $subdir = '';
        } else {
            $subdir = $para . "/";
        }

        $dir = env('APP_PATH') . env('SONG_PATH') . $subdir;
        $o_path = env('APP_PATH') . env('FFMPEG_PATH');

        //mp3 compression
        if ($typ === 'audio/mpeg') {

            exec($o_path . ' -i ' . $dir . $song_name . ' -ab 110k ' . $dir . '_' . $song_name, $o, $r);

            //deletes raw if compression worked
            if ($r === 0) {
                unlink($dir . $song_name);
            } else {
                Log::warning('mp3 compression for '.$song_name.' failed. Code: '.$r.' fullpath: '.$dir . $song_name);
                Log::info($o_path . ' -i ' . $dir . $song_name . ' -ab 110k ' . $dir . '_' . $song_name);
                return false;
            }

            //wav conversion
        } elseif ($typ === 'audio/x-wav') {
            exec($o_path. ' -i ' . $dir . $song_name . ' -f mp3 ' . $dir . '_' . str_replace('.wav', '', $song_name) . '.mp3', $oc, $rc);

            //deletes raw if conversion worked
            if ($rc === 0) {
                unlink($dir . $song_name);
            } else {
                Log::warning('wav conversion for '.$song_name.' failed');
                return false;
            }
        } elseif ($typ === 'audio/x-aiff') {

            // ffmpeg -i sauce.aif -f mp3 -acodec libmp3lame -ab 192000 -ar 44100 sauce.mp3
            exec($o_path. ' -i ' . $dir . $song_name . ' -f mp3 -acodec libmp3lame -ab 192000 -ar 44100 ' . $dir . '_' . str_replace('.aif', '', $song_name) . '.mp3', $ao, $ac);
            //deletes raw if conversion worked
            if ($ac === 0) {
                unlink($dir . $song_name);
            } else {
                Log::warning('aiff conversion for '.$song_name.' failed');
                return false;
            }
        }

        $this->logRecord('_'.$song_name, $para === '' ? '/' : $para);
        return true;

    }

    public function saveFile($request, $para)
    {
        $file = $request->file('song');
        $typ = $file->getMimeType();

        //make sure propper validation somewhere
        if ($typ === 'audio/mpeg' || $typ === 'audio/x-wav' || $typ === 'audio/x-aiff') {

            //removes spaces in name
            $song_name = str_replace(" ", "_", $file->getClientOriginalName());
            $request->file('song')->store('upload');
            $file->move(env('STORAGE_PATH') . ($para === '-' ? '' : $para), $song_name);
            if (!$this->compressConvertFile($para, $song_name, $typ)) {
                return false;
            }
        } else {
            Log::warning('wrong file type: '.$typ);
            return false;
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiBaselineTest extends TestCase
{
    public function test_class_dan_style_halaman_farel_tidak_berubah(): void
    {
        $raw = file_get_contents(base_path('tests/Fixtures/ui-baseline.json'));
        $baseline = json_decode(ltrim($raw, "\xEF\xBB\xBF"), true);
        $baris = [];

        foreach ($baseline as $file => $info) {
            $path = base_path($file);
            if (! is_file($path)) {
                $baris[] = "{$file} | DIHAPUS";
                continue;
            }
            $txt = file_get_contents($path);

            preg_match_all('/class="([^"]*)"/', $txt, $m);
            $now = [];
            foreach ($m[1] as $nilai) {
                $nilai = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}/s', ' ', $nilai);
                foreach (preg_split('/\s+/', trim($nilai)) as $t) {
                    if ($t !== '' && ! preg_match('/[{}$@]/', $t)) {
                        $now[$t] = true;
                    }
                }
            }
            $now = array_map('strval', array_keys($now));
            $lama = array_map('strval', (array) ($info['classes'] ?? []));
            $hilang = array_values(array_diff($lama, $now));
            $baru = array_values(array_diff($now, $lama));

            preg_match_all('/<style.*?<\/style>/s', $txt, $s);
            $style = preg_replace('/[^\x00-\x7F]/', '', preg_replace('/\s+/', ' ', implode('', $s[0])));
            $styleSama = sha1($style) === ($info['style'] ?? '');

            if ($hilang || $baru || ! $styleSama) {
                $baris[] = $file;
                $baris[] = '   style : '.($styleSama ? 'sama' : 'BERUBAH');
                $baris[] = '   hilang ('.count($hilang).'): '.implode(' ', array_slice($hilang, 0, 25));
                $baris[] = '   baru   ('.count($baru).'): '.implode(' ', array_slice($baru, 0, 25));
            }
        }

        $this->assertTrue($baris === [], "Tampilan berbeda dari versi Farel:\n".implode("\n", $baris));
    }
}

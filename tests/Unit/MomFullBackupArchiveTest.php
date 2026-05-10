<?php

use App\Services\Settings\MomFullBackupArchive;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

test('full backup archive round trips sqlite database, storage directories, and application tree', function (): void {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'momfb-'.uniqid('', true);
    File::ensureDirectoryExists($base.DIRECTORY_SEPARATOR.'pub');
    File::ensureDirectoryExists($base.DIRECTORY_SEPARATOR.'priv');
    File::ensureDirectoryExists($base.DIRECTORY_SEPARATOR.'app');
    File::ensureDirectoryExists($base.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'pkg');
    File::put($base.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'note.txt', 'hello');
    File::put($base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'marker.txt', 'code');
    File::put($base.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'pkg'.DIRECTORY_SEPARATOR.'npm.txt', 'dep');

    $dbLive = $base.DIRECTORY_SEPARATOR.'live.sqlite';
    $pdo = new PDO('sqlite:'.$dbLive);
    $pdo->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, v TEXT); INSERT INTO t (v) VALUES ("original");');

    $zip = sys_get_temp_dir().DIRECTORY_SEPARATOR.'momfb-export-'.uniqid('', true).'.zip';

    $packer = new MomFullBackupArchive($dbLive, $base.DIRECTORY_SEPARATOR.'pub', $base.DIRECTORY_SEPARATOR.'priv', $base);
    $packer->createZipAt($zip);

    $opened = tap(new \ZipArchive, fn (\ZipArchive $z) => $z->open($zip));
    expect($opened->locateName('project/node_modules/pkg/npm.txt'))->not->toBeFalse();
    $opened->close();

    File::put($base.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'note.txt', 'mutated');
    File::put($base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'marker.txt', 'mutated-app');
    $pdo = new PDO('sqlite:'.$dbLive);
    $pdo->exec('DELETE FROM t; INSERT INTO t (v) VALUES ("mutated");');

    $restore = new MomFullBackupArchive($dbLive, $base.DIRECTORY_SEPARATOR.'pub', $base.DIRECTORY_SEPARATOR.'priv', $base);
    $restore->restoreFromZipFile($zip, null);

    expect(File::get($base.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'note.txt'))->toBe('hello');
    expect(File::get($base.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'marker.txt'))->toBe('code');
    $pdo = new PDO('sqlite:'.$dbLive);
    expect($pdo->query('SELECT v FROM t')->fetchColumn())->toBe('original');

    File::deleteDirectory($base);
    if (File::exists($zip)) {
        File::delete($zip);
    }
});

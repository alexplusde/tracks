<?php

namespace Tracks;


use rex_addon;
use rex_file;
use rex_path;
use rex_sql;
use rex_string;
use DateTimeImmutable;
use rex_backup;

class Tracks
{

    private  const QUERY = '🦖.%';

    public static function getInstallOrUpdatePath(string $addon = 'tracks', string $installFolder = 'module')
    {

        if (rex_addon::get($addon)->getProperty('is_update')) {
            return rex_path::src('addons' . \DIRECTORY_SEPARATOR . '.new.' . $addon . \DIRECTORY_SEPARATOR . 'install' . \DIRECTORY_SEPARATOR . $installFolder . \DIRECTORY_SEPARATOR);
        }
        return rex_path::src('addons' . \DIRECTORY_SEPARATOR . $addon . \DIRECTORY_SEPARATOR . 'install' . \DIRECTORY_SEPARATOR . $installFolder . \DIRECTORY_SEPARATOR);
    }

    public static function updateModule($addonName = 'tracks')
    {
        $path = self::getInstallOrUpdatePath($addonName, 'module');
        $modules = preg_grep('~\.(json)$~', scandir($path));

        foreach ($modules as $module) {
            // Anstelle von .json ist die Endung .php für die Template-Datei
            $module_array = json_decode(rex_file::get($path . $module), 1);
            $module_array['input'] = rex_file::get($path . str_replace('.json', '.input.php', $module));
            $module_array['output'] = rex_file::get($path . str_replace('.json', '.output.php', $module));

            rex_sql::factory()->setDebug(0)->setTable('rex_module')
                ->setValue('name', $module_array['name'])
                ->setValue('key', $module_array['key'])
                ->setValue('input', $module_array['input'])
                ->setValue('output', $module_array['output'])
                ->setValue('createuser', 'tracks')
                ->setValue('updateuser', 'tracks')
                ->setValue('createdate', date('Y-m-d H:i:s'))
                ->setValue('updatedate', date('Y-m-d H:i:s'))
                ->insertOrUpdate();
        }
    }

    public static function writeModule($addonName = 'tracks', $query = '')
    {
        if(!$query) {
            $query = self::QUERY;
        }
        $modules = rex_sql::factory()->setDebug(0)->getArray('SELECT * FROM rex_module WHERE `key` LIKE :query', ['query' => $query]);

        foreach ($modules as $module) {
            rex_file::put(rex_path::addon($addonName, 'install/module/' . rex_string::normalize($module['key']) . '.input.php'), $module['input']);
            rex_file::put(rex_path::addon($addonName, 'install/module/' . rex_string::normalize($module['key']) . '.output.php'), $module['output']);
            unset($module['input']);
            unset($module['output']);
            rex_file::put(rex_path::addon($addonName, 'install/module/' . rex_string::normalize($module['key']) . '.json'), json_encode($module, JSON_PRETTY_PRINT));
        }
    }

    public static function updateTemplate($addonName = 'tracks')
    {
        $path = self::getInstallOrUpdatePath($addonName, 'template');

        $templates = preg_grep('~\.(json)$~', scandir($path));

        foreach ($templates as $template) {
            $template_array = json_decode(rex_file::get($path . $template), 1);
            // Anstelle von .json ist die Endung .php für die Template-Datei
            $template_array['content'] = rex_file::get($path . str_replace('.json', '.php', $template));
            rex_sql::factory()->setDebug(0)->setTable('rex_template')
                ->setValue('name', $template_array['name'])
                ->setValue('key', $template_array['key'])
                ->setValue('content', $template_array['content'])
                ->setValue('createuser', 'tracks')
                ->setValue('updateuser', 'tracks')
                ->setValue('createdate', date('Y-m-d H:i:s'))
                ->setValue('updatedate', date('Y-m-d H:i:s'))
                ->insertOrUpdate();
        }
    }

    public static function writeTemplate(string $addonName = 'tracks', string $query = '')
    {
        if(!$query) {
            $query = self::QUERY;
        }

        $templates = rex_sql::factory()->setDebug(0)->getArray('SELECT * FROM rex_template WHERE `key` LIKE :query', ['query' => $query]);

        foreach ($templates as $template) {
            rex_file::put(rex_path::addon($addonName, 'install/template/' . rex_string::normalize($template['key']) . '.php'), $template['content']);
            unset($template['content']);
            rex_file::put(rex_path::addon($addonName, 'install/template/' . rex_string::normalize($template['key']) . '.json'), json_encode($template, JSON_PRETTY_PRINT));
        }
    }

    
    public static function forceBackup($prefix = 'plus_bs5', $type = 'update', $filename = '', $tables = ['rex_module', 'rex_template'])
    {
        $dir = rex_backup::getDir() . '/';

        if (!$filename) {
            $now = new DateTimeImmutable();
            $filename = $now->format('Y') . '-' . $now->format('m') . '-' . $now->format('d') . '_' . $now->format('H') . '-' . $now->format('i') . '-' . $now->format('s');
        }
        $file = $prefix . '_' . $filename . '.' . $type . '.sql';

        $exportFilePath = $dir . $file;

        if (rex_backup::exportDb($exportFilePath, $tables)) {
            return true;
        }
        return false;
    }
    
    public static function packageExists(array $packages): bool
    {
        $packages = explode(', ', array_pop($packages));
        foreach ($packages as $package) {
            if (!rex_addon::get($package) || true !== rex_addon::get($package)->isAvailable()) {
                return false;
            }
        }
        return true;
    }

}
